<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/db/HatAccessor.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Logger.php";

use codehousing\db\HatAccessor;
use codehousing\io\Logger;

$hatUrlChunk = $_REQUEST["hatId"];

$hatAccessor = new HatAccessor(new Logger(), $hatUrlChunk);
$slipOfPaper = $hatAccessor->pull();

$response = [
    "id" => $slipOfPaper->id,
    "displayText" => $slipOfPaper->display_text
];
echo json_encode($response);