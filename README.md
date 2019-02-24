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
 
## Contributing

Contributions are encouraged and welcome; I am always happy to get feedback or pull requests on Github :) Create [Github Issues](https://github.com/stelgenhof/opensubtitles/issues) for bugs and new features and comment on the ones you are interested in.

If you enjoy what I am making, an extra cup of coffee is very much appreciated :). Your support helps me to put more time into Open-Source Software projects like this.

<a href="https://www.buymeacoffee.com/sachatelgenhof" target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: auto !important;width: auto !important;" ></a> 
 
 
Disclaimer
----------
Please feel free to use, modify, hack this script as you like. It is not perfect, but does the trick for now :).
