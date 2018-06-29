<?php

namespace library\util;

class Conf {

    private static $files = [];

    private static $delimiter = '.';

    public static function get($key){
        if (strpos($key, '.') !== false) {
            list($file, $path) = explode('.', $key, 2);
        }else{
            $file = $key;
        }
        if (!in_array(strtolower($file), ['redis', 'mysql'])) {
            $config_file = APPLICATION_PATH . '/configs/' . $file . '.ini';    
            if (!file_exists($config_file)) {
                return false;
            }
            $env = getenv('ENVIRON');
            if (!isset(self::$files[$file][$env])) {
                self::$files[$file][$env] = new \Yaf\Config\Ini($config_file, $env);
            }
            $fileArray = self::$files[$file][$env]->toArray();
        } else {
            $fileArray = getenv($file);
            $fileArray = unserialize($fileArray);
        }

        if (!isset($path)) {
            return $fileArray;
        } else {
            if(isset($fileArray[$path])){
                return $fileArray[$path];
            }
            $keys = explode(self::$delimiter, $path);
            $array = $fileArray;
            while($keys){
                $key = array_shift($keys);
                if(isset($array[$key])){
                    if($keys){
                        if(!is_array($array[$key])){
                            return NULL;
                        }else{
                            $array = $array[$key];
                        }
                    }else{
                        return $array[$key];
                    }
                }
            }
        }
    }
}
