<?php
/**
 * 
 */
class FacebookConnect extends Facebook
{
    protected static $fb_instance;
    
    protected static $user_exists = null;
    
    protected static $uid;
    
    protected static $pdo;
    
    public static $user_configures_acct;
    
    public static $enabled;
    
    public static $session;
    
    public static $user_info;
    
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
        // Check if user is logged in
        $status = self::is_logged_in();
        
        // Check for an error ( means facebook login is disabled ) and return results
        if( $status['error'] === true )
        {
            return $status;
        }
        
        // Get FacebookConnect object
        $facebook = self::get_instance();
                
        if( $session = self::$session )
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
                    redirect(get_url('new_user') . '/' . URL_SUFFIX);
                }
                else
                {
                    // Add user to Wolf's user system so it is integrated
         
/** FIX THIS - NEED A METHOD FOR AUTO-ADD **/
                    $facebook->add_user_to_wolf();
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
        $session       = $facebook->getSession();
        self::$session = $session;
        
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
            return true;
        }
        else
        {
            return false;
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
        self::$session = $facebook->getSession();

        // Session based API call
        if( self::$session )
        {
            // Try making remote call for user info
            try 
            {
                self::$user_info = $facebook->api('/me');
            } catch (FacebookApiException $e) { /* Do something here if you like */ }
        }
        
        if( !self::$user_info || !self::$session )
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
    * This method checks if the user has been added to the 'facebook_users' table
    * or not.  This method doesn't check if the user has been added as a user to
    * the Wolf CMS user table.
    *
    * @param    int     $uid    The userID for the user to check
    * @return   bool            true if they do exist, false otherwise
    * @access   protected
    */
    protected function check_user_exists($uid)
    {
        self::check_db_object();
        
        if( !self::db_select_one('facebook_users', "uid='{$uid}'") ) 
        {
            self::$user_exists = false;
            return false;
        } 
        else 
        {
            self::$user_exists = true;
            return true;
        }
    }
    
    protected function add_new_user($me)
    {
        if( !is_array($me) || !isset($me['id'], $me['name']) )
        {
            return false;
        }
        
        if( is_null(self::$user_exists) )
        {
            $this->check_user_exists($me['id']);
        }
        
        try 
        {
            if( self::$user_exists )
            {
                throw new Exception("User already exists!  Please check your code.");
            }
        }
        catch(Exception $e)
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
        
        $fields = array('uid', 'name', 'first_name', 'last_name', 'link', 'gender');
        $me['uid'] = $me['id'];
        foreach(array_diff_key($me, array_flip($fields)) as $key => $blah)
        {
            unset($me[$key]);
        }
        
        return self::db_insert('facebook_users', $me);
    }
    
    protected function add_user_to_wolf($me)
    {
        if( !is_array($me) || !isset($me['id'], $me['name']) )
        {
            return false;
        }
        $row = self::db_select_one('facebook_users', "uid='{$me['id']}'", 'wolf_uid');
        
        if( !is_null($row['wolf_uid']) )
        {
            throw new Exception("User `{$me['name']}` already exists in the Wolf User table.");
            return false;
        }
        
        $sql = "INSERT INTO ". TABLE_PREFIX ."";
    }
    
    protected static function check_db_object()
    {
        if( !is_object( self::$pdo ) )
        {
            self::$pdo = Record::getConnection();
        }
    }
    
    public static function db_insert($table, $data = array())
    {
        self::check_db_object();
        
        if( empty($table) || count($data) < 1 )
        {
            return false;
        }
        
        $sql = sprintf("INSERT INTO ". TABLE_PREFIX ."%s (%s) VALUES ('%s')",  
            $table,
            implode(", ", array_keys($data)),
            implode("', '", $data));
        
        if( self::$pdo->execute($sql) )
        {
            self::$last_insert['id'] = self::$pdo->lastInsertId();
            self::$last_insert['rows'] = self::$pdo->rowCount();
            return true;
        }
        else
        {
            return false;
        }
    }
    
    protected static function db_select($table, $where=null, $col=null)
    {
        self::check_db_object();
        
        $sql = sprintf("SELECT %s FROM ". TABLE_PREFIX ."%s%s",
            empty($col) ? "*":$col,
            $table, 
            empty($where) ? '':" WHERE $where");
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
    
    public static function db_select_one($table, $where, $col=null)
    {
        if( empty($table) || empty($where) )
        {
            throw new Exception("Method `db_select_one` was called with invalid parameters.");
        }
        $stmt = self::db_select($table, $where, $col);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function db_select_all($table, $where=null, $col=null)
    {
        if( empty($table) )
        {
            throw new Exception("Method `db_select_all` MUST have a valid table parameter");
        }
        $stmt = self::db_select($table, $where, $col);
        
        while( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
        {
            $return[] = $row;
        }
        return $return;
    }
    
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
}
