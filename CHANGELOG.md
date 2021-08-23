# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres
to [Semantic Versioning](http://semver.org).

## [Unreleased]

### Added

- Argument for an optional IMDB Movie number. If not given, a prompt will be shown.
- Argument to display usage/help instructions

### Changed

- Renamed main script to `opensubtitles` (extension is not necessary as it is an executable).
- Upgraded Guzzle to v7.
- Simplified fXmlRpc client use by using its default HTTP transport.
- Replaced Illuminate Cache by Symfony Cache.
- Extracted code into separate functions to make it the script more clean.
- Renamed environment variables to make them easier to read.

### Fixed

### Removed

- API URL from the environment file as this is merely a static value.
- Replaced Illuminate Cache by Symfony Cache.
- Unnecessary comments.

## [v0.3] 2020-07-27

### Changed

- Bumped required PHP version (as 7.1 and 7.2 - soon - are out of support).
- Replaced `getenv` function call with direct access to environment variables (faster)
- Updated dependencies.
- Code cleanup.

## [v0.2] 2019-02-24

### Changed

- Upgraded to PHP 7. PHP 5 is not supported any longer.
- Next to a OpenSubtitles account, now also a User Agent is required (see the configuration).
- Updated dependencies.

## [v0.1] 2019-02-24

- Initial release

[Unreleased]: https://github.com/stelgenhof/opensubtitles/compare/0.3...HEAD

[v0.3]: https://github.com/stelgenhof/opensubtitles/compare/0.2...0.3

[v0.2]: https://github.com/stelgenhof/opensubtitles/compare/0.1...0.2

[v0.1]: https://github.com/stelgenhof/opensubtitles/releases/tag/0.1
