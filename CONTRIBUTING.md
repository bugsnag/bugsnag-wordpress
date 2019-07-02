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

1. Update the version number and changelog in `readme.txt` and `bugsnag.php`
2. Commit, tag, and push the new version as `vX.X.X`
3. [Build the release from source](#building-from-source)
4. Attach the release zip to the GitHub releases page for the new tag
