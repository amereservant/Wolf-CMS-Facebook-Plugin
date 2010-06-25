<h1><?php echo __('Facebook Plugin - Index'); ?></h1>
<p>This plugin allows developers to integrate Facebook connectivity with their
    projects.</p>
<p>The user will be able use their Facebook account to login into their accounts 
    on the website.<br />
    It intigrates with the Wolf CMS user system and controls their login status of
    their local account.
</p>
<table id="facebook_table" class="index">
    <thead>
        <tr>
            <th class="page"><?php echo __('Section'); ?></th>
            <th class="code"><?php echo __('Code'); ?></th>
            <th class="notes"><?php echo __('Notes'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="bold">Login</td>
            <td class="bold"><span class="red">&lt;?php</span> $this->includeSnippet('facebook-login'); <span class="red">?&gt;</span></td>
            <td>Displays Facebook login/logout buttons w/links.</td>
        </tr>
    </tbody>
</table>
<p>Add the tag to a public page <em>( recommended to add to sidebar )</em> and the
    users will be able to login via Facebook.</p>
<p>You will need to go to the 
    <a href="<?php echo get_url('plugin/facebook/settings'); ?>" title="Settings Page">Settings Page</a>
    and fill in your Facebook Application details.
    <br />
    If you have not created a Facebook Application yet, you will need to do so at
    <a href="http://www.facebook.com/developers/createapp.php" title="Create a Facebook App">create
    a Facebook App</a>.
</p>
<br />
<p>This project is hosted and documented at <a href="http://github.com/amereservant/Wolf-CMS-Facebook-Plugin" title="Facebook Plugin hosted at GitHub">Facebook Plugin &amp; GitHub</a>.
</p>
