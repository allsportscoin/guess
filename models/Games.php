<?php
 
class GamesModel {
    
    public $_tableName = 'games';
    private $_fields = 'id, name, status, create_time';
    protected static $_model = null;

    private $_arrGame = array(
        '0'   => array(
            'id' => 1,
            'name'  => '',
        ),
        '1'   => array(
            'id' => 2,
            'name'  => '',
        ),
        '2'   => array(
            'id' => 3,
            'name'  => '',
        ),
        '3'   => array(
            'id' => 4,
            'name'  => '',
        ),
    );

    public static function getInstance() {
        if (self::$_model === null) {
            self::$_model = new self();
        }
        return self::$_model;
    }

    public function insert() {
    }

    public function getGameById($id) {
        if (!isset($this->_arrGame[$id])) {
            return null;
        }
        return $this->_arrGame[$id];
    }

    public function getGamesByStatus($status) {
        return $this->_arrGame;
    }
}
