<?php

namespace codehousing\db;

class MysqlException extends \Exception {
}

class TryAgainMysqlException extends MysqlException {
}

class InputException extends \Exception {

}