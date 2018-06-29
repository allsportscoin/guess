<?php
 
use library\db\Database;

class PrizeResultModel {
    
    public $_tableName = 'prize_result';
    private $_fields = 'id, txid, address, value, matchid, gameid, sp, status, create_time';

    protected static $_model = null;

    public static function getInstance() {
        if (self::$_model === null) {
            self::$_model = new self();
        }
        return self::$_model;
    }

    public function insertSingle($txid, $address, $value, $matchId, $gameId, $sp, $status = 1) {
        $table = $this->_tableName;
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return false;
        }
        $row = array (
            'txid'  => $txid,
            'address' => $address,
            'matchid' => $matchId,
            'gameid' => $gameId,
            'value' => $value,
            'sp' => $sp,
            'status' => $status,
            'create_time' => date("Y-m-d H:i:s"),
        );
        $ret = $db->insert($table, $row);
        if ($ret === false) {
            return false;
        }
        return $ret;
    }

    public function getByMatchid($matchId, $size = 20, $time = '2020-01-01') {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE matchid = " . intval($matchId) . " AND create_time < '$time'";
        $sql .= " ORDER BY create_time DESC LIMIT " . intval($size);
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

    public function getByMatchidAddress($matchId, $address) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE matchid = " . intval($matchId) . " AND address = '$address' LIMIT 1";
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getByMatchIdGameIdAddress($matchId, $gameId, $address) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE matchid = " . intval($matchId) . " AND address = '$address' AND gameid = " . intval($gameId) . " LIMIT 1";
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
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

    public function updateTxidById($id, $txid) {
        $row = array(
            'txid' => $txid,
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
