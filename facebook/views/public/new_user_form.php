<?php echo '<pre>' . var_export($_POST, true) . '</pre>'; 
$req = array();
?>
<p style="font-weight:bold">Welcome <?php echo $first_name; ?>!</p>
<p>Since this is your first time logging in via Facebook, we need to verify a
    few details right quick.<br />
    This is a one-time thing, so you won't see this again.
</p>
<p>The fields below are all optional and therfore you can simply click "save"
    and just use the default values, however, this limits your
    ability to logon to this site using only Facebook.<br />
    In addition to that, if you do not provide an email, you will not be able
    to later change your information via this site nor will you be able to be
    notified of any events, recieve system notices, etc.
</p>
<hr />
<form action="<?php echo get_url('new_user').URL_SUFFIX; ?>" method="post" id="fb_new_user_form"> 
    <h3>Facebook Information</h3>
    <p>This information was automatically retrieved from Facebook.</p>
    <p>
        <input type="hidden" name="fb_new_id" value="<?php echo $id; ?>" />
        <input type="text" name="fb_new_full_name" id="fb_new_full_name" value="<?php echo $name; ?>" size="22" /> 
        <label for="fb_new_full_name"> Full Name </label>
    </p>
    <p>
        <input type="text" name="fb_new_first_name" id="fb_new_first_name" value="<?php echo $first_name; ?>" size="22" />
        <label for="fb_new_first_name"> First Name <em>(required)</em></label>
    </p>
    <p>
        <input type="text" name="fb_new_last_name" id="fb_new_last_name" value="<?php echo $last_name; ?>" size="22" />
        <label for="fb_new_last_name"> Last Name <em>(required)</em></label>
    </p>
    <p>
        <select name="fb_new_gender" id="fb_new_gender">
            <option value="male"<?php echo($gender === 'male' ? $selected:''); ?>>Male</option>
            <option value="female"<?php echo($gender === 'female' ? $selected:''); ?>>Female</option>
            <option value=""<?php echo(empty($gender) ? $selected:''); ?>><em>Not Specified</em></option>
        </select>
        <label for="fb_new_gender"> Gender </label>
    </p>
    <p>
        <input type="text" name="fb_new_link" id="fb_new_link" value="<?php echo $link; ?>" size="22" />
        <label for="fb_new_link"> Facebook Profile Link </label>
    </p>
    <hr />
    <h3>User Information</h3>
    <p>This information only pertains to this website.<br />
        If you already have an account here, you can skip this and link to it in the next section.
    </p>
    <p>
        <label>Use this section? <em>(If you specify an email here, it will still be used regardless of this setting.)</em></label>
        <br />
        <input type="radio" name="local_new_use" value="1" /> True&nbsp;&nbsp;&nbsp;
        <input type="radio" name="local_new_use" value="0"<?php echo $checked; ?> /> False
    </p>
    <p>
        <input type="text" name="local_new_user_email" id="local_new_user_email" value="" size="22" /> 
        <label for="local_new_user_email"> Email <em>(Will not be published.  Optional)</em></label>
    </p>
    <p>
        <input type="text" name="local_new_username" id="local_new_username" value="" size="22" /> 
	    <label for="local_new_username"> Username <em>(Used for this site only. Optional)</em></label>
    </p>
    <p>
	    <input type="password" name="local_new_password" id="local_new_password" value="" size="22" />
	    <label for="local_new_password"> Password <em>(Allows login without Facebook Account.)</em></label>
    </p>
    <p>
	    <input type="password" name="local_new_password_confirm" id="local_new_password_confirm" value="" size="22" />
	    <label for="local_new_password_confirm"> Password Confirm <em>(Confirm password.)</em></label>
    </p>
    <hr />
    <h3>Existing Local Account</h3>
    <p>If you have an existing account with this website, you can use this section to link it
        to your Facebook account.<br />
        You may specify either your username or email, but you must specify one if using this section.
    </p>
    <p>
        <label>Use this section?</label>
        <br />
        <input type="radio" name="existing_user_use" value="1" /> True&nbsp;&nbsp;&nbsp;
        <input type="radio" name="existing_user_use" value="0"<?php echo $checked; ?> /> False
    </p>
    <p>
        <input type="text" name="existing_user_username" id="existing_user_username" size="22" />
        <label for="existing_user_username"> Existing Username</label>
    </p>
    <p>
        <input type="text" name="existing_user_email" id="existing_user_email" size="22" />
        <label for="existing_user_email"> Existing Email Address</label>
    </p>
    <p>
	    <input type="password" name="existing_user_password" id="existing_user_password" value="" size="22" />
	    <label for="existing_user_password"> Account Password</label>
    </p>
    <hr />
    <p>
	    <input type="submit" name="fb_commit" id="fb_commit" value="Save" />
    </p>
</form>
