<?php
/*
Plugin Name: Bugsnag Error Monitoring
Plugin URI: https://bugsnag.com
Description: Bugsnag monitors for errors and crashes on your wordpress site, sends them to your bugsnag.com dashboard, and notifies you by email of each error.
Version: 1.1.1
Author: Bugsnag Inc.
Author URI: https://bugsnag.com
License: GPLv2 or later
*/

class Bugsnag_Wordpress
{
    private static $COMPOSER_AUTOLOADER = 'vendor/autoload.php';
    private static $PACKAGED_AUTOLOADER = 'bugsnag-php/Autoload.php';
    private static $DEFAULT_NOTIFY_SEVERITIES = 'fatal,error';

    private static $NOTIFIER = array(
        'name' => 'Bugsnag Wordpress (Official)',
        'version' => '1.1.1',
        'url' => 'https://bugsnag.com/notifiers/wordpress'
    );

    private $client;
    private $apiKey;
    private $notifySeverities;
    private $filterFields;

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

        // Load bugsnag settings
        $this->apiKey = get_option('bugsnag_api_key');
        $this->notifySeverities = get_option('bugsnag_notify_severities');
        $this->filterFields = get_option('bugsnag_filterfields');

        // Activate the bugsnag client
        if(!empty($this->apiKey)) {
            $this->client = new Bugsnag_Client($this->apiKey);

            $this->client->setReleaseStage($this->releaseStage())
                         ->setErrorReportingLevel($this->errorReportingLevel())
                         ->setFilters($this->filterFields());

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

    private function errorReportingLevel()
    {
        $notifySeverities = empty($this->notifySeverities) ? self::$DEFAULT_NOTIFY_SEVERITIES : $this->notifySeverities;
        $level = 0;

        $severities = explode(",", $notifySeverities);
        foreach($severities as $severity) {
            $level |= Bugsnag_ErrorTypes::getLevelsForSeverity($severity);
        }

        return $level;
    }

    private function filterFields()
    {
        return array_map('trim', explode(",", $this->filterFields));
    }

    private function releaseStage()
    {
        return defined('WP_ENV') ? WP_ENV : "production";
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

    private function renderOption($name, $value, $current)
    {
        $selected = ($value == $current) ? " selected=\"selected\"" : "";
        echo "<option value=\"$value\"$selected>$name</option>";
    }
}

$bugsnagWordpress = new Bugsnag_Wordpress();
