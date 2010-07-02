<?php
/**
 * Facebook Plugin
 *
 * This class provides a developer with Facebook integration.
 * It is intended for the developer to build on this and does not provide a 
 * turn-key plugin.
 *
 */
Plugin::setInfos(array(
    'id'                    => 'facebook',
    'title'                 => 'Facebook',
    'description'           => 'Allows developers to impliment Facebook API.',
    'version'               => '1.0',
    'license'               => 'Apache License, Version 2.0',
    'author'                => 'David Miles',
    'required_wolf_version' => '0.6.0',
    'update_url'            => 'http://amereservant.com/fbplugin/current_version.xml',
    'type'                  => 'both')
    );

define('FB_PLUGIN_ROOT', PLUGINS_ROOT . '/facebook');

define('FB_URL_ROOT', URL_PUBLIC . (endsWith(URL_PUBLIC, '/') ? '': '/').'wolf/plugins/facebook/');

// Load the Facebook API class into the system.
AutoLoader::addFile('Facebook', FB_PLUGIN_ROOT . '/facebook.php');

// Load the FacebookConnect class into the system.
AutoLoader::addFile('FacebookConnect', FB_PLUGIN_ROOT . '/facebookconnect.php');

// Add the controller (tab) to the administration
Plugin::addController('facebook', 'Facebook', 'administrator');

// Add Logout Dispatcher
Dispatcher::addRoute( array(
    '/logout/' => '/plugin/facebook/fblogout/',
    '/new_user' => '/plugin/facebook/new_user_page/') );

// Login Form Display - Used by the facebook-login snippet
function fb_login()
{
    return FacebookConnect::fb_login();
}

// Custom function used by the {@link FacebookConnect} class to see if array keys match
function array_keys_match($array1, $array2)
{
    $diff = array_diff_key($array1, $array2);
    if( count($diff) < 1 )
    {
        return true;
    }
    else
    {
        return false;
    }
}
