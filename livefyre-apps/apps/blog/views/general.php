<div id="lfapps-general-metabox-holder" class="metabox-holder clearfix">
    <?php
    wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
    wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
            if (typeof postboxes !== 'undefined')
                postboxes.add_postbox_toggles('plugins_page_livefyre_blog');
        });
    </script>    
    <div class="postbox-container postbox-large">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="referrers" class="postbox ">
                <div class="handlediv" title="Click to toggle"><br></div>
                <h3 class="hndle"><span><?php esc_html_e('LiveBlog Settings', 'lfapps-blog'); ?></span></h3>
                <form name="livefyre_comments_blog" id="livefyre_blog_general" action="options.php" method="POST">
                    <?php @settings_fields('livefyre_apps_settings_blog'); ?>
                    <?php @do_settings_fields('livefyre_apps_settings_blog'); ?>
                    <div class='inside'>
                        <table cellspacing="0" class="lfapps-form-table">
                            <tr>                               
                                <?php
                                $available_versions = Livefyre_Apps::get_available_package_versions('fyre.conv');
                                if (empty($available_versions)) {
                                    $available_versions = array(LFAPPS_Blog::$default_package_version);
                                }
                                $available_versions['latest'] = 'latest';
                                $available_versions = array_reverse($available_versions);
                                ?>
                                <th align="left" scope="row" style="width: 40%">
                                    <?php esc_html_e('Package version', 'lfapps-blog'); ?><br/>
                                    <span class="info"><?php esc_html_e('(If necessary you can revert back to an older version if available)', 'lfapps-blog'); ?></span>
                                </th>
                                <td align="left" valign="top">
                                    <select name="livefyre_apps-livefyre_blog_version">
                                        <?php foreach ($available_versions as $available_version): ?>
                                            <?php $selected_version = get_option('livefyre_apps-livefyre_blog_version', 'latest') == $available_version ? 'selected="selected"' : ''; ?>
                                            <option value="<?php echo esc_attr($available_version); ?>" <?php echo esc_html($selected_version); ?>>
                                                <?php echo ucfirst(esc_html($available_version)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>                            
                            <tr>
                                <td colspan='2' class="info">
                                    <strong>Note:</strong>
                                    <p>There multiple configuration options available for LiveBlog and you can specify them by
                                        declaring "liveBlogConfig" variable in your theme header. For example:</p>
                                    <blockquote class="code">
                                        <?php echo esc_html("<script>
                                          var liveBlogConfig = { readOnly: true; }
                                          </script>"); ?>                                           
                                    </blockquote>
                                    <p><a target="_blank" href="http://answers.livefyre.com/developers/app-integrations/liveblog/#convConfigObject">Click here</a> for a full explanation of LiveBlog options.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div id="major-publishing-actions">									
                        <div id="publishing-action">
                            <?php @submit_button(); ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                </form>
            </div> 
        </div>
    </div>
</div>
<div class="postbox-container postbox-large">
    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
        <div id="referrers" class="postbox ">
            <div class="handlediv" title="Click to toggle"><br></div>
            <h3 class="hndle"><span><?php esc_html_e('LiveBlog Shortcode', 'lfapps-blog'); ?></span></h3>
            <div class='inside'>
                <p>To activate LiveBlog, you must add a shortcode to your content.</p>
                <p>The shortcode usage is pretty simple. Let's say we wish to generate a LiveBlog inside post content. We could enter something like this
                    inside the content editor:</p>
                <p class='code'>[livefyre_liveblog]</p>
                <p>LiveBlog streams are separated by the "Article ID" and if not specified it will use the current post ID. You can define the "Article ID"
                    manually like this:</p>
                <p class='code'>[livefyre_liveblog article_id="123"]</p>
            </div> 
        </div>
    </div>
</div>     
</div>