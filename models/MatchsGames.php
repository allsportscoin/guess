<?php
 
use library\db\Database;

class MatchsGamesModel {
    
    public $_tableName = 'matchs_games';
    private $_fields = 'id, matchid, gameid, address_gid, handicap, match_sp, status, create_time';

    protected static $_model = null;

    public static function getInstance() {
        if (self::$_model === null) {
            self::$_model = new self();
        }
        return self::$_model;
    }

    public function insertBatch() {
    }

    public function insertSingle($matchId, $gameId, $addressGId, $matchSp, $handicap) {
        $table = $this->_tableName;
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return false;
        }
        $row = array (
            'matchid' => $matchId,
            'gameid' => $gameId,
            'address_gid' => $addressGId,
            'handicap' => $handicap,
            'match_sp' => $matchSp,
            'status' => 0,
            'create_time' => date("Y-m-d H:i:s"),
        );
        $ret = $db->insert($table, $row);
        if ($ret === false) {
            return false;
        }
        return $ret;
    }

    public function getByMatchId($matchId, $status = 0) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE matchid = " . intval($matchId) . " AND status = " . intval($status);
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return false;
        }
        return $ret;
    }

    public function getByMatchIdGameId($matchId, $gameId, $status = 0) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE matchid = " . intval($matchId) . " AND status = " . intval($status) . " AND gameid = " . intval($gameId);
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return false;
        }
        return $ret[0];
    }

    public function getByMatchIdGroupId($matchId, $groupId, $status = 0) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE matchid = " . intval($matchId) . " AND status = " . intval($status) . " AND address_gid = " . intval($groupId);
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return false;
        }
        return $ret[0];
    }

    public function getByAddressGId($groupId, $status = 0) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "SELECT $this->_fields FROM $this->_tableName WHERE status = " . intval($status) . " AND address_gid = " . intval($groupId);
        $sql .= " ORDER BY id DESC LIMIT 1";
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return false;
        }
        return $ret[0];
    }

    public function updateStatusByMatchIdGameId($matchId, $status, $gameId = null) {
        $row = array(
            'status' => $status,
        );
        $conds = array(
            'matchid' => $matchId,
        );
        if ($gameId !== null) {
            $conds['gameid'] = $gameId;
        }
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
