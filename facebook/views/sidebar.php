<?php
/**
 * Facebook Plugin Sidebar
 */
?>
<p class="button">
    <a href="<?php echo get_url('plugin/facebook/settings/'); ?>" title="Settings">
        <img src="<?php echo FB_URL_ROOT; ?>images/settings.png" align="middle" alt="Settings" />
        <?php echo __('Settings'); ?>
    </a>
</p>
<p class="button">
    <a href="<?php echo get_url('user'); ?>" title="Users">
        <img src="<?php echo FB_URL_ROOT; ?>images/user.png" align="middle" alt="Users" />
        <?php echo __('Users'); ?>
    </a>
</p>
<p class="button">
    <a href="<?php echo get_url('plugin/facebook/documentation/'); ?>" title="Documentation">
        <img src="<?php echo FB_URL_ROOT; ?>images/documentation.png" align="middle" alt="Documentation" />
        <?php echo __('Documentation'); ?>
    </a>
</p>
<!--
<div class="box">
    <h2><?php echo __('Settings'); ?></h2>
    <p>
        <?php echo __('Configure the settings for the Facebook plugin.'); ?>
    </p>
</div>
-->
