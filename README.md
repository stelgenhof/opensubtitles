OpenSubtitles Downloader
==========

OpenSubtitles Downloader is a simple console application to download subtitles from [Opensubtitles.org]. Just provide
the IMDB Movie ID, and it will download all subtitles for the specified languages.

System Requirements
-------------------

You need **PHP >= 7.3** to use OpenSubtitles Downloader but the latest stable version of PHP is recommended. In
addition, the following PHP extensions are needed:

- mbstring
- intl
- simplexml
- iconv
- xmlrpc
- zlib

Also, you need an OpenSubtitles developer account. Please follow the instructions
here: [OpenSubtitles Developer Information](https://trac.opensubtitles.org/projects/opensubtitles/wiki/DevReadFirst).

Installation
------------

Install OpenSubtitles Downloader by cloning this repository:

```
$ git clone https://gitlab.com/stelgenhof/opensubtitles.git
```

Configuration
------------

For the OpenSubtitles Downloader to run correctly, the configuration variables in the `.env` need to be populated with
the correct values:

- **OPEN_SUBTITLES_USER_AGENT** The user agent string provided by OpenSubtitles. A temporary test user agent can be
  used, but it is strongly recommended applying for one.
- **OPEN_SUBTITLES_USERNAME** Your OpenSubtitles username.
- **OPEN_SUBTITLES_PASSWORD** Your OpenSubtitles password.
- **OPEN_SUBTITLES_TARGET_ENCODING** The targeted encoding. OpenSubtitles Downloader will transcode the subtitles if
  they contain foreign characters. Usually `UTF-8` as a value should work fine.
- **OPEN_SUBTITLES_LANGUAGES** A (comma delimited) list of language codes for the preferred translations.

Please check the [OpenSubtitles.org] webpage for more information on how to obtain a user account and a application user
agent.

Basic Usage
-----------

```
$ ./opensubtitles
```

Then just enter the IMDB Movie ID. The downloaded subtitles will be saved in a folder by the movie name. For subtitles
files that are not encoded in UTF-8, these will be automatically converted to UTF-8 (if possible).

## Contributing

Contributions are encouraged and welcome; I am always happy to get feedback or pull requests on GitLab :)
Create [Issues](https://gitlab.com/stelgenhof/opensubtitles/-/issues) for bugs and new features and comment on the ones
you are interested in.

If you enjoy what I am making, an extra cup of coffee is very much appreciated :). Your support helps me to put more
time into Open-Source Software projects like this.

<a href="https://www.buymeacoffee.com/sachatelgenhof" target="_blank"><img alt="Buy Me A Coffee" src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" title="Buy Me A Coffee"/></a>


Disclaimer
----------
Please feel free to use, modify, hack this script as you like. It is not perfect, but does the trick for now :). Consult
the [LICENSE](LICENSE) file that comes with this program for more details regarding its license.

[OpenSubtitles.org]: https://www.opensubtitles.org