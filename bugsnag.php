<?php
/*
Plugin Name: Bugsnag Error Monitoring
Plugin URI: https://bugsnag.com
Description: Bugsnag monitors for errors and crashes on your wordpress site, sends them to your bugsnag.com dashboard, and notifies you by email of each error.
Version: 1.1.0
Author: Bugsnag Inc.
Author URI: https://bugsnag.com
License: GPLv2 or later
*/

class Bugsnag_Wordpress
{
    private static $COMPOSER_AUTOLOADER = 'vendor/autoload.php';
    private static $PACKAGED_AUTOLOADER = 'bugsnag-php/Autoload.php';

    private static $NOTIFIER = array(
        'name' => 'Bugsnag Wordpress (Official)',
        'version' => '1.1.0',
        'url' => 'https://bugsnag.com/notifiers/wordpress'
    );

    private $client;
    private $apiKey;

    public function __construct()
    {
        // Activate bugsnag error monitoring as soon as possible
        $this->activateBugsnag();

        // Run init actions (loading wp user)
        add_action('init', array($this, 'initActions'));

        // Load admin actions (admin links and pages)
        add_action('admin_menu', array($this, 'adminMenuActions'));
    }

    private function activateBugsnag()
    {
        // Require bugsnag-php
        if(file_exists($this->relativePath(self::$COMPOSER_AUTOLOADER))) {
            require_once $this->relativePath(self::$COMPOSER_AUTOLOADER);
        } elseif (file_exists($this->relativePath(self::$PACKAGED_AUTOLOADER))) {
            require_once $this->relativePath(self::$PACKAGED_AUTOLOADER);
        } else {
            error_log("Bugsnag Error: Couldn't activate Bugsnag Error Monitoring due to missing Bugsnag library!");
            return;
        }

        // Activate the bugsnag client
        $this->apiKey = get_option('bugsnag_api_key');
        if(!empty($this->apiKey)) {
            $this->client = new Bugsnag_Client($this->apiKey);

            // Set the releaseStage if a WordPress environment is set
            if(defined('WP_ENV')) {
                $this->client->setReleaseStage(WP_ENV);
            }

            $this->client->setNotifier(self::$NOTIFIER);

            // Hook up automatic error handling
            set_error_handler(array($this->client, "errorHandler"));
            set_exception_handler(array($this->client, "exceptionHandler"));
        }
    }

    private function relativePath($path)
    {
        return dirname(__FILE__) . '/' . $path;
    }


    // Action hooks
    public function initActions()
    {
        // Set the bugsnag user using the current WordPress user if available
        $wpUser = wp_get_current_user();
        if(!empty($this->client) && !empty($wpUser)) {
            $user = array();

            if(!empty($wpUser->user_login)) {
                $user['id'] = $wpUser->user_login;
            }

            if(!empty($wpUser->user_email)) {
                $user['email'] = $wpUser->user_email;
            }

            if(!empty($wpUser->user_display_name)) {
                $user['name'] = $wpUser->user_display_name;
            }

            $this->client->setUser($user);
        }
    }

    public function adminMenuActions()
    {
        // Add the "settings" link to the Bugsnag row of plugins.php
        add_filter('plugin_action_links', array($this, 'pluginActionLinksFilter'), 10, 2);

        // Create the settings page
        add_options_page('Bugsnag Settings', 'Bugsnag', 'manage_options', 'bugsnag', array($this, 'renderSettings'));
    }


    // Filter hooks
    public function pluginActionLinksFilter($links, $file)
    {
        // Add the "settings" link to the Bugsnag plugin row
        if(basename($file) == basename(__FILE__)) {
            $settings_link = '<a href="options-general.php?page=bugsnag">Settings</a>'; 
            array_push($links, $settings_link); 
        }

        return $links; 
    }


    // Renderers
    public function renderSettings()
    {
        include $this->relativePath('views/settings.php');
    }
}

$bugsnagWordpress = new Bugsnag_Wordpress();
