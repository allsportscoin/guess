<?php
namespace services;

use library\base\ServiceBase;
use library\define\Constant;
use library\util\Utils;
use library\util\Conf;
use services\Match as MatchService;
use services\Gamble as GambleService;
use services\InchargeRecord as InchargeRecordService;
use services\AddressService;

class ScanTxService extends ServiceBase {

    public static function scanTx() {
        $apikey = Conf::get("setting.apikey");
        $token = Conf::get("setting.token");
        $blockNumber = self::_apcu_fetch("blockNumber");
        if($blockNumber == false){
            $blockNumber = 0;
        }
        $data = array(
            "module" => "account",
            "action" => "txlist",
            "startblock" => $blockNumber + 1,
            "endblock" => "latest",
            "sort" => "desc",
            "apikey" => $apikey,
            "address" => $token,
        );
        $newTxs = self::callEthersanCurl("api", $data);
        if(!$newTxs){
            return false;
        }
        $result = $newTxs['result'];
        $currBlock = $blockNumber;
        foreach($result as $tx){
            if($tx['isError'] != 0){
                continue;
            }
            $hash = $tx['hash'];
            $input = $tx['input'];
            if($input == "0x"){
                continue;
            }
            $currBlock = $tx['blockNumber'] > $currBlock ? $tx['blockNumber'] : $currBlock;
            $ret = InchargeRecordService::getRecordByTxid($hash);
            if(false !== $ret) {
                self::_apcu_store("blockNumber", $currBlock);
                continue;
            }
            $result = system("ENV=" . ENVIRON . " " . Constant::NODE_BIN . " scripts/detailTx_soc.js ${input}");
            if(strlen($result) == 0){
                continue;
            }
            $txData = json_decode($result, 1);
            if($txData['status'] === "error"){
                continue;
            }
            $value = floatval($txData['value']) / Constant::DECIMALS_18;
            if ($value < 100) {
                self::_apcu_store("blockNumber", $currBlock);
                continue;
            }

            if($value >= Conf::get("setting.bigAmount")){
                $monitor = Conf::get("setting.monitor");
                Utils::sendEmail($hash, $tx['from'], $txData['to'], $value, $monitor);
            }
            //get some soc as fee
            $value = $value - intval(Conf::get("setting.fee"));
            try{
                $ret = AddressService::getAddrInfoByAddr($tx['from']);
                if(false !== $ret) {
                    self::_apcu_store("blockNumber", $currBlock);
                    continue;
                }
                $matchInfo = AddressService::getAddrMatchGameByAddr($txData['to']);
                if ($matchInfo === false) {
                    self::_apcu_store("blockNumber", $currBlock);
                    continue;
                }
                $matchId = $matchInfo['matchid'];
                $gameId = $matchInfo['gameid'];
                //check match is over or cancel?
                $checkMatch = MatchService::getMatchInfoByMatchId($matchId);
                if ($checkMatch === false) {
                    continue;
                }
                if ($checkMatch['status'] > 1) {
                    self::_apcu_store("blockNumber", $currBlock);
                    continue;
                }
                $guessResult = $matchInfo['bet_label'];
                $ret = InchargeRecordService::insert($hash, $tx['from'], $txData['to'], $value, $matchId, $gameId);
                $ret = GambleService::insert($guessTxid='', $matchId, $tx['from'], $value, $guessResult, $gameId);
                self::_apcu_store("blockNumber", $currBlock);
                $homeNum = $drawNum = $awayNum = 0;
                if ($guessResult == 0) {
                    $homeNum = $value;
                } else if ($guessResult == 1) {
                    $drawNum = $value;
                } else if ($guessResult == 2) {
                    $awayNum = $value;
                }
                $ret = MatchService::updateMatchOdds($matchId, $gameId, $homeNum, $drawNum, $awayNum);
                sleep(30);
            }catch(Exception $e){
                continue;
            }
        }
    }

    public static function createTokenTx($address, $value, $from, $pk){
        if (strpos($pk, '0x') === 0) {
            $pk = substr($pk, 2);
        }
        $value = strval($value) . Constant::APPEND_DECIMALS_18;
        $strtx = system("ENV=" . ENVIRON . " " . Constant::NODE_BIN . " scripts/createTx_soc.js ${address} ${value} ${from}");
        $arrtx = json_decode($strtx, 1);
        if($arrtx['status'] == "error"){
            return false;;
        }
        $txid = $arrtx['txhash'];
        return $txid;
    }

    public static function createGuessTx($mid, $guesser, $type, $value){
        $result = system("ENV=" . ENVIRON . " " . Constant::NODE_BIN . " scripts/createTx_guess.js ${mid} ${guesser} ${type} $value");
        $arrResult = json_decode($result, 1);
        if($arrResult['status'] == "error"){
            return false;
        }
        return $arrResult;
    }

    public static function _apcu_fetch($name){
        $resultFile = "/tmp/$name";
        return file_get_contents($resultFile);
    }

    public static function _apcu_store($name, $value){
        $resultFile = "/tmp/$name";
        file_put_contents($resultFile, $value);
    }

    public function callEthersanCurl($url, $data){
        $ch = curl_init();
        $url = "http://api.etherscan.io/api?" . http_build_query($data);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        $response = curl_exec ($ch);
        $newTxs = json_decode($response, 1);
        if($newTxs['status'] != 1){
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $newTxs;
    }
}
