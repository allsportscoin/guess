<?php
 
namespace services;

use library\base\ServiceBase;
use library\define\Constant;
use library\util\Utils;
 
 
class GamesService extends ServiceBase {

    public static function insert($name) {
        $objAddr = \GamesModel::getInstance();
        $ret = $objAddr->insertSingle($name);
        return $ret;
    }

    public static function getAvaliableGame() {
        $objAddr = \GamesModel::getInstance();
        $ret = $objAddr->getGamesByStatus($status=0);
        return $ret;
    }
}
