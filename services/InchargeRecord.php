<?php
 
namespace services;

use library\base\ServiceBase;

class InchargeRecord extends ServiceBase {

    public static function insert($txid, $fromAddress, $toAddress, $value, $matchId, $gameId, $status = 0) {
        $obj = \InchargeRecordModel::getInstance();
        $ret = $obj->insertSingle($txid, $fromAddress, $toAddress, $value, $matchId, $gameId, $status);
        return $ret;
    }

    public static function getRecordByStatus($status) {
        $obj = \InchargeRecordModel::getInstance();
        $ret = $obj->getByStatus($status);
        return $ret;
    }

    public static function getRecordByTxid($txid) {
        $obj = \InchargeRecordModel::getInstance();
        $ret = $obj->getRecordByTxid($txid);
        if (empty($ret) || empty($ret[0])) {
            return false;
        }
        return $ret[0];
    }
 
    public static function updateTxidById($insertId, $txid) {
        $obj = \InchargeRecordModel::getInstance();
        $ret = $obj->updateTxidById($insertId, $txid);
        return $ret;
    }

    public static function updateStatusById($status, $id) {
        $obj = \InchargeRecordModel::getInstance();
        $ret = $obj->updateStatus($status, $id);
        return $ret;
    }
 
}
