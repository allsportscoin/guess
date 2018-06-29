<?php
 
use library\db\Database;
 
class MatchPrizeListModel {
    
    public $_tableName = 'match_prize_list';
    private $_fields = 'id, matchid, status, create_time';

    protected static $_model = null;

    public static function getInstance() {
        if (self::$_model === null) {
            self::$_model = new self();
        }
        return self::$_model;
    }

    public function insertBatch() {
    }

    public function insertSingle($matchId) {
        $table = $this->_tableName;
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return false;
        }
        $row = array (
            'matchid' => $matchId,
            'status' => 1,
            'create_time' => date("Y-m-d H:i:s"),
        );
        $ret = $db->insert($table, $row);
        if ($ret === false) {
            return false;
        }
        return $ret;
    }

    public function getByStatus($status, $size = 20) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE status = " . intval($status) . " ORDER BY create_time ASC LIMIT " . intval($size);
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function updateStatus($matchId, $status) {
        $row = array(
            'status' => $status,
        );
        $conds = array(
            'matchid' => $matchId,
        );
        return $this->update($row, $conds);
    }

    public function updateStatusBatch($arrMatchId, $status) {
        $row = array(
            'status' => $status,
        );
        $conds = array(
            'matchid' => $arrMatchId,
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
