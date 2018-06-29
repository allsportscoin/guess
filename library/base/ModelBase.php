<?php
namespace library\base;

use library\util\Utils;
use library\define\Constant;

class ModelBase{
    public $_db = null;
    public function __construct() {
	}

	public function rollback() {
	}

	public function commit() {
	}

	public function startTransaction() {
	}

}
