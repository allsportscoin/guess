<?php
 
use library\base\ControllerBase;
use library\util\Conf;
use services\ScanTxService;
use services\MatchsGamesService;
use services\Match as MatchService;
use services\AddressToMatch as AddressToMatchService;
use services\Gamble as GambleService;
use services\PrizeRecord as PrizeRecordService;
use services\InchargeRecord as InchargeRecordService;
use library\util\Utils;


class BetController extends ControllerBase {


    public function IndexAction() {
        date_default_timezone_set('UTC');
        $beginTime = $this->getParams('begin_time');
        $size = $this->getParams('size');
        $desc = $this->getParams('desc');
        if ($beginTime == null) {
            $beginTime = date('Y-m-d H:i:s');
        } else {
            $beginTime = date('Y-m-d H:i:s', $beginTime);
        }
        if ($size == null) {
            $size = 40;
        }
        $status = 0;
        if ($desc == null || $desc == 0) {
            $desc = 0;
            $status = 1;
        }
        $matchList = MatchService::getMatchByStatus($status, $beginTime, $size, $desc);

        $arrRet = array();
        $after = strtotime(end($matchList)['begin_time']) - 1;
        $days = [];
        $matchs = [];
        foreach ($matchList as $item) {
            $matchId = $item['matchid'];
            $arrGameMatch = MatchsGamesService::getGameAddrByMatchId($matchId);
            if (empty($arrGameMatch)) {
                continue;
            }
            unset($item['id']);
            unset($item['end_time']);
            unset($item['create_time']);
            $games = [];
            foreach($arrGameMatch as $gameId => $value) {
                $tmp = [];
                $tmp['game_id'] = $gameId;
                if ($desc == 1) {
                    $tmp['address_home'] = substr_replace($value['addr'][0], '***', 5, 12);
                    $tmp['address_draw'] = substr_replace($value['addr'][1], '***', 5, 12);
                    $tmp['address_away'] = substr_replace($value['addr'][2], '***', 5, 12);
                } else {
                    $tmp['address_home'] = $value['addr'][0];
                    $tmp['address_draw'] = $value['addr'][1];
                    $tmp['address_away'] = $value['addr'][2];
                }
                $tmp['sp_home'] = sprintf("%01.2f", $value['match_sp']['sp_home']);
                $tmp['sp_draw'] = sprintf("%01.2f", $value['match_sp']['sp_draw']);
                $tmp['sp_away'] = sprintf("%01.2f", $value['match_sp']['sp_away']);
                $tmp['matchid'] = intval($matchId);
                if ($value['handicap'] > 0) {
                    $tmp['handicap'] = '+' . floatval($value['handicap']);
                } else {
                    $tmp['handicap'] = strval($value['handicap']);
                }
                $tmp['total_soc'] = intval($value['total_soc']);
                if ($item['status'] == 4 && ($gameId == 2 || $gameId == 4)) {
                    $isZoushui = MatchService::getAsiaResult($matchId, $item['score'], $value['handicap']);
                    $tmp['refunded'] = $isZoushui === 0 ? 1 : 0;
                    if ($value['handicap'] > 0) {
                        if ($isZoushui > 0) {
                            $result = 2;
                        }else if ($isZoushui <= 0) {
                            $result = 0;
                        }
                    }else if ($value['handicap'] <= 0) {
                        if ($isZoushui > 0) {
                            $result = 0;
                        }else if ($isZoushui <= 0) {
                            $result = 2;
                        }
                    }
                    $tmp['result'] = $result;
                }
                if ($item['status'] == 4 && ($gameId == 1 || $gameId == 3)) {
                    $tmp['result'] = Utils::parseScore($item['score']);
                }
                $games[] = $tmp;
            }
            $item['games'] = $games;
            $item['status'] = intval($item['status']);
            if ($item['status'] == 4) {
                $item['result'] = Utils::parseScore($item['score']);
            }

            $item['begin_time'] = strtotime($item['begin_time']);
            $matchs[] = $item;
        }
        $arrRet['begin_time'] = $after;
        $arrRet['list'] = $matchs;
        $this->returnResultCache(0, 'succ', $arrRet, 60*5);
        return;
    }

    public function detailAction() {
        date_default_timezone_set('UTC');
        $betTime = $this->getParams('bet_time');
        $matchId = $this->getParams('matchid');
        $gameId = $this->getParams('game_id');
        $size = $this->getParams('size');

        $matchInfo = MatchService::getMatchInfoByMatchId($matchId);
        if (empty($matchInfo)) {
            $this->returnResult(-1, 'no match info', $matchInfo);
            return;
        } else {
            $matchInfo = $matchInfo;
        }
        $arrTrans = GambleService::getRecordByMatchIdGameId($matchId, $gameId, $betTime, $size);
        $arrList = array();
        $txUrl = Conf::get('setting.txurl');
        foreach ($arrTrans as $val) {
            $tmp = array();
            $tmp['id'] = intval($val['id']);
            if ($val['result'] == 0) {
                $tmp['bet_team'] = $matchInfo['teamA'];
            } else if ($val['result'] == 1) {
                $tmp['bet_team'] = 'Draw';
            } else {
                $tmp['bet_team'] = $matchInfo['teamB'];
            }
            if ($matchInfo['status'] == 4) {
                $tmp['bet_result'] = 3; 
                if ($val['result'] == $val['match_result']){
                    $tmp['bet_result'] = 1;
                    $prize = PrizeRecordService::getByMatchIdGameIdAddress($matchId, $gameId, $val['address']);
                    if (false === $prize) {
                        continue;
                    }
                    $tmp['reward_total'] = floor($prize['sp'] * $val['value']);
                    if (empty($prize['txid'])) {
                        $tmp['reward_record'] = array(
                            'txid'  => 'opening...',
                            'txid_url'  => $txUrl . $prize['txid'],
                        );
                    } else {
                        $tmp['reward_record'] = array(
                            'txid'  => $prize['txid'],
                            'txid_url'  => $txUrl . $prize['txid'],
                        );
                    }
                }
            } else if ($matchInfo['status'] == 5) {
                $tmp['bet_result'] = 2; 
            } else {
                $tmp['bet_result'] = 4;
                if (empty($val['txid'])) {
                    $val['txid'] = 'Verifying, wait a moment...';
                }
            }
            $tmp['bet_soc'] = intval($val['value']);
            $tmp['bet_time'] = strtotime($val['create_time']);
            $tmp['txid_list'] = array(
                array(
                    'txid'  => $val['txid'],
                    'txid_url'  => $txUrl . $val['txid'],
                ),
            );
            $arrList[] = $tmp;
        }

        $ret = array(
            'bet_time' => end($arrList)['bet_time'],
            'records' => $arrList,
        );
        $this->returnResultCache(0, 'succ', $ret, 60*5);
        return;
    }
}
