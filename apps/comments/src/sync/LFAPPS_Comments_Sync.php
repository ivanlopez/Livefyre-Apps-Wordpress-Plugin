<?php
<<<<<<< HEAD:apps/comments/src/sync/LFAPPS_Comments_Sync.php
interface LFAPPS_Comments_Sync {
=======
/*
Author: Livefyre, Inc.
Version: 4.2.1
Author URI: http://livefyre.com/
*/

interface Livefyre_Sync {
>>>>>>> origin/staging:livefyre-comments/src/sync/Livefyre_Sync.php

    /**
     *
     */
    public function do_sync();

    /**
     *
     */
    public function schedule_sync( $timeout );
    
    /**
     *
     */
    public function comment_update();
    
    /**
     *
     */
    public function profile_update( $user_id );

    /**
     *
     */
    public function check_profile_pull();

    /**
     *
     */
    public function save_post( $post_id );

    /**
     *
     */
    public function post_param( $name, $plain_to_html = false, $default = null );
    
    /**
     *
     */
    public function is_signed_profile_pull();

    /**
     *
     */
    public function site_rest_url();

    /**
     *
     */
    public function livefyre_report_error( $message );

    /**
     *
     */
    public function livefyre_insert_activity( $data );
    
}
