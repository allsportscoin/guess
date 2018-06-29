<?php
 
namespace services;

use library\base\ServiceBase;
use services\AddressService;
use services\Match as MatchService;

class MatchsGamesService extends ServiceBase {

    public static function insertMatchGame($matchId, $gameId, $addressGId, $matchSp, $concede) {
        $objMatch = \MatchsGamesModel::getInstance();
        $ret = $objMatch->insertSingle($matchId, $gameId, $addressGId, $matchSp, $concede);
        if (false === $ret) {
            return false;
        }
        return $ret;
    }

    public static function getGameAddrByMatchId($matchId) {
        $objMatch = \MatchsGamesModel::getInstance();
        $gameAddr = $objMatch->getByMatchId($matchId);
        if (false === $gameAddr) {
            return false;
        }
        $arrGroupId = [];
        $arrOdds = [];
        foreach($gameAddr as $game) {
            $arrGroupId[] = $game['address_gid'];
            $gameId = $game['gameid'];
            $odds = MatchService::getOddsByMatchIdGameId($matchId, $gameId);
            $arrOdds[$gameId] = $odds;
        }
        $arrAddr = AddressService::getAddrInfoByGroupIdBatch($arrGroupId);
        $arrRet = [];
        foreach($gameAddr as $game) {
            $arrRet[$game['gameid']]['addr'] = $arrAddr[$game['address_gid']];
            $arrRet[$game['gameid']]['handicap'] = floatval($game['handicap']);
            if ($game['gameid'] == 3 || $game['gameid'] == 4) {
                $sp= explode("|", $game['match_sp']);
                $arrRet[$game['gameid']]['match_sp'] = array(
                    'sp_home' => $sp[0],
                    'sp_draw' => $sp[1],
                    'sp_away' => $sp[2],
                );
            } else {
                $arrRet[$game['gameid']]['match_sp'] = array(
                    'sp_home' => $arrOdds[$game['gameid']]['home_sp'],
                    'sp_draw' => $arrOdds[$game['gameid']]['draw_sp'],
                    'sp_away' => $arrOdds[$game['gameid']]['away_sp'],
                );
            }
            $arrRet[$game['gameid']]['total_soc'] = $arrOdds[$game['gameid']]['total_soc'];
        }
        
        return $arrRet;
    }

    public static function getAddrInfoByMatchId($matchId) {
        $objMatch = \MatchsGamesModel::getInstance();
        $gameAddr = $objMatch->getByMatchId($matchId);
        if (false === $gameAddr) {
            return false;
        }
        $arrGid = [];
        foreach($gameAddr as $game) {
            $arrGid[] = $game['address_gid'];
        }
        $arrAddr = AddressService::getAddrInfoByGroupIdBatch($arrGid);
        $arrRet = [];
        foreach($gameAddr as $game) {
            $arrRet[$game['gameid']] = $arrAddr[$game['address_gid']];
        }
        
        return $arrRet;
    }

    public static function getAddrInfoByMatchIdGameId($matchId, $gameId) {
        $objMatch = \MatchsGamesModel::getInstance();
        $gameAddr = $objMatch->getByMatchIdGameId($matchId, $gameId);
        if (false === $gameAddr) {
            return false;
        }
        $groupId = $gameAddr['address_gid'];
        $arrAddr = AddressService::getAddrInfoByGroupId($groupId);
        
        return $arrAddr[$groupId];
    }

    public static function getGameIdByMatchId($matchId) {
        $objMatch = \MatchsGamesModel::getInstance();
        $gameAddr = $objMatch->getByMatchId($matchId);
        if (false === $gameAddr) {
            return false;
        }
        $arrGameId = [];
        foreach($gameAddr as $item) {
            $arrGameId[] = $item['gameid'];
        }
        return $arrGameId;
    }

    public static function getGroupIdByMatchId($matchId) {
        $objMatch = \MatchsGamesModel::getInstance();
        $gameAddr = $objMatch->getByMatchId($matchId);
        if (false === $gameAddr) {
            return false;
        }
        $arrGid = [];
        foreach($gameAddr as $game) {
            $arrGid[] = $game['address_gid'];
        }
        return $arrGid;
    }

    public static function getMatchGameByMatchIdGameId($matchId, $gameId) {
        $objMatch = \MatchsGamesModel::getInstance();
        $gameAddr = $objMatch->getByMatchIdGameId($matchId, $gameId);
        if (false === $gameAddr) {
            return false;
        }
        
        return $gameAddr;
    }

}
