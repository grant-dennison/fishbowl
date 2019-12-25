<?php

namespace codehousing\config;

class SiteConfig {
    const SITE_VERSION = "1.0.0";

    private static $systemMessages = [];
    private static $isDatabaseReadDisabled = false;
    private static $isDatabaseWriteDisabled = false;

    private static $allowRobots = false;

    private static $requireHTTPS = true;

    private static $brandName = "Into the Hat";
    private static $domainName = "intothehat.test";

    private static $dbHost;
    private static $dbUser;
    private static $dbPassword;
    private static $dbDatabaseName;

    private static $recaptchaSiteKey;
    private static $recaptchaSecretKey;

    private static $knowsAccessingSecure = false;

    public static function yesIKnowImAccessingSomethingSecure() {
        self::$knowsAccessingSecure = true;
    }

    private static function ensureUserKnowsSecure() {
        if(!self::$knowsAccessingSecure) {
            throw new \Exception("Developer inadvertently accessed secure config.");
        }
        self::$knowsAccessingSecure = false;
    }

    public static function systemMessage(...$messages) {
        self::$systemMessages = array_merge(self::$systemMessages, $messages);
    }

    public static function getSystemMessages() {
        return self::$systemMessages;
    }

    public static function disableAllDatabaseAccess() {
        self::$isDatabaseReadDisabled = true;
        self::$isDatabaseWriteDisabled = true;
    }

    public static function disableDatabaseWrites() {
        self::$isDatabaseWriteDisabled = true;
    }

    public static function isDatabaseReadEnabled() {
        return !self::$isDatabaseReadDisabled;
    }

    public static function isDatabaseWriteEnabled() {
        return !self::$isDatabaseWriteDisabled;
    }

    public static function setAllowRobots($allowRobots) {
        self::$allowRobots = $allowRobots;
    }
    public static function getAllowRobots() {
        return self::$allowRobots;
    }

    public static function setBrandName($name) {
        self::$brandName = $name;
    }
    public static function getBrandName() {
        self::$knowsAccessingSecure = false;
        return self::$brandName;
    }

    public static function setDomainName($name) {
        self::$domainName = $name;
    }
    public static function getDomainName() {
        self::$knowsAccessingSecure = false;
        return self::$domainName;
    }

    public static function requireHTTPS($isRequired) {
        self::$requireHTTPS = $isRequired;
    }
    public static function isHTTPSRequired() {
        return self::$requireHTTPS;

    }

    public static function setDatabaseParameters($host, $username, $password, $dbName) {
        self::$dbHost = $host;
        self::$dbUser = $username;
        self::$dbPassword = $password;
        self::$dbDatabaseName = $dbName;
    }

    public static function getDatabaseHost() {
        self::$knowsAccessingSecure = false;
        return self::$dbHost;
    }

    public static function getDatabaseUsername() {
        self::$knowsAccessingSecure = false;
        return self::$dbUser;
    }

    public static function getDatabasePassword() {
        self::ensureUserKnowsSecure();
        return self::$dbPassword;
    }

    public static function getDatabaseName() {
        self::$knowsAccessingSecure = false;
        return self::$dbDatabaseName;
    }

    public static function setReCAPTCHASecretKey($secretKey) {
        self::$recaptchaSecretKey = $secretKey;
    }
    public static function setReCAPTCHASiteKey($siteKey) {
        self::$recaptchaSiteKey = $siteKey;
    }
    public static function getReCAPTCHASecretKey() {
        self::ensureUserKnowsSecure();
        return self::$recaptchaSecretKey;
    }
    public static function getReCAPTCHASiteKey() {
        self::$knowsAccessingSecure = false;
        return self::$recaptchaSiteKey;
    }
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/config.php";
