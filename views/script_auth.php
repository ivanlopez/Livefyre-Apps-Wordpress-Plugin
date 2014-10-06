<?php if(Livefyre_Apps::get_option('auth_type') === 'wordpress'): ?>
<script>
    Livefyre.require(['auth'], function(auth) {
        auth.delegate({
            //Called when "sign in" on the widget is clicked. Should sign in to WP
            login: function(cb) {
                href = "<?php echo site_url() . '/wp-login.php'; ?>";
                window.location = href;
            },
            //Called when "sign out" on the widget is clicked. Should sign out of WP
            logout: function(cb) {
                href = "<?php echo urldecode(html_entity_decode(wp_logout_url(site_url()))); ?>";
                window.location = href;
            },
            viewProfile: function() {
                href = "<?php echo admin_url( 'profile.php' ); ?>";
                window.location = href;
            },
            editProfile: function() {
                href = "<?php echo admin_url( 'profile.php' ); ?>";
                window.location = href;
            }
        });
        
        <?php if ( is_user_logged_in() ): ?>
            auth.authenticate("<?php echo esc_js(Livefyre_Apps::generate_wp_user_token()); ?>");
        <?php endif; ?>
        window.authDelegate = auth.delegate;
    });
</script>
<?php else: ?>
<script type="text/javascript">

Livefyre.require(['auth', 'backplane-auth-plugin#qa'], function (XAuth, backplanePluginFactory) {
    
    var authDelegateName = null;
    if(typeof(<?php echo esc_js(Livefyre_Apps::get_option('livefyre_auth_delegate_name', 'authDelegate')); ?>) !== 'undefined') {
        authDelegateName = <?php echo esc_js(Livefyre_Apps::get_option('livefyre_auth_delegate_name', 'authDelegate')); ?>;
    }
    if(authDelegateName) {
        window.auth = XAuth;
        XAuth.plugin(backplanePluginFactory('<?php echo esc_url(Livefyre_Apps::get_option('livefyre_domain_name')); ?>'));
        XAuth.delegate(authDelegateName);
    } else {
        window.auth = XAuth;        
        XAuth.delegate(XAuth.createDelegate('<?php echo esc_js('http://admin.' . Livefyre_Apps::get_option('livefyre_domain_name')); ?>'));
    }
});
</script>
<?php endif; ?>
