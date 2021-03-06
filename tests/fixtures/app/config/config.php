<?php
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(__DIR__)));

return array(
    'application' => array(
        'environment'   =>  \Vegas\Constants::TEST_ENV,
        'serviceDir'   =>  APP_ROOT . '/app/services/',
        'configDir'     => dirname(__FILE__) . DIRECTORY_SEPARATOR,
        'libraryDir'     => dirname(APP_ROOT) . DIRECTORY_SEPARATOR . '/lib/',
        'pluginDir'      => APP_ROOT . '/app/plugins/',
        'moduleDir'      => APP_ROOT . '/app/modules/',
        'taskDir'      => APP_ROOT . '/app/tasks/',
        'baseUri'        => '/',
        'language'       => 'nl_NL',
        'subModules'    =>  array(
            'frontend', 'backend', 'custom'
        ),
        'view'  => array(
            'cacheDir'  =>  APP_ROOT . '/cache/',
            'layout'    =>  'main.volt',
            'layoutsDir'    =>  APP_ROOT . '/app/layouts/'
        ),
    ),

    'plugins' => array(
        'acl' => array(
            'class' => 'AclPlugin',
            'attach' => 'dispatch'
        )
    ),
    
    'mongo' => array(
        'db' => 'vegas_test',
    ),
    
    'acl' => array(
        \Vegas\Security\Acl\Resource::WILDCARD   =>  array(
            'description'   =>  'All privileges (for super admin)',
            'accessList'    =>  array(
                array(
                    'name'  => \Vegas\Security\Acl\Resource::ACCESS_WILDCARD,
                    'description' => 'All',
                    'inherit' => ''
                )
            )
        )
    ),
    
    'db'    =>  array(
        "host" => "localhost",
        "dbname" => "vegas_test",
        "port" => 3306,
        "username" => "root",
        "options" => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        )
    )
);
