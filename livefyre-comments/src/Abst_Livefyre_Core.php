<?php
/*
Livefyre Realtime Comments Core Module

This library is shared between all Livefyre plugins.

Author: Livefyre, Inc.
Version: 4.0.5
Author URI: http://livefyre.com/
*/

define( 'LF_DEFAULT_PROFILE_DOMAIN', 'livefyre.com' );
define( 'LF_DEFAULT_TLD', 'livefyre.com' );

abstract class Abst_Livefyre_Core {

    function __construct() { 

        $this->add_extension();
        $this->require_php_api();
        $this->require_Livefyre_Logger();
        $this->define_globals();
        $this->require_subclasses();
        $this->require_raven();
        $this->Livefyre_Logger->add( "Livefyre: Constructing a Livefyre_core." );
        
    }
    
    function define_globals() {
    
        $this->options = array( 
            'livefyre_site_id', // - name ( id ) of the livefyre record associated with this blog
            'livefyre_site_key' // - shared key used to sign requests to/from livefyre
        );

        $client_key = $this->ext->get_network_option( 'livefyre_domain_key', '' );
        $profile_domain = $this->ext->get_network_option( 'livefyre_domain_name', LF_DEFAULT_PROFILE_DOMAIN );
        $dopts = array(
            'livefyre_tld' => LF_DEFAULT_TLD
        );
        $uses_default_tld = (strpos(LF_DEFAULT_TLD, 'livefyre.com') === 0);
        $this->lf_domain_object = new Livefyre_Domain( $profile_domain, $client_key, null, $dopts);
        $site_id = $this->ext->get_option( 'livefyre_site_id' );
        $this->site = $this->lf_domain_object->site( 
            $site_id, 
            trim( $this->ext->get_option( 'livefyre_site_key' ) )
        );
        $this->debug_mode = false;
        $this->top_domain = ( $profile_domain == LF_DEFAULT_PROFILE_DOMAIN ? LF_DEFAULT_TLD : $profile_domain );
        $this->http_url = ( $uses_default_tld ? "http://www." . LF_DEFAULT_TLD : "http://" . LF_DEFAULT_TLD );
        $this->api_url = "http://api.$this->top_domain";
        $this->quill_url = "http://quill.$this->top_domain";
        $this->admin_url = "http://admin.$this->top_domain";
        $this->assets_url = "http://zor." . LF_DEFAULT_TLD;
        $this->bootstrap_url = "http://bootstrap.$this->top_domain";
        
        // for non-production environments, we use a dev url and prefix the path with env name
        $bootstrap_domain = 'bootstrap-json-dev.s3.amazonaws.com';
        $environment = $dopts['livefyre_tld'] . '/';
        if ( $uses_default_tld ) {
            $bootstrap_domain = 'data.bootstrap.fyre.co';
            $environment = '';
        }

        $this->bootstrap_url_v3 = "http://$bootstrap_domain/$environment$profile_domain/$site_id";
        
        $this->home_url = $this->ext->home_url();
        $this->plugin_version = LF_PLUGIN_VERSION;

    }
    
    function require_php_api() {

        require_once(dirname(__FILE__) . "/../livefyre-api/libs/php/Livefyre.php");

    }

    function require_Livefyre_Logger() {

        require_once(dirname(__FILE__) . "/../libs/php/Logger/livefyre_logger.php");

    }

    function require_raven() {

        require_once(dirname(__FILE__) . "/../libs/php/Raven/Autoloader.php");
        Raven_Autoloader::register();
        $this->Raven = new Raven_Client('http://0f5245e17ee1418a905268a6032ef829:3c1ef304db44449ab27988d6f0b4dfcf@sentry.livefyre.com:9000/3');
    }

    function add_extension() {

        $this->ext = new Livefyre_Application();
    }

    function require_subclasses() {

    }

} //  Livefyre_core
