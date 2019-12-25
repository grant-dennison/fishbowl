<?php
namespace codehousing\db;

require_once $_SERVER["DOCUMENT_ROOT"] . "/config/SiteConfig.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/db/exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Logger.php";

use codehousing\config\SiteConfig;
use codehousing\io\Logger;

class DatabaseQueryResult {
    private $insertId;
    private $rawResult;
    private $rowCount;
    private $used;

    /**
     * DatabaseConnection constructor.
     * @param Logger $logger
     * @param \mysqli $rawConn
     * @param \mysqli_result $rawResult
     * @throws MysqlException
     */
    public function __construct($logger, $rawConn, $rawResult) {
        $this->logger = $logger;
        $this->insertId = $rawConn->insert_id;
        $this->rawResult = $rawResult;
        $this->rowCount = $rawResult->num_rows;
        $this->used = false;
    }

    public function getNumRows() {
        return $this->rowCount;
    }

    public function getInsertId() {
        return $this->insertId;
    }

    /**
     * @return array
     */
    public function getSingleResult() {
        $row = $this->rawResult->fetch_assoc();
        return $row;
    }

    /**
     * @param callable $callback
     */
    public function forEachResult($callback) {
        while($row = $this->rawResult->fetch_assoc()) {
            call_user_func($callback, $row);
        }
    }
}
