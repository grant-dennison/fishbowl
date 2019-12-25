<?php

namespace codehousing\io;


class Logger {
    const NO_ARG = Logger::class;

    public function getLogLocation() {
        return $_SERVER["DOCUMENT_ROOT"] . "/.logger.log";
    }

    public function error($title, $body = self::NO_ARG) {
        $this->logMessage("ERROR", $title, $body);
    }

    public function debug($title, $body = self::NO_ARG) {
        $this->logMessage("DEBUG", $title, $body);
    }

    public function warn($title, $body = self::NO_ARG) {
        $this->logMessage("WARN", $title, $body);
    }

    private function logMessage($level, $title, $body) {
        $message = "$level: " . $this->stringify($title);
        if($body !== self::NO_ARG) {
            $message .= ":\n" . $this->stringify($body);
        }
        $this->errorLog($message);
    }

    private function errorLog($message) {
        //Default error log (php.ini)
        error_log($message);
        //Website-local error log
        error_log("[" . date("Y-m-d H:i:s") . "] "
            . $message . "\n", 3,
            $this->getLogLocation());
    }

    private static function stringify($thing) {
        if(!is_string($thing)) {
            return print_r($thing, true);
        }
        return $thing;
    }
}