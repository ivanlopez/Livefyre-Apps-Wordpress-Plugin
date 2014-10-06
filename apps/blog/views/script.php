<div id="<?php echo esc_attr($livefyre_element); ?>"></div>
<script type="text/javascript">
    var networkConfig = {
        network: "<?php echo esc_js($network->getName()); ?>"<?php echo $strings !== null ? ', strings: ' . esc_js($strings) : ''; ?>
    };
    var convConfigBlog = {
        siteId: "<?php echo esc_js($siteId); ?>",
        articleId: "<?php echo esc_js($articleId); ?>",
        el: "<?php echo esc_js($livefyre_element); ?>",
        collectionMeta: "<?php echo esc_js($collectionMetaToken); ?>",
        checksum: "<?php echo esc_js($checksum); ?>"
    };

    Livefyre.require(['fyre.conv#3'], function(Conv) {
        new Conv(networkConfig, [convConfigBlog], function(blogWidget) {
        }());
    });
</script>