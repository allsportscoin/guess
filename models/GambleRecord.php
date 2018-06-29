<?php
 
use library\db\Database;
 
class GambleRecordModel {
    
    public $_tableName = 'gamble_record';
    private $_fields = 'id, txid, address, value, result, matchid, gameid, match_result, status, is_cancel, create_time';

    protected static $_model = null;

    public static function getInstance() {
        if (self::$_model === null) {
            self::$_model = new self();
        }
        return self::$_model;
    }

    public function insertBatch($arrData) {
    }

    public function insertSingle($txid, $address, $value, $result, $matchId, $gameId, $status = 1) {
        $table = $this->_tableName;
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }

        $row = array (
            'txid'  => $txid,
            'address' => $address,
            'value' => $value,
            'result' => $result,
            'matchid' => $matchId,
            'gameid' => $gameId,
            'is_cancel' => 0,
            'status' => $status,
            'create_time' => date("Y-m-d H:i:s"),
        );
        $ret = $db->insert($table, $row);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getRecordByMatchIdGameId($matchId, $gameId, $time = null, $size = null, $status = null) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "select $this->_fields from $this->_tableName where is_cancel=0 and matchid=" . intval($matchId) . " and gameid = " . intval($gameId);
        if ($time !== null) {
            $sql .= " AND create_time < '$time' ";
        }
        if ($status !== null) {
            $sql .= " AND status = " . intval($status);
        }
        $sql .= ' ORDER BY create_time DESC ';
        if ($size !== null) {
            $sql .= " LIMIT " . intval($size);
        }

        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getByMatchid($matchId, $gameId=null, $size = null, $time = null, $status = null) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE is_cancel=0 AND matchid=" . intval($matchId);
        if ($gameId !== null) {
            $sql .=  " AND gameid = " . intval($gameId);
        }
        if ($time !== null) {
            $sql .=  " AND create_time < '$time' ";
        }
        if ($status !== null) {
            $sql .=  " AND status = " . intval($status);
        }
        $sql .= ' ORDER BY create_time DESC ';
        if ($size !== null) {
            $sql .= " LIMIT " . intval($size);
        }

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
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE is_cancel=0 AND status = " . intval($status) . " LIMIT " . intval($size);
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }

        return $ret;
    }

    public function getByMatchResult($matchId, $matchResult) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return false;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE is_cancel=0 AND matchid= " . intval($matchId);
        $sql .= " AND match_result = " . intval($matchResult) . " ORDER BY ASC";
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }

        return $ret;
    }

    public function getCountAddressByResult($matchId, $result) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return false;
        }
        $sql = 'SELECT COUNT(*) AS num FROM ' . $this->_tableName . ' WHERE matchid=' . intval($matchId) . ' AND result=' . intval($result);
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return false;
        }
    }

    public function getRecordByResult($matchId, $result, $gameId) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return false;
        }
        $sql = 'SELECT * FROM ' . $this->_tableName . ' WHERE matchid=' . intval($matchId) . ' AND result=' . intval($result) . ' AND gameid=' . intval($gameId);
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return false;
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
