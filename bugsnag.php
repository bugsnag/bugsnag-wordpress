<?php
/*
Plugin Name: Bugsnag Error Monitoring
Plugin URI: https://bugsnag.com
Description: Bugsnag monitors for errors and crashes on your wordpress site, sends them to your bugsnag.com dashboard, and notifies you by email of each error.
Version: 1.5.0
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
        'version' => '1.5.0',
        'url' => 'https://bugsnag.com/notifiers/wordpress',
    );

    private $client;
    private $apiKey;
    private $notifySeverities;
    private $filterFields;
    private $pluginBase;

    public function __construct()
    {
        // Activate bugsnag error monitoring as soon as possible
        $this->activateBugsnag();

        $this->pluginBase = 'bugsnag/bugsnag.php';

        // Run init actions (loading wp user)
        add_action('init', array($this, 'initActions'));

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
            $this->filterFields = get_option('bugsnag_filterfields');
        } else {
            // Multisite
            $this->apiKey = get_site_option('bugsnag_api_key');
            $this->notifySeverities = get_site_option('bugsnag_notify_severities');
            $this->filterFields = get_site_option('bugsnag_filterfields');
        }

        $this->constructBugsnag();
    }

    private function constructBugsnag()
    {
        // Activate the bugsnag client
        if (!empty($this->apiKey)) {
            $this->client = new Bugsnag_Client($this->apiKey);

            $this->client->setReleaseStage($this->releaseStage())
                         ->setErrorReportingLevel($this->errorReportingLevel())
                         ->setFilters($this->filterFields());

            $this->client->mergeDeviceData(['runtimeVersions' => ['wordpress' => get_bloginfo('version')]]);

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
                set_error_handler(array($this->client, 'errorHandler'));
                set_exception_handler(array($this->client, 'exceptionHandler'));
            }
        }
    }

    private function requireBugsnagPhp()
    {
        // Bugsnag-php was already loaded by some 3rd-party code, don't need to load it again.
        if (class_exists('Bugsnag_Client')) {
            return true;
        }

        // Try loading bugsnag-php with composer autoloader.
        $composer_autoloader_path = $this->relativePath(self::$COMPOSER_AUTOLOADER);
        $composer_autoloader_path_filtered = apply_filters('bugsnag_composer_autoloader_path', $composer_autoloader_path);
        if (file_exists($composer_autoloader_path_filtered)) {
            require_once $composer_autoloader_path_filtered;

            return true;
        }

        // Try loading bugsnag-php from packaged autoloader.
        $packaged_autoloader_path = $this->relativePath(self::$PACKAGED_AUTOLOADER);
        $packaged_autoloader_path_filtered = apply_filters('bugsnag_packaged_autoloader_path', $packaged_autoloader_path);
        if (file_exists($packaged_autoloader_path_filtered)) {
            require_once $packaged_autoloader_path_filtered;

            return true;
        }

        return false;
    }

    private function relativePath($path)
    {
        return dirname(__FILE__).'/'.$path;
    }

    private function errorReportingLevel()
    {
        $notifySeverities = empty($this->notifySeverities) ? self::$DEFAULT_NOTIFY_SEVERITIES : $this->notifySeverities;
        $level = 0;

        $severities = explode(',', $notifySeverities);
        foreach ($severities as $severity) {
            $level |= Bugsnag_ErrorTypes::getLevelsForSeverity($severity);
        }

        return $level;
    }

    private function filterFields()
    {
        $filter_fields = apply_filters('bugsnag_filter_fields', $this->filterFields);

        // Array with empty string will break things.
        if ($filter_fields === '') {
            return array();
        }

        return array_map('trim', explode("\n", $filter_fields));
    }

    /**
     * Set Release Stage.
     *
     * @return $release_stage_filtered Release Stage Filtered.
     */
    private function releaseStage()
    {
        if (function_exists('wp_get_environment_type')) {
            $release_stage = wp_get_environment_type(); // Defaults to production when not set.
        } else {
            $release_stage = defined('WP_ENV') ? WP_ENV : 'production';
        }
        $release_stage_filtered = apply_filters('bugsnag_release_stage', $release_stage);

        return $release_stage_filtered;
    }

    // Action hooks
    public function initActions()
    {
        // This should be handled on stage of initializing,
        // not even adding action if init failed.
        //
        // Leaving it here for now.
        if (empty($this->client)) {
            return;
        }

        // Set the bugsnag user using the current WordPress user if available,
        // set as anonymous otherwise.
        $user = array();
        if (is_user_logged_in()) {
            $wp_user = wp_get_current_user();

            // Removed checks for !empty($wp_user->display_name), it should not be required.
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

        $this->client->setUser($user);
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

    private function updateNetworkSettings($settings)
    {
        // Update options
        update_site_option('bugsnag_api_key', isset($_POST['bugsnag_api_key']) ? $_POST['bugsnag_api_key'] : '');
        update_site_option('bugsnag_notify_severities', isset($_POST['bugsnag_notify_severities']) ? $_POST['bugsnag_notify_severities'] : '');
        update_site_option('bugsnag_filterfields', isset($_POST['bugsnag_filterfields']) ? $_POST['bugsnag_filterfields'] : '');
        update_site_option('bugsnag_network', true);

        // Update variables
        $this->apiKey = get_site_option('bugsnag_api_key');
        $this->notifySeverities = get_site_option('bugsnag_notify_severities');
        $this->filterFields = get_site_option('bugsnag_filterfields');

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
        $this->apiKey = $_POST['bugsnag_api_key'];
        $this->notifySeverities = $_POST['bugsnag_notify_severities'];
        $this->filterFields = $_POST['bugsnag_filterfields'];

        $this->constructBugsnag();
        $this->client->notifyError(
            'BugsnagTest',
            'Testing bugsnag',
            array('notifier' => self::$NOTIFIER)
        );

        die();
    }

    // Renderers
    public function renderSettings()
    {
        if (!empty($_POST['action']) && $_POST['action'] == 'update') {
            $this->updateNetworkSettings($_POST);
        }

        include $this->relativePath('views/settings.php');
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

        if (method_exists($this->client, $method)) {
            return call_user_func_array(array($this->client, $method), $arguments);
        }

        throw new BadMethodCallException(sprintf('Method %s does not exist on %s or Bugsnag_Client', $method, __CLASS__));
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
