<?php

class Logger {
    
    function __construct() {

    }

    function add( $message, $database_flag = false ) {
        if ( WP_DEBUG === true ) {
            if ( $database_flag ) {
                global $wpdb;
                error_log( $message );
                $wpdb->query("insert into livefyre_debug_blobs (text) values ('". $message . "');");
            } else if ( is_array( $message ) || is_object( $message ) ) {
                error_log( print_r( $message, true ) );
            } else {
                error_log ( $message );
            }
        }
    }
}

?>