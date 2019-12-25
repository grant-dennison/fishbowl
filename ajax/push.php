<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/db/HatAccessor.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/db/SlipOfPaper.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Logger.php";

use codehousing\db\HatAccessor;
use codehousing\db\SlipOfPaper;
use codehousing\io\Logger;

$hatUrlChunk = $_REQUEST["hatId"];
$slipText = $_REQUEST["displayText"];

$hatAccessor = new HatAccessor(new Logger(), $hatUrlChunk);
$slipOfPaper = new SlipOfPaper();
$slipOfPaper->display_text = $slipText;
$hatAccessor->push($slipOfPaper);