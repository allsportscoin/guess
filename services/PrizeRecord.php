<?php
 
namespace services;

use library\base\ServiceBase;

class PrizeRecord extends ServiceBase {

    public static function insert($matchId, $gameId, $address, $value, $sp, $txid) {
        $objPrizeRecord = \PrizeResultModel::getInstance();
        $ret = $objPrizeRecord->insertSingle($txid, $address, $value, $matchId, $gameId, $sp);
        return $ret;
    }

    public static function updateTxidById($insertId, $txid) {
        $objPrizeRecord = \PrizeResultModel::getInstance();
        $ret = $objPrizeRecord->updateTxidById($insertId, $txid);
        return $ret;
    }

    public static function updateStatusById($status, $id) {
        $objPrizeRecord = \PrizeResultModel::getInstance();
        $ret = $objPrizeRecord->updateStatus($status, $id);
        return $ret;
    }

    public static function getRecordByStatus($status) {
        $objPrizeRecord = \PrizeResultModel::getInstance();
        $ret = $objPrizeRecord->getByStatus($status);
        return $ret;
    }

    public static function getByMatchidAddress($matchId, $address) {
        $objPrizeRecord = \PrizeResultModel::getInstance();
        $ret = $objPrizeRecord->getByMatchidAddress($matchId, $address);
        if (empty($ret) || empty($ret[0])) {
            return false;
        }
        return $ret[0];
    }

    public static function getByMatchIdGameIdAddress($matchId, $gameId, $address) {
        $objPrizeRecord = \PrizeResultModel::getInstance();
        $ret = $objPrizeRecord->getByMatchIdGameIdAddress($matchId, $gameId, $address);
        if (empty($ret) || empty($ret[0])) {
            return false;
        }
        return $ret[0];
    }
}
