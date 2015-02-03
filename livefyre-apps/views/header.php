<div class="wrap" id="lfapps">
    <h2 class="logo"><?php esc_html_e('Livefyre Apps', 'lfapps'); ?></h2>
    <?php if(Livefyre_Apps::$form_saved): ?>
    <div id="setting-error-settings_updated" class="updated settings-error"> 
        <?php if(Livefyre_Apps::$form_saved_msg): ?>
            <p><strong><?php esc_html_e(Livefyre_Apps::$form_saved_msg); ?></strong></p>
        <?php else: ?>
            <p><strong><?php esc_html_e('Settings saved.'); ?></strong></p>
        <?php endif; ?>        
    </div>
    <?php endif; ?>