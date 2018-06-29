<?php

namespace services;

use library\base\ServiceBase;
use library\util\Conf;
use services\Match as MatchService;
use services\AddressToMatch as AddressToMatchService;
use services\InchargeRecord as InchargeRecordService;

class Gamble extends ServiceBase {

    public static function insert($txid, $matchId, $address, $value, $result, $gameId = 1) {
        $objGamble = \GambleRecordModel::getInstance();
        $ret = $objGamble->insertSingle($txid, $address, $value, $result, $matchId, $gameId);
        return $ret;
    }

    public static function updateMatchResult($matchId, $result) {
        $objGamble = \GambleRecordModel::getInstance();
        $row = array(
            'match_result' => $result,
        );
        $conds = array(
            'matchid' => $matchId,
        );
        $ret = $objGamble->update($row, $conds);
    }

    public static function updateMatchResultByMatchIdGameId($matchId, $gameId, $result) {
        $objGamble = \GambleRecordModel::getInstance();
        $row = array(
            'match_result' => $result,
        );
        $conds = array(
            'matchid' => $matchId,
            'gameid' => $gameId,
        );
        $ret = $objGamble->update($row, $conds);
    }

    public static function getByMatchResult($matchId, $result) {
        $objGamble = \GambleRecordModel::getInstance();
        $arrRet = $objGamble->getByMatchResult($matchId, $result);

        return $arrRet;
    }

    public static function getAllByMatchid($matchId, $gameId = null) {
        $objGamble = \GambleRecordModel::getInstance();
        $arrRet = $objGamble->getByMatchid($matchId, $gameId);

        return $arrRet;
    }

    public static function getByMatchid($matchId, $betTime, $size = 20, $status = 0) {
        $objGamble = \GambleRecordModel::getInstance();
        $arrRet = $objGamble->getByMatchid($matchId, $size, $betTime);

        return $arrRet;
    }

    public static function getRecordByMatchIdGameId($matchId, $gameId, $betTime, $size = 20, $status = 0) {
        $objGamble = \GambleRecordModel::getInstance();
        $arrRet = $objGamble->getRecordByMatchIdGameId($matchId, $gameId, $betTime, $size);
        return $arrRet;
    }


    public static function getRecordByStatus($status) {
        $obj = \GambleRecordModel::getInstance();
        $ret = $obj->getByStatus($status);
        return $ret;
    }
 
    public static function updateTxidById($insertId, $txid) {
        $obj = \GambleRecordModel::getInstance();
        $ret = $obj->updateTxidById($insertId, $txid);
        return $ret;
    }

    public static function updateStatusById($status, $id) {
        $obj = \GambleRecordModel::getInstance();
        $ret = $obj->updateStatus($status, $id);
        return $ret;
    }
}
