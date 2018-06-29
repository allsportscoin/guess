<?php
$libpath = ini_get('yaf.library');

return __scriptInit();

function __scriptHelp($msg = '')
{
    $helpMsg =<<<EOD
usage:
     php cli.php \{\$command\} \{\$action\} {\$args}
EOD;
    
    if(!empty($msg)) echo $msg."\n";
    echo $helpMsg."\n";
    exit;
}


function __scriptInit()
{
    global $argc, $argv;
    //echo "script init\n";
    
    if($argc < 3) {
        __scriptHelp("args wrong");
    }
   
    $command = trim($argv[1]);
    $action = trim($argv[2]);
    
    if($argc == 4) {
        $args = trim($argv[3]);
    }
    
    $bt = debug_backtrace();
    $indexFile = $bt[0]['file'];
    $arrItems = explode('/', $indexFile);
    array_pop($arrItems);
    array_pop($arrItems);
    $rootPath = implode('/', $arrItems);
   
    date_default_timezone_set ( "Asia/Shanghai" );
    define ( 'ROOT_PATH', $rootPath);
    define ( 'APPLICATION_PATH', ROOT_PATH );
    define ( 'APPLICATION_NAME', basename(ROOT_PATH));
    define ( 'PUBLIC_PATH', ROOT_PATH . '/public' );
    define ('VENDOR_PATH', ROOT_PATH . '/vendor');
    define ('CONFIG_PATH', ROOT_PATH. '/configs');
    define ('SCRIPT_PATH', ROOT_PATH. '/scripts');
    
    define('ENV_PRODUCT', 'product');
    define('ENV_TEST','test');
    define('ENV_DEVELOP', 'develop');
    
    $scriptFile = SCRIPT_PATH.'/'.$command.'Command.php';
    
    if(!file_exists($scriptFile)) {
        echo "script file '{$scriptFile}' no exist\n";
        exit;
    }
    
    require $scriptFile;
    $className = $command.'Command';
    if(!class_exists($className)) {
        echo "class '{$className}' no exist\n";
        exit;
    }
    $objClass = new $className;
    
    $methodName = $action.'Action';
    if(!method_exists($objClass, $methodName)) {
        echo "method '{$methodName}' not exist\n";
        exit;
    }
    
    if(!file_exists(CONFIG_PATH.'/application-cli.ini')) {
        echo "application-cli.ini not exist\n";
        exit;
    }
   
    $app = new \Yaf\Application(CONFIG_PATH.'/application-cli.ini');
    if(!empty($args)) {
        $app->bootstrap()->execute(array($objClass,$methodName), $args);
    } else {
        $app->bootstrap()->execute(array($objClass,$methodName));
    }
    
}
