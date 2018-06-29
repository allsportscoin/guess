<?php

namespace library\util;

class  Utils {

    public static function sort($a, $b){
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? 1 : -1;
    }

    public static function commandLog($message){
        echo date('Y-m-d H:i:s').": $message \n";
    }


    private static $password = "";
    private static $method = 'aes-256-cbc';


    private static function getPasswd(){
        return substr(hash('sha256', self::$password, true), 0, 32);
    }

    private static function getIV(){
        // IV must be exact 16 chars (128 bit)
        return chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0). chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
    }

    /**
     * encrypt your key
     */
    public static function encrypt($str)
    {
        return $encrypted = base64_encode(openssl_encrypt($str, self::$method, self::getPasswd(), OPENSSL_RAW_DATA, self::getIv()));
    }

    /**
    * decrypt your key
    */
    public static function decrypt($str)
    {
        return $decrypted = openssl_decrypt(base64_decode($str), self::$method, self::getPasswd(), OPENSSL_RAW_DATA, self::getIv());
    }

    public static function getCurrUser(){
        $env = \Yaconf::get("env.user");
        if(empty($env)){
            $env = '';
        }

        return $env;
    }

    public static function parseScore($score) {
        $arrScore = explode(':', $score);
        if (count($arrScore) != 2) {
            return false;
        }
        if ($arrScore[0] > $arrScore[1]) {
            return 0;
        } else if ($arrScore[0] == $arrScore[1]) {
            return 1;
        } else {
            return 2;
        }
    }

}

