<?php
class Bootstrap extends \Yaf\Bootstrap_Abstract {

    public function _initLoader (\Yaf\Dispatcher $dispatcher) {
        Yaf\Loader::getInstance()->registerLocalNameSpace(['library','services','define']);
        if(file_exists(APPLICATION_PATH .'/vendor/autoload.php')){
            require_once APPLICATION_PATH .'/vendor/autoload.php';
        }
    }

    public function _initPlugin( \Yaf\Dispatcher $dispatcher ) {
    }

    public function _initEnv (\Yaf\Dispatcher $dispatcher) {
        $envPath = APPLICATION_PATH.'/configs/env.ini';
        if(file_exists($envPath)){
            $envs = new \Yaf\Config\Ini($envPath);
            foreach($envs as $key => $value){
                if (is_object($value)) {
                    $value = array_values((array)$value);
                    $value = serialize($value[0]);
                }
                putenv("${key}=${value}");
            }
        }
        define('ENVIRON', getenv('ENVIRON'));
    }

    public function _initRoute( \Yaf\Dispatcher $dispatcher ) {
    }

    public function _initView(Yaf\Dispatcher $dispatcher){
        $dispatcher->disableView();
    }
}
