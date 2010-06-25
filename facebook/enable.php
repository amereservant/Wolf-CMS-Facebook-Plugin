<?php
error_reporting(E_ALL);
define('FACEBOOK_ROOT', PLUGINS_ROOT . '/facebook');
require FACEBOOK_ROOT . '/facebook.php';
require FACEBOOK_ROOT . '/facebookconnect.php';

$settings = array(
    'fb_installed_version'  => '0.1.0',
    'allow_fb_connect'      => 1,
    'fb_api_key'            => 'Facebook API Key',
    'fb_application_secret' => 'Facebook Application Secret',
    'fb_use_cookies'        => 1,
    'user_sets_wolf_acc'    => 1
    );

Plugin::setAllSettings($settings, 'facebook');

$PDO = FacebookConnect::get_db_instance();
$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite = ( $PDO->getAttribute(Record::ATTR_DRIVER_NAME) === 'sqlite' );

if( !Record::query( "SELECT id FROM ". TABLE_PREFIX ."facebook_users" ) )
{
    $PDO->exec("CREATE TABLE ". TABLE_PREFIX ."facebook_users (
        id INTEGER". ($sqlite ? '':'(11)') ." NOT NULL PRIMARY KEY,
        uid INTEGER". ($sqlite ? '':'(15)') ." NOT NULL UNIQUE,
        wolf_uid INTEGER". ($sqlite ? '':'(4)') ." NOT NULL UNIQUE,
        wolf_username VARCHAR(125) NOT NULL UNIQUE,
        name VARCHAR(75) NOT NULL,
        first_name VARCHAR(50) default NULL,
        last_name VARCHAR(50) default NULL,
        link VARCHAR(120) default NULL,
        gender VARCHAR(10) default NULL)");
}

$row = FacebookConnect::db_select_one( 'plugin_settings', "name='fb_snippet_created'" );
if( !$row || $row['value'] !== '1' )
{
    $snippet = <<<______EOD
    <?php if( \$fb_login = fb_login() ) {; 
    \$logged_in = \$fb_login['logged_in']; ?>
    <p>
        <a href="<?php echo \$fb_login['link']; ?>" title="<?php echo(\$logged_in ? 'Logout':'Login With Facebook'); ?>">
            <img src="<?php echo \$fb_login['image']; ?>" alt="<?php echo(\$logged_in ? 'Facebook Logout':'Facebook Connect'); ?>" />
        </a>
    </p>
    <?php } // End of if 
    ?>
______EOD;

    $values = array(
        'name'              => 'facebook-login',
        'content'           => $snippet,
        'content_html'      => $snippet,
        'created_on'        => date('Y-m-d H:i:s'),
        'updated_on'        => date('Y-m-d H:i:s'),
        'created_by_id'     => 1,
        'updated_by_id'     => 1,
        'position'          => 0
        );
        
    if( FacebookConnect::db_insert( 'snippet', $values ) )
    {
        $tablename = TABLE_PREFIX .'plugin_settings';
        $plugin_id = "'facebook'";
        $name      = "'fb_snippet_created'";
        
        $existingSettings = array();

        $sql = "SELECT name FROM $tablename WHERE plugin_id=$plugin_id";
        $stmt = $PDO->prepare($sql);
        $stmt->execute();

        while ($settingname = $stmt->fetchColumn())
        {
            $existingSettings[$settingname] = $settingname;
        }
        
        if (in_array($name, $existingSettings)) {
            $sql = "UPDATE $tablename SET value='1' WHERE name=$name AND plugin_id=$plugin_id";
        }
        else {
            $sql = "INSERT INTO $tablename (value, name, plugin_id) VALUES ('1', $name, $plugin_id)";
        }

        $stmt = $PDO->prepare($sql);
        $stmt->execute();
    }
    else
    {
        var_dump($PDO->errorInfo());
    }
}
