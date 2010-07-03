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
    * Facebook Instance Object
    */
    protected static $fb_instance;
   
   /**
    * Selected syntax for new_user form
    */ 
    public $selected = ' selected="selected"';
    
   /**
    * Checked syntax for new_user form
    */
    public $checked = ' checked="checked"';
    
    
    public function __construct()
    {
        // Check if $__CMS_CONN__ is an object (PDO)
        if( !is_object( self::$__CMS_CONN__ ) )
        {
            self::$__CMS_CONN__ = Record::getConnection();
        }
        // Load user authorization
        AuthUser::load();
        
        // Check if this is a Backend view
        if( defined('CMS_BACKEND') )
        {
            // Set backend view
            $this->setLayout('backend');
            $this->assignToLayout('sidebar', new View('../../plugins/facebook/views/sidebar'));
        }
        else
        {
            // Set front-end view
            $page       = $this->findByUri();
            $layout_id  = $this->getLayoutId($page);
            $layout     = Layout::findById($layout_id);
            $this->setLayout($layout->name);
        }
    }
    
   /**
    * Default Plugin View - ( Back-end view )
    */
    public function index()
    {
        $this->display('facebook/views/index');
    }
    
   /**
    * Documentation View - ( Back-end view )
    */
    public function documentation() {
		$this->display('facebook/views/documentation');
	}
    
   /**
    * Display Settings View - ( Back-end view )
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
    * Update Settings - ( Back-end method, used to update posted settings changes )
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
    
   /**
    * Log User Out
    *
    * This is called by the Dispatcher in {@link index.php} after the user logs
    * out of Facebook on this website.
    *
    * The logout callback is defined in the {@link FacebookConnect::getLogoutUrl()}
    * method.
    */
    public function fblogout()
    {
        FacebookConnect::user_logout();
    }
    
   /**
    * New User Page
    *
    * This is called by the Dispatcher in the {@link index.php} file when a user
    * logs in with Facebook and doesn't already have a local account.
    * This is disabled by setting the `Allow Facebook Connect` option in the settings
    * to `No`.
    */
    public function new_user_page()
    {
        // Get FacebookConnect class instance
        $facebook = FacebookConnect::get_instance();

        // Get form array keys so all keys will be set
        $data = array_flip(FacebookConnect::$new_user_form_keys);
        
        // Remove all values ( They will be the numerical array keys as the values)
        foreach($data as $key => $val)
        {
            $data[$key] = null;
        }
        
        // Check a few of the $_POST keys for a submitted form
        if(isset($_POST['fb_new_id'], $_POST['local_new_use'], $_POST['fb_commit']))
        {
            // Try adding the new user
            $result = FacebookConnect::add_new_user($_POST);
            if( !$result['error'] )
            {
                echo 'SUCCESS';
                Flash::set('success', __('Your settings have been successfully applied!'));
                // Redirect to Home page on success
                redirect(URL_PUBLIC);
            }
        }
        
        $user = FacebookConnect::get_user_info();
        
        // Check if user already exists
        if( $facebook->check_user_exists($user['id']) )
        {
            // Check if account is already linked to a Wolf account
            if( !empty(FacebookConnect::$wolf_uid) )
            {
                // Redirect them if so
                redirect(URL_PUBLIC);
            }
        }
        // Form array of data to pass to the `new_user_form` view
        $data['checked']            = $this->checked;
        $data['selected']           = $this->selected;
        $data['fb_new_full_name']   = $user['name'];
        $data['fb_new_id']          = $user['id'];
        $data['fb_new_first_name']  = $user['first_name'];
        $data['fb_new_last_name']   = $user['last_name'];
        $data['fb_new_link']        = $user['link'];
        $data['fb_new_gender']      = $user['gender'];
        
        // Merge Posted data so if the submitted form fails, the values are filled in
        if( isset($_POST['fb_new_id'], $_POST['local_new_use'], $_POST['fb_commit']) )
        {
            $data = array_merge($data, $_POST);
           
            if( $result['error'] )
            {
                Flash::set('error', __($result['msg']));
            }
        }
        // Display the new_user_form view
        $this->display('../../plugins/facebook/views/public/new_user_form', $data);
    }
    
   /**
    * Used by the {@link __construct()} method for front view
    */
    private function getLayoutId($page) 
    {
		if ($page->layout_id)
		{
		    return $page->layout_id;
		} 
		else if ($page->parent) 
		{
		    return $this->getLayoutId($page->parent);
		}
		else 
		{
		    exit('This page is not valid...');
	    }
	}

   /**
    * Used by the {@link __construct()} method for front view
    */
	public function content($part=false, $inherit=false)
	{
	    if( !$part )
	    {
			return $this->content;
		}
		else
		{
			return false;
	    }
	}

   /**
    * Testing view
    *
    * Used for testing/debugging the `enable.php` script.
    * Since enable.php is called via an AJAX request, it is easier to debug and test
    * by creating a direct view.
    * To use, go to the admin section and visit .../admin/plugin/facebook/testing/
    */
	public function testing()
	{
	    require FB_PLUGIN_ROOT . '/enable.php';
	    exit();
	}
}
