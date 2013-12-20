require "fileutils"

class Wordpress < Thor
  PLUGIN_NAME = "bugsnag"
  PLUGIN_FILES = %W{bugsnag.php readme.txt views LICENSE.txt}
  BUILD_FILES = %W{build vendor composer.lock svn}
  VENDORED_BUGSNAG_PHP = "vendor/bugsnag/bugsnag/src/Bugsnag"
  VERSION_REGEX = /^\d+\.\d+\.\d+$/

  desc "build", "create a clean build of the plugin"
  def build(build_dir="build")
    # Prepare the build directory
    FileUtils.mkdir_p build_dir

    # Install dependencies
    puts "- Installing dependencies"
    `composer install`

    # Copy plugin files to the build directory
    puts "- Copying plugin files"
    FileUtils.cp_r PLUGIN_FILES, build_dir

    # Copy vendored bugsnag to the build directory
    puts "- Copying vendored bugsnag-php"
    FileUtils.cp_r VENDORED_BUGSNAG_PHP, "#{build_dir}/bugsnag-php"
  end

  desc "update_version <version>", "update the plugin to the given version"
  def update_version(version)
    return $stderr.puts "Invalid version number #{version}" unless version =~ VERSION_REGEX

    replace_in_file("readme.txt", /Stable tag: 1.0.0/, "Stable tag: #{version}")
    replace_in_file("bugsnag.php", /Version: 1.0.0/, "Version: #{version}")
  end

  desc "release_svn <version> <wordpress-username>", "perform a release to svn"
  def release_svn(version, username)
    # Checkout a fresh copy of the svn repo
    checkout_svn(username)

    # Build a release copy into svn/trunk
    build "svn/trunk"

    # Move into the svn repo
    Dir.chdir "svn" do
      # Commit changes to svn
      `svn add trunk/*`
      `svn ci -m "Release version #{version}"`

      # Tag in svn
      `svn cp trunk tags/#{version}`
      `svn ci -m "Tagging version #{version}"`
    end

    # Remove temporary files
    FileUtils.rm_rf "svn"
  end

  desc "release_git <version>", "perform a release to git"
  def release_git(version)
    # Commit version changes
    `git add readme.txt bugsnag.php`
    `git ci -m "Release version #{version}"`

    # Tag release
    `git tag v#{version}`

    # Push to git
    `git push origin master && git push --tags`
  end

  desc "release <version> <wordpress-username>", "perform a release to git and svn"
  def release(version, wordpress_username)
    # Update version number
    update_version(version)

    # Release and tag in git
    release_git(version)

    # Release a new version via svn to wordpress.org/plugins
    release_svn(version, wordpress_username)
  end

  desc "zip <zip-name>", "create a zip of the plugin"
  def zip(zip_name="bugsnag-wordpress.zip", build_dir="build")
    # Build a clean plugin
    build File.join(build_dir, PLUGIN_NAME)

    # Zip up the build
    puts "- Generating #{zip_name}"
    Dir.chdir build_dir do
      `zip -r #{zip_name} #{PLUGIN_NAME}`
      FileUtils.cp zip_name, "../"
    end

    # Remove temporary files
    FileUtils.rm_rf build_dir
  end

  desc "clean", "clean up any build files"
  def clean()
    FileUtils.rm_rf BUILD_FILES
  end

  desc "checkout_svn <wordpress-username>", "checkout a copy of the svn repo"
  def checkout_svn(username)
    `svn co http://plugins.svn.wordpress.org/bugsnag svn --username #{username}`
  end

  private
  def replace_in_file(filename, find, replace)
    str = File.read(filename)
    File.open(filename, 'w') {|f| f.write(str.gsub(find, replace)) }
  end
end