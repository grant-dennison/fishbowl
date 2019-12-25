<?php
namespace codehousing\db;

require_once $_SERVER["DOCUMENT_ROOT"] . "/config/SiteConfig.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/db/DatabaseQueryResult.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/db/exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Logger.php";

use codehousing\config\SiteConfig;
use codehousing\io\Logger;

class DatabaseConnection {
    private static $READ_COMMANDS = ["SELECT"];
    private static $WRITE_COMMANDS = ["INSERT", "UPDATE", "DELETE", "LOCK", "UNLOCK"];

    const MYSQL_DATE_TIME_FORMAT = "Y-m-d H:i:s";

    /**
     * @var \mysqli
     */
    private $mysqli;

    /** @var Logger */
    private $logger;

    /** @var bool */
    private $inSafeTransaction = false;

    /**
     * DatabaseConnection constructor.
     * @param Logger $logger
     * @throws MysqlException
     */
    public function __construct($logger) {
        $this->logger = $logger;
        $host = SiteConfig::getDatabaseHost();
        $username = SiteConfig::getDatabaseUsername();
        SiteConfig::yesIKnowImAccessingSomethingSecure();
        $password = SiteConfig::getDatabasePassword();
        $dbName = SiteConfig::getDatabaseName();
        // Create connection
        $this->mysqli = new \mysqli($host, $username, $password, $dbName);
        if ($this->mysqli->connect_error) {
            throw new MysqlException("Connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function extract_int($str) {
        if(preg_match('/[+-]?\d*/', $str, $matches) == 1) {
            return $matches[0];
        }
        else {
            return "";
        }
    }

    public function extract_decimal($str) {
        if(preg_match('/[+-]?(\d+(\.\d+)?|\.\d+)/', $str, $matches) == 1) {
            return $matches[0];
        }
        else {
            return "";
        }
    }

    public function formatDateTime(\DateTime $dateTime) {
        return $dateTime->format(self::MYSQL_DATE_TIME_FORMAT);
    }

    public function clean($str) {
        return $this->mysqli->escape_string($str);
    }

    public function log_error_query($query) {
        $this->log_error("Problem query: \"$query\"");
    }

    public function log_error($description) {
        $this->logger->error($description, $this->mysqli->error);
    }

    public function begin_safe_transaction() {
        $result = $this->mysqli->autocommit(false);
        if($result) {
            $this->inSafeTransaction = true;
        }
        return $result;
    }

    public function commit_safe() {
        if(!$this->inSafeTransaction) {
            return false;
        }

        $result = $this->mysqli->commit();
        $this->mysqli->autocommit(true);
        $this->inSafeTransaction = false;
        return $result;
    }

    public function rollback_safe() {
        $result = $this->mysqli->rollback();
        $this->mysqli->autocommit(true);
        return $result;
    }

    /**
     * @param string $query
     * @param int $resultmode
     * @return DatabaseQueryResult
     * @throws MysqlException
     */
    public function query($query, $resultmode = MYSQLI_STORE_RESULT) {
        if(!$this->isAllowed($query)) {
            throw new MysqlException("query not allowed");
        }

//        Logger::debug("Running query", $query);

        $result = $this->mysqli->query($query, $resultmode);
        if(!$result) {
            $this->log_error_query($query);
            // Flag this transaction as no longer safe (if applicable)
            $this->inSafeTransaction = false;
            throw new MysqlException("query failed");
        }
        return new DatabaseQueryResult($this->logger, $this->mysqli, $result);
    }

    public function checked_multi_query($fullQuery) {
        $queryArray = explode(';', $fullQuery);

        foreach($queryArray as $lineQuery) {
            if(trim($lineQuery) !== "" && !$this->isAllowed($lineQuery)) {
                return false;
            }
        }

        // Run the SQL
        $i = 0;
        if($this->mysqli->multi_query($fullQuery)) {
            do {
                $this->mysqli->next_result();
                $i++;
            }
            while($this->mysqli->more_results());
        }

        if($this->mysqli->errno) {
            $errorMessage = "Query " . ($i + 1) . " of multi_query failed.\n"
                . "\t\t (possibly) \"" . $queryArray[$i];
            $this->log_error($errorMessage);
            return false;
        }
        return true;
    }

    private function isAllowed($query) {
        $trimmedQuery = trim($query);
        foreach(self::$READ_COMMANDS as $command) {
            if(substr($trimmedQuery, 0, strlen($command)) === $command) {
                return SiteConfig::isDatabaseReadEnabled();
            }
        }

        foreach (self::$WRITE_COMMANDS as $command) {
            if(substr($trimmedQuery, 0, strlen($command)) === $command) {
                return SiteConfig::isDatabaseWriteEnabled();
            }
        }

        $this->logger->error("Foreign SQL command in query", $trimmedQuery);
        return false;
    }
}
