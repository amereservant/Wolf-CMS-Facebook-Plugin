<h1><?php echo __('Facebook Plugin - Index'); ?></h1>
<p>This plugin allows developers to integrate Facebook connectivity with their
    projects.  "As-is", the plugin is designed to allow Facebook user connections
    and the ability to automatically create new user accounts.</p>
<p>The user will use their Facebook login to sign into their accounts on the 
    website since they will not have standard account passwords, etc. and therefore
    their login authentication is all done through this plugin.
</p>
<table id="facebook_table" class="index">
    <thead>
        <tr>
            <th class="page"><?php echo __('Page'); ?></th>
            <th class="code"><?php echo __('Code'); ?></th>
            <th class="notes"><?php echo __('Notes'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Login</td>
            <td><span class="red">&lt;?php</span> fb_login(); <span class="red">?&gt;</span></td>
            <td>Displays Facebook login/logout buttons w/links.</td>
        </tr>
    </tbody>
</table>
<p class="bold">Add the tag to a public page <em>( recommended to add to sidebar )</em> and the
    users will be able to login via Facebook.</p>
<p class="bold">You will need to go to the 
    <a href="<?php echo get_url('plugin/facebook/settings'); ?>" title="Settings Page">Settings Page</a>
    and fill in your Facebook Application details.
    <br />
    If you have not created a Facebook Application yet, you will need to do so at
    <a href="http://www.facebook.com/developers/createapp.php" title="Create a Facebook App">create
    a Facebook App</a>.
</p>
