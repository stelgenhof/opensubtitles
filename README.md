OpenSubtitles Downloader
==========

OpenSubtitles Downloader is a simple console application to download subtitles from Opensubtitles.org. Just provide the IMDB Movie ID and it will download 
all subtitles for the specified languages.

System Requirements
-------------------

You need **PHP >= 5.5.0** to use OpenSubtitles Downloader but the latest stable version of PHP is recommended.
In addition, the following PHP extensions are needed:

  - mbstring
  - intl
  - simplexml


Installation
------------

Install OpenSubtitles Downloader by cloning this repository:

```
$ git clone https://github.com/stelgenhof/opensubtitles.git
```

Basic Usage
-----------

```
$ ./opensubtitles.php
```

Then just enter the IMDB Movie ID. The downloaded subtitles will be saved in a folder by the movie name. For subtitles files that are not
 encoded in UTF-8, these will be automatically converted to UTF-8 (if possible).
 
Disclaimer
----------
Please feel free to use, modify, hack this script as you like. It is not perfect, but does the trick for now :).