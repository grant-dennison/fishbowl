<?php
namespace codehousing\db;

require_once $_SERVER["DOCUMENT_ROOT"] . "/db/DatabaseConnection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/db/exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/db/Hat.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/db/SlipOfPaper.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Logger.php";

use codehousing\io\Logger;

class HatAccessor {
    /** @var Logger */
    private $logger;

    private $hatId;

    /** @var DatabaseConnection */
    private $conn;

    /**
     * @param Logger $logger
     * @param string $hatUrlChunk
     * @throws MysqlException
     */
    public function __construct($logger, $hatUrlChunk) {
        $this->logger = $logger;
        $this->conn = new DatabaseConnection($this->logger);
        $cleanHatUrlChunk = $this->conn->clean($hatUrlChunk);
        $hatResult = $this->conn->query("SELECT * FROM " . Hat::TABLE . " WHERE url_chunk = '$cleanHatUrlChunk';");
        if($hatResult->getNumRows() < 1) {
            $result = $this->conn->query("INSERT INTO " . Hat::TABLE . "(url_chunk) VALUES ('$cleanHatUrlChunk');");
            $this->hatId = $result->getInsertId();
        } else {
            $this->hatId = $hatResult->getSingleResult()["id"];
        }
    }

    /**
     * @param SlipOfPaper $slipOfPaper
     */
    public function push($slipOfPaper) {
        $cleanText = $this->conn->clean($slipOfPaper->display_text);
        $this->conn->query("INSERT INTO " . SlipOfPaper::TABLE . "(hat_id, display_text) VALUES ('$this->hatId', '$cleanText');");
    }

    /**
     * @return SlipOfPaper
     * @throws MysqlException
     */
    public function pull() {
        $this->conn->begin_safe_transaction();
        $result = $this->conn->query("SELECT * FROM " . SlipOfPaper::TABLE . " WHERE hat_id = '$this->hatId' ORDER BY RAND() LIMIT 1;");
        if($result->getNumRows() < 1) {
            throw new MysqlException("Hat empty");
        }
        $slip = new SlipOfPaper();
        $row = $result->getSingleResult();
        $slip->id = $row["id"];
        $slip->display_text = $row["display_text"];
        $this->conn->query("DELETE FROM " . SlipOfPaper::TABLE . " WHERE id = '$slip->id';");
        $this->conn->commit_safe();
        return $slip;
    }
}
