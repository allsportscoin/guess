<?php
use library\util\Utils;
use library\define\Constant;
use services\ScanTxService;
use services\Gamble as GambleService;
use services\Match as MatchService;
use services\AddressToMatch as AddressToMatchService;
use services\InchargeRecord as InchargeRecordService;
use services\PrizeRecord as PrizeRecordService;
use services\MatchsGamesService;

class rebuildCommand {

    public function rebuildSpAction($matchId = null){
        try{
            if (empty($matchId)) {
                echo "run: /home/work/php7/bin/php webroot/cli.php rebuild rebuildSp matchid\n";
                return;
            }
            $arrGameId = MatchsGamesService::getGameIdByMatchId($matchId);
            foreach($arrGameId as $gameId) {
                //$matchId = ;
                $result = 0;
                $objGamble = \GambleRecordModel::getInstance();
                $ret = $objGamble->getRecordByResult($matchId, $result, $gameId);
                if (false === $ret || empty($ret)) {
                    $ret = array();
                }
                $homeTotal = 0;
                foreach($ret as $val) {
                    $homeTotal += intval($val['value']);
                }
                $result = 1;
                $ret = $objGamble->getRecordByResult($matchId, $result, $gameId);
                if (false === $ret || empty($ret)) {
                    $ret = array();
                }
                $drawTotal = 0;
                foreach($ret as $val) {
                    $drawTotal += intval($val['value']);
                }
                $result = 2;
                $ret = $objGamble->getRecordByResult($matchId, $result, $gameId);
                if (false === $ret || empty($ret)) {
                    $ret = array();
                }
                $awayTotal = 0;
                foreach($ret as $val) {
                    $awayTotal += intval($val['value']);
                }

                $arrMG = MatchsGamesService::getMatchGameByMatchIdGameId($matchId, $gameId);
                $arrOdds = explode("|", $arrMG['match_sp']);
                $arrOdds = array(
                    'home_sp' => $arrOdds[0],
                    'draw_sp' => $arrOdds[1],
                    'away_sp' => $arrOdds[2],
                    'home_soc' => $homeTotal,
                    'draw_soc' => $drawTotal,
                    'away_soc' => $awayTotal,
                );
                $ret = MatchService::rebuildMatchOdds($matchId, $gameId, $arrOdds);
            }
            Utils::commandLog("rebuildCommand rebuildSpAction done");
        }catch(Exception $e){
            Utils::commandLog("rebuildCommand rebuildSpAction failed");
            return;
        }
        Utils::commandLog("rebuildCommand rebuildSpAction done");
    }

}
