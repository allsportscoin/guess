<?php
namespace services;

use library\db\Redis;
use library\base\ServiceBase;
use library\define\Constant;
use library\util\Utils;
use library\util\Conf;
use services\ScanTxService;
use services\AddressToMatch as AddressToMatchService;
use services\Gamble as GambleService;
use services\PrizeRecord as PrizeRecordService;
use services\InchargeRecord as InchargeRecordService;
use services\SoccerDataService;

class Match extends ServiceBase {

    public static function insertMatch($matchId, $teamA, $teamB, $beginTime, $competition = '') {
        $objMatch = \MatchModel::getInstance();
        $ret = $objMatch->insertSingle($matchId, $teamA, $teamB, $beginTime, $competition);
        if (false === $ret) {
            return false;
        }
        return $ret;
    }

    public static function updateStatus($matchId, $status) {
        if (!in_array($status, array(1,2,3,4,5))) {
            return false;
        }
        $objMatch = \MatchModel::getInstance();
        $ret = $objMatch->updateStatus($status, $matchId);
        if (false === $ret) {
            return false;
        }
        return $ret;
    }

    public static function updateStatusBatch($arrMatchId, $status) {
        if (!in_array($status, array(1,2,3,4,5))) {
            return false;
        }
        $objMatch = \MatchModel::getInstance();
        $ret = $objMatch->updateStatusBatch($status, $arrMatchId);
        if (false === $ret) {
            return false;
        }
        return $ret;
    }

    public static function updateStatusScore($matchId, $status, $score) {
        if (!in_array($status, array(1,2,3,4,5))) {
            return false;
        }
        $objMatch = \MatchModel::getInstance();
        $row = array(
            'status' => $status,
            'score' => $score,
        );
        $conds = array(
            'matchid' => $matchId,
        );
        $ret = $objMatch->update($row, $conds);
        if (false === $ret) {
            return false;
        }
        return $ret;
    }

    public static function getOddsByMatchIdGameId($matchId, $gameId) {
        $key = Constant::MATCH_COUNT . $matchId . '_gameId_' . $gameId;
        $objRedis = Redis::getInstance()->connect('redis_of_soc_guess', 0);
        $fields = array('home_sp', 'draw_sp', 'away_sp', 'total_soc');
        $ret = $objRedis->hmget($key, $fields);
        return $ret;
    }

    public static function getOddsByMatchIdBatch($arrMatchId, $gameId) {
        $objRedis = Redis::getInstance()->connect('redis_of_soc_guess', 0);
        $fields = array('home_sp', 'draw_sp', 'away_sp', 'total_soc');
        $pipe = $objRedis->multi(\Redis::PIPELINE);
        foreach($arrMatchId as $id) {
            $key = Constant::MATCH_COUNT . $id . '_gameId_' . $gameId;
            $pipe && $pipe->hmget($key, $fields);
        }
        $cacheRet = $pipe ? $pipe->exec() : array();
        $arrRet = array();
        foreach ($cacheRet as $index => $val) {
            $matchId = $arrMatchId[$index];
            if (empty($val)) {
                continue;
            }
            $arrRet[$matchId] = $val;
        }

        return  $arrRet;
    }

    public static function getMatchByStatus($status, $beginTime, $size, $desc = 0) {
        $objMatch = \MatchModel::getInstance();
        $arrMatch = $objMatch->getByStatus($status, $beginTime, $size, $desc);
        return $arrMatch;
    }

    public static function getMatchInfoByMatchId($matchId) {
        $objMatch = \MatchModel::getInstance();
        $match = $objMatch->getByMatchId($matchId);
        if (empty($match) || empty($match[0])) {
            return false;
        }
        return $match[0];
    }

    public static function updateMatchOdds($matchId, $gameId, $homeNum, $drawNum, $awayNum, $handicap = null) {
        $key = Constant::MATCH_COUNT . $matchId . '_gameId_' . $gameId;
        $objRedis = Redis::getInstance()->connect('redis_of_soc_guess', 0);

        if ($gameId == 2 || $gameId == 4) {
            $drawNum = 0;
        }
        $homeSoc = $objRedis->hIncrBy($key, 'home_soc', $homeNum);
        $drawSoc = $objRedis->hIncrBy($key, 'draw_soc', $drawNum);
        $awaySoc = $objRedis->hIncrBy($key, 'away_soc', $awayNum);
        $totalSoc = $objRedis->hIncrBy($key, 'total_soc', $homeNum + $drawNum + $awayNum);

        if (($gameId == 3 || $gameId == 4) && $handicap === null) {
            return true;
        }
        $homeSp = floor(100.0 / ($homeSoc / $totalSoc)) / 100;
        $awaySp = floor(100.0 / ($awaySoc / $totalSoc)) / 100;
        if ($gameId == 1) {
            $drawSp = floor(100.0 / ($drawSoc / $totalSoc)) / 100;
        } else {
            $drawSp = $handicap;
        }
        $arrData = array(
            'home_sp' => $homeSp,
            'away_sp' => $awaySp,
        );
        if ($drawSp !== null) {
            $arrData['draw_sp'] = $drawSp;
        }
        $objRedis->hmset($key, $arrData);
        return true;
    }

    public static function setFixedMatchOdds($matchId, $gameId, $arrOdds) {
        $key = Constant::MATCH_COUNT . $matchId . '_gameId_' . $gameId;
        $objRedis = Redis::getInstance()->connect('redis_of_soc_guess', 0);
        $objRedis->hmset($key, $arrOdds);

        return true;
    }

    public static function rebuildMatchOdds($matchId, $gameId, $arrOdds) {
        $key = Constant::MATCH_COUNT . $matchId . '_gameId_' . $gameId;
        $objRedis = Redis::getInstance()->connect('redis_of_soc_guess', 0);

        $totalSoc = $arrOdds['home_soc'] + $arrOdds['draw_soc'] + $arrOdds['away_soc'];
        $homeSp = floor(100.0 / (1.0 * $arrOdds['home_soc'] / $totalSoc)) / 100;
        $awaySp = floor(100.0 / (1.0 * $arrOdds['away_soc'] / $totalSoc)) / 100;
        $arrOdds['total_soc'] = $totalSoc;
        if ($gameId == 1) {
            $drawSp = floor(100.0 / (1.0 * $arrOdds['draw_soc'] / $totalSoc)) / 100;
            $arrOdds['home_sp'] = $homeSp;
            $arrOdds['draw_sp'] = $drawSp;
            $arrOdds['away_sp'] = $awaySp;
        } else if ($gameId == 2) {
            $arrOdds['home_sp'] = $homeSp;
            $arrOdds['away_sp'] = $awaySp;
        }
        $objRedis->hmset($key, $arrOdds);

        return true;
    }

    public static function recordMatchPrizeList($matchId) {
        $objMatchList = \MatchPrizeListModel::getInstance();
        $ret = $objMatchList->insertSingle($matchId);
        return $ret;
    }

    public static function cancelGuess($matchId, $gameId) {
        $arrRecord = GambleService::getAllByMatchid($matchId, $gameId);
        $arrRefund = [];
        $homeNum = $drawNum = $awayNum = 0;
        foreach ($arrRecord as $key => $val) {
            $value = floor(floatval($val['value']));
            $address = strtolower($val['address']);
            if (!isset($arrRefund[$address])) {
                $arrRefund[$address] = $value;
            } else {
                $arrRefund[$address] += $value;
            }
            if ($val['result'] == 1) {
                $drawNum += $value;
            } else if ($val['result'] == 2) {
                $awayNum += $value;
            } else if ($val['result'] == 0) {
                $homeNum += $value;
            }
        }
        $ret = self::transferSoc($matchId, $gameId, $drawNum, $awayNum, $homeNum);
        foreach ($arrRefund as $addr => $val) {
            $num = $val;
            $txid = '';
            $insertId = PrizeRecordService::insert($matchId, $gameId, $addr, $num, $odds=1, $txid);
            if (false === $insertId) {
                $insertId = PrizeRecordService::insert($matchId, $gameId, $addr, $num, $odds=1, $txid);
                if (false === $insertId) {
                    continue;
                }
            }
        }
    }

    public static function openPrize($matchId, $gameId, $status, $score) {
        if ($status == 5) {
            return $this->cancelGuess($matchId, $gameId);
        }
        $arrOdds = self::getOddsByMatchIdGameId($matchId, $gameId);
        if ($gameId == 1 || $gameId == 3) {
            $result = Utils::parseScore($score);
            if ($result == 0) {
                $odds = floor($arrOdds['home_sp'] * 100) / 100;
            } else if ($result == 1) {
                $odds = floor($arrOdds['draw_sp'] * 100) / 100;
            } else {
                $odds = floor($arrOdds['away_sp'] * 100) / 100;
            }
        }
        if ($gameId == 2 || $gameId == 4) {
            $handicap = $arrOdds['draw_sp'];
            $asiaResult = self::getAsiaResult($matchId, $score, $handicap);
            if ($handicap > 0) {
                if ($asiaResult > 0) {
                    $odds = floor(100 * $arrOdds['away_sp']) / 100;
                    $result = 2;
                }else if ($asiaResult < 0) {
                    $odds = floor($arrOdds['home_sp'] * 100) / 100;
                    $result = 0;
                }
            }else if ($handicap <= 0) {
                if ($asiaResult > 0) {
                    $odds = floor($arrOdds['home_sp'] * 100) / 100;
                    $result = 0;
                }else if ($asiaResult < 0) {
                    $odds = floor(100 * $arrOdds['away_sp']) / 100;
                    $result = 2;
                }
            }
            if ($asiaResult == 0) {
                $odds = 1;
                $result = 1;
            } else {
                $odds = $odds * abs($asiaResult);
            }
        }
        $ret = GambleService::updateMatchResultByMatchIdGameId($matchId, $gameId, $result);
        $arrRecord = GambleService::getAllByMatchid($matchId, $gameId);
        $homeNum = $drawNum = $awayNum = $totalUser = 0;
        $arrAllInfo = array(
            'totalUser' => 0,
            'totalSoc' => 0,
            'homeNum' => 0,
            'drawNum' => 0,
            'awayNum' => 0,
            'prize' => array(),
        );
        foreach ($arrRecord as $item) {
            $arrAllInfo['totalUser']++;
            $arrAllInfo['totalSoc'] += $item['value'];
            if ($item['result'] == 0) {
                $arrAllInfo['homeNum'] += $item['value'];
            } else if ($item['result'] == 1) {
                $arrAllInfo['drawNum'] += $item['value'];
            } else {
                $arrAllInfo['awayNum'] += $item['value'];
            }
            if ($status == 4 && $item['result'] !== $item['match_result'] && ($gameId == 1 || $gameId == 3)) {
                continue;
            }
            if ($gameId == 2 || $gameId == 4) {
                if (abs($asiaResult) == 0.5) {
                    if (intval($item['result']) == intval($result)) {
                        $value = floor(floatval($item['value']) * $odds + $item['value'] * 0.5);
                    } else {
                        $value = floor(floatval($item['value']) * 0.5);
                    }
                } else {
                    if ($result == 1) {
                        $value = floor(floatval($item['value']));
                    }
                    if ($result == $item['result']) {
                        $value = floor(floatval($item['value']) * $odds);
                    }
                }
                if (!isset($value)) continue;
            } else {
                $value = floor(floatval($item['value']) * $odds);
            }
            $address = strtolower($item['address']);
            if (isset($arrAllInfo['prize'][$address])) {
                $arrAllInfo['prize'][$address] += $value;
            } else {
                $arrAllInfo['prize'][$address] = $value;
            }
            unset($value);
        }
        $homeNum = $arrAllInfo['homeNum'];
        $drawNum = $arrAllInfo['drawNum'];
        $awayNum = $arrAllInfo['awayNum'];
        $ret = self::transferSoc($matchId, $gameId, $drawNum, $awayNum, $homeNum);
        $prize = $arrAllInfo['prize'];
        foreach ($prize as $addr => $val) {
            $num = $val;
            $txid = '';
            $insertId = PrizeRecordService::insert($matchId, $gameId, $addr, $num, $odds, $txid);
            if (false === $insertId) {
                $insertId = PrizeRecordService::insert($matchId, $gameId, $addr, $num, $odds, $txid);
                if (false === $insertId) {
                    continue;
                }
            }
        }

        return true;
    }

    public static function transferSoc($matchId, $gameId, $drawNum, $awayNum, $homeNum = 0) {
        $arrInfo = MatchsGamesService::getAddrInfoByMatchIdGameId($matchId, $gameId);
        $objTransfer = \TransferRecordModel::getInstance();
        foreach ($arrInfo as $addr) {
            if ($addr['bet_label'] == 0) {
                $homeAddress = $addr['address'];
            } else if ($addr['bet_label'] == 1) {
                $drawAddress = $addr['address'];
            } else {
                $awayAddress = $addr['address'];
            }
        }
        $centerAddress = Conf::get('setting.jiangchi_address');
        if ($homeNum > 0) {
            $ret = $objTransfer->insertSingle('', $homeAddress, $centerAddress, $drawNum, $matchId, $gameId);
        }
        if ($drawNum > 0) {
            $ret = $objTransfer->insertSingle('', $drawAddress, $centerAddress, $drawNum, $matchId, $gameId);
        }
        if ($awayNum > 0) {
            $ret = $objTransfer->insertSingle('', $awayAddress, $centerAddress, $awayNum, $matchId, $gameId);
        }
        return $ret;
    }

    public static function getAsiaOdds($matchId, $type = 'asia') {
        $objSoccerData = SoccerDataService::getInstance();
        $arrOdds = $objSoccerData->oddsIndex($matchId, $type);
        $arrRet = null;
        if (!empty($arrOdds) && isset($arrOdds[0]['odds'])) {
            if (!isset($arrOdds[0]['odds']['last'])) {
                $item = $arrOdds[0]['odds']['first'];
            } else {
                $item = $arrOdds[0]['odds']['last'];
            }
            $handicap = $item['draw'];
            if (strpos($handicap, '/') !== false) {
                $symbol = 1;
                if (strpos($handicap, '-') !== false) {
                    $symbol = -1;
                }
                $arrSplit = array_map('abs', explode('/', $handicap));
                $handicap = $symbol * (array_sum($arrSplit) / 2);
            }
            $handicap = floatval($handicap);
            $homeSp = floatval($item['home']) + 1;
            $awaySp = floatval($item['away']) + 1;
            if ($handicap > 0) {
                $flag = '-';
            } else if ($handicap < 0) {
                $flag = '+';
            } else if ($handicap == 0) {
                if ($homeSp > $awaySp) {
                    $flag = '+';
                } else if ($homeSp < $awaySp) {
                    $flag = '-';
                }
            }
            $arrRet = array(
                'home_p' => 1 / $homeSp / (1/$homeSp + 1/$awaySp),
                'away_p' => 1 / $awaySp / (1/$homeSp + 1/$awaySp),
                'draw_p' => $flag . abs($handicap),
            );
        }
        return $arrRet;
    }

    public static function getAsiaResult($matchId, $score, $handicap) {
        $arrScore = explode(":", $score);
        if ($handicap <= 0) { 
            $scoreA = $arrScore[0];
            $scoreB = $arrScore[1];
        } else {
            $scoreA = $arrScore[1];
            $scoreB = $arrScore[0];
        }
        $q = $scoreA - ($scoreB + abs($handicap));
        if ($q >= 0.5) {
            $ret = 1;
        } else if ($q == 0.25) {
            $ret = 0.5;
        } else if ($q == 0) {
            $ret = 0;
        } else if ($q == -0.25) {
            $ret = -0.5;
        } else if ($q <= -0.5) {
            $ret = -1;
        } else {
            $ret = false;
        }
        return $ret;
    }

    public static function getEuroOdds($matchId, $type = 'euro') {
        $objSoccerData = SoccerDataService::getInstance();
        $arrOdds = $objSoccerData->oddsIndex($matchId, $type);
        $arrRet = null;
        if (!empty($arrOdds)) {
            foreach($arrOdds as $item) {
                if (!isset($item['company']) || !isset($item['company']['name']) || $item['company']['name'] != 'Bet365') {
                    continue;
                }
                if (!isset($item['odds']) || !isset($item['odds']['last'])) {
                    continue;
                }
                $home = 1 / floatval($item['odds']['last']['home']);
                $draw = 1 / floatval($item['odds']['last']['draw']);
                $away = 1 / floatval($item['odds']['last']['away']);
                $pHome = $home / ($home + $draw + $away);
                $pDraw = $draw / ($home + $draw + $away);
                $pAway = $away / ($home + $draw + $away);
                $arrRet = array (
                    'home_p' => $pHome,
                    'draw_p' => $pDraw,
                    'away_p' => $pAway,
                );
            }
        }
        return $arrRet;
    }

}
