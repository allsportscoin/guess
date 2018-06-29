<?php
use library\util\Utils;
use library\define\Constant;
use library\util\Conf;
use services\ScanTxService;
use services\AddressService;
use services\MatchsGamesService;
use services\Gamble as GambleRecordService;
use services\Match as MatchService;
use services\InchargeRecord as InchargeRecordService;
use services\PrizeRecord as PrizeRecordService;

class scanDbCommand {

    public function checkInchargeAction(){
        date_default_timezone_set('UTC');
        try{
            $status = 1;
            $arrInchargeRecord = InchargeRecordService::getRecordByStatus($status);
            foreach ($arrInchargeRecord as $record) {
                $txid = $record['txid'];
                $succ = 1;
                if (!empty($txid)) {
                    $result = system("ENV=" . ENVIRON . " " . Constant::NODE_BIN . " scripts/checkTx.js ${txid}");
                    Utils::commandLog("scanDbCommand checkTx " . $result);
                    $result = json_decode($result, true);
                    $succ = $result['status'];
                    if ($succ === 'pending' || $succ === 'error') {
                        continue;
                    }
                    if ($succ == 0) { 
                        $fromAddress = $record['from_address'];
                        $addrInfo = AddressService::getAddrInfoByAddr($fromAddress);
                        if ($addrInfo === false) {
                            Utils::commandLog("get addr info failed, addr[$addr]");
                            continue;
                        }
                        $privateKey = Utils::decrypt($addrInfo['private_key']);
                        $nTxid = ScanTxService::createTokenTx($record['to_address'], $record['value'], $fromAddress, $privateKey);
                        if ($nTxid !== false && !empty($nTxid)) {
                            $ret = InchargeRecordService::updateTxidById($record['id'], $nTxid);
                        }
                        Utils::commandLog("scanDbCommand createTokenTx " . $nTxid);
                        sleep(20);
                    }
                    if ($succ == 1) {
                        $ret = InchargeRecordService::updateStatusById(0, $record['id']);
                    }
                } else {
                    $fromAddress = $record['from_address'];
                    $addrInfo = AddressService::getAddrInfoByAddr($fromAddress);
                    if ($addrInfo === false) {
                        Utils::commandLog("get addr info failed, addr[$addr]");
                        continue;
                    }
                    $privateKey = Utils::decrypt($addrInfo['private_key']);
                    $nTxid = ScanTxService::createTokenTx($record['to_address'], $record['value'], $fromAddress, $privateKey);
                    if ($nTxid !== false && !empty($nTxid)) {
                        $ret = InchargeRecordService::updateTxidById($record['id'], $nTxid);
                    }
                    sleep(20);
                }
            }
            Utils::commandLog("scanDbCommand checkInchargeAction done");
        }catch(Exception $e){
            Utils::commandLog("scanDbCommand checkInchargeAction faild");
            return;
        }
    }

    public function checkGambleAction(){
        date_default_timezone_set('UTC');
        try{
            $status = 1;
            $arrGambleRecord = GambleRecordService::getRecordByStatus($status);
            foreach ($arrGambleRecord as $record) {
                $txid = $record['txid'];
                $succ = 1;
                if (!empty($txid)) {
                    $result = system("ENV=" . ENVIRON . " " . Constant::NODE_BIN . " scripts/checkTx.js ${txid}");
                    Utils::commandLog("scanDbCommand checkTx " . $result);
                    $result = json_decode($result, true);
                    $succ = $result['status'];
                    if ($succ === 'pending' || $succ === 'error') {
                        continue;
                    }
                    if ($succ == 0) {
                        $nTxid = ScanTxService::createGuessTx($record['matchid'], $record['address'], $record['result'], $record['value']);
                        if ($nTxid !== false && !empty($nTxid)) {
                            $nTxid = $nTxid['txhash'];
                            $ret = GambleRecordService::updateTxidById($record['id'], $nTxid);
                            if (false === $ret) {
                                Utils::commandLog("update txid by id failed, id[" . $record['id'] . "], txid[$nTxid]");
                            }
                        }
                        sleep(20);
                    } 
                    if ($succ == 1) {
                        $ret = GambleRecordService::updateStatusById(0, $record['id']);
                        if (false === $ret) {
                            Utils::commandLog("update status by id failed, id[" . $record['id'] . "]");
                        }
                    }
                } else {
                    $arrGuess = ScanTxService:: createGuessTx($record['matchid'], $record['address'], $record['result'], $record['value']);
                    if ($arrGuess !== false && !empty($arrGuess)) {
                        $nTxid = $arrGuess['txhash'];
                        $ret = GambleRecordService::updateTxidById($record['id'], $nTxid);
                        if (false === $ret) {
                            Utils::commandLog("update txid by id failed, id[" . $record['id'] . "], txid[$nTxid]");
                        }
                    }
                    sleep(20);
                }
            }
            Utils::commandLog("scanDbCommand checkGambleAction done");
        }catch(Exception $e){
            Utils::commandLog("scanDbCommand checkGambleAction faild");
            return;
        }
    }

    public function checkPrizeAction(){
        date_default_timezone_set('UTC');
        try{
            $status = 1;
            $arrPrizeRecord = PrizeRecordService::getRecordByStatus($status);
            $objTransfer = \TransferRecordModel::getInstance();
            foreach ($arrPrizeRecord as $record) {
                $txid = $record['txid'];
                $succ = 1;
                if (!empty($txid)) {
                    $result = system("ENV=" . ENVIRON . " " . Constant::NODE_BIN . " scripts/checkTx.js ${txid}");
                    Utils::commandLog("scanDbCommand checkTx " . $result);
                    $result = json_decode($result, true);
                    $succ = $result['status'];
                    if ($succ === 'pending' || $succ === 'error') {
                        continue;
                    }
                    if ($succ == 0) {
                        if ($record['gameid'] == 3 || $record['gameid'] == 4) {
                            $addr = Conf::get("setting.zhuangjia_address");
                            $addrInfo = AddressService::getAddrInfoByAddr($addr);
                        } else {
                            $addr = Conf::get('setting.jiangchi_address');
                            $addrInfo = AddressService::getAddrInfoByAddr($addr);
                        }
                        if (!isset($addrInfo) || $addrInfo === false) {
                            continue;
                        }
                        $fromAddress = $addrInfo['address'];
                        $privateKey = Utils::decrypt($addrInfo['private_key']);
                        $nTxid = ScanTxService::createTokenTx($record['address'], $record['value'], $fromAddress, $privateKey);
                        if ($nTxid !== false && !empty($nTxid)) {
                            $ret = PrizeRecordService::updateTxidById($record['id'], $nTxid);
                            if (false === $ret) {
                                Utils::commandLog("update txid by id failed, id[" . $record['id'] . "], txid[$nTxid]");
                            }
                        }
                        sleep(20);
                    } 
                    if ($succ == 1) {
                        $ret = PrizeRecordService::updateStatusById(0, $record['id']);
                        if (false === $ret) {
                            Utils::commandLog("update status by id failed, id[" . $record['id'] . "], txid[$nTxid]");
                        }
                    }
                } else {
                    $needWait = $objTransfer->getByMatchIdGameIdStatus($record['matchid'], $record['gameid'], 1);
                    if (!empty($needWait)) {
                        continue;
                    }
                    if ($record['gameid'] == 3 || $record['gameid'] == 4) {
                        $addr = Conf::get('setting.zhuajia_address');
                        $addrInfo = AddressService::getAddrInfoByAddr($addr);
                    } else {
                        $addr = Conf::get('setting.jiangchi_address');
                        $addrInfo = AddressService::getAddrInfoByAddr($addr);
                    }
                    if (!isset($addrInfo) || $addrInfo === false) {
                        continue;
                    }
                    $fromAddress = $addrInfo['address'];
                    $privateKey = Utils::decrypt($addrInfo['private_key']);
                    $nTxid = ScanTxService::createTokenTx($record['address'], $record['value'], $fromAddress, $privateKey);
                    if ($nTxid !== false && !empty($nTxid)) {
                        $ret = PrizeRecordService::updateTxidById($record['id'], $nTxid);
                        if (false === $ret) {
                            Utils::commandLog("update txid by id failed, id[" . $record['id'] . "], txid[$nTxid]");
                        }
                    }
                    sleep(20);
                }
            }
            Utils::commandLog("scanDbCommand checkPrizeAction done");
        }catch(Exception $e){
            Utils::commandLog("scanDbCommand checkPrizeAction faild");
            return;
        }
    }

    public function checkTransferAction(){
        date_default_timezone_set('UTC');
        try{
            $status = 1;
            $objModelTransfer = \TransferRecordModel::getInstance();
            $arrTransferRecord = $objModelTransfer->getByStatus($status);
            foreach ($arrTransferRecord as $record) {
                $txid = $record['txid'];
                $succ = 1;
                if (!empty($txid)) {
                    $result = system("ENV=" . ENVIRON . " " . Constant::NODE_BIN . " scripts/checkTx.js ${txid}");
                    Utils::commandLog("scanDbCommand checkTx " . $result);
                    $result = json_decode($result, true);
                    $succ = $result['status'];
                    if ($succ === 'pending' || $succ === 'error') {
                        continue;
                    }
                    if ($succ == 0) {
                        $fromAddress = $record['from_address'];
                        $addrInfo = AddressService::getAddrInfoByAddr($fromAddress);
                        if ($addrInfo === false) {
                            Utils::commandLog("get addr info failed, addr[$addr]");
                            continue;
                        }
                        $privateKey = Utils::decrypt($addrInfo['private_key']);
                        $nTxid = ScanTxService::createTokenTx($record['to_address'], $record['value'], $fromAddress, $privateKey);
                        if ($nTxid !== false && !empty($nTxid)) {
                            $ret = $objModelTransfer->updateTxidById($record['id'], $nTxid);
                        }
                        Utils::commandLog("scanDbCommand createTokenTx " . $nTxid);
                        sleep(20);
                    }
                    if ($succ == 1) {
                        $ret = $objModelTransfer->updateStatus(0, $record['id']);
                    }
                } else {
                    $fromAddress = $record['from_address'];
                    $addrInfo = AddressService::getAddrInfoByAddr($fromAddress);
                    if ($addrInfo === false) {
                        Utils::commandLog("get addr info failed, addr[$addr]");
                        continue;
                    }
                    $privateKey = Utils::decrypt($addrInfo['private_key']);
                    $nTxid = ScanTxService::createTokenTx($record['to_address'], $record['value'], $fromAddress, $privateKey);
                    if ($nTxid !== false && !empty($nTxid)) {
                        $ret = $objModelTransfer->updateTxidById($record['id'], $nTxid);
                    }
                    sleep(20);
                }
            }
        }catch(Exception $e){
            Utils::commandLog("scanDbCommand checkTransferAction faild");
            return;
        }
        Utils::commandLog("scanDbCommand checkTransferAction done");
    }

    public function openPrizeAction(){
        date_default_timezone_set('UTC');
        $objMatchPrizeList = \MatchPrizeListModel::getInstance();
        $needProcessMatchList = $objMatchPrizeList->getByStatus($status=1, $size=20);
        foreach($needProcessMatchList as $key => $val) {
            $matchId = $val['matchid'];
            $arrGameId = MatchsGamesService::getGameIdByMatchId($matchId);
            $matchInfo = MatchService::getMatchInfoByMatchId($matchId);
            foreach ($arrGameId as $gameId) {
                $ret = MatchService::openPrize($matchId, $gameId, $matchInfo['status'], $matchInfo['score']);
            }
            $objMatchPrizeList->updateStatus($matchId, $status = 0);
        }
        Utils::commandLog("scanDbCommand checkOpenPrize succ");
    }

}
