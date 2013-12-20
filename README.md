Bugsnag Notifier for WordPress
==============================

The Bugsnag Notifier for WordPress gives you instant notification of errors 
in your WordPress sites.

[Bugsnag](https://bugsnag.com) captures errors in real-time from your web, 
mobile and desktop applications, helping you to understand and resolve them 
as fast as possible. [Create a free account](https://bugsnag.com) to start 
capturing errors from your applications.

The Bugsnag Notifier for WordPress supports WordPress 2.0+, PHP 5.2+ and
requires the cURL extension to be available in PHP.

You can always read about the plugin or download it from the
[WordPress Plugin Directory](http://wordpress.org/plugins/bugsnag/).


How to Install
--------------

### Automatic Installation (Recommended)

1.  Open the *Add New* page inside the *Plugins* section of your WordPress
    admin dashboard.

2.  Search for *bugsnag* and click *Install Now*.

3.  Click *Activate Plugin* to activate the plugin.

4.  Click *Configure* and enter your Bugsnag API Key from your
    [Bugsnag Dashboard](https://bugsnag.com).


### Manual Installation

1.  Download the latest [bugsnag.zip](http://downloads.wordpress.org/plugin/bugsnag.zip).

2.  Unzip `bugsnag.zip`.

3.  Upload the `bugsnag` folder using ftp or scp to the `wp-content/plugins`
    folder of your WordPress site.

4.  Click *Activate* on the *Bugsnag* plugin inside the *Plugins* section of
    your WordPress admin dashboard.

5.  Click *Configure* and enter your Bugsnag API Key from your
    [Bugsnag Dashboard](https://bugsnag.com).


Building from Source
--------------------

If you would like to build a new zip file of the plugin from source, you can
run our build script using [Thor](http://whatisthor.com/). You'll need both
`ruby` and the `thor` gem installed (`gem install thor`). Then you can
generate a new zip by running:

```shell
thor wordpress:zip bugsnag-wordpress.zip
```


Reporting Bugs or Feature Requests
----------------------------------

Please report any bugs or feature requests on the github issues page for this
project here:

<https://github.com/bugsnag/bugsnag-wordpress/issues>


Contributing
------------

-   [Fork](https://help.github.com/articles/fork-a-repo) the [notifier on github](https://github.com/bugsnag/bugsnag-wordpress)
-   Commit and push until you are happy with your contribution
-   [Make a pull request](https://help.github.com/articles/using-pull-requests)
-   Thanks!


License
-------

The Bugsnag WordPress notifier is free software released under the WordPress-friendly GPLv2 License. 
See [LICENSE.txt](https://github.com/bugsnag/bugsnag-wordpress/blob/master/LICENSE.txt) for details.