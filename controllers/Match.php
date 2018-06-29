<?php
 
use library\util\Sign;
use library\util\Utils;
use library\base\ControllerBase;
use services\ScanTxService;
use services\AddressService;
use services\MatchsGamesService;
use services\Match as MatchService;
use services\AddressToMatch as AddressToMatchService;
use services\Gamble as GambleService;
use services\PrizeRecord as PrizeRecordService;
use services\InchargeRecord as InchargeRecordService;


class MatchController extends ControllerBase {

    public function addMatchAction() {
        date_default_timezone_set('UTC');
        $checkSign = Sign::checkSign();
        if (false === $checkSign) {
            $this->returnResult(-1, 'params error');
            return;
        }
        $matchid = $this->getParams('matchid');
        $teamA = $this->getParams('teamA');
        $teamB = $this->getParams('teamB');
        $beginTime = $this->getParams('begin_time');

        $ret = MatchService::getMatchInfoByMatchId($matchid);
        if ($ret !== false) {
            $this->returnResult(-1, 'match is already added');
            return;
        }
        $arrAddress = AddressToMatchService::selectUsableAddress($matchid, $beginTime);
        if (false === $arrAddress) {
            $this->returnResult(-1, 'no avaliable address');
            return;
        }
        $ret = MatchService::insertMatch($matchid, $teamA, $teamB, $beginTime);
        if (false === $ret) {
            $this->returnResult(-1, 'insert match error');
            return;
        }
        $ret = MatchsGamesService::insertMatchGame($matchid, $gameId=1, $arrAddress[0]['group_id'], '', '');
        $this->returnResult(0, 'succ');
        return;
    }

    public function setMatchStatusAction() {
        date_default_timezone_set('UTC');
        $checkSign = Sign::checkSign();
        Log::addNotice('params', $_POST);
        if (false === $checkSign) {
            $this->returnResult(-1, 'params error, check failed');
            return;
        }
        $matchId = $this->getParams('matchid');
        $status = $this->getParams('status');
        if ($status == 5) {
            return $this->_processMatch($status);
        }
        $ret = MatchService::getMatchInfoByMatchId($matchId);
        if (false === $ret) {
            $this->returnResult(0, "no matchid[$matchId] info");
            return;
        }
        if ($ret['status'] == $status) {
            $this->returnResult(0, "matchid[$matchid] is already operated");
            return;
        }
        if ($status == 4) {
            $score = $this->getParams('score');
            $ret = MatchService::updateStatusScore($matchId, $status, $score);
            if ($ret === false) {
                Log::warning("update match status and score error, matchid[$matchId]");
                $this->returnResult(0, 'update match status and score error, matchid[' . $matchId . ']');
                return;
            }
            $ret = AddressService::releaseAddressByMatchId($matchId);
            if ($ret === false) {
                Log::warning("release address error, matchid[$matchId]");
                $this->returnResult(0, 'release failed matchid[' . $matchId . ']');
                return;
            }
            $ret = MatchService::recordMatchPrizeList($matchId);
            if ($ret === false) {
                Log::warning("open Prize error, matchid[$matchId]");
                $this->returnResult(0, 'open Prize failed matchid[' . $matchId . ']');
                return;
            }
        } else if ($status == 3) {
            $ret = MatchService::updateStatus($matchId, $status);
        } else {
            $ret = MatchService::updateStatus($matchId, $status);
            if (false === $ret) {
                $this->returnResult(0, 'update match status error, matchid[' . $matchId . ']');
                return;
            }
        }
        $this->returnResult(0, 'succ', $ret);
        return;
    }

    private function _processMatch($status) {
        $strMatchId = $this->getParams('matchids');
        $arrMatchId = explode(',', $strMatchId);
        $ret = MatchService::updateStatusBatch($arrMatchId, $status);
        $arrFailed = array();
        foreach ($arrMatchId as $matchId) {
            $ret = AddressService::releaseAddressByMatchId($matchId);
            if ($ret === false) {
                Log::warning("release address error, matchid[$matchId]");
                $arrFaild[$matchId] = 'release address error';
                continue;
            }
            $ret = MatchService::recordMatchPrizeList($matchId);
            if ($ret === false) {
                Log::warning("open prize error, matchid[$matchId]");
                $arrFaild[$matchId] = 'open prize error';
                continue;
            }
        }
        $code = 0; $msg = 'succ';
        if (!empty($arrFailed)) {
            $msg = 'error';
        }
        $this->returnResult($code, $msg, $arrFailed);
    }

}
