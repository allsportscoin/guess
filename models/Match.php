<?php
 
use library\db\Database;

class MatchModel {
    
    public $_tableName = 'matchs';
    private $_fields = 'id, matchid, teamA, teamB, begin_time, end_time, score, competition, status, create_time';

    protected static $_model = null;

    public static function getInstance() {
        if (self::$_model === null) {
            self::$_model = new self();
        }
        return self::$_model;
    }

    public function insertBatch() {
    }

    public function insertSingle($matchId, $teamA, $teamB, $beginTime, $competition='') {
        $table = $this->_tableName;
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }

        $row = array (
            'matchid' => $matchId,
            'teamA' => $teamA,
            'teamB' => $teamB,
            'begin_time' => $beginTime,
            'score' => '',
            'competition' => $competition,
            'status' => 1,
            'create_time' => date("Y-m-d H:i:s"),
        );
        $ret = $db->insert($table, $row);
        if ($ret === false) {
            return false;
        }
        return $ret;
    }

    public function getByStatus($status, $beginTime = '2018-04-10', $size = 20, $desc = 0) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "select $this->_fields from $this->_tableName where";
        if ($status == 1) {
            $sql .= " status = " . intval($status);
        } else {
            $sql .= ' status != 1';
        }
        if ($desc == 0) {
            $sql .= " AND begin_time > '$beginTime' order by begin_time asc limit $size";
        } else {
            $sql .= " AND begin_time < '$beginTime' order by begin_time desc limit $size";
        }
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }

        return $ret;
    }

    public function getByMatchId($matchId) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "select $this->_fields from $this->_tableName where matchid = " . intval($matchId) . " limit 1 ";
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function updateStatus($status, $matchId) {
        $row = array(
            'status' => $status,
        );
        $conds = array(
            'matchid' => $matchId,
        );
        return $this->update($row, $conds);
    }

    public function updateStatusBatch($status, $arrMatchId) {
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
