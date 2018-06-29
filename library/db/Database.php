<?php
/**
 *
 * query:
 * $rows = $db->query($this->_table_name,array("content"=>"123","ip"=>"1","Type"=>1));
 *
 * insert a row
 * $uid = $db->insert($this->_table_name,array("content"=>"123","ip"=>"1","Type"=>1,"ctime"=>date("Y-m-d H:i:s")));
 *
 * update a row
 * $db->update($this->_table_name,array("ctime"=>date("Y-m-d H:i:s")),array("content"=>"123","ip"=>"1","Type"=>1));
 *
 * delete row
 * $db->delete($this->_table_name,array("content"=>"123","ip"=>"1","Type"=>1));
 * 
 */

namespace library\db;

use library\util\Conf;

class Database {

    const TIMEOUT = 2;
    const PARAMETER_IS_EMPTY = 30030001;
    const MYSQL_CONFIG_NAME_EMPTY = 30030002;
    const MYSQL_CONFIG_VALUE_CHECK = 30030003;

    private static $_dbPoll = [];
    private static $_db = null;
    private $_alias = '';
    private $_master = '';

    public static function getInstance() {
        if (self::$_db === null) {
            self::$_db = new self();
        }
        return self::$_db;
    }

    /**
     * 
     * @param  string  $alias  [description]
     * @param  boolean $master 1 master 0 slave
     * @return resource          [description]
     */
    public function selectDb($alias, $master = 1) {
        if (!isset(self::$_dbPoll[$alias][$master])) {
            self::$_dbPoll[$alias][$master] = $this->_connect($alias, $master);
        }

        $this->_alias = $alias;
        $this->_master = $master;

        return self::$_db;
    }

    /**
     *
     * select *|col1.col2... from $table where..order by...limit...
     * return all rows
     *
     * @param string $table  
     * @param array  $params where
     * @param array  $cols   select
     * @param array  $orders order by
     *                       array(
     *                          'col' => 'asc',
     *                       )
     * @param int    $limit 
     * @param int    $style
     *
     * @return array
     */
    public function query($table, array $params, array $cols = array(), array $orders = array(), $limit = 0, $style = \PDO::FETCH_ASSOC) {
        $sql  = $this->arrayToSql($table, $params, $cols, $orders, $limit);
        $stmt = $this->execute($sql['sql'], $sql['param']);
        return $stmt ? $stmt->fetchAll($style) : [];
    }

    /**
     * 
     * @param  string $sql    [description]
     * @param  array  $params [description]
     * @param  int $style  [description]
     * @return array         [description]
     */
    public function querySql($sql, array $params = array(), $style = \PDO::FETCH_ASSOC) {
        $stmt = $this->execute($sql, $params);

        return $stmt ? $stmt->fetchAll($style) : false;
    }

    /**
     * select *|col1.col2... from $table where..order by...limit...
     * return all rows/a row
     *
     * @param string $table  table name
     * @param array  $params after where
     * @param array  $cols   table.col_name
     * @param int    $style
     * @param array  $orders
     * @param int    $limit
     *
     * @return array
     */
    public function queryOne($table, array $params, array $cols = array(), array $orders = array(), $limit = 0, $style = \PDO::FETCH_ASSOC) {
        $sql  = $this->arrayToSql($table, $params, $cols, $orders, $limit);
        $stmt = $this->execute($sql['sql'], $sql['param']);

        return $stmt ? $stmt->fetch($style) : false;
    }

    public function querySqlOne($sql, array $params = array(), $style = \PDO::FETCH_ASSOC) {
        $stmt = $this->execute($sql, $params);

        return $stmt ? $stmt->fetch($style) : false;
    }

    /**
     * SELECT SQL_CALC_FOUND_ROWS * FROM tbl_name;SELECT FOUND_ROWS();
     *
     * @param       $table
     * @param array $params
     * @param array $cols
     * @param int   $style
     * @param array $orders
     * @param int   $limit
     *
     * @return array|bool
     */
    public function queryCount($table, array $params, array $cols = array(), array $orders = array(), $limit = 0, $style = \PDO::FETCH_ASSOC) {
        $sql = $this->arrayToSql($table, $params, $cols, $orders, $limit);
        $sql = preg_replace('/^\s*SELECT\s+/i', 'SELECT SQL_CALC_FOUND_ROWS ', $sql);

        $stmt = $this->execute($sql['sql'], $sql['param']);
        if (!$stmt) return false;
        //总数
        $rows['total'] = self::$_dbPoll[$this->_alias][$this->_master]->query('SELECT FOUND_ROWS()')->fetchColumn();
        $rows['data']  = $stmt->fetchAll($style);

        return $rows;
    }

    /**
     * 
     * @param  [type]  $table    [description]
     * @param  array   $params   [description]
     * @param  string  $col      [description]
     * @param  boolean $distinct [description]
     * @return [type]            [description]
     */
    public function count($table, array $params = array(), $col = "", $distinct = false) {
        $where_info = $this->_conditionToSqlInfo($params);
        $where      = $where_info['where'] ? "WHERE {$where_info['where']}" : "";
        if ($col) {
            $col = $distinct ? "DISTINCT " . $col : $col;
        } else {
            $col = "*";
        }

        $sql  = "SELECT count({$col}) FROM {$table} {$where}";
        $stmt = $this->execute($sql, $where_info['param']);

        return $stmt ? current($stmt->fetch(\PDO::FETCH_ASSOC)) : false;
    }

    public function querysum($table, array $params, $sum_col) {
        $where_info = $this->_conditionToSqlInfo($params);
        $sql        = "SELECT sum($sum_col) FROM {$table} WHERE {$where_info['where']}";
        $stmt       = $this->execute($sql, $where_info['param']);

        return $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
    }

    /**
     *
     * @param        $table
     * @param        $key
     * @param string $prefix
     * @param array  $params
     * @param array  $cols
     * @param array  $orders
     *
     * @return array|bool
     */
    public function queryKey($table, $key, $prefix, array $params, array $cols = array(), array $orders = array()) {
        $sql = $this->arrayToSql($table, $params, $cols, $orders);

        $stmt = $this->execute($sql['sql'], $sql['param']);
        if (!$stmt) return false;
        $data = array();
        if (is_array($key)) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $nkey = $prefix;
                foreach ($key as $k) {
                    $nkey .= "{$row[$k]}";
                }
                $data[$nkey] = $row;
            }
        } else {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $data[$prefix . $row[$key]] = $row;
            }
        }

        return $data;
    }

    /**
     *
     * @param       $table
     * @param       $key
     * @param       $value
     * @param       $params
     * @param array $orders
     * @param int   $limit
     *
     * @return array|bool
     */
    public function queryKv($table, $key, $value, $params = array(), array $orders = array(), $limit = 0) {
        $is_value_array = is_array($value);
        if ($key) {
            $cols = $is_value_array ? array_merge($value, array($key)) : array($key, $value);
        } else {
            $cols = $is_value_array ? $value : array($value);
        }
        $sql  = $this->arrayToSql($table, $params, $cols, $orders, $limit);
        $stmt = $this->execute($sql['sql'], $sql['param']);
        if (!$stmt) return false;

        $data = array();
        $i    = 0;
        if ($is_value_array) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $rowk = $row[$key];
                unset($row[$key]);
                $data[$rowk] = $row;
            }
        } else {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($key) {
                    if ($value) {
                        $data[$row[$key]] = $row[$value];
                    } else {
                        $data[$row[$key]] = $i++;
                    }
                } else {
                    $data[] = $row[$value];
                }
            }
        }

        return $data;
    }

    /**
     * 
     * @param  [type] $sql    [description]
     * @param  [type] $key    [description]
     * @param  [type] $value  [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public function querySqlKv($sql, $key, $value, $params = array()) {
        $stmt = $this->execute($sql, array_values($params));
        if (!$stmt) return false;

        $data           = array();
        $i              = 0;
        $is_value_array = is_array($value);
        if ($is_value_array) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $rowk = $row[$key];
                unset($row[$key]);
                $data[$rowk] = $row;
            }
        } else {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($key) {
                    if ($value) {
                        $data[$row[$key]] = $row[$value];
                    } else {
                        $data[$row[$key]] = $i++;
                    }
                } else {
                    $data[] = $row[$value];
                }
            }
        }

        return $data;
    }

    /**
     *
     * @param       $table
     * @param array $data
     * @param bool  $returnid
     * @param bool  $replaceinto
     *
     * @return bool|int
     * @throws \S\Exception
     */
    public function insert($table, array $data, $returnid = false, $replaceinto = false) {
        if (empty($data)) {
            return false;
            ///throw new Exception('data is empty:' . __LINE__, self::PARAMETER_IS_EMPTY);
        }

        $names       = array_keys($data);
        $values      = array_values($data);
        $fields      = implode('`,`', $names);
        $placeholder = substr(str_repeat('?, ', count($values)), 0, -2);
        $sql         = ($replaceinto ? 'REPLACE' : 'INSERT') . sprintf(' INTO `%s` (`%s`) VALUES (%s)', $table, $fields, $placeholder);

        $statement = $this->execute($sql, $values);
        if ($returnid) {
            return $statement === false ? false : (int)(self::$_dbPoll[$this->_alias][$this->_master]->lastInsertId());
        } else {
            return $statement ? true : false;
        }
    }

    /**
     *
     * @param       $table
     * @param array $primary
     * @param array $data
     * @param bool  $returnid
     *
     * @return bool|int 1 insert 2 update
     * @throws \S\Exception
     */
    public function duplicateInsert($table, array $primary, array $data, $returnid = false) {
        if (empty($data)) {
            //throw new Exception('data is empty:' . __LINE__, self::PARAMETER_IS_EMPTY);
            return false;
        }

        //$duplicationNames = array_keys($data);
        $insertData  = array_merge($primary, $data);
        $insertKeys  = array_keys($insertData);
        $fields      = implode('`,`', $insertKeys);
        $values      = '"' . implode('", "', $insertData) . '"';
        $sql         = sprintf('INSERT INTO `%s` (`%s`) VALUES (%s)', $table, $fields, $values) . ' ON DUPLICATE KEY UPDATE ';

        $duplication = [];
        foreach ($data as $key => $value) {
            $duplication[] = '`' . $key . '` = "' . $value . '"';
        }
        $sql .= implode(', ', $duplication);
        
        $statement = $this->execute($sql);
        if ($returnid) {
            return $statement === false ? false : (int)($statement->rowCount());
        } else {
            return $statement ? true : false;
        }
    }

    /**
     * 
     * @return int [description]
     */
    public function lastInsertId() {
        return self::$_dbPoll[$this->_alias][$this->_master]->lastInsertId();
    }

    /**
     *
     * @param       $table
     * @param array $fields
     * @param array $data
     * @param bool  $replaceinto
     *
     * @return bool
     * @throws \S\Exception
     */
    public function multiInsert($table, array $fields, array $data, $replaceinto = false) {
        if (empty($fields) || empty($data)) {
            //throw new Exception('fields or data is empty:' . __LINE__, self::PARAMETER_IS_EMPTY);
            return false;
        }

        $values     = array();
        $field_size = count($fields);
        foreach ($data as $v) {
            $vsize = count($v);
            if ($vsize < $field_size) {
                $v = array_fill($vsize, $field_size - $vsize, '');
            } elseif ($vsize > $field_size) {
                $v = array_slice($v, 0, $field_size);
            }
            $values = array_merge($values, array_values($v));
        }

        $fields      = implode('`,`', $fields);
        $placeholder = substr(str_repeat('?, ', $field_size), 0, -2);
        $value_sql   = substr(str_repeat("({$placeholder}),", count($data)), 0, -1);
        $sql         = ($replaceinto ? 'REPLACE' : 'INSERT') . sprintf(' INTO `%s` (`%s`) VALUES %s', $table, $fields, $value_sql);

        $statement = $this->execute($sql, $values);

        return $statement ? true : false;
    }

    /**
     *
     * @param       $table
     * @param array $data
     * @param array $condition
     * @param int   $limit
     * @param bool  $affectRow
     *
     * @return bool
     * @throws \S\Exception
     */
    public function update($table, array $data, array $condition, $limit = 0, $affectRow = false) {
        if (empty($data) || empty($condition)) {
            //throw new Exception('data is not an array or empty:' . __LINE__, self::PARAMETER_IS_EMPTY);
            return false;
        }

        $set_sql   = $this->_updateToSqlinfo($data);
        $where_sql = $this->_conditionToSqlInfo($condition);

        $sql = "UPDATE `{$table}` SET {$set_sql['field']} WHERE {$where_sql['where']}";
        if (intval($limit) >= 1) {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = $this->execute($sql, array_merge($set_sql['param'], $where_sql['param']), $affectRow);

        return $stmt ? true : false;
    }

    /**
     * delete a array data to $table
     *
     * @param       $table
     * @param array $condition
     * @param int   $limit
     * @param bool  $count 
     *
     * @return bool|int
     * @throws \S\Exception
     */
    public function delete($table, array $condition, $limit = 0, $count = false) {
        if (empty($condition)) {
            //throw new Exception('condition is empty:' . __LINE__, self::PARAMETER_IS_EMPTY);
            return false;
        }

        $condition_sql = $this->_conditionToSqlInfo($condition);

        $sql = "DELETE FROM `{$table}` WHERE " . $condition_sql['where'];
        if (intval($limit) >= 1) {
            $sql .= "LIMIT " . $limit;
        }
        $stmt = $this->execute($sql, $condition_sql['param']);

        return $stmt ? ($count ? $stmt->rowCount() : true) : false;
    }

    /**
     *
     * @param       $sql
     * @param array $params
     * @param bool  $rowcount 
     *
     * @return bool|\PDOStatement
     * @throws \S\Exception
     */
    public function execute($sql, array $params = array(), $rowcount = false) {
        try {
            $stmt = self::$_dbPoll[$this->_alias][$this->_master]->prepare($sql);
            if ($params) {
                $return = $stmt->execute($params);
            } else {
                $return = $stmt->execute();
            }
        } catch (\PDOException $e) {
            //throw new Exception($e->getMessage(), '3001' . $e->getCode());
            return false;
        }

        return $return ? ($rowcount ? $stmt->rowCount() : $stmt) : false;
    }

    /**
     *
     * @return bool
     */
    public function close($alias, $master) {
        if (isset(self::$_dbPoll[$alias])) {
            if ($master) {
                self::$_dbPoll[$alias]['master'] = null;
            } else {
                self::$_dbPoll[$alias]['slave'] = null;
            }
        }
        return true;
    }

    /**
     *
     * @param string $mode
     *
     * @return array
     */
    private function _config($alias, $master) {
        if ($master) {
            $config = Conf::get('mysql.' . $alias . '.write');
        } else {
            $config = Conf::get('mysql.' . $alias . '.read');
        }
        
        // if (!$master) {
        //     if (!$config['host'] && $config[0]['host']) {
        //         $config = $config[rand(0, count($config) - 1)];
        //     }
        // }
        $config['dsn'] = sprintf('mysql:host=%s;port=%s;dbname=%s', $config['host'], $config['port'], $config['name']);

        if (isset($config['charset'])) {
            $config['dsn'] .= ';charset=' . $config['charset'];
        }

        return $config;
    }

    /**
     * 
     * @param  string $alias  [description]
     * @param  bool $master [description]
     * @return resource         [description]
     */
    private function _connect($alias, $master) {
        $config = $this->_config($alias, $master);

        $option = array();
        if (isset($config['pconnect']) && $config['pconnect'] == 1) {
            $option[\PDO::ATTR_PERSISTENT] = true;
        }

        //      $option[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;

        $option[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;

        if (!empty($config['encoding'])) {
            $option[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES '{$config['encoding']}'";
        }

        $option[\PDO::ATTR_TIMEOUT] = self::TIMEOUT;
        if (isset($config['timeout'])) {
            $option[\PDO::ATTR_TIMEOUT] = $config['timeout'];
        }

        return new \PDO($config['dsn'], $config['user'], $config['pass'], $option);
    }

    /**
     *
     * @param       $table
     * @param array $params
     * @param array $cols
     * @param array $orders
     * @param int   $limit
     *
     * @return array('sql'=>'', 'param'=>array())
     * @throws \S\Exception
     */
    protected function arrayToSql($table, array $params, array $cols, array $orders = array(), $limit = 0) {
        $strcols = $this->arrayToFieldString($cols);
        if (!empty($params)) {
            $where_info = $this->_conditionToSqlInfo($params);
            $sql        = "SELECT {$strcols} FROM {$table} WHERE {$where_info['where']}";
        } else {
            $sql = "SELECT {$strcols} FROM {$table}";
        }

        $sql .= $this->arrayToOrderString($orders);

        if (is_array($limit)) {
            $sql .= ' LIMIT ' . intval(array_shift($limit)) . ", " . intval(array_shift($limit));
        } elseif (intval($limit) > 0) {
            $sql .= ' LIMIT ' . intval($limit);
        }

        return array('sql' => $sql, 'param' => isset($where_info['param']) ? $where_info['param'] : array());
    }

    /**
     *
     * @param array $params
     *
     * @return string
     */
    protected function arrayToFieldString(array $params) {
        return !empty($params) ? '`' . join('`,`', $params) . '`' : ' * ';
    }

    protected function arrayToOrderString(array $orders) {
        $sql = '';
        if (!empty($orders)) {
            $sql         = ' ORDER BY ';
            $orderstring = array();
            foreach ($orders as $col => $order) {
                $orderstring[] = "`{$col}` {$order}";
            }
            $sql .= join(',', $orderstring);
        }

        return $sql;
    }

    /**
     *
     * @param $condition
     *
     * @return array
     */
    private function _conditionToSqlInfo(array $condition) {
        $where = '';
        $param = array();
        foreach ($condition as $field => $value) {
            if (is_array($value)) {
                $tmp = rtrim(str_repeat('?,', count($value)), ',');
                $where .= "`{$field}` IN ({$tmp}) AND ";
                $param = array_merge($param, array_values($value));
            } else {
                $where .= "`{$field}`=? AND ";
                $param[] = $value;
            }

        }

        return array('where' => substr($where, 0, -5), 'param' => $param);
    }

    /**
     *
     * @param array $data
     *
     * @return array
     */
    private function _updateToSqlinfo(array $data) {
        $values = array_values($data);
        $fields = implode('`=?,`', array_keys($data));

        return array('field' => "`{$fields}`=?", 'param' => $values);
    }
}


