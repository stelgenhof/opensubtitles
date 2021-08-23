#!/usr/bin/env php
<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use fXmlRpc\Client as fXmlRpcClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use League\CLImate\CLImate;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

const APP_NAME = 'OpenSubtitles Downloader';
const APP_VERSION = '1.0';
$app_name = APP_NAME.' v'.APP_VERSION;
const SUBTITLES_PATH = __DIR__.DIRECTORY_SEPARATOR.'subtitles';

$cli = new CLImate();

$cli->description($app_name);
$cli->lightGreen($app_name);
$cli->lightGreen()->border('-*-', strlen($app_name));
$cli->clearLine();

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (InvalidPathException $e) {
    $cli->error(sprintf('ERROR: %s', $e->getMessage()));
    exit();
}

$input = $cli->input('Please enter an IMDB Movie Number:');
$imdbID = $input->prompt();

$httpClient = new GuzzleClient();

try {
    $response = (new FilesystemAdapter())->get(md5($_ENV['OPENSUBTITLES_LANGUAGES'].$imdbID), function (ItemInterface $item) use ($imdbID): array {
        $item->expiresAfter(3600);

        $client = new fXmlRpcClient($_ENV['OPENSUBTITLES_API_URL']);

        $response = $client->call('LogIn', [
            $_ENV['OPENSUBTITLES_USERNAME'],
            $_ENV['OPENSUBTITLES_PASSWORD'],
            'en',
            $_ENV['OPENSUBTITLES_USER_AGENT'],
        ]);

        if ('200 OK' === $response['status'] && !empty($response['token'])) {
            $response = $client->call(
                'SearchSubtitles',
                [$response['token'], [['sublanguageid' => $_ENV['OPENSUBTITLES_LANGUAGES'], 'imdbid' => $imdbID]]]
            );
        } else {
            throw new RuntimeException(sprintf('Unable to retrieve the subtitles (%s).', $response['status']));
        }

        return $response;
    });

    $subtitleCount = count($response['data']);

    if (0 === $subtitleCount) {
        $languages = implode(', ', array_map(static function ($a) {
            return locale_get_display_language($a, 'en');
        }, explode(',', $_ENV['OPENSUBTITLES_LANGUAGES'])));

        $cli->error(sprintf(
            'No %s subtitles found for IMDB ID %s. Please make sure to provide a valid IMDB ID.',
            $languages,
            $imdbID
        ));
    }

    $cli->br()->whisper(sprintf('%d %s found:', $subtitleCount, ($subtitleCount > 1 ? 'subtitles' : 'subtitle')));

    foreach ($response['data'] as $i => $hit) {
        $cli->whisper(sprintf(
            '%d: %s (%s) - %s - [%s] ',
            $i + 1,
            $hit['MovieName'],
            $hit['MovieYear'],
            $hit['IDSubtitleFile'],
            $hit['SubFileName']
        ));

        downloadSubtitle($hit, $httpClient);
    }
} catch (Exception | \Psr\Cache\InvalidArgumentException $e) {
    $cli->error(sprintf('ERROR: %s', $e->getMessage()));
}

$cli->br()->info('Completed.');

/**
 * Deflates a GZipped file.
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
        throw new RuntimeException('unable to open destination file for writing');
    }

    while (!gzeof($sfp)) {
        $data = gzread($sfp, 4096);

        if (!$data) {
            throw new RuntimeException('unable to read source file');
        }

        fwrite($fp, $data, strlen($data));
    }

    gzclose($sfp);
    fclose($fp);
}

/**
 * Returns the directory path of the selected movie.
 *
 * It will create the directory if not exists.
 *
 * @param array<string> $hit search result (subtitle entry)
 */
function movieDirectory(array $hit): string
{
    $dir = SUBTITLES_PATH.DIRECTORY_SEPARATOR.$hit['MovieName'].' - '.$hit['MovieYear'];

    if (is_dir($dir)) {
        return $dir;
    }

    if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
    }

    return $dir;
}

/**
 * Downloads the specified subtitle (if not yet downloaded).
 *
 * @param array<string> $hit subtitle entry
 */
function downloadSubtitle(array $hit, ClientInterface $client): void
{
    $movieDir = movieDirectory($hit);
    $subtitleFile = $movieDir.'/'.basename($hit['SubDownloadLink']);

    if (!is_readable($subtitleFile)) {
        try {
            $client->request(
                'GET',
                $hit['SubDownloadLink'],
                ['sink' => $subtitleFile]
            );
        } catch (Exception | GuzzleException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    try {
        $srtFile = $movieDir.DIRECTORY_SEPARATOR.basename($subtitleFile, '.gz').'.srt';

        deflate($subtitleFile, $srtFile);
        transcode($srtFile, $hit['SubEncoding']);
    } catch (Throwable $e) {
        throw new RuntimeException($e->getMessage());
    }
}

/**
 * (Optionally) transcode the subtitle file.
 */
function transcode(string $srtFile, string $encoding): void
{
    if ($_ENV['OPENSUBTITLES_TARGET_ENCODING'] === $encoding) {
        return;
    }

    $srtContents = file_get_contents($srtFile);

    if (!$srtContents) {
        throw new RuntimeException('unable to open subtitle file for reading');
    }

    $srtContents = iconv($encoding, $_ENV['OPENSUBTITLES_TARGET_ENCODING'], $srtContents);

    if (!$srtContents) {
        throw new RuntimeException('unable to transcode the subtitle file');
    }

    file_put_contents($srtFile, $srtContents);
}
