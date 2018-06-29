<?php
use library\db\Database;
 
class  InchargeRecordModel {
    
    public $_tableName = 'incharge_record';
    private $_fields = 'id, txid, from_address, to_address, value, matchid, gameid, status, create_time';

    protected static $_model = null;

    public static function getInstance() {
        if (self::$_model === null) {
            self::$_model = new self();
        }
        return self::$_model;
    }

    public function insertSingle($txid, $fromAddress, $toAddress, $value, $matchId, $gameId, $status = 0) {
        $table = $this->_tableName;
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $row = array (
            'txid'  => $txid,
            'from_address' => $fromAddress,
            'to_address' => $toAddress,
            'matchid' => $matchId,
            'gameid' => $gameId,
            'value' => $value,
            'status' => $status,
            'create_time' => date("Y-m-d H:i:s"),
        );
        $ret = $db->insert($table, $row);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getByMatchid($matchid, $size = 20, $time = '2020-01-01') {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE matchid = " . intval($matchid) . " AND create_time < '$time'";
        $sql .= " ORDER BY create_time DESC LIMIT " . intval($size);
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getRecordByTxid($txid) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE txid ='$txid' LIMIT 1";
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }

        return $ret;
    }

    public function getByStatus($status, $size = 1000) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE status = " . intval($status) . " LIMIT " . intval($size);
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function updateTxidById($id, $txid) {
        $row = array(
            'txid' => $txid,
        );
        $conds = array(
            'id' => $id,
        );
        return $this->update($row, $conds);
    }

    public function updateStatus($status, $id) {
        $row = array(
            'status' => $status,
        );
        $conds = array(
            'id' => $id,
        );
        return $this->update($row, $conds);
    }

    public function update($row, $conds) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }

        $ret = $db->update($this->_tableName, $row, $conds);
        if ($ret === false) {
            return false;
        }
        return $ret;
    }
}
