class Wordpress < Thor
  PLUGIN_FILES = %W{bugsnag.php readme.txt views LICENSE.txt}
  BUILD_FILES = %W{build vendor composer.lock svn}
  VENDORED_BUGSNAG_PHP = "vendor/bugsnag/bugsnag/src/Bugsnag"
  VERSION_REGEX = /^\d+\.\d+\.\d+$/

  desc "build", "create a clean build of the plugin"
  def build(build_dir="build")
    # Prepare the build directory
    `mkdir -p #{build_dir}`

    # Install dependencies
    puts "- Installing dependencies"
    `composer install`

    # Copy plugin files to the build directory
    puts "- Copying plugin files"
    `cp -r #{PLUGIN_FILES.join(" ")} #{build_dir}`

    # Copy vendored bugsnag to the build directory
    puts "- Copying vendored bugsnag-php"
    `cp -r #{VENDORED_BUGSNAG_PHP} #{build_dir}/bugsnag-php`
  end

  desc "update_version VERSION", "update the plugin to the given version"
  def update_version(version)
    return $stderr.puts "Invalid version number #{version}" unless version =~ VERSION_REGEX

    replace_in_file("readme.txt", /Stable tag: 1.0.0/, "Stable tag: #{version}")
    replace_in_file("bugsnag.php", /Version: 1.0.0/, "Version: #{version}")
  end

  desc "release_svn VERSION", "perform a release to svn"
  def release_svn(version)
    # Checkout a fresh copy of the svn repo
    checkout_svn

    # Build a release copy into svn/trunk
    build("svn/trunk")

    # Move into the svn repo
    `cd svn`

    # Commit changes to svn
    `svn add trunk/*`
    `svn ci -m "Release version #{version}"`

    # Tag in svn
    `svn cp trunk tags/#{version}`
    `svn ci -m "Tagging version #{version}"`
  end

  desc "release_git VERSION", "perform a release to git"
  def release_git(version)
    `git add readme.txt bugsnag.php`
    `git ci -m "Release version #{version}"`
    `git tag v#{version}`
    `git push origin master && git push --tags`
  end

  desc "release VERSION", "perform a release to git and svn"
  def release(version)
    # Update version number
    update_version(version)

    # Release and tag in git
    release_git(version)

    # Release a new version via svn to wordpress.org/plugins
    release_svn(version)
  end

  desc "clean", "clean up any build files"
  def clean()
    `rm -rf #{BUILD_FILES.join(" ")}`
  end

  desc "checkout_svn", "checkout a copy of the svn repo"
  def checkout_svn
    `svn co http://plugins.svn.wordpress.org/bugsnag svn --username loopj`
  end

  private
  def replace_in_file(filename, find, replace)
    str = File.read(filename)
    File.open(filename, 'w') {|f| f.write(str.gsub(find, replace)) }
  end
end