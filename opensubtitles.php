#!/usr/bin/env php
<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use fXmlRpc\Client as fXmlRpcClient;
use fXmlRpc\Parser\NativeParser;
use fXmlRpc\Serializer\NativeSerializer;
use fXmlRpc\Transport\HttpAdapterTransport;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Http\Adapter\Guzzle6\Client as AdapterGuzzle6Client;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use League\CLImate\CLImate;

const APP_NAME = 'OpenSubtitles Downloader';
const APP_VERSION = '0.3';
$app_name = APP_NAME.' v'.APP_VERSION;

// Start CLI
$cli = new CLImate();
$cli->clear();

$cli->description($app_name);
$cli->lightGreen($app_name);
$cli->lightGreen()->border('-*-', strlen($app_name));
$cli->clearLine();

// Load the configuration
try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (InvalidPathException $e) {
    $cli->error(sprintf('ERROR: %s', $e->getMessage()));
    exit();
}

// Initialize the filesystem/cache
$container = new Container();
$container['config'] = [
    'cache.default' => 'file',
    'cache.stores.file' => [
        'driver' => 'file',
        'path' => __DIR__.'/_cache',
    ],
];

$container['files'] = new Filesystem();
$cacheManager = new CacheManager($container);
$cache = $cacheManager->store();

// Initialize the HTTP and XML-RPC Clients
$httpClient = new GuzzleClient();
$client = new fXmlRpcClient(
    $_ENV['OPENSUBTITLES_API_URL'],
    new HttpAdapterTransport(new GuzzleMessageFactory(), new AdapterGuzzle6Client($httpClient)),
    new NativeParser(),
    new NativeSerializer()
);

$input = $cli->input('Please enter the IMDB Movie Number:');
$imdbID = $input->prompt();

try {
    $cache_key = md5($_ENV['OPENSUBTITLES_LANGUAGES'].$imdbID);

    $response = $cache->get($cache_key);

    // Get data from OpenSubtitles if not cached
    if (null === $response) {
        // Login to OpenSubtitles
        $response = $client->call('LogIn', [
            $_ENV['OPENSUBTITLES_USERNAME'],
            $_ENV['OPENSUBTITLES_PASSWORD'],
            'en',
            $_ENV['OPENSUBTITLES_USER_AGENT'],
        ]);

        // Proceed if ok and token is provided
        if ('200 OK' === $response['status'] && !empty($response['token'])) {
            $response = $client->call(
                'SearchSubtitles',
                [$response['token'], [['sublanguageid' => $_ENV['OPENSUBTITLES_LANGUAGES'], 'imdbid' => $imdbID]]]
            );

            $cache->put($cache_key, $response, 60);
        } else {
            $cli->error('Unable to retrieve the subtitles ('.$response['status'].').');
            exit();
        }
    }

    // Process retrieved data
    if (count($response['data'])) {
        foreach ($response['data'] as $i => $hit) {
            $cli->comment(sprintf(
                '%d: %s (%s) - %s - [%s] ',
                $i + 1,
                $hit['MovieName'],
                $hit['MovieYear'],
                $hit['IDSubtitleFile'],
                $hit['SubFileName']
            ));

            $movieDir = $hit['MovieName'].' - '.$hit['MovieYear'];
            if (!$container['files']->isDirectory($movieDir)) {
                $container['files']->makeDirectory($movieDir);
            }

            // Download subtitle file if not yet downloaded
            $subtitleFile = $movieDir.'/'.basename($hit['SubDownloadLink']);
            if (!is_readable($subtitleFile)) {
                try {
                    $httpClient->request(
                        'GET',
                        $hit['SubDownloadLink'],
                        ['sink' => $movieDir.'/'.basename($hit['SubDownloadLink'])]
                    );
                } catch (Exception | GuzzleException $e) {
                    $cli->error($e->getMessage());
                    exit();
                }
            }

            $srtFile = $movieDir.'/'.basename($hit['SubDownloadLink'], '.gz').'.srt';
            deflate($subtitleFile, $srtFile);

            $srtContents = $container['files']->get($srtFile);

            if ($_ENV['OPENSUBTITLES_TARGET_ENCODING'] !== $hit['SubEncoding']) {
                $srtContents = iconv($hit['SubEncoding'], $_ENV['OPENSUBTITLES_TARGET_ENCODING'], $srtContents);
            }
            $container['files']->put(
                $movieDir.'/'.$hit['IDSubtitleFile'].'-'.$hit['MovieName'].'.'.$hit['LanguageName'].'.srt',
                $srtContents
            );

            // Clean up
            $container['files']->delete($srtFile);
        }
    } else {
        $languages = implode(', ', array_map(static function ($a) {
            return locale_get_display_language($a, 'en');
        }, explode(',', $_ENV['OPENSUBTITLES_LANGUAGES'])));

        $cli->error(sprintf(
            'No %s subtitles found for IMDB ID %s. Please make sure to provide valid IMDB ID.',
            $languages,
            $imdbID
        ));
    }
} catch (Exception | \Psr\SimpleCache\InvalidArgumentException $e) {
    $cli->error(sprintf('ERROR: %s', $e->getMessage()));
}

$cli->br()->info('Completed.');

/**
 * Inflates a GZipped file.
 *
 * @param string $srcName the filepath of the original GZipped file
 * @param string $dstName the filepath of the uncompressed file (destination)
 */
function deflate(string $srcName, string $dstName): void
{
    $sfp = gzopen($srcName, 'rb');
    if (!$sfp) {
        throw new RuntimeException('unable to open source file for reading');
    }

    $fp = fopen($dstName, 'wb');
    if (!$fp) {
        throw new RuntimeException('unable to open destination file for swriting');
    }

    while (!gzeof($sfp)) {
        $string = gzread($sfp, 4096);
        fwrite($fp, $string, strlen($string));
    }
    gzclose($sfp);
    fclose($fp);
}
