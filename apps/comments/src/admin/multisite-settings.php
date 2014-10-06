<?php
/*
Author: Livefyre, Inc.
Version: 4.2.0
Author URI: http://livefyre.com/
*/
if(!defined('WP_CONTENT_URL')) {
    die('Direct access is forbidden');
}
?>

<div id="fyresettings">
    <div id="fyreheader" style="<?php echo 'background-image: url(' .LF_PLUGIN_URL . '/images/header-bg.png'. ')' ?>">
        <img src="<?php echo LF_PLUGIN_URL . '/images/logo.png'; ?>" rel="Livefyre" style="padding: 5px; padding-left: 15px;" />
    </div>
    <div id="fyrebody">
        <div id="fyrebodycontent">
            <div id="fyrestatus">
                <?php
                $status = Array('All systems go!', 'green');
                echo '<h1><span class="statuscircle' .$status[1]. '"></span>Livefyre Status: <span>' .$status[0]. '</span></h1>';
                echo '<p>Everything should be set at your local site level.</p>';
                ?>
            </div>

            <div id="fyresidepanel">
                <div id="fyresidesettings">
                    <h1>Network Settings</h1>
                        <p class="lf_label">Livefyre Network: </p>
                        <?php echo '<p class="lf_text">livefyre.com</p>'; ?>
                    <h1>Site Settings</h1>
                        <?php echo '<p class="lf_text">Specific to each site</p>'; ?>
                    <h1>Links</h1>
                        <a href="http://livefyre.com/admin" target="_blank">Livefyre Admin</a>
                        <br />
                        <a href="http://support.livefyre.com" target="_blank">Livefyre Support</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    <?php wp_enqueue_style("livefyre-m-css", LF_PLUGIN_URL . '/src/admin/settings-template.css' ); ?>
</style>
