<?php

use \Swoft\App;

// Constants
!defined('DS') && define('DS', DIRECTORY_SEPARATOR);
// app name
!defined('APP_NAME') && define('APP_NAME', 'swoft');
// base path
!defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
// cli namespace
!defined('COMMAND_NS') && define('COMMAND_NS', "App\Commands");

$aliases = [
    '@root'       => BASE_PATH,
    '@app'        => '@root/app',
    '@res'        => '@root/resources',
    '@runtime'    => '@root/runtime',
    '@configs'    => '@root/config',
    '@resources'  => '@root/resources',
    '@beans'      => '@configs/beans',
    '@properties' => '@configs/properties',
    '@console'    => '@beans/console.php',
];
App::setAliases($aliases);
