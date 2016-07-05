OpenSubtitles
==========

OpenSubtitles is a simple console application to download subtitles from Opensubtitles.org. Just provide the IMDB Movie ID and it will download 
all subtitles for the specified languages.

System Requirements
-------------------

You need **PHP >= 5.5.0** to use `sachatelgenhofoudekoehorst/opensubtitles` but the latest stable version of PHP is recommended.


Installation
------------

Install `sachatelgenhofoudekoehorst/opensubtitles` using Composer.

```
$ composer require sachatelgenhofoudekoehorst/opensubtitles
```

Basic Usage
-----------

```
$ ./opensubtitles.php
```

Then just enter the IMDB Movie ID. The downloanded subtitles will be saved in a folder by the moviename. For subtitles files that are not
 encoded in UTF-8, these will be automatically converted to UTF-8 (if possible).
 
Disclaimer
----------
Please feel free to use, modify, hack this script as you like. It is not perfect, but does the trick for now :).