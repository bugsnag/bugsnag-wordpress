<div align="center">
  <a href="https://www.bugsnag.com/platforms/php/wordpress/">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://assets.smartbear.com/m/3dab7e6cf880aa2b/original/BugSnag-Repository-Header-Dark.svg">
      <img alt="SmartBear BugSnag logo" src="https://assets.smartbear.com/m/3945e02cdc983893/original/BugSnag-Repository-Header-Light.svg">
    </picture>
  </a>
  <h1>Error monitoring plugin for WordPress</h1>
</div>

[![Documentation](https://img.shields.io/badge/documentation-latest-blue.svg)](https://docs.bugsnag.com/platforms/php/wordpress/)

The BugSnag Error Monitoring plugin for WordPress gives you instant notification of errors in your WordPress sites.

[BugSnag](https://www.bugsnag.com/) captures errors in real-time from your web, mobile and desktop applications, helping you to understand and resolve them as fast as possible. Create a free account to start capturing errors from your applications. Learn more about [error monitoring and error reporting](https://www.bugsnag.com/) with BugSnag.

This plugin supports WordPress 2.0+, PHP 5.2+ and requires the cURL extension to be available in PHP.

You can read about the plugin or download it from the [WordPress Plugin Directory](http://wordpress.org/plugins/bugsnag/).


## How to Install

1.  Download the latest [bugsnag.zip](https://github.com/bugsnag/bugsnag-wordpress/releases/latest).

2.  Unzip `bugsnag.zip`.

3.  Upload the `bugsnag` folder using ftp or scp to the `wp-content/plugins`
    folder of your WordPress site.

4.  Click *Activate* on the *Bugsnag* plugin inside the *Plugins* section of
    your WordPress admin dashboard.

5.  Click *Configure* and enter your Bugsnag API Key from your
    [Bugsnag Dashboard](https://bugsnag.com).


## Building from Source

If you would like to build a new zip file of the plugin from source, you can run our build script using [Thor](http://whatisthor.com/). You'll need both `ruby` and the `thor` gem installed (`gem install thor`). Then you can generate a new zip by running:

```shell
thor wordpress:zip bugsnag-wordpress.zip
```

## Support

* [Read the integration guide](https://docs.bugsnag.com/platforms/php/wordpress)
* [Search open and closed issues](https://github.com/bugsnag/bugsnag-wordpress/issues?utf8=✓&q=is%3Aissue) for similar problems
* [Report a bug or request a feature](https://github.com/bugsnag/bugsnag-wordpress/issues/new)

## Contributing

-   [Fork](https://help.github.com/articles/fork-a-repo) the [notifier on github](https://github.com/bugsnag/bugsnag-wordpress)
-   Commit and push until you are happy with your contribution
-   [Make a pull request](https://help.github.com/articles/using-pull-requests)
-   Thanks!

## License

The BugSnag WordPress plugin is free software released under the WordPress-friendly GPLv2 License. See [LICENSE.txt](https://github.com/bugsnag/bugsnag-wordpress/blob/master/LICENSE.txt) for details.
