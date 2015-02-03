<?php
//Disallow direct access to this file
if(!defined('LFAPPS__PLUGIN_PATH')) 
    die('Bye');

require_once LFAPPS__PLUGIN_PATH . 'libs/php/LFAPPS_View.php';

if ( ! class_exists( 'Livefyre_Apps_Admin' ) ) {
    class Livefyre_Apps_Admin {        
        private static $initiated = false;
    
        public static function init() {
            if ( ! self::$initiated ) {
                self::$initiated = true;                
                self::init_hooks();     
                self::init_apps();                 
            }
        }
        
        /**
         * Initialise WP hooks
         */
        private static function init_hooks() {
            add_action( 'admin_menu', array('Livefyre_Apps_Admin', 'init_admin_menu' ) );           
            add_action( 'admin_enqueue_scripts', array( 'Livefyre_Apps_Admin', 'load_resources' ) );
        }
        
        /**
         * Initialise admin menu items
         */
        public static function init_admin_menu() {
            add_submenu_page( 'livefyre_apps', 'General', 'General', "manage_options", 'livefyre_apps', array('Livefyre_Apps_Admin', 'menu_general'));
            add_menu_page('Livefyre Apps', 'Livefyre Apps', 'manage_options', 'livefyre_apps', array('Livefyre_Apps_Admin', 'menu_general'), LFAPPS__PLUGIN_URL."assets/img/livefyre-icon_x16.png"); 
            //community authentication page (invisible and only handles data sent back from livefyre.com)
            add_submenu_page( null, 'Livefyre', 'Livefyre', "manage_options", 'livefyre', array('Livefyre_Apps_Admin', 'menu_general'));            
        }
        
        
        /**
         * Initialise Livefyre Apps that have been switched on (Admin Classes)
         */
        private static function init_apps() {
            $conflicting_plugins = Livefyre_Apps::get_conflict_plugins();
            if(count($conflicting_plugins) > 0) {
                return;
            }
            //check if we are inside admin and apps are being switched on/off
            self::process_app_switches();
            if(isset($_GET['type'])) {
                if($_GET['type'] === 'community' || $_GET['type'] === 'enterprise') {
                    Livefyre_Apps::update_option('package_type', sanitize_text_field($_GET['type']));
                }
            }
            
            $apps = Livefyre_Apps::get_option('apps');
            foreach($apps as $app=>$switch) {
                if(Livefyre_Apps::get_option('package_type') == 'community' && ($app == 'chat' || $app == 'blog')) {
                    $switch = false;
                }
                if($switch) {
                    self::init_app($app);
                }
            }
        }
        
        /**
         * Init app
         * @param string $app
         */
        public static function init_app($app) {
            if(isset(Livefyre_Apps::$apps[$app])) {
                $app_class = Livefyre_Apps::$apps[$app] . '_Admin';
                $app_class_path = LFAPPS__PLUGIN_PATH . "apps". DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . $app_class . ".php";
                if(file_exists($app_class_path)) {
                    require_once ( $app_class_path );
                    $app_class::init();
                }
            }
        }
        
        /**
         * Add assets required by Livefyre Apps Admin section
         */
        public static function load_resources() {
            wp_register_style( 'lfapps.css', LFAPPS__PLUGIN_URL . 'assets/css/lfapps.css', array(), LFAPPS__VERSION );
			wp_enqueue_style( 'lfapps.css');
            
            wp_register_script( 'lfapps-admin.js', LFAPPS__PLUGIN_URL . 'assets/js/lfapps-admin.js', array('jquery', 'postbox', 'thickbox'), LFAPPS__VERSION );
			wp_enqueue_script( 'lfapps-admin.js');
        }
        
        /**
         * Generate admin URL for specified page
         * @param string $page
         * @return string URL
         */
        public static function get_page_url( $page ) {
            $args = array( 'page' => $page );
            
            $url = add_query_arg( $args, admin_url( 'admin.php' ) );

            return $url;
        }
        
        /**
         * Check to see if this request is a response back from livefyre.com which sets the site id + key
         * @return boolean
         */

        public static function verified_blog() {
            return isset($_GET['lf_login_complete']) && $_GET['lf_login_complete'] === 'true' 
                    && isset( $_GET['page'] ) && $_GET['page'] === 'livefyre_apps';
        }
        
        /**
         * Check if post sent from General settings and manage the apps switched on/off
         */
        public static function process_app_switches() {
            if(isset($_POST['livefyre_app_management']) && check_admin_referer('form-livefyre_apps_management')) {            
                $apps = array();
                if(isset($_POST['lfapps_comments_enable'])) {
                    $apps['comments'] = true;                   
                }
                if(isset($_POST['lfapps_sidenotes_enable'])) {
                    $apps['sidenotes'] = true;                    
                } 
                if(isset($_POST['lfapps_blog_enable'])) {
                    $apps['blog'] = true;                    
                } 
                if(isset($_POST['lfapps_chat_enable'])) {
                    $apps['chat'] = true;                    
                } 
                Livefyre_Apps::update_option('apps', $apps);
                Livefyre_Apps::$form_saved = true;
            }
        }
        
        public static function menu_plugin_conflict() {
            LFAPPS_View::render('plugin_conflict');
        }
        
        /**
         * Run Livefyre Apps General page
         */
        public static function menu_general() {           
            $conflicting_plugins = Livefyre_Apps::get_conflict_plugins();
            if(count($conflicting_plugins) > 0) {
                self::menu_plugin_conflict();
                return;
            }
            //process data returned from livefyre.com community sign up
            if(self::verified_blog()) {
                Livefyre_Apps::update_option('livefyre_domain_name', 'livefyre.com');
                Livefyre_Apps::update_option('livefyre_site_id', sanitize_text_field( $_GET["site_id"] ));
                Livefyre_Apps::update_option('livefyre_site_key', sanitize_text_field( $_GET["secretkey"] ));
            }
            #Livefyre_Apps::update_option('initial_modal_shown', false);
            if(isset($_GET['type'])) {
                if($_GET['type'] === 'community' || $_GET['type'] === 'enterprise') {
                    Livefyre_Apps::update_option('initial_modal_shown', true);
                    Livefyre_Apps::update_option('package_type', sanitize_text_field($_GET['type']));
                    wp_redirect(self::get_page_url('livefyre_apps') . '&msg=environment_changed');
                }
            }
            
            if(isset($_GET['msg'])) {
                if($_GET['msg'] === 'environment_changed') {
                    Livefyre_Apps::$form_saved = true;
                    Livefyre_Apps::$form_saved_msg = 'Livefyre Environment has been changed!';
                }
            }
                        
            //process access form
            if(isset($_POST['livefyre_app_general']) && check_admin_referer('form-livefyre_apps_general')) {  
                Livefyre_Apps::update_option('package_type', sanitize_text_field( $_POST["package_type"] ));
                
                Livefyre_Apps::update_option('livefyre_site_id', sanitize_text_field( $_POST["livefyre_site_id"] ));
                Livefyre_Apps::update_option('livefyre_site_key', sanitize_text_field( $_POST["livefyre_site_key"] ));
                
                if(Livefyre_Apps::get_option('package_type') === 'enterprise') {                   
                    
                    Livefyre_Apps::update_option('livefyre_domain_name', sanitize_text_field( $_POST["livefyre_domain_name"] ));
                    Livefyre_Apps::update_option('livefyre_domain_key', sanitize_text_field( $_POST["livefyre_domain_key"] ));
                    Livefyre_Apps::update_option('auth_type', sanitize_text_field($_POST['auth_type']));    
                    
                    Livefyre_Apps::update_option('livefyre_auth_delegate_name', sanitize_text_field( $_POST["livefyre_auth_delegate_name"] ));
                } else {
                    Livefyre_Apps::update_option('auth_type', 'wordpress'); 
                }
                Livefyre_Apps::update_option('livefyre_environment', isset( $_POST["livefyre_environment"] ) ? 'production' : 'staging');
                Livefyre_Apps::$form_saved = true;
            }
            
            //process language form
            if(isset($_POST['livefyre_language']) && check_admin_referer('form-livefyre_language')) {   
                Livefyre_Apps::update_option('livefyre_language', sanitize_text_field( $_POST["lf_language"] ));
                Livefyre_Apps::$form_saved = true;
            }
            LFAPPS_View::render('general');
        }
    }
}