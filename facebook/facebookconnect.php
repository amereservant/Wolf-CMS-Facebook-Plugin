<?php
/**
 * Facebook Plugin Class
 *
 * This is the class that integrates the Facebook SDK with WolfCMS.
 * 
 * It is used to add Facebook login capabilities to the Wolf CMS and integrates
 * with the Wolf core user system.
 * It can be expanded to allow user information retrieval and further integration
 * within a Wolf CMS project.  It is planned in future versions to have these
 * options already integrated as optional functionality.
 *
 * @package     Facebook Plugin
 * @link        http://github.com/amereservant/Wolf-CMS-Facebook-Plugin Facebook Plugin @ GitHub
 * @author      David Miles <david@amereservant.com>
 * @copyright   2010 David Miles
 * @version     1.0
 */
class FacebookConnect extends Facebook
{
   /**
    * @staticvar    obj         Instance of FacebookConnect
    * @access       protected
    */
    protected static $fb_instance;
    
   /**
    * @staticvar    int         Facebook User ID
    * @access       public
    */
    public static $uid;
   
   /**
    * @staticvar    int         Wolf user ID
    * @access       public
    */
    public static $wolf_uid;
    
   /**
    * @staticvar    obj         PDO object
    * @access       protected
    */
    protected static $pdo;
    
   /**
    * @staticvar    string      printf formatted string for PDO error messages
    * @access       private
    */
    private static $pdo_error = ' PDO ERROR: %3$s';
    
   /**
    * @staticvar    int         Integer based on the Error Mode constants for PDO.
    *                           See {@link http://php.net/manual/pdo.constants.php}
    * @access       private
    */
    private static $pdo_error_mode;
    
   /**
    * @staticvar    array       Array containing last database insert ID and row count
    * @access       protected
    */
    protected static $last_insert;
    
   /**
    * @staticvar    bool        Determines if the `new_user_page` should be displayed
    *                           for new users.
    * @access       public
    */
    public static $user_configures_acct;
    
   /**
    * @staticvar    bool        Whether or not Facebook Logins are enabled.
    * @access       public
    */
    public static $enabled;
    
   /**
    * @staticvar    array       Facebook user session data.
    * @access       public
    */
    public static $usr_session;
    
   /**
    * @staticvar    array
    * @access       public
    * @TODO         Compare this value/usage to {@link $usr_session}.
    *               This may be a duplicate that can be removed.
    */
    public static $user_info;
    
   /**
    * @staticvar    array       All of the new user form keys.
    * @access       public
    */
    public static $new_user_form_keys = array (
        'fb_new_id', 'fb_new_full_name', 'fb_new_first_name', 'fb_new_last_name', 
        'fb_new_gender', 'fb_new_link', 'local_new_use', 'local_new_user_email',
        'local_new_username', 'local_new_password', 'local_new_password_confirm',
        'existing_user_use', 'existing_user_username', 'existing_user_email',
        'existing_user_password','fb_commit');
    
   /**
    * Class Constructor
    *
    * Constructs the parent class and passes an array of initialization values
    * to the parent class.
    *
    * See {@link parent::__construct()} for more information.
    * @param    array
    * @access   public
    */
    public function __construct($array)
    {
        parent::__construct($array);
    }
   
   /**
    * Get FacebookConnect Class Object
    *
    * This method determines if an instance already exists in the {@link $fb_instance}
    * property and if not, it creates one.
    * It also passes the plugin's settings to the parent class and istantiates it.
    *
    * @param    void
    * @return   object      FacebookConnect Class Instance
    * @static
    * @access   public
    */
    public static function get_instance()
    {
        if( !is_object(self::$fb_instance) )
        {
            // Get plugin's settings from the database
            $settings = Plugin::getAllSettings('facebook');
            
            self::$fb_instance = new FacebookConnect( array(
                'appId'     => $settings['fb_api_key'],
                'secret'    => $settings['fb_application_secret'],
                'cookie'    => $settings['fb_use_cookies']
            ));
            
            // Set class static properties from settings
            self::$enabled              = (bool) $settings['allow_fb_connect'];
            self::$user_configures_acct = (bool) $settings['user_sets_wolf_acc'];
        }
        return self::$fb_instance;
    }

   /**
    * Facebook Login/Logout Image & URL
    *
    * This is used to validate the user's session on pages it appears on and
    * will return either a logout URL & image OR login URL & image based on the
    * user's determined session status.
    *
    * If a user needs to be validated and the image is not neccessary, see the
    * {@link is_logged_in()} method.
    *
    * This method returns the array from {@link is_logged_in()} method if facebook  
    *   login isn't enabled, else an array with the following values:
    * 
    *   $return['link']      = Either the login URL or logout URL for an anchor link
    *   $return['image']     = Either the login or logout image
    *   $return['logged_in'] = (bool) false if user isn't logged in, true otherwise
    *
    * @param    void
    * @return   array       See the description for details
    * @static
    * @access   public
    */    
    public static function fb_login()
    {
        // Get FacebookConnect object
        $facebook = self::get_instance();
        
        // Check if Facebook Login is enabled
        if( !self::$enabled )
        {
            return false;
        }
        
        // Check if user is logged in
        $status = self::is_logged_in();

        // Check for an error ( means facebook login is disabled ) and return results
        if( $status['error'] === true )
        {
            return $status;
        }
        
        $logged_in = $status['logged_in'];
        
        if( $session = self::$usr_session )
        {
            // Refresh to remove the query string from the URL
            if($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^session=(.*)#', 
                $_SERVER['QUERY_STRING']))
            {
                redirect(URL_PUBLIC);
            }
        }

        // Make sure a UserID has been set
        if( self::$uid )
        {
            /*
             * See if user is a new Facebook user to this site and if so, 
             * redirect them to the new user page if 'User Configures Account' is
             * set to true.
             */
            if( !$facebook->check_user_exists(self::$uid) )
            {
                if( self::$user_configures_acct )
                {
                    // Send user to new user account details view
                    redirect(get_url('new_user') . URL_SUFFIX);
                }
                else
                {
                    // Add user to Local login system
                    self::add_new_user();
                }
            }
        }

        // If user isn't logged in ...
        if(!$logged_in) 
        {
            // Clear ALL session data ( clears cookies & PHP session )
            $facebook->setSession(null, $facebook->useCookieSupport());
            $return['link']      = $facebook->getLoginUrl();
            $return['image']     = FB_URL_ROOT . 'images/connect.png';
            $return['logged_in'] = false;
            return $return;
        }
        else
        {
            $return['link']      = $facebook->getLogoutUrl();
            $return['image']     = FB_URL_ROOT . 'images/logout.png';
            $return['logged_in'] = true;
            return $return;
        }
    }
   
   /**
    * Check if current user is logged in
    *
    * This method is used to check if a Facebook is or isn't logged in.
    * If the parameter $check_facebook is 'true', then a call is made to Facebook
    * to validate the user's login status with Facebook.  
    * Otherwise, session/cookie data is used if it hasn't expired.
    *
    * $check_facebook should only be set to 'true' only if needed since this will
    * add to the page load time due to the delay making the query.
    *
    * Returns an array with the following keys:
    *   
    *       $return['error']     = (bool) If an error was found, such as Facebook
    *                                     login isn't enabled.
    *       $return['logged_in'] = (bool) Whether or not the user is logged in.
    *
    * @param    bool    $check_facebook     Determines if the user's login session
    *                                       should be validated with the Facebook
    *                                       servers.
    * @return   array   See notes above for details.
    * @static
    * @access   public
    */
    public static function is_logged_in($check_facebook=false)
    {
        // Get FacebookConnect object
        $facebook = self::get_instance();

        // Check if Facebook login is enabled
        if( !self::$enabled ) 
        {
            $return['error']     = true;
            $return['logged_in'] = false;
            return $return;
        }
        // Get user session details
        $session           = $facebook->getSession();
        self::$usr_session = $session;

        $logged_in = false;
        
        if($session)
        {
            // Get UserID
            self::$uid = $facebook->getUser();
            
            // Check if the user's session has expired
            $logged_in = !$facebook->check_expired_session($session['expires']);
            
            if( $check_facebook === true )
            {
                // Make Facebook API call to validate user
                try 
                {
                    $me = $facebook->api('/me');
                } catch (FacebookApiException $e) { /* Do something here if you like */ }
                
                // If $me doesn't return false, it will return JSON data, 
                // so we type cast as boolean
                $logged_in = (bool) $me;
            }
        }
        
        if( !$logged_in ) 
        { 
            $return['error']     = false;
            $return['logged_in'] = false;
            return $return; 
        } 
        else 
        { 
            if( !AuthUser::isLoggedIn() )
            {
                $id = self::db_select_one('facebook_users', "uid='". self::$uid ."'", 'wolf_uid');
                if( $id ) { $facebook->login_wolf_user($id['wolf_uid']); }
            }
            $return['error']     = false;
            $return['logged_in'] = true;
            return $return; 
        }
    }

   /**
    * Check if session is expired
    *
    * This is used to check if the cookie or session's expires time has expired,
    * which is used to validate the user's session.
    * If it is invalid, the session or cookie will be destroyed.
    *
    * @param    int         $expires    UNIX timestamp for when the session expires.
    * @return   bool                    True if the cookie has expired, false otherwise
    * @access   protected
    */
    protected function check_expired_session($expires)
    {
        if( $expires < time() )
        {
            // Destory session 
            $this->setSession(null, $this->useCookieSupport());
            // Log the user out
            self::user_logout();
            return true;
        }
        else
        {
            return false;
        }
    }
   
   /**
    * Get Cookie/Session Expiration Time
    *
    * This returns the cookie expiration time based on the session data from
    * Facebook.  The user's Wolf account cookie will be set to this instead of
    * the value defined in the AuthUser class.
    *
    * @param    void
    * @return   mixed       (int)UNIX timestamp for when the cookie expires,
    *                       (bool) false on failure
    * @access   private
    */
    private function get_cookie_expiration_time()
    {
        $session = $this->getSession();
        if( !is_array($session) ) 
        { 
            return false;
        }
        else
        {
            return $session['expires'];
        }
    }
    
   /**
    * Get User Info
    *
    * This retrieves a user's Facebook profile details.
    *
    * @param    bool    $always_make_remote_call    If set to true, the user's info
    *                                               will always be retrieved from
    *                                               Facebook.  Otherwise, local
    *                                               data or previously set data will
    *                                               be used.
    * @return   mixed       (bool)  false if user isn't logged in or if user's details
    *                               couldn't be retrieved.
    *                       (array) User's profile information if successful.
    * @static
    * @access   public
    */
    public static function get_user_info( $always_make_remote_call = false )
    {
        // Check if user is logged in
        $logged_in = self::is_logged_in();
        if( $logged_in['error'] === true || $logged_in['logged_in'] === false )
        {
            return false;
        }

        // Return data in the $user_info property if remote call isn't required
        if( self::$user_info && !$always_make_remote_call )
        {
            return self::$user_info;
        }
        
        $facebook = self::get_instance();
        self::$usr_session = $facebook->getSession();

        // Session based API call
        if( self::$usr_session )
        {
            // Try making remote call for user info
            try 
            {
                self::$user_info = $facebook->api('/me');
            } catch (FacebookApiException $e) { /* Do something here if you like */ }
        }
        
        if( !self::$user_info || !self::$usr_session )
        {
            return false;
        }
        else
        {
            return self::$user_info;
        }
    }
    
   /**
    * Check if user exists in database
    *
    * This method checks if the user has already exists in the 'facebook_users' table
    * or the Wolf `user` table.  
    *
    * @param    string      $user       If testing a facebook user, this will be the
    *                                   user id, for a wolf user, it will be the username
    * @param    string      $usertbl    Either 'facebook' or 'wolf' to determine
    *                                   which table should be checked.
    * @return   bool                    true if they do exist, false otherwise
    * @access   public
    */
    public function check_user_exists($user, $usertbl='facebook')
    {
        self::get_db_instance();
        
        // Determine which table we're checking
        $table = ($usertbl === 'facebook' ? 'facebook_users':'user');
        
        // Determine the correct "where" statement
        $where = ($usertbl === 'facebook' ? "uid='{$user}'" : "username='{$user}'");
        
        if( !$result = self::db_select_one($table, $where) ) 
        {
            return false;
        } 
        else 
        {
            if( $usertbl === 'facebook' ) 
            { 
                self::$uid = $result['uid'];
                self::$wolf_uid = $result['wolf_uid']; 
            }
            return true;
        }
    }
    
   /**
    * Add New User
    *
    * This method is called by the Facebook Controller to add a new user.
    * This method will use {@link add_facebook_user()} and {@link add_user_to_wolf()}
    * to add the new user to both the `facebook_users` table and the Wolf `user` table.
    *
    * @param    array   $post   New user $_POST data if {@link $user_configures_acct}
    *                           is set to `true`, otherwise this should be left 
    *                           NULL and the {@link $user_info} property will be used.
    * @return   array
    * @access   public
    * @static
    */
    public static function add_new_user($post=NULL)
    {
        $facebook = self::get_instance();
        
        // Get user's Facebook account info
        if( !$info = self::get_user_info() )
        {
            throw new Exception("Facebook user information couldn't be retrieved.");
        }
        
        // Define part of the new Wolf user information
        $user['created_on']     = date('Y-m-d H:i:s');
        $user['created_by_id']  = 1;
        $user['language']       = 'en';
        $user['email']          = '';
        
        // Validate user data if it is a $_POST array and new users configure accounts
        if( is_array($post) && self::$user_configures_acct )
        {
            // Validate $_POST array
            $valid = $facebook->validate_new_user_data($post);
            if( $valid['error'] === true )
            {
                return $valid;
            }
            $user['name'] = ( !empty($post['fb_new_full_name']) ? $post['fb_new_full_name']:
                                $post['fb_new_first_name'] );
                
            // Determine if using an existing account or not
            if( $post['existing_user_use'] === '1' )
            {
                // Check user login
                if( !$result = $facebook->verify_wolf_user(array( 
                    'username' => $post['existing_user_username'],
                    'password' => $post['existing_user_password'])) )
                {
                    return array( 'error' => true,
                                  'msg'   => "Your user account could not be verified!" . 
                                             " Check your password and username.");
                }
                $fbuser['wolf_uid'] = $result['id'];
                $fbuser['wolf_username'] = $result['username'];
            }
            
            // Use new User account section
            elseif( $post['local_new_use'] === '1' )
            {
                // Define user's details for Wolf user table
                $user['password']  = ( !empty($post['local_new_password']) ? 
                                        $post['local_new_password']:self::generate_password());
                $user['username']  = $post['local_new_username'];
            }
            // User doesn't want to specify account info
            else
            {
                $user['password']   = self::generate_password();
                $username           = mb_strtolower( str_replace(" ", "_", trim($info['name'])) );
                $user['username']   = $username . mt_rand(1000, 999999);
            }
            
            // Used to verify user doesn't already exist in `facebook_users` table
            $user['uid']   = $post['fb_new_id'];
            $user['email'] = ( !empty($post['local_new_user_email']) ? 
                                $post['local_new_user_email']:'');
                
            // Not using an existing account
            if( $post['existing_user_use'] === '0' )
            {
                // Check if Wolf user already exists
                if( $facebook->check_user_exists($post['local_new_username'], 'wolf') )
                {
                    return array( 'error' => true,
                                  'msg'   => "The username `{$post['local_new_username']}` " . 
                                             "already exists! Please choose another username " .
                                             " or use the Existing User section.");
                }
            }
        } /* End of $post is array */
        // If user configures account, $post will be an array since it requires them
        // submitting the user details form.
        elseif( self::$user_configures_acct )
        {
            try
            {
                throw new Exception("`add_new_user` was called without being provided" . 
                    " a POST array while `\$user_configures_acct` is set to TRUE.");
            }
            catch(Exception $e)
            {
                self::handleException($e);
                return array( 'error' => true,
                              'msg'   => "An error in the code was detected and your" . 
                                         " account cannot be created at this time. " . 
                                         "Please notify a system administrator of this error.");
            }
        }
        // User doesn't configure account, so $post won't be an array
        elseif( !self::$user_configures_acct )
        {
            // Generate user data fields - Facebook-only logins will be used for new users
            $user['uid']      = $info['id'];
            $user['name']     = $info['name'];
            $username         = mb_strtolower( str_replace(" ", "_", trim($info['name'])) );
            $user['username'] = $username . mt_rand(1000, 999999);
            $user['password'] = self::generate_password();
        }
           
        // Try to add user to Wolf user table
        if( !is_array($post) || isset($post['existing_user_use']) && 
            $post['existing_user_use'] === '0' )
        {
            if( !$facebook->add_user_to_wolf($user) )
            {
                return array( 'error' => true,
                              'msg'   => "There was an error creating your account.  " . 
                                         "Please notify a system administrator of this error.");
            }
        }
        
        // Create Facebook user array
        $fbuser['uid']          = $user['uid'];
        $fbuser['wolf_uid']     = (isset($fbuser['wolf_uid']) ? $fbuser['wolf_uid'] : 
                                    self::$last_insert['id']);
        $fbuser['wolf_username']= (isset($fbuser['wolf_username']) ? $fbuser['wolf_username'] :
                                    $user['username']);
        $fbuser['name']         = $user['name'];
        $fbuser['first_name']   = (isset($post['fb_new_first_name']) ? $post['fb_new_first_name']:
                                    $info['first_name']);
        $fbuser['last_name']    = (isset($post['fb_new_last_name']) ? $post['fb_new_last_name']:
                                    $info['last_name']);
        $fbuser['link']         = (isset($post['fb_new_link']) ? $post['fb_new_link']:
                                    $info['link']);
        $fbuser['gender']       = (isset($post['fb_new_gender']) ? $post['fb_new_gender']:
                                    $info['gender']);
        
        // Try to add user to the Facebook database table
        try
        {
            if( !$facebook->add_facebook_user($fbuser) )
            {
                $return = array( 'error' => true,
                              'msg'   => "There was an error creating your account. " . 
                                         "Please notify a system administrator of this error.");
                throw new Exception("User's wolf account was successfully created, but" . 
                    " their Facebook account failed.  UID: {$fbuser['uid']} WOLFUID: " . 
                    "{$fbuser['wolf_uid']} NAME: {$fbuser['name']}");
            }
        }
        catch(Exception $e)
        {
            self::handleException($e);
            return $return;
        }
        // Return error false on success
        return array( 'error' => false );
    }
        
   /**
    * Add New User To Facebook Database Table
    *
    * This method adds the user to the `facebook_users` table if they don't already
    * exist.
    *
    * @param    void
    * @return   bool        true on success, false on failure
    * @access   protected
    */
    protected function add_facebook_user($user)
    {
        try 
        {
            // Check if $user is valid (Partial check)
            if( !is_array($user) || ( !isset($user['uid']) && !isset($user['id'])) )
            {
                throw new Exception("Invalid user array as param for `add_facebook_user` method.");
            }
            
            $user['uid'] = ( isset($user['uid']) ? $user['uid'] : $user['id'] );
            
            // Check if user already exists
            if( $this->check_user_exists($user['uid']) )
            {
                throw new Exception("Facebook user `{$user['name']}` with UID " .
                    "`{$user['uid']}` already exists in the Facebook user table!");
            }
            
            // Specify which keys we need for the database
            $fields = array('uid', 'wolf_uid', 'wolf_username', 'name', 'first_name', 'last_name', 'link', 'gender');
            
            /**
             * Loop over the user info array and remove any values we don't need
             * This also makes sure the keys are set and throws an exception if one isn't.
             */
            foreach( array_diff_key($user, array_flip($fields)) as $key => $blah )
            {
                if( !isset($user[$key]) )
                {
                    throw new Exception("The \$user array is missing key `{$key}`! " . 
                        "New Facebook user account failed.");
                }
                unset($user[$key]);
            }
        }
        catch(Exception $e)
        {
            self::handleException($e);
            return false;
        }
        
        // Try to insert user into the database
        if( !self::db_insert('facebook_users', $user) ) 
        { 
            return false; 
        }
        else
        {
            return true;
        }
    }
    
   /**
    * Add User To Wolf User Table
    *
    * This method is used to add a new user to the Wolf `user` database table
    * so the user's Facebook account will integrate with a Wolf CMS account.
    *
    * @param    array       $user   The user's Facebook information. {@link $user_info}
    * @return   bool                true if success, false if failure
    * @access   protected
    * @throws   Exception
    */
    protected function add_user_to_wolf($user)
    {
        // Verify param is valid, throw exception if not
        // ( Every field isn't checked, just some of the ones that should be present )
        if( !is_array($user) || !isset($user['uid'], $user['username'], $user['password']) )
        {
            try 
            {
                throw new Exception("Method `add_user_to_wolf` was provided an invalid" . 
                    " user data array.");
            }
            catch(Exception $e)
            {
                self::handleException($e);
                return false;
            }
        }
        
        try 
        {
            // Check if user already has a Facebook account ( If so, this should've never been called )
            if( $row = self::db_select_one('facebook_users', "uid='{$user['uid']}'") )
            {
                throw new Exception("User `{$user['name']}` with UID `{$user['uid']}` " .
                    "already exists in the `facebook_users` table.");
            }
            
            // Check if Wolf user already exists
            if( $row = self::db_select_one('user', "username='{$user['username']}'") )
            {
                throw new Exception("User `{$user['username']}` already exists! " . 
                    "`add_user_to_wolf()` shouldn't have been called.");
            }
            
            // Encode user password using SHA1
            $user['password'] = sha1($user['password']);
            
            // Remove values irrelevant to the Wolf user table
            unset($user['uid']);
            
            // Try adding new user to Wolf user table
            if( !self::db_insert('user', $user) )
            {
                throw new Exception(" Add user to Wolf `users` table failed!");
            }
        }
        catch(Exception $e)
        {
            self::handleException($e);
            return false;
        }
        // If all passed, return true
        return true;
    }
    
   /**
    * Validate New User Form Data
    *
    * This is used if {@link $user_configures_acct} is set to `true` to validate
    * the data submitted by the new_user() method in the FacebookController class.
    *
    * It will return an array with an error message & error bool.
    *
    * @param    array   $post   The $_POST data array
    * @return   array           Array with keys `error`(bool) to determine if it
    *                           failed and `msg` containing error message if there
    *                           was one.
    * @access   protected
    * @throws   Exception
    */
    protected function validate_new_user_data($post)
    {
        // All keys that should be in the new_user_form $_POST data.
        // (Used for verification)
        $new_user_form_keys = self::$new_user_form_keys;
        
        // Specify required fields and their user-friendly names
        $new_user_required_keys = array (
            'fb_new_id'         => 'Facebook User ID', 
            'fb_new_first_name' => 'First Name',
            'local_new_use'     => 'Use Section User Info',
            'existing_user_use' => 'Use Section Existing Local Account'
        );
        
        // Add `Use This Section` keys to required keys if it is set to TRUE
        if( (bool)$post['local_new_use'] )
        {
            $other_keys = array (
                'local_new_user_email'       => 'Email',
                'local_new_password'         => 'Password',
                'local_new_password_confirm' => 'Password Confirmation',
                'local_new_username'         => 'Username'
            );
            $new_user_required_keys = array_merge($new_user_required_keys, $other_keys);
        }
        
        if( (bool)$post['existing_user_use'] )
        {
            // We test this here since only one is required
            if( empty($post['existing_user_username']) && empty($post['existing_user_email']) )
            {
                $return['error'] = true;
                $return['msg']   = "`Use Existing Account` is set to True, but you" . 
                    " did not specify an email address or username!";
                return $return;
            }
            
            $other_keys = array();
            if( !empty($post['existing_user_username']) )
            {
                $other_keys['existing_user_username'] = 'Existing Username';
            }
            
            if( !empty($post['existing_user_email']) )
            {
                $other_keys['existing_user_email'] = 'Existing User Email';
            }
            $other_keys['existing_user_password'] = 'Existing User Password';
        }  
                
        /**
         * Check if ONLY and ALL of the correct keys are present in the $_POST data
         * array_keys_match() function is defined in index.php.
         * Perhaps add 'write-data-to-file-on-fail' to this part so user data isn't lost.
         */
        if( !array_keys_match($post, array_flip($new_user_form_keys)) )
        {
            try
            {
                // Throw exception for developer/server admin
                throw new Exception('$_POST array keys do not match the form keys' . 
                    ' defined by the `$new_user_form_keys` variable.');
            }
            catch(Exception $e)
            {
                self::handleException($e);
            }
            $return['error'] = true;
            $return['msg']   = "Submitted data doesn't appear to be valid. ";
            $return['msg']  .= "If the problem persists, please notify an administrator.";
            return $return;
        }
        
        $empties   = array();
        $are_empties    = false;
        
        // Check if required fields are empty
        foreach($new_user_required_keys as $key => $val)
        {
            if( empty($post[$key]) && $post[$key] !== '0' )
            {
                $empties[] = "'$val'";
                $are_empties = true;
            }
        }
        
        // Form error message with all empty values
        if( $are_empties ) 
        {  
            $return['error'] = true;
            $return['msg']   = "The fields " . implode(", ", $empties) .
                " cannot be empty!  Please correct these and try again.";
            return $return;
        }
        
        // Check if passwords match
        if( $_POST['local_new_password'] !== $_POST['local_new_password_confirm'] )
        {
            $return['error'] = true;
            $return['msg']   = "Passwords do not match!";
            return $return;
        }
        return $return['error'] = false;
    }
    
   /**
    * Validate Existing Wolf User Account
    *
    * This is used to validate a Wolf user account username/password.
    * This is used during creating a new account {@link add_new_user()} when a
    * user wants to add an existing Wolf account to a new Facebook login.
    *
    * @param    array   $user   Array with username and password
    * @return   mixed           User info array on success, false if not
    * @access   private
    */
    private function verify_wolf_user($user=array())
    {
        try
        {
            if(!isset($user['password'], $user['username']) )
            {
                throw new Exception("Both `password` and `username` keys were not set!");
            }
            
            if( !$info = self::db_select_one('user', "username='{$user['username']}' AND password='" . 
                sha1($user['password']) . "'") )
            {
                throw new Exception("User `{$user['username']}` failed to provide " . 
                    "a valid login.");
            }
        }
        catch(Exception $e)
        {
            self::handleException($e);
            return false;
        }
        return $info;
    }
   
   /**
    * Log Facebook User Into Wolf Account
    *
    * This method mimicks the AuthUser model's login() method, which is where
    * user's are logged in.
    *
    * Great care should be used when using this method since a password is not
    * required and therefore should ONLY be called after the Facebook user has
    * been validated.
    *
    * TODO Change $user parameter to username instead of user id
    *
    * @param    int     Facebook user's Wolf account user id
    * @return   bool    True on successful login, False on fail
    * @access   private
    * @throws   Exception
    */
    private function login_wolf_user($user)
    {
        try
        {
            if( empty($user) )
            {
                throw new Exception("Param for `login_wolf_user` cannot be empty!");
            }
            // Attempt to find user
            $user = User::findBy('id', $user);
            
            if( !$user instanceof User )
            {
                throw new Exception("User could not be found!");
            }
        }
        catch(Exception $e)
        {
            self::handleException($e);
            return false;
        }
        
        // Add new login time
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
        
        if ( self::$fb_instance->cookieSupport ) 
        {
            $time = $this->get_cookie_expiration_time();
            
            setcookie(AuthUser::COOKIE_KEY, self::bakeUserCookie($time, $user), 
                $time, '/', null, 
                (isset($_ENV['SERVER_PROTOCOL']) && ((strpos($_ENV['SERVER_PROTOCOL'],'https') || strpos($_ENV['SERVER_PROTOCOL'],'HTTPS')))));
        }
        AuthUser::setInfos($user);
        return true;
    }
    
   /**
    * Get PDO database Instance/Object
    *
    * Determines if a database object has already been set and if not, set it and
    * returns the PDO object.
    * This also sets the {@link $pdo_error_mode}, which is used to determine how
    * to handle PDO errors.
    *
    * @param    void
    * @return   object  PDO Object
    * @access   public
    * @static
    */
    public static function get_db_instance()
    {
        if( !is_object( self::$pdo ) )
        {
            self::$pdo = Record::getConnection();
        }
        self::$pdo_error_mode = self::$pdo->getAttribute(PDO::ATTR_ERRMODE);
        return self::$pdo;
    }
   
   /**
    * Database Insert Method
    *
    * This method is used to insert data into a database.
    * This method throws an exception on error and returns false, otherwise
    * it will set the {@link $last_insert} array.
    * 
    * @param    string  $table      Table name to insert data into.
    * @param    array   $data       An array containing the column names as
    *                               the array keys and the values to insert.
    *                               This method will fail if the column names
    *                               aren't correct.
    * @return   bool                True on success, false on fail
    * @static
    * @access   public
    */
    public static function db_insert($table, $data = array())
    {
        $pdo = self::get_db_instance();
        
       /**
        * Determine if extra check measures should be taken if PDO error mode 
        * isn't Exception.  A fatal error will be thrown if we don't.
        */
        $check = self::$pdo_error_mode !== PDO::ERRMODE_EXCEPTION;
        
        if( empty($table) || count($data) < 1 )
        {
            return false;
        }
        
        // Form prepared statement with bind params
        $sql = sprintf("INSERT INTO ". TABLE_PREFIX ."%s (%s) VALUES (%s)",  
            $table,
            implode(", ", array_keys($data)),
            ":" . implode(", :", array_keys($data)));
        
        // Try to prepare statement and bind params
        try
        {
            if( (!$stmt = self::$pdo->prepare($sql)) && $check )
            {
                throw new Exception( "Insert data failed. ". 
                    vsprintf(self::$pdo_error, self::$pdo->errorInfo()) );
            }
            
            // Loop over each value and bind it
            foreach( $data as $key => &$val )
            {
                // Break loop if bindParam fails to avoid fatal errors
                if( !$stmt->bindParam(':' . $key, $val) )
                {
                    break;
                }
            }
        }
        catch(Exception $e)
        {
            self::handleException($e);
            return false;
        }
        
        // Try to execute the statement
        try
        {
            if( $stmt->execute() )
            {
                self::$last_insert['id'] = self::$pdo->lastInsertId();
                self::$last_insert['rows'] = $stmt->rowCount();
                return true;
            }
            else
            {
                // Throw new exception with the error message
                $error = "Database INSERT into `$table` failed! " . 
                    vsprintf(self::$pdo_error, self::$pdo->errorInfo());
                throw new Exception($error);
            }
        }
        catch(Exception $e)
        {
            self::handleException($e);
            return false;
        }
    }
    
   /**
    * Database Select
    *
    * This method serves as the base for the other database select methods, 
    * which is why it returns the PDOStatement object on success so the results
    * can be itterated over depending on the method used.
    *
    * Here's an example of the $where parameter:
    * <code>
    *   $where = "username='johndoe123'";
    * </code>
    *
    * @param    string      $table      The table name the query should be performed on.
    * @param    string      $where      A "WHERE" statement string.
    * @param    string      $col        A specific column to query results from.
    * @return   mixed                   (bool)false on fail, PDOStatement object on success.
    * @access   protected
    * @static
    * @TODO     Change this to use bind params instead.
    */
    protected static function db_select($table, $where=null, $col=null)
    {
        // Make sure $pdo property is set
        self::get_db_instance();
        
        // Form SQL statement using sprintf
        $sql = sprintf("SELECT %s FROM ". TABLE_PREFIX ."%s%s",
            empty($col) ? "*":$col,
            $table, 
            empty($where) ? '':" WHERE $where");
        try
        {
            $stmt = self::$pdo->prepare($sql);
        }
        catch(PDOException $e)
        {
            self::handleException($e);
            return false;
        }

        // Make sure $stmt is an object to avoid fatal warnings
        if( !is_object($stmt) )
        {
            return false;
        }
        
        $stmt->execute();
        return $stmt;
    }
    
   /**
    * Select One Row
    *
    * This is used when wanting to only retrieve one result row from the database.
    *
    * @param    string      $table      The table name the query should be performed on.
    * @param    string      $where      A "WHERE" statement string.
    * @param    string      $col        A specific column to query results from.
    * @return   mixed                   Array containing the row data on success,
    *                                   (bool) false on failure.
    * @access   public
    * @static
    */
    public static function db_select_one($table, $where, $col=null)
    {
        // Check if the two required parameters aren't empty
        if( empty($table) || empty($where) )
        {
            throw new Exception("Method `db_select_one` was called with invalid parameters.");
        }
        // Query the database using the {@link db_select()} method
        $stmt = self::db_select($table, $where, $col);

        if( !is_object($stmt) ) { return false; }
        
        // Return associative array
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
   /**
    * Select All Rows
    *
    * This method returns all matching rows based on the parameters provided.
    *
    * @param    string      $table      The table name the query should be performed on.
    * @param    string      $where      A "WHERE" statement string.
    * @param    string      $col        A specific column to query results from.
    * @return   mixed                   Multi-dimensional associative array on success,
    *                                   (bool) false on fail.
    * @access   public
    * @static
    */
    public static function db_select_all($table, $where=null, $col=null)
    {
        $return = array();
        
        if( empty($table) )
        {
            throw new Exception("Method `db_select_all` MUST have a valid table parameter");
        }
        $stmt = self::db_select($table, $where, $col);
        
        if( !is_object($stmt) ) { return false; }
        
        while( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
        {
            $return[] = $row;
        }
        return $return;
    }
    
   /**
    * Get Facebook Logout URL
    *
    * This is used to form the URL for users to logout.
    * It is used as the `href` value for the Logout button.
    *
    * @param    $params
    * @return   string      String with the logout URL
    * @access   public
    */
    public function getLogoutUrl($params=array()) 
    {
        $session = $this->getSession();
        return $this->getUrl(
            'www',
            'logout.php',
            array_merge( array(
                'api_key'     => $this->getAppId(),
                'next'        => URL_PUBLIC . 'logout/',
                'session_key' => $session['session_key'],
                ), $params)
        );
    }
    
    public static function user_logout()
    {
        // Check if the cookie is set
        if( is_array($_COOKIE) )
        {
            foreach($_COOKIE as $key => $blah)
            {
                echo $key;
                setcookie( $key, false, $_SERVER['REQUEST_TIME'] - 3600, '/', 
                (isset($_ENV['SERVER_PROTOCOL']) && (strpos($_ENV['SERVER_PROTOCOL'],'https') 
                || strpos($_ENV['SERVER_PROTOCOL'],'HTTPS'))) );
                unset($_COOKIE[$key]);
            }
        }
        AuthUser::logout();
        // Delete the session cookie as well
        if( ini_get("session.use_cookies") )
        {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600,
                $params['path'], $params['domain'], 
                $params['secure'], $params['httponly']);
        }
        // Destroy the session
        session_destroy();
        
        redirect(URL_PUBLIC);
    }
    
    public static function generate_password( $length=10 )
    {
        $val = '';
        for ($i=0; $i<$length; $i++) 
        {
            $d=rand(1,30)%2;
            $val .= $d ? (rand(10,99)%2 ? mb_strtolower(chr(rand(65,90))):
                        chr(rand(65,90))) : chr(rand(48,57));
        }
        return $val;
    }
   
   /**
    * Copied from AuthUser Model
    *
    * This method was copied from the Wolf core AuthUser model since it is a protected
    * method and therefore cannot be called directly.
    *
    * @param    int     $time   Unix timestamp for when the cookie should expire
    * @param    obj     $user   Wolf User object
    * @return   string          string with cookie data
    * @access   protected
    */ 
    static protected function bakeUserCookie($time, $user) {
        return 'exp='.$time.'&id='.$user->id.'&digest='.md5($user->username.$user->password);
    }
    
    private static function handleException($e)
    {
        if(DEBUG === true) 
        {
            echo $e->getMessage();
        }
        else
        {
            error_log($e->getMessage());
        }
        return false;
    }
}
