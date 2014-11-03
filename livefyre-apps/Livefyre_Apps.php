<?php
//Disallow direct access to this file
if(!defined('LFAPPS__PLUGIN_PATH')) 
    die('Bye');

require_once LFAPPS__PLUGIN_PATH . 'libs/php/Livefyre/Livefyre.php';
require_once LFAPPS__PLUGIN_PATH . 'libs/php/Livefyre/Core/Network.php';
require_once LFAPPS__PLUGIN_PATH . 'libs/php/Livefyre/Core/Site.php';
require_once LFAPPS__PLUGIN_PATH . 'libs/php/Livefyre/Utils/IDNA.php';
require_once LFAPPS__PLUGIN_PATH . 'libs/php/Livefyre/Utils/JWT.php';

require_once LFAPPS__PLUGIN_PATH . 'libs/php/LFAPPS_View.php';
require_once(LFAPPS__PLUGIN_PATH . "libs/php/LFAPPS_JWT.php");

use Livefyre\Livefyre;

if ( ! class_exists( 'Livefyre_Apps' ) ) {
    class Livefyre_Apps {
        public static $apps = array(
            'comments'=>'LFAPPS_Comments',
            'sidenotes'=>'LFAPPS_Sidenotes',
            'blog'=>'LFAPPS_Blog',
            'chat'=>'LFAPPS_Chat'
        );
        public static $languages = array(
            'English'=>'English',
            'Spanish'=>'Spanish',
            'French'=>'French',
            'Portuguese'=>'Portuguese'
        );
        private static $conflicting_plugins = array(
            'livefyre-comments/livefyre.php'=>'Livefyre Comments',
            'livefyre-sidenotes/livefyre_sidenotes.php'=>'Livefyre Sidenotes'
        );
        private static $options_name = 'livefyre_apps_options';
        public static $form_saved = false;
        public static $form_saved_msg = false;
        private static $initiated = false;
        private static $auth_initiated = false;
        
        public static function init() {
            if ( ! self::$initiated ) {
                self::$initiated = true;
                self::set_default_options();
                self::init_hooks();
                self::init_apps();                
            }
        }
        
        /**
         * Initialise Livefyre Apps that have been switched on
         */
        private static function init_apps() {
            $conflicting_plugins = self::get_conflict_plugins();
            if(count($conflicting_plugins) > 0) {
                return false;
            }
            $apps = self::get_option('apps');
            foreach($apps as $app=>$switch) {
                if($switch) {
                    self::init_app($app);
                }
            }
        }
        
        /**
         * Load app class
         * @param string $app name
         */
        public static function init_app($app) {
            if(isset(self::$apps[$app])) {
                $app_class = self::$apps[$app];
                require_once ( LFAPPS__PLUGIN_PATH . "apps". DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . $app_class . ".php" );
                $app_class::init();
            }
        }
        
        /**
         * Initialise WP hooks
         */
        private static function init_hooks() {
            self::load_resources();
        }
        
        /**
         * Initialise Livefyre auth
         */
        public static function init_auth() {
            if(self::$auth_initiated) {
                return false;
            }
            self::$auth_initiated = true;
            LFAPPS_View::render_partial('script_auth');   
        }
        
        public static function load_resources() {
            wp_register_script('lfapps.js', LFAPPS__PLUGIN_URL . 'assets/js/lfapps.js', array(), LFAPPS__VERSION, false);
            wp_enqueue_script('lfapps.js');
            
            wp_register_script('Livefyre.js', 'http://cdn.livefyre.com/Livefyre.js', array(), LFAPPS__VERSION, false);
            wp_enqueue_script('Livefyre.js');
        }
        
        /**
         * First time load set default Livefyre Apps options 
         * + import previous Livefyre plugin options
         */
        private static function set_default_options() {
            if(!Livefyre_Apps::get_option('livefyre_options_imported')) {
                self::import_options();
            }
            
            //set default apps
            if(!is_array(self::get_option('apps'))) {
                $apps = array(
                    'comments'=>true
                );
                self::update_option('apps', $apps);
            }
            
            if(!self::get_option('livefyre_environment') 
                    || (self::get_option('livefyre_environment') != 'staging' 
                        && self::get_option('livefyre_environment') != 'production')) {
                self::update_option('livefyre_environment', 'staging');
            }
            
            //set default package type (community/enterprise)
            if(!self::get_option('package_type')) {
                self::update_option('package_type', 'community');
            }
            
            //set default auth type - if community always set to auth_delegate
            if(!self::get_option('auth_type') || self::get_option('package_type') === 'community') {
                self::update_option('auth_type', 'auth_delegate');
            }
            
            //set default auth delegate name
            if(!self::get_option('livefyre_auth_delegate_name')) {
                self::update_option('livefyre_auth_delegate_name', 'authDelegate');
            }
            //set default language
            if(!self::get_option('livefyre_language')) {
                self::update_option('livefyre_language', 'English');
            }
        }
        
        /**
         * Import plugin options from previous Livefyre Plugins
         */
        private static function import_options() {
            if(get_option('livefyre_site_id', false) !== false) {
                self::update_option('livefyre_site_id', get_option('livefyre_site_id'));
            } elseif(get_option('livefyre_sidenotes_site_id', false) !== false) {
                self::update_option('livefyre_site_id', get_option('livefyre_sidenotes_site_id'));
            }
            if(get_option('livefyre_site_key', false) !== false) {
                self::update_option('livefyre_site_key', get_option('livefyre_site_key'));
            } elseif(get_option('livefyre_sidenotes_site_key', false) !== false) {
                self::update_option('livefyre_site_key', get_option('livefyre_sidenotes_site_key'));
            }
            if(get_option('livefyre_domain_name', false) !== false) {
                self::update_option('livefyre_domain_name', get_option('livefyre_domain_name'));
            }
            if(get_option('livefyre_domain_key', false) !== false) {
                self::update_option('livefyre_domain_key', get_option('livefyre_domain_key'));
            }
            if(get_option('livefyre_auth_delegate_name', false) !== false) {
                self::update_option('livefyre_auth_delegate_name', get_option('livefyre_auth_delegate_name'));
            }
            self::update_option('livefyre_environment', 'staging');
            if(get_option('livefyre_environment', false) === '1') {
                self::update_option('livefyre_environment', 'production');
            }
            if(get_option('livefyre_language', false) !== false) {
                self::update_option('livefyre_language', get_option('livefyre_language'));
            }
            
            Livefyre_Apps::update_option('livefyre_options_imported', true);
        }
        
        /**
         * Get site option
         * @param string $name
         * @param mixed $default
         */
        public static function get_option($name, $default='') {
            $options = get_option(self::$options_name, array());
            if(is_array($options) && isset($options[$name])) {
                return $options[$name];
            }
            return $default;
        }
        
        /**
         * Set site options
         * @param string $name Option name. Expected to not be SQL-escaped.
         * @param mixed $value Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
         * @return boolean False if value was not updated and true if value was updated.
         */
        public static function update_option($name, $value) {
            $options = get_option(self::$options_name, array());
            if(is_array($options)) {
                if(is_string($value)) {
                    $value = stripslashes($value);
                }
                $options[$name] = $value;
                return update_option(self::$options_name, $options);
            }
            return false;
        }
        
        /**
         * Check if app is enabled
         * @param string $app name of app
         */
        public static function is_app_enabled($app) {
            $apps = self::get_option('apps', array());
            return isset($apps[$app]) && $apps[$app] === true;
        }
        
        /**
         * Check if Site ID and Key have been entered
         * @return boolean
         */
        public static function check_site_keys() {
            return strlen(self::get_option('livefyre_site_id')) > 0 
                && strlen(self::get_option('livefyre_site_key')) > 0; 
        }
        
        /**
         * Check if Network has been entered (enterprise only)
         * @return boolean
         */
        public static function check_network() {
            return strlen(self::get_option('livefyre_domain_name')) > 0; 
        }
        
        /**
         * Check if Livefyre Apps are active (i.e. required data is in place)
         * @return boolean
         */
        public static function active() {
            $conflicting_plugins = self::get_conflict_plugins();
            if(count($conflicting_plugins) > 0) {
                return false;
            }
            $package_type = Livefyre_Apps::get_option('package_type');
            if($package_type === 'community') {
                return self::check_site_keys();
            } elseif($package_type === 'enterprise') {
                return self::check_site_keys() && self::check_network();
            }
            return false;
        }
        
        public static function generate_wp_user_token() {
            $current_user = wp_get_current_user();
            $network_key = self::get_option('livefyre_domain_key', '');
            $network = Livefyre::getNetwork(self::get_option('livefyre_domain_name', 'livefyre.com'), strlen($network_key) > 0 ? $network_key : null);  
            return $network->buildUserAuthToken($current_user->ID.'', $current_user->display_name, 3600);
        }
        
        /**
         * Get the Livefyre.require package reference name and version
         * @param string $name
         * @return string
         */
        public static function get_package_reference($name) {
            $enterprise = self::get_option('package_type') == 'enterprise';
            $uat = self::get_option('livefyre_environment') == 'staging';
            switch($name) {
                case 'sidenotes':
                    return 'sidenotes#' . (($uat && $enterprise) ? 'uat' : 'v1');
                break;
                case 'fyre.conv':
                    return 'fyre.conv#' . (($uat && $enterprise) ? 'uat' : '3');
                break;
            }
            return '';
        }
        
        /**
         * Get list of plugins that conflict with Livefyre Apps
         * @return array
         */
        public static function get_conflict_plugins() {
            $plugins = array();
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            foreach (self::$conflicting_plugins as $key => $value) {
                if (is_plugin_active($key)) {
                    $plugins[$key] = $value;
                }
            }
            return $plugins;
        }
    }
}