# Contributing

* [Fork](https://help.github.com/articles/fork-a-repo) the
  [library on GitHub](https://github.com/bugsnag/bugsnag-wordpress)
* Commit and push until you are happy with your contribution
* [Make a pull request](https://help.github.com/articles/using-pull-requests)
* Thanks!


## Building from source

If you would like to build a new zip file of the plugin from source, you can
run our build script using [Thor](http://whatisthor.com/). You'll need both
`ruby` and the `thor` gem installed (`gem install thor`). Then you can
generate a new zip by running:

```shell
thor wordpress:zip bugsnag-wordpress.zip
```

## Releasing

In order to make a release your WordPress account must have committer access on the [Bugsnag WordPress plugin](https://wordpress.org/plugins/bugsnag), otherwise the SVN release will fail.

1. Update the changelog in `readme.txt`
1. Run the [Thor](http://whatisthor.com/) `release` command to update version numbers, create a Git tag, update SVN and create an SVN tag:
    ```
    $ thor wordpress:release <version> <wordpress-username>
    ```

    Where `<version>` is the version number you're updating to with no leading 'v' (e.g. use "1.6.0" rather than "v1.6.0") and `<wordpress-username>` is your [WordPress.org](https://wordpress.org) account username
1. [Build the release from source](#building-from-source)
1. Attach the release zip to the GitHub releases page for the new tag
