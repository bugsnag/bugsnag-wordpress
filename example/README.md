# Bugsnag WordPress demo

This docker application provides a worked example of how to install Bugsnag into a wordpress installation.

Try this out with [your own Bugsnag account](https://app.bugsnag.com/user/new)!

## Starting the WordPress app

Create a `.env` file in the root of this directory.  This file should specify two passwords:
```
MYSQL_PASSWORD="your-password-here"
MYSQL_ROOT_PASSWORD="your-root-password-here"
```

These are used to setup the WordPress database on the MYSQL server.

Start the docker application using:
```
docker-compose up -d
```

Once the application is running navigate to `http://localhost:8080` and follow the on-screen instructions to finalize the settings of your local wordpress application.

## Installing Bugsnag

First Bugsnag needs to be added to your WordPress app. This can be accomplished in one of two ways:

### Using the plugin installer

- Inside the admin dashboard of your WordPress app, navigate to the `Plugins` section.
- From there, select the `Add New` button next to the `Plugins` title.
- Search for `Bugsnag` in the `Search plugins...` search field in the top right.
- Select `Install Now` on the plugin titled `WordPress Error Monitoring by Bugsnag` By `Bugsnag Inc.`

### Manual install

- Visit the [WordPress plugin index](https://wordpress.org/plugins/bugsnag/) and search for `Bugsnag`.
- Download the Bugsnag ZIP file and unzip it.
- Move the `bugsnag` folder from the unzipped file to the `app/wp-content/plugins` folder.

## Activating and testing Bugsnag

On your WordPress admin dashboard visit the `Plugins` section. In the list of plugins, find `Bugsnag Error Monitoring` and press `activate`.
Once activated, press `settings` and enter your Bugsnag API key in the resulting screen.

You can test your installation by sending an example notification from your WordPress app to Bugsnag using the `Test Bugsnag` button on the settings page.


For more information, see our documentation:
https://docs.bugsnag.com/platforms/php/wordpress/

### Notes on docker-volumes
The docker-compose file in this example exposes the `/var/www/html/` folder of the WordPress app within the `app` folder in this directory. This allows you to easily modify various aspects of your WordPress installation and see the result instantly on your local machine.
