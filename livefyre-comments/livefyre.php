<?php
/*
Plugin Name: Livefyre Realtime Comments
Plugin URI: http://livefyre.com
Description: Implements Livefyre realtime comments for WordPress
Author: Livefyre, Inc.
<<<<<<< HEAD
Version: 4.2.0
=======
Version: 4.1.3
>>>>>>> 596dce696d2a1bec69af865fd0591c817ae5950b
Author URI: http://livefyre.com/
*/

require_once( dirname( __FILE__ ) . "/src/Livefyre_WP_Core.php" );

// Constants
define( 'LF_CMETA_PREFIX', 'livefyre_cmap_' );
define( 'LF_AMETA_PREFIX', 'livefyre_amap_' );
define( 'LF_DEFAULT_HTTP_LIBRARY', 'Livefyre_Http_Extension' );
define( 'LF_NOTIFY_SETTING_PREFIX', 'livefyre_notify_' );

/*
 * Initial Livefyre WP class. Needed by WordPress to handle the initialization of the plugin.
 * Build the initial Livefyre functionality for WordPress core.
 *
 */
class Livefyre {

    function __construct() {
    
        $this->lf_core = new Livefyre_WP_Core();

    }

} // Livefyre

$livefyre = new Livefyre();
