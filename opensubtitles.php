#!/usr/bin/php
<?php
require 'vendor/autoload.php';

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;

const APP_NAME = 'OpenSubtitles Downloader';
const APP_VERSION = '0.1';
$appname = APP_NAME . ' v' . APP_VERSION;

date_default_timezone_set('Asia/Tokyo');

// Start CLI
$climate = new League\CLImate\CLImate;
$climate->clear();

$climate->description($appname);
$climate->lightGreen($appname);
$climate->lightGreen()->border('-*-', strlen($appname));

$input = $climate->input('Please enter the IMDB Movie Number:');
$imdbID = $input->prompt();

// Load the configuration
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Initialize the filesystem/cache
$container = new Container;
$container['config'] = [
    'cache.default' => 'file',
    'cache.stores.file' => [
        'driver' => 'file',
        'path' => __DIR__ . '/_cache'
    ]
];

$container['files'] = new Filesystem;
$cacheManager = new CacheManager($container);
$cache = $cacheManager->store();

// Initialize the HTTP and XMLRPC Clients
$httpClient = new GuzzleHttp\Client();
$client = new fXmlRpc\Client(
    getenv('OPENSUBTITLES_API_URL'),
    new fXmlRpc\Transport\HttpAdapterTransport(
        new \Http\Message\MessageFactory\GuzzleMessageFactory(),
        new \Http\Adapter\Guzzle6\Client($httpClient)
    ),
    new fXmlRpc\Parser\NativeParser(),
    new fXmlRpc\Serializer\NativeSerializer()
);

try {
    $cache_key = md5(getenv('OPENSUBTITLES_LANGUAGES') . $imdbID);

    $response = $cache->get($cache_key);

    // Get data from OpenSubtitles if not cached
    if (is_null($response)) {

        // Login to OpenSubtitles
        $response = $client->call('LogIn', [getenv('OPENSUBTITLES_USERNAME'), getenv('OPENSUBTITLES_PASSWORD'), 'en', 'OSTestUserAgent']);

        // Proceed if ok and token is provided
        if ($response['status'] == '200 OK' && !empty($response['token'])) {
            $response = $client->call('SearchSubtitles', [$response['token'], [['sublanguageid' => getenv('OPENSUBTITLES_LANGUAGES'), 'imdbid' => $imdbID]]]);

            $cache->put($cache_key, $response, 60);
            $em = $response;
        } else {
            $climate->error('Something wrong.');
        }
    }

    // Process retrieved data
    if (sizeof($response['data'])) {
        foreach ($response['data'] as $i => $hit) {
            $climate->comment(sprintf('%d: %s (%s) - [%s] ', $i + 1, $hit['MovieName'], $hit['MovieYear'], $hit['SubFileName']));

            $movieDir = $hit['MovieName'] . ' - ' . $hit['MovieYear'];
            if (!$container['files']->isDirectory($movieDir)) {
                $container['files']->makeDirectory($movieDir);
            }

            // Download subtitle file if not yet downloaded
            $subtitleFile = $movieDir . '/' . basename($hit['SubDownloadLink']);
            if (!is_readable($subtitleFile)) {
                $httpClient->request('GET', $hit['SubDownloadLink'], ['sink' => $movieDir . '/' . basename($hit['SubDownloadLink'])]);
            }

            $srtFile = $movieDir . '/' . basename($hit['SubDownloadLink'], '.gz') . '.srt';
            uncompress($subtitleFile, $srtFile);

            $srtContents = $container['files']->get($srtFile);

            if (getenv('OPENSUBTITLES_TARGET_ENCODING') !== $hit['SubEncoding']) {
                $srtContents = iconv($hit['SubEncoding'], getenv('OPENSUBTITLES_TARGET_ENCODING'), $srtContents);
            }
            $container['files']->put($movieDir . '/' . $hit['IDSubtitleFile'] . '-' . $hit['MovieName'] . '.' . $hit['LanguageName'] . '.srt', $srtContents);

            // Clean up
            $container['files']->delete($srtFile);
        }
    } else {
        $climate->error(sprintf('No subtitles found for IMDB ID %s. Please make sure to provide valid IMDB ID.', $imdbID));
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

$climate->br()->info('Completed.');

/**
 * Uncompresses a GZipped file
 *
 * @param string $srcName the filepath of the original GZipped file
 * @param string $dstName the filepath of the uncompressed file (destination)
 */
function uncompress($srcName, $dstName)
{
    $sfp = gzopen($srcName, 'rb');
    $fp = fopen($dstName, 'w');

    while (!gzeof($sfp)) {
        $string = gzread($sfp, 4096);
        fwrite($fp, $string, strlen($string));
    }
    gzclose($sfp);
    fclose($fp);
}
