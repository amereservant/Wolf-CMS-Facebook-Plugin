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
        // Get form array keys so all keys will be set
        $data = array_flip(FacebookConnect::$new_user_form_keys);
        
        // Remove all values ( They will be the numerical array keys as the values)
        foreach($data as $key => $val)
        {
            $data[$key] = null;
        }
        
        if(isset($_POST['fb_new_id'], $_POST['local_new_use'], $_POST['fb_commit']))
        {
            $result = FacebookConnect::add_new_user($_POST);
            if( !$result['error'] )
            {
                echo 'SUCCESS';
                sleep(1);
                Flash::set('success', __('Your settings have been successfully applied!'));
                redirect(URL_PUBLIC);
            }
        }
        
        $user = FacebookConnect::get_user_info();
        
        $data['checked']            = $this->checked;
        $data['selected']           = $this->selected;
        $data['fb_new_full_name']   = $user['name'];
        $data['fb_new_id']          = $user['id'];
        $data['fb_new_first_name']  = $user['first_name'];
        $data['fb_new_last_name']   = $user['last_name'];
        $data['fb_new_link']        = $user['link'];
        $data['fb_new_gender']      = $user['gender'];
        echo '<pre>' . print_r($result, true) . '</pre>';
        if( isset($_POST['fb_new_id'], $_POST['local_new_use'], $_POST['fb_commit']) )
        {
            $data = array_merge($data, $_POST);
           
            if( $result['error'] )
            {
                Flash::set('error', __($result['msg']));
            }
        }
        
        $this->display('../../plugins/facebook/views/public/new_user_form', $data);
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
