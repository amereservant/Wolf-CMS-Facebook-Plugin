<?php
define('FACEBOOK_ROOT', PLUGINS_ROOT . '/facebook');

// Get the total count for the plugin to see if settings already exist
$count = Record::countFrom('plugin_settings', "plugin_id='comment'");

var_dump($count);

$sql = "SELECT name FROM " .TABLE_PREFIX. "plugin_settings WHERE plugin_id='facebook'";
$stmt = Record::query($sql);

$records = array();
while( $rows = $stmt->fetch(PDO::FETCH_ASSOC) ) 
{
    $records[] = $rows['name'];
}

$settings = array(
    'fb_installed_version'  => '0.1.0',
    'allow_fb_connect'      => 1,
    'fb_api_key'            => 'Facebook API Key',
    'fb_application_secret' => 'Facebook Application Secret'
    );

if( count($records) < 1 )
{
    $stmt = Record::$__CONN__->prepare("INSERT INTO " .TABLE_PREFIX. "plugin_settings " .
     "(plugin_id, name, value) VALUES ('facebook', :name, :value)");
    foreach($settings as $key => $val)
    {
        $stmt->bindParam(':name', $key);
        $stmt->bindParam(':value', $val);
        $stmt->execute();
    }
}
/**
 * Change Exception message here if table count doesn't match future versions
 * Also, perhaps provide a way to remove/repair broken/missing rows
 */
elseif( count($records) !== count($settings) )
{
    throw new Exception('Facebook `plugin_settings` entries already exist, but there ' . 
        'should be '. count($settings) .' tables and only '. count($records) .' were found!');
}

//echo '<pre>' . print_r($records, true) . '</pre>';
