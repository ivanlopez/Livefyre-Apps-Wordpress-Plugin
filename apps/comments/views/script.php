<?php
if($display_template) {
    global $wp_query, $post;
    if ( $parent_id = wp_is_post_revision( $wp_query->post->ID ) ) {
        $post_id = $parent_id;
    } else {
        $post_id = $post->ID;
    }
    $url = LFAPPS_Comments_Core::$bootstrap_url_v3 . '/' . base64_encode($post_id) . '/bootstrap.html';
    $lfHttp = new LFAPPS_Http_Extension();
    $result = $lfHttp->request( $url );
    $cached_html = '';
    if ( $result['response']['code'] == 200 ){
        $cached_html = $result['body'];
        $cached_html = preg_replace( '(<script>[\w\W]*<\/script>)', '', $cached_html );
    }
    echo '<div id="'. esc_attr($livefyre_element).'">' . wp_kses_post( $cached_html ) . '</div>';
    
}
?>
<script type="text/javascript">
    var networkConfig = {
        network: "<?php echo esc_js($network->getName()); ?>"<?php echo $strings !== null ? ', strings: ' . esc_js($strings) : ''; ?>
    };
    var convConfigComments = {
        siteId: "<?php echo esc_js($siteId); ?>",
        articleId: "<?php echo esc_js($articleId); ?>",
        el: "<?php echo esc_js($livefyre_element); ?>",
        collectionMeta: "<?php echo esc_js($collectionMetaToken); ?>",
        checksum: "<?php echo esc_js($checksum); ?>"
    };

    Livefyre.require(['fyre.conv#3'], function(Conv) {
        new Conv(networkConfig, [convConfigComments], function(commentsWidget) {
        }());
    });
</script>