<?php
 
namespace services;

use library\base\ServiceBase;
use library\define\Constant;
use library\util\Utils;
 
 
class AddressService extends ServiceBase {

    public static function insert($address, $groupId, $privateKey, $betLabel) {
        $objAddr = \AddressModel::getInstance();
        $ret = $objAddr->insertSingle($address, $groupId, $privateKey, $betLabel);
        return $ret;
    }

    public static function selectUsableAddress($matchId, $beginTime){
        $objAddress = \AddressModel::getInstance();
        $arrAddress = $objAddress->getAvaliableGroupAddress();
        if (empty($arrAddress) || count($arrAddress) != 3) {
            return false;
        }
        $objAddress->updateStatusByGroupId($arrAddress[0]['group_id'], 1);
        return $arrAddress;
    }

    public static function releaseAddressByGroupId($groupId) {
        $objAddr = \AddressModel::getInstance();
        $status = 0;
        $ret = $objAddr->updateStatusByGroupId($groupId, $status);
        return $ret;
    }

    public static function releaseAddressByMatchId($matchId) {
        $arrGroupId = MatchsGamesService::getGroupIdByMatchId($matchId);
        if (empty($arrGroupId)) {
            return false;
        }
        $objAddr = \AddressModel::getInstance();
        $status = 0;
        $ret = $objAddr->updateStatusByGroupIdBatch($arrGroupId, $status);
        return $ret;
    }

    public static function getAddressInfoByAddressBatch($arrAddr) {
        $objAddr = \AddressModel::getInstance();
        $ret = $objAddr->getAddrInfoByAddrBatch($arrAddr);
        if (empty($ret)) {
            return false;
        }
        return $ret;
    }

    public static function getAddrInfoByGroupId($groupId) {
        $objAddr = \AddressModel::getInstance();
        $arrAddr = $objAddr->getAddressByGroupId($groupId);
        if (empty($arrAddr)) {
            return false;
        }
        $arrRet = array();
        $arrRet[$groupId] = $arrAddr;
        return $arrRet;
    }

    public static function getAddrInfoByGroupIdBatch($arrGroupId) { 
        $objAddr = \AddressModel::getInstance();
        $arrAddr = $objAddr->getAddrByGroupIdBatch($arrGroupId);
        if (empty($arrAddr)) {
            return false;
        }
        $arrRet = array();
        foreach ($arrAddr as $addr) {
            if (!isset($arrRet[$addr['group_id']])) {
                $arrRet[$addr['group_id']] = array();
            }
            $arrRet[$addr['group_id']][$addr['bet_label']] = $addr['address'];
        }
        return $arrRet;
    }

    public static function getAddrInfoByAddr($addr) {
        $objAddr = \AddressModel::getInstance();
        $address = $objAddr->getAddrInfoByAddr($addr);
        if (empty($address) || empty($address[0])) {
            return false;
        }
        $address = $address[0];
        return $address;
    }

    public static function getAddrMatchGameByAddr($addr) {
        $objAddr = \AddressModel::getInstance();
        $address = $objAddr->getAddrInfoByAddr($addr);
        if (empty($address) || empty($address[0])) {
            return false;
        }
        $address = $address[0];
        $objMatchGame = \MatchsGamesModel::getInstance();
        $ret = $objMatchGame->getByAddressGId($address['group_id']);
        if (empty($ret)) {
            return false;
        }
        $arrRet = array_merge($address, $ret);

        return $arrRet;
    }

}
