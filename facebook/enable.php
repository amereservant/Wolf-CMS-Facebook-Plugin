<?php
define('FACEBOOK_ROOT', PLUGINS_ROOT . '/facebook');

$settings = array(
    'fb_installed_version'  => '0.1.0',
    'allow_fb_connect'      => 1,
    'fb_api_key'            => 'Facebook API Key',
    'fb_application_secret' => 'Facebook Application Secret',
    'fb_use_cookies'        => 0,
    'user_sets_wolf_acc'    => 0,
    'fb_page_slug'          => 'facebook_new_user'
    );

Plugin::setAllSettings($settings, 'facebook');

$PDO = Record::getConnection();

$sqlite = ( $PDO->getAttribute(Record::ATTR_DRIVER_NAME) === 'sqlite' ?
    true : false );

if( !Record::query( "SELECT id FROM ". TABLE_PREFIX ."facebook_users" ) )
{
    $PDO->exec("CREATE TABLE ". TABLE_PREFIX ."facebook_users (
        id INTEGER". ($sqlite ? '':'(11)') ." NOT NULL PRIMARY KEY,
        uid INTEGER". ($sqlite ? '':'(15)') ." NOT NULL,
        wolf_uid INTEGER". ($sqlite ? '':'(4)') ." default NULL,
        name VARCHAR(75) NOT NULL,
        first_name VARCHAR(50) default NULL,
        last_name VARCHAR(50) default NULL,
        link VARCHAR(120) default NULL,
        gender VARCHAR(10) default NULL)");
}

if( !$row = FacebookConnect::db_select_one( 'plugin_settings', "name='fb_page_created'" ) ||
    $row['value'] !== 1 )
{
    $values = array(
        'title'             => 'Facebook - New User',
        'slug'              => $settings['fb_page_slug'],
        'breadcrumb'        => 'Facebook - New User',
        'parent_id'         => 1,
        'status_id'         => 101,
        'created_on'        => date('Y-m-d H:i:s'),
        'published_on'      => date('Y-m-d H:i:s'),
        'created_by_id'     => 1,
        'updated_by_id'     => 1,
        'position'          => 0,
        'is_protected'      => 0,
        'needs_login'       => 0,
        'comment_status'    => 0
        );
        
    if( FacebookConnect::db_insert( 'page', $values ) )
    {
        Plugin::setSetting('fb_page_created', 1, 'facebook');
    }
    
    $values =     
        
