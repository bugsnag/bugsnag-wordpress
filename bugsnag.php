<?php
/*
Plugin Name: BugSnag Error Monitoring plugin
Plugin URI: https://bugsnag.com
Description: Automatically detects errors & crashes on your WordPress site using BugSnag to notify you by email, chat or issues system.
Version: 1.6.5
Author: BugSnag
Author URI: https://bugsnag.com
License: GPLv2 or later
*/

use Bugsnag\Client;
use Bugsnag\Handler;
use Bugsnag\ErrorTypes;
use Bugsnag\Report;

class Bugsnag_Wordpress
{
    private static $COMPOSER_AUTOLOADER = 'vendor/autoload.php';
    private static $DEFAULT_NOTIFY_SEVERITIES = 'fatal,error';

    private static $DISABLED_NOTIFIER_METHODS = array(
        'setAutoCaptureSessions',
        'shouldCaptureSessions'
    );

    private static $NOTIFIER = array(
        'name' => 'Bugsnag Wordpress (Official)',
        'version' => '2.0.0',
        'url' => 'https://github.com/bugsnag/bugsnag-wordpress',
    );

    private Client $client;
    private $apiKey;
    private $notifySeverities;
    private $redactedKeys;
    private $appVersion;
    private $notifyEndpoint;
    private $releaseStageConfig;
    private $pluginBase;

    public function __construct()
    {
        // Activate bugsnag error monitoring as soon as possible
        $this->activateBugsnag();

        $this->pluginBase = 'bugsnag/bugsnag.php';

        // Run init actions (loading wp user)
        add_action('init', array($this, 'registerUser'));

        // Load admin actions (admin links and pages)
        add_action('admin_menu', array($this, 'adminMenuActions'));

        // Load network admin menu if using multisite
        add_action('network_admin_menu', array($this, 'networkAdminMenuActions'));

        add_action('wp_ajax_test_bugsnag', array($this, 'testBugsnag'));
    }

    private function activateBugsnag()
    {
        $is_load_success = $this->requireBugsnagPhp();
        if (!$is_load_success) {
            error_log("Bugsnag Error: Couldn't activate Bugsnag Error Monitoring due to missing Bugsnag library!");

            return;
        }

        // Load bugsnag settings
        if (!get_site_option('bugsnag_network')) {
            // Regular
            $this->apiKey = get_option('bugsnag_api_key');
            $this->notifySeverities = get_option('bugsnag_notify_severities');
            $this->redactedKeys = get_option('bugsnag_redacted_keys');
            $this->appVersion = get_option('bugsnag_app_version');
            $this->notifyEndpoint = get_option('bugsnag_notify_endpoint');
            $this->releaseStageConfig = get_option('bugsnag_release_stage');
        } else {
            // Multisite
            $this->apiKey = get_site_option('bugsnag_api_key');
            $this->notifySeverities = get_site_option('bugsnag_notify_severities');
            $this->redactedKeys = get_site_option('bugsnag_redacted_keys');
            $this->appVersion = get_site_option('bugsnag_app_version');
            $this->notifyEndpoint = get_site_option('bugsnag_notify_endpoint');
            $this->releaseStageConfig = get_site_option('bugsnag_release_stage');
        }

        $this->constructBugsnag();
    }

    private function constructBugsnag()
    {
        // Activate the bugsnag client
        if (!empty($this->apiKey)) {
            $this->client = Client::make($this->apiKey);

            $this->client->setReleaseStage($this->releaseStage())
                ->setErrorReportingLevel($this->errorReportingLevel())
                ->setRedactedKeys($this->redactedKeys())
                ->setAppType('wordpress');

            // Set app version if configured
            if (!empty($this->appVersion)) {
                $this->client->setAppVersion($this->appVersion);
            }

            // Set notify endpoint if configured
            if (!empty($this->notifyEndpoint)) {
                $this->client->setNotifyEndpoint($this->notifyEndpoint);
            }

            $this->client->getConfig()->mergeDeviceData(['runtimeVersions' => ['wordpress' => get_bloginfo('version')]]);

            $this->client->setNotifier(self::$NOTIFIER);

            // If handlers are not set, errors are still going to be reported
            // to bugsnag, difference is execution will not stop.
            //
            // Can be useful to see inline errors and traces with xdebug too.
            $set_error_and_exception_handlers = apply_filters(
                'bugsnag_set_error_and_exception_handlers',
                defined('BUGSNAG_SET_EXCEPTION_HANDLERS') ? BUGSNAG_SET_EXCEPTION_HANDLERS : true
            );

            if ($set_error_and_exception_handlers === true) {
                // Hook up automatic error handling
                Handler::register($this->client);
            }
        }
    }

    private function requireBugsnagPhp()
    {
        // Bugsnag-php was already loaded by some 3rd-party code, don't need to load it again.
        if (class_exists('Bugsnag\Client')) {
            return true;
        }

        // Try loading bugsnag-php with composer autoloader.
        try {
            require_once $this->relativePath(self::$COMPOSER_AUTOLOADER);
            return true;
        } catch (Exception $e) {
            return false;
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

        $severities = explode(',', $notifySeverities);
        foreach ($severities as $severity) {
            $level |= ErrorTypes::getLevelsForSeverity($severity);
        }

        return $level;
    }

    private function redactedKeys()
    {
        $redacted_keys = apply_filters('bugsnag_redacted_keys', $this->redactedKeys);

        // Array with empty string will break things.
        if ($redacted_keys === '') {
            return array();
        }

        return array_map('trim', explode("\n", $redacted_keys));
    }

    /**
     * Set Release Stage.
     *
     * @return $release_stage_filtered Release Stage Filtered.
     */
    private function releaseStage()
    {
        // Use configured release stage if available
        if (!empty($this->releaseStageConfig)) {
            $release_stage = $this->releaseStageConfig;
        } elseif (function_exists('wp_get_environment_type')) {
            $release_stage = wp_get_environment_type(); // Defaults to production when not set.
        } else {
            $release_stage = defined('WP_ENV') ? WP_ENV : 'production';
        }
        $release_stage_filtered = apply_filters('bugsnag_release_stage', $release_stage);

        return $release_stage_filtered;
    }

    // Action hooks
    public function registerUser()
    {
        if (!$this->isStarted()) { // This might attempt to run before the client is configured.
            return;
        }
        $this->client->registerCallback(function (Report $report) {
            // Set the bugsnag user using the current WordPress user if available,
            // set as anonymous otherwise.
            $user = [];

            if (is_user_logged_in()) {
                $wp_user = wp_get_current_user();
                $user['id'] = $wp_user->user_login;
                $user['email'] = $wp_user->user_email;
                $user['name'] = $wp_user->display_name;
            } else {
                $use_unsafe_spoofable_ip_address_getter = apply_filters('bugsnag_use_unsafe_spoofable_ip_address_getter', true);
                $user['id'] = $use_unsafe_spoofable_ip_address_getter ?
                    $this->getClientIpAddressUnsafe() :
                    $this->getClientIpAddress();
                $user['name'] = 'anonymous';
            }

            $report->setUser($user);
        });
    }

    // Unsafe: client can spoof address.
    // http://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php
    private function getClientIpAddressUnsafe()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }

    // Can not be spoofed, but can show ip of NAT or proxies.
    private function getClientIpAddress()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function adminMenuActions()
    {
        if (!function_exists('is_plugin_active_for_network') || !is_plugin_active_for_network($this->pluginBase)) {
            // Add the "settings" link to the Bugsnag row of plugins.php
            add_filter('plugin_action_links', array($this, 'pluginActionLinksFilter'), 10, 2);

            // Create the settings page
            add_options_page('Bugsnag Settings', 'Bugsnag', 'manage_options', 'bugsnag', array($this, 'renderSettings'));
        }
    }

    public function networkAdminMenuActions()
    {
        if (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network($this->pluginBase)) {
            // Create the network settings page
            add_submenu_page('settings.php', 'Bugsnag Settings', 'Bugsnag', 'manage_network_options', 'bugsnag', array($this, 'renderSettings'));
        }
    }

    private function updateNetworkSettings()
    {
        // Update options
        update_site_option('bugsnag_api_key', isset($_POST['bugsnag_api_key']) ? $_POST['bugsnag_api_key'] : '');
        update_site_option('bugsnag_notify_severities', isset($_POST['bugsnag_notify_severities']) ? $_POST['bugsnag_notify_severities'] : '');
        update_site_option('bugsnag_redacted_keys', isset($_POST['bugsnag_redacted_keys']) ? $_POST['bugsnag_redacted_keys'] : '');
        update_site_option('bugsnag_app_version', isset($_POST['bugsnag_app_version']) ? $_POST['bugsnag_app_version'] : '');
        update_site_option('bugsnag_notify_endpoint', isset($_POST['bugsnag_notify_endpoint']) ? $_POST['bugsnag_notify_endpoint'] : '');
        update_site_option('bugsnag_release_stage', isset($_POST['bugsnag_release_stage']) ? $_POST['bugsnag_release_stage'] : '');
        update_site_option('bugsnag_network', true);

        // Update variables
        $this->apiKey = get_site_option('bugsnag_api_key');
        $this->notifySeverities = get_site_option('bugsnag_notify_severities');
        $this->redactedKeys = get_site_option('bugsnag_redacted_keys');
        $this->appVersion = get_site_option('bugsnag_app_version');
        $this->notifyEndpoint = get_site_option('bugsnag_notify_endpoint');
        $this->releaseStageConfig = get_site_option('bugsnag_release_stage');

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Filter hooks
    public function pluginActionLinksFilter($links, $file)
    {
        // Add the "settings" link to the Bugsnag plugin row
        if (basename($file) == basename(__FILE__)) {
            $settings_link = '<a href="options-general.php?page=bugsnag">Settings</a>';
            array_push($links, $settings_link);
        }

        return $links;
    }

    public function testBugsnag()
    {
        // Verify nonce for CSRF protection
        if (!wp_verify_nonce($_POST['_wpnonce'], 'test_bugsnag_nonce')) {
            wp_die('Security check failed.');
        }

        $this->apiKey = $_POST['bugsnag_api_key'];
        $this->notifySeverities = $_POST['bugsnag_notify_severities'];
        $this->redactedKeys = $_POST['bugsnag_redacted_keys'];
        $this->appVersion = $_POST['bugsnag_app_version'];
        $this->notifyEndpoint = $_POST['bugsnag_notify_endpoint'];
        $this->releaseStageConfig = $_POST['bugsnag_release_stage'];

        $this->client->notifyError(
            'BugsnagTest',
            'Testing bugsnag',
            function (Report $report) {
                $report->setSeverity('info');
                $report->setMetaData([
                    'notifier' => self::$NOTIFIER,
                    'docs' => array('url' => 'https://docs.bugsnag.com/platforms/php/wordpress/'),
                ]);
            }
        );

        die();
    }

    // Renderers
    public function renderSettings()
    {
        if (!empty($_POST['action']) && $_POST['action'] == 'update') {
            // Verify nonce for CSRF protection
            if (!wp_verify_nonce($_POST['_wpnonce'], 'update-options')) {
                wp_die('Security check failed. Please try again.');
            }
            $this->updateNetworkSettings($_POST);
        }

        include $this->relativePath('views/settings.php');
    }

    public function isStarted()
    {
        return isset($this->apiKey) && isset($this->client);
    }

    private function renderOption($name, $value, $current)
    {
        $selected = ($value == $current) ? ' selected="selected"' : '';
        echo "<option value=\"$value\"$selected>$name</option>";
    }

    /**
     * Fluent interface to $this->client, simply call the methods on this object and this will proxy them through.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        // If we don't have an API key here then the plugin has not been setup, but
        // methods are already being called. We can't forward these calls through
        // because the client needs an API key on construction and we need to fail
        // loudly so the user knows their site isn't setup correctly.
        if (empty($this->apiKey)) {
            throw new BadMethodCallException(
                'No Bugsnag API Key set. Please enter your API Key on the Bugsnag Settings page.'
            );
        }

        if (in_array($method, self::$DISABLED_NOTIFIER_METHODS)) {
            throw new BadMethodCallException(sprintf('Method %s is disabled in BugSnag for Wordpress', $method));
        }

        if (method_exists($this->client, $method)) {
            return call_user_func_array(array($this->client, $method), $arguments);
        }

        throw new BadMethodCallException(sprintf('Method %s does not exist on %s or Bugsnag\Client', $method, __CLASS__));
    }
}

/**
 * Add ability to define Bugsnag API Key as constant in wp-config.php.
 *
 * @return either the API from wp-config or false (to use the option value)
 */
function bugsnag_define_api_key()
{
    return defined('BUGSNAG_API_KEY') ? BUGSNAG_API_KEY : false;
}
add_filter('pre_option_bugsnag_api_key', 'bugsnag_define_api_key');
add_filter('pre_site_option_bugsnag_api_key', 'bugsnag_define_api_key');

global $bugsnagWordpress;
$bugsnagWordpress = new Bugsnag_Wordpress();
