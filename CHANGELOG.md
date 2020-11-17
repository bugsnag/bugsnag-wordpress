Changelog
=========

## v1.6.0 (2020-11-17)

### Enhancements

* Use `wp_get_environment_type` to set the release stage, when it's available [bhubbard](https://github.com/bhubbard) [#53](https://github.com/bugsnag/bugsnag-wordpress/pull/53)
* Update bundled bugsnag-php version to v2.10.1

## 1.5.0 (2020-04-29)

### Enhancements

* Clarify the "Test Bugsnag" form action [#46](https://github.com/bugsnag/bugsnag-wordpress/pull/46)
* Improve error message when methods are called with no api key set [#47](https://github.com/bugsnag/bugsnag-wordpress/pull/47)
* Add a constant to control setting error handlers [#45](https://github.com/bugsnag/bugsnag-wordpress/pull/45)

## 1.4.0 (2019-07-02)

### Enhancements

* Add WordPress version string to report payloads (device.runtimeVersions) [#39](https://github.com/bugsnag/bugsnag-wordpress/pull/39)

### Fixes

* Added ability to define API key as constant [chrisatomix](https://github.com/chrisatomix) [#18](https://github.com/bugsnag/bugsnag-wordpress/issues/18) [#40](https://github.com/bugsnag/bugsnag-wordpress/pull/40)
* Removed form action to prevent deprecation warnings [fiskhandlarn](https://github.com/fiskhandlarn) [#36](https://github.com/bugsnag/bugsnag-wordpress/issues/36) [#40](https://github.com/bugsnag/bugsnag-wordpress/pull/40)
* Fixed broken links on settings page bugsnag.com [#40](https://github.com/bugsnag/bugsnag-wordpress/pull/40)
* Made bugsnagWordpress global to make available for CLI scripts [#23](https://github.com/bugsnag/bugsnag-wordpress/issues/23) [#40](https://github.com/bugsnag/bugsnag-wordpress/pull/40)
