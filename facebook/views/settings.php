<h1><?php echo __('Facebook Plugin - Settings'); ?></h1>
<div class="form-area">
    <div id="tab-control-facebook" class="tab_control">
        <form action="<?php echo get_url('plugin/facebook/update_settings/'); ?>" method="post">
            <div id="tabs-admin" class="tabs">
                <div id="tabls-admin-toolbar" class="tabs_toolbar">&nbsp;</div>
            </div>
            <div id="admin-pages" class="pages">
                <!-- Facebook Account Details -->
                <div id="fb-account-page" class="page">
                    <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td class="label">
                                <label for="allow_fb_connect">Allow Facebook Connect?</label>
                            </td>
                            <td class="field">
                                <select name="allow_fb_connect" id="allow_fb_connect">
                                    <option value="0"<?php echo ($allow_fb_connect === '0' ? $selected:''); ?>>No</option>
                                    <option value="1"<?php echo ($allow_fb_connect === '1' ? $selected:''); ?>>Yes</option>
                                </select>
                            </td>
                            <td class="help">
                                <p>Should users be able to login with their Facebook account?</p>
                            </td>
                        </tr>

                        <tr>
                            <td class="label">
                                <label for="fb_api_key">Facebook APP ID: </label>
                            </td>
                            <td class="field">
                                <input type="text" name="fb_api_key" maxlength="32" value="<?php echo $fb_api_key; ?>" />
                            </td>
                            <td class="help">
                                <p>Enter your Facebook Aplication ID.  If you don't have one,
                                    you need to create an app at <a href="http://www.facebook.com/developers/createapp.php" title="Create a Facebook App">create a new app</a>.
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="label">
                                <label for="fb_application_secret">Application Secret: </label>
                            </td>
                            <td class="field">
                                <input type="text" maxlength="32" name="fb_application_secret" value="<?php echo $fb_application_secret; ?>" />
                            </td>
                            <td class="help">
                                <p>Enter your Facebook Application Secret.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="label">
                                <label for="fb_use_cookies">Use Cookies?</label>
                            </td>
                            <td class="field">
                                <select name="fb_use_cookies" id="fb_use_cookies">
                                    <option value="0"<?php echo ($fb_use_cookies === '0' ? $selected:''); ?>>No</option>
                                    <option value="1"<?php echo ($fb_use_cookies === '1' ? $selected:''); ?>>Yes</option>
                                </select>
                            </td>
                            <td class="help">
                                <p>Use cookies to store session data?  If set to no, a SESSION will be used instead.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="label">
                                <label for="user_sets_wolf_acc">User Configures Account?</label>
                            </td>
                            <td class="field">
                                <select name="user_sets_wolf_acc" id="user_sets_wolf_acc">
                                    <option value="0"<?php echo ($user_sets_wolf_acc === '0' ? $selected:''); ?>>No</option>
                                    <option value="1"<?php echo ($user_sets_wolf_acc === '1' ? $selected:''); ?>>Yes</option>
                                </select>
                            </td>
                            <td class="help">
                                <p>Allow users to define wolf CMS account settings?
                                   If set to no, the user will be restricted to Facebook-only logins
                                   and a generated username & password will be created.</p>
                            </td>
                        </tr>
                        <!-- DISABLED - Page not used, changed to using a view instead.
                        <tr>
                            <td class="label">
                                <label for="fb_page_slug">New User Configuration Page: </label>
                            </td>
                            <td class="field">
                                <p style="font-weight:bold"><small><?php echo URL_PUBLIC ?>
                                    <input type="text" name="fb_page_slug" value="<?php echo $fb_page_slug; ?>" size="10" />
                                    <?php echo URL_SUFFIX ?></small>
                                </p>
                            </td>
                            <td class="help">
                                <p>Enter the <strong>Facebook - New User</strong> page slug if changed from
                                    the default value.  This is the page new users will see if the previous
                                    setting is set to '<strong>Yes</strong>'.
                                </p>
                            </td>
                        </tr>
                        -->
                    </table>
                </div><!-- End of fb-account-page -->
                
                <p style="text-align:center">
                    <label>&nbsp;</label>
                    <input type="submit" name="edit_settings" class="button" value="Submit Changes" />
                </p>
                <p>&nbsp;</p>
            </div>
        </form>
    </div>
</div>
<!-- Create JS menu tabs ( JS from /admin/javascripts/wolf.js ) -->
<script type="text/javascript">
	var tabControlMeta = new TabControl('tab-control-facebook');
	tabControlMeta.addTab('tab-account', 'Account', 'fb-account-page');
	tabControlMeta.select(tabControlMeta.firstTab());
</script>
