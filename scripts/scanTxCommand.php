<?php
use services\ScanTxService;
use library\util\Utils;

class scanTxCommand {

    public function scanAllTxAction(){
        try{
            date_default_timezone_set('UTC');
            $ret = ScanTxService::scanTx();
            Utils::commandLog("scanTxCommand scanAllTx: done");
        }catch(Exception $e){
            Utils::commandLog("scanTxCommand scanAllTx faild");
            return;
        }
        Utils::commandLog("scanTxCommand scanAllTx done");
    }

    public function guessCheckAction(){
        try{
            date_default_timezone_set('UTC');
            $ret = ScanTxService::scanTx();
            Utils::commandLog("scanTxCommand guessCheckAction done");
        }catch(Exception $e){
            Utils::commandLog("scanTxCommand guessCheckAction faild");
            return;
        }
        Utils::commandLog("scanTxCommand guessCheckAction done");
    }

}
