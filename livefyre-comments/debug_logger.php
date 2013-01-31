<?php


class Debug_Logger {
    
    function __construct() {

    }

    function add_log( $message ) {
        if ( WP_DEBUG === true ) {
            if ( is_array( $message ) || is_object( $message ) ) {
                error_log( print_r( $message, true ) );
            } else {
                error_log ( $message );
            }
        }
    }

    function add_database_log( $message ) {
        global $wpdb;

        if ($this->lf_core->debug_mode) {
            $this->add_log( $message );
            $wpdb->query("insert into livefyre_debug_blobs (text) values ('". $message . "');");
        }
    }
}

?>