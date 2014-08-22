<?php
/*
Author: Livefyre, Inc.
Version: 4.2.0
Author URI: http://livefyre.com/
*/

class Livefyre_Display {

    /*
     * Designates what Livefyre's widget is binding to.
     *
     */
    function __construct( $lf_core ) {
        
        if ( !self::livefyre_comments_off() ) {
            add_action( 'wp_enqueue_scripts', array( &$this, 'lf_embed_head_script' ) );
            add_action( 'wp_enqueue_scripts', array( &$this, 'load_strings' ) );
            add_action( 'wp_footer', array( &$this, 'lf_init_script' ) );
            add_action( 'wp_footer', array( &$this, 'lf_debug' ) );
            // Set comments_template filter to maximum value to always override the default commenting widget
            add_filter( 'comments_template', array( &$this, 'livefyre_comments' ), $this->lf_widget_priority() );
            add_filter( 'comments_number', array( &$this, 'livefyre_comments_number' ), 10, 2 );
        }
    
    }

    /*
     * Helper function to test if comments shouldn't be displayed.
     *
     */
    function livefyre_comments_off() {
    
        return ( get_option( 'livefyre_site_id', '' ) == '' );

    }

    /*
     * Gets the Livefyre priority.
     *
     */
    function lf_widget_priority() {

        return intval( get_option( 'livefyre_widget_priority', 99 ) );

    }
    
    /*
     * Embed Livefyre's JS lib.
     *
     */
    function lf_embed_head_script() {
        $source_url = 'http://zor.'
                . ( get_option( 'livefyre_environment', '0' == 1 ) ?  "livefyre.com" : 't402.livefyre.com' )
                . '/wjs/v3.0/javascripts/livefyre.js';
        wp_enqueue_script( 'livefyre-js', esc_url( $source_url ) );

    }
        
    /*
     * Builds the Livefyre JS code that will build the conversation and load it onto the page. The
     * bread and butter of the whole plugin.
     *
     */
    function lf_init_script() {
    /*  Reset the query data because theme code might have moved the $post gloabl to point 
        at different post rather than the current one, which causes our JS not to load properly. 
        We do this in the footer because the wp_footer() should be the last thing called on the page.
        We don't do it earlier, because it might interfere with what the theme code is trying to accomplish.  */
        wp_reset_query();

        global $post, $current_user, $wp_query;
        if ( comments_open() && $this->livefyre_show_comments() ) {   // is this a post page?
            $network = get_option( 'livefyre_domain_name', 'livefyre.com' );
            $network = ( $network == '' ? 'livefyre.com' : $network );
            $networkKey = get_option( 'livefyre_domain_key' );
            $authDelegate = get_option( 'livefyre_auth_delegate_name', '' );
            $callback = get_option( 'livefyre_callback_name', '' );
            $lf = new Livefyre();
            $lf_network = $lf->getNetwork($network, $networkKey);
            $siteId = get_option( 'livefyre_site_id' );
            $siteKey = get_option( 'livefyre_site_key' );
            $lf_site = $lf_network->getSite($siteId, $siteKey);
            $environment = "livefyre.com";
            $post = get_post();
            $articleId = get_the_ID();
            $title = get_the_title($articleId);
            $url = get_permalink($articleId);
            $tags = array();
            $posttags = get_the_tags( $wp_query->post->ID );
            if ( $posttags ) {
                foreach( $posttags as $tag ) {
                    array_push( $tags, $tag->name );
                }
            }
            $post_categories = wp_get_post_categories( $articleId );
            $topics = array();
            foreach($post_categories as $c){
                $cat = get_category( $c );
                $sub =  array( 'label' => $cat->name, 
                                'id' => 'urn:livefyre:'.$network.':site='.$siteId.":topic=".str_replace( "-", "_", $cat->slug )
                );
                array_push( $topics, $sub);
            }
            function custom_sort($a,$b) {return strcmp($a['id'], $b['id']);}
            usort($topics, 'custom_sort');
            try{
                $collectionMeta = $lf_site->buildCollectionMetaToken( $title, $articleId, $url, implode( $tags ) );
                $checksum = $lf_site->buildChecksum( $title, $url, implode( $tags ) );
            } catch (Exception $e) {
                echo "Message: " . $e->getMessage();
            }
            $extensions = array(
                'wp_version' => get_bloginfo( 'version' ),
                'post_author' => $post->post_author
            );
            // echo $extensions;
            $collectionMetaString = "'$collectionMeta'";
            $collectionMeta = array(
                'articleId' => $articleId,
                'title' => $title . "Changed!",
                'url' => $url,
                'tags' => $tags,
                'topics' => $topics,
                'extensions' => $extensions
            );
            $checksum = md5( json_encode( $collectionMeta ) );
            $jwtString = JWT::encode($collectionMeta, $siteKey);
            $collectionMetaString = "'$jwtString'";
            $convConfig = 'var convConfig = [{
                "collectionMeta": ' .$collectionMetaString. ',
                "checksum": "' .$checksum. '",
                "siteId": ' .$siteId. ',
                "articleId": ' .$articleId. ',
                "el": "livefyre-comments"
            }]';
            $networkConfig = array();
            if ( get_option( 'livefyre_language', 'English') != 'English' ) {
                $networkConfig['strings'] = 'customStrings';
            }
            if ( $network != 'livefyre.com' ) {
                $networkConfig['network'] = "\"$network\"";
            }
            if ( $authDelegate != '' ) {
                $networkConfig['authDelegate'] = $authDelegate;
            }
            #for each in $networkConfig, build the string
            foreach ( $networkConfig as $key => $value ) {
                $networkConfigString .= "\"$key\": $value,\n";
            }
            $networkConfigString = "var networkConfig = {
                $networkConfigString          }";
            $callback = ($callback == '') ? "" : " , $callback";
            $lfLoad = "fyre.conv.load(networkConfig, convConfig$callback)";
            $commentsJS = "$networkConfigString;\n          $convConfig;\n         $lfLoad;";
            echo "<script>
                $commentsJS
            </script>";
        }

        if ( !is_single() ) {
            $ccjs = 'http://zor.livefyre.com/wjs/v1.0/javascripts/CommentCount.js';
            echo '<script type="text/javascript" data-lf-domain="' . esc_attr( $network ) . '" id="ncomments_js" src="' . esc_html( $ccjs ) . '"></script>';
        }

    }

    /*
     * Debug script that will point customers to what could be potential issues.
     *
     */
    function lf_debug() {

        global $post;
        $post_type = get_post_type( $post );
        $article_id = $post->ID;
        $site_id = get_option( 'livefyre_site_id', '' );
        $display_posts = get_option( 'livefyre_display_posts', 'true' );
        $display_pages = get_option( 'livefyre_display_pages', 'true' );
        echo "\n";
        ?>
            <!-- LF DEBUG
            site-id: <?php echo esc_html($site_id) . "\n"; ?>
            article-id: <?php echo esc_html($article_id) . "\n"; ?>
            post-type: <?php echo esc_html($post_type) . "\n"; ?>
            comments-open: <?php echo esc_html(comments_open() ? "true\n" : "false\n"); ?>
            is-single: <?php echo is_single() ? "true\n" : "false\n"; ?>
            display-posts: <?php echo esc_html($display_posts) . "\n"; ?>
            display-pages: <?php echo esc_html($display_pages) . "\n"; ?>
            -->
        <?php
        
    }

    /*
     * The template for the Livefyre div element.
     *
     */
    function livefyre_comments( $cmnts ) {

        return dirname( __FILE__ ) . '/comments-template.php';

    }

    /*
     * Handles the toggles on the settings page that decide which post types should be shown.
     * Also prevents comments from appearing on non single items and previews.
     *
     */
    function livefyre_show_comments() {
        
        global $post;
        /* Is this a post and is the settings checkbox on? */
        $display_posts = ( is_single() && get_option( 'livefyre_display_posts','true') == 'true' );
        /* Is this a page and is the settings checkbox on? */
        $display_pages = ( is_page() && get_option( 'livefyre_display_pages','true') == 'true' );
        /* Are comments open on this post/page? */
        $comments_open = ( $post->comment_status == 'open' );

        $display = $display_posts || $display_pages;
        $post_type = get_post_type();
        if ( $post_type != 'post' && $post_type != 'page' ) {
            
            $post_type_name = 'livefyre_display_' .$post_type;            
            $display = ( get_option( $post_type_name, 'true' ) == 'true' );
        }

        return $display
            && !is_preview()
            && $comments_open;

    }

    /*
     * Build the Livefyre comment count variable.
     *
     */
    function livefyre_comments_number( $count ) {

        global $post;
        return '<span data-lf-article-id="' . esc_attr($post->ID) . '" data-lf-site-id="' . esc_attr(get_option( 'livefyre_site_id', '' )) . '" class="livefyre-commentcount">'.esc_html($count).'</span>';

    }

    /*
     * Loads in JS variable to enable the widget to be internationalized.
     *
     */
    function load_strings() {

        $language = get_option( 'livefyre_language', 'English' );
        if ( $language == 'English' ) {
            return;
        }
        $lang_file = plugins_url() . "/livefyre-comments/languages/" . $language;
        wp_enqueue_script( 'livefyre-lang-js', esc_url( $lang_file ) );

    }
    
}
