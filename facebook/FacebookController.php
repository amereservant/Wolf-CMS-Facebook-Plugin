<?php
/**
 * Facebook Controller Class
 *
 */
class FacebookController extends PluginController
{
   /**
    * The Database PDO instance
    */
    protected static $__CMS_CONN__;
    
   /**
    * Plugin Path
    */
    protected static $plugin_path;
    
   /**
    * Facebook Instance Object
    */
    protected static $fb_instance;
    
    public $selected = ' selected="selected"';
    
    public $checked = ' checked="checked"';
    
    public function __construct()
    {
        self::$plugin_path = PLUGINS_ROOT . '/facebook';
        
        if( !is_object( self::$__CMS_CONN__ ) )
        {
            self::$__CMS_CONN__ = Record::getConnection();
        }
        
        AuthUser::load();
        
        if( defined('CMS_BACKEND') )
        {
            $this->setLayout('backend');
            $this->assignToLayout('sidebar', new View('../../plugins/facebook/views/sidebar'));
        }
        else
        {
            $page       = $this->findByUri();
            $layout_id  = $this->getLayoutId($page);
            $layout     = Layout::findById($layout_id);
            $this->setLayout($layout->name);
        }
    }
    
   /**
    * Default Plugin View
    */
    public function index()
    {
        $this->display('facebook/views/index');
    }
    
   /**
    * Display Settings View
    *
    * See http://www.wolfcms.org/wiki/tutorial:settings_page#adding_the_settings_function 
    * on passing variables.
    */
    public function settings()
    {
        $settings             = Plugin::getAllSettings('facebook');
        $settings['selected'] = $this->selected;
        $settings['checked']  = $this->checked;
        
        $this->display('facebook/views/settings', $settings);
    }
    
   /**
    * Update Settings
    */
    public function update_settings()
    {
        if( Plugin::setAllSettings($_POST, 'facebook') )
        {
            Flash::set('success', __('Facebook Settings have been updated'));
            redirect(get_url('plugin/facebook/settings'));
        }
        else
        {
            Flash::set('error', __('Facebook Settings update failed'));
        }
    }
    
    public function fblogout()
    {
        FacebookConnect::user_logout();
    }
    
    public function new_user_page()
    {
        if(isset($_POST))
        {
            $result = $this->validate_new_user_data();
            if( $result['error'] )
            {
                Flash::set('error', __($result['msg']));
            }
            else
            {
                Flash::set('success', __('Your settings have been successfully applied!'));
                redirect(URL_PUBLIC);
            }
        }
        
        $data             = FacebookConnect::get_user_info();
        $data['checked']  = $this->checked;
        $data['selected'] = $this->selected;
        $this->display('../../plugins/facebook/views/public/new_user_form', $data);
    }
    
    private function validate_new_user_data()
    {
        // Specify all keys that should be in the new_user_form $_POST data.
        // (Used for verification)
        $new_user_form_keys = array (
            'fb_new_id', 'fb_new_full_name', 'fb_new_first_name', 'fb_new_last_name', 
            'fb_new_gender', 'fb_new_link', 'local_new_use', 'local_new_user_email',
            'local_new_username', 'local_new_password', 'local_new_password_confirm',
            'existing_user_use', 'existing_user_username', 'existing_user_email',
            'existing_user_password','fb_commit'
         );
        
        // Specify required fields and their user-friendly names
        $new_user_required_keys = array (
            'fb_new_id'         => 'Facebook User ID', 
            'fb_new_first_name' => 'First Name',
            'local_new_use'     => 'Use Section User Info',
            'existing_user_use' => 'Use Section Existing Local Account'
        );
        
        /**
         * Check if ONLY and ALL of the correct keys are present in the $_POST data
         * array_keys_match() function is defined in index.php.
         * Perhaps add 'write-data-to-file-on-fail' to this part so user data isn't lost.
         */
        if( !array_keys_match($_POST, array_flip($new_user_form_keys)) )
        {
            $return['error'] = true;
            $return['msg']   = "Submitted data doesn't appear to be valid. ";
            $return['msg']  .= "If the problem persists, please notify an administrator.";
            return $return;
        }
        
        $empties   = array();
        $are_empties    = false;
        // Check required fields aren't empty
        foreach($new_user_required_keys as $key => $val)
        {
            if( empty($_POST[$key]) )
            {
                $empties[] = "'$val'";
                $are_empties = true;
            }
        }
        
        if( $are_empties ) 
        {  
            $return['error'] = true;
            $return['msg']   = "The fields " . implode(", ", $empties) .
                " cannot be empty!  Please correct these and try again.";
            return $return;
        }
        
        // Check if passwords match
        if( $_POST['password'] !== $_POST['password_confirm'] )
        {
            $return['error'] = true;
            $return['msg']   = "Passwords do not match!";
            return $return;
        }
        return $return['error'] = false;
    }    
        
    private function getLayoutId($page) {
		if ($page->layout_id){
		    return $page->layout_id;
		} else if ($page->parent) {
				return $this->getLayoutId($page->parent);
		}else {
				exit ('This page is not valid...');
	    }
	}

	public function content($part=false, $inherit=false) {
		if (!$part)
			return $this->content;
		else
			return false;
	}
}
