<?php

$libpath = ini_get('yaf.library');

$currPath = getcwd();
define('ROOT_PATH', realpath(dirname($currPath)));
define('APPLICATION_PATH', ROOT_PATH );
define('APP_PATH', ROOT_PATH );
define('APPLICATION_NAME', basename(ROOT_PATH));
define('PUBLIC_PATH', ROOT_PATH . '/public' );
define('VENDOR_PATH', ROOT_PATH . '/vendor');
define('CONFIG_PATH', ROOT_PATH. '/configs');
define('ENV_PRODUCT', 'product');
define('ENV_TEST','test');
define('ENV_DEVELOP', 'develop');

$application = new \Yaf\Application ( CONFIG_PATH."/application.ini");
$application->bootstrap()->run();



?>
