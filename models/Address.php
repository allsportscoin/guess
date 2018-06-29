<?php

use library\db\Database;
 
class AddressModel {
    
    public $_tableName = 'address';
    private $_fields = 'id, address, group_id, private_key, bet_label, status, create_time';

    protected static $_model = null;

    public static function getInstance() {
        if (self::$_model === null) {
            self::$_model = new self();
        }
        return self::$_model;
    }

    public function insertBatch() {
    }

    public function insertSingle($address, $groupId, $privateKey, $betLabel, $status=0) {
        $table = $this->_tableName;
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $row = array (
            'address' => $address,
            'group_id' => $groupId,
            'private_key' => $privateKey,
            'bet_label' => $betLabel,
            'status' => $status,
            'create_time' => date("Y-m-d H:i:s"),
        );
        $ret = $db->insert($table, $row);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getAllAddress() {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "select * from $this->_tableName order by id";
        $ret = $db->querySql($sql, []);
        if (!$ret) {
            return false;
        }
        return $ret;
    }

    public function getAvaliableGroupAddress() {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "select $this->_fields from $this->_tableName where status = 0 order by group_id asc limit 3";
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getAddressByGroupId($groupId) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "select $this->_fields from $this->_tableName where group_id = $groupId limit 3";
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getAddrByGroupIdBatch($arrGroupId) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $gidStr = implode(',', $arrGroupId);
        $sqlgids = 'group_id in ( ' . $gidStr . ' )';
        $sql = "select $this->_fields from $this->_tableName where " . $sqlgids;
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getAddrInfoByAddrBatch($arrAddr) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        foreach($arrAddr as &$addr) {
            $addr = "'" . trim($addr) . "'";
        }
        $addrStr = implode(',', $arrAddr);
        $addStrSql = ' address in ( ' . $addrStr . ' )';
        $sql = "select $this->_fields from $this->_tableName where " . $addStrSql;
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function getAddrInfoByAddr($addr) {
        $db = Database::getInstance()->selectDb('socguess', 0);
        if ($db === false) {
            return null;
        }
        $sql = "select $this->_fields from $this->_tableName where address = '$addr' limit 1";
        $ret = $db->querySql($sql, []);
        if ($ret === false) {
            return array();
        }
        return $ret;
    }

    public function updateStatus($id, $status) {
        $row = array(
            'status' => $status,
        );
        $conds = array(
            'id' => $id,
        );
        return $this->update($row, $conds);
    }

    public function updateStatusByGroupIdBatch($arrGroupId, $status) {
        if (!is_array($arrGroupId)) {
            return $this->updateStatusByGroupId($arrGroupId, $status);
        }
        $row = array(
            'status' => $status,
        );
        $conds = array(
            'group_id' => $arrGroupId,
        );
        return $this->update($row, $conds);
    }

    public function updateStatusByGroupId($groupId, $status) {
        $row = array(
            'status' => $status,
        );
        $conds = array(
            'group_id' => $groupId,
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
