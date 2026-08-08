<?php

require_once 'config.php';

class Database {
    public const FETCH_NONE = 0;
    public const FETCH_ONE = 1;
    public const FETCH_ALL = 2;

    private static string $host = DB_SERVER;
    private static string $username = DB_USERNAME;
    private static string $password = DB_PASSWORD;
    private static string $dbname = DB_NAME;
    private static ?mysqli $instance = null;

    private function __construct() {}

    public static function instance(): mysqli {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new mysqli(self::$host, self::$username, self::$password, self::$dbname);

        if (self::$instance->connect_error){
            throw new RuntimeException("ERROR: Database connection failed");
        }

        return self::$instance;
    }

    public static function query(string $query, array $params, string $types, int $mode = self::FETCH_NONE): ?array {
        if (self::$instance === null) {
            self::instance();
        }

        if (!is_string($query) || !is_array($params) || !is_string($types) || !is_int($mode)) {
            return null;
        }
        elseif ($mode < self::FETCH_NONE || $mode > self::FETCH_ALL) {
            return null;
        }
        elseif (count($params) !== strlen($types)) {
            return null;
        }
        elseif (!($stmt = self::$instance->prepare($query))) {
            return null;
        }
        elseif (!($stmt->bind_param($types, ...$params))) {
            return null;
        }
        elseif (!($stmt->execute())) {
            return null;
        }
        
        $data = [];
        $result = null;

        if ($mode !== self::FETCH_NONE && !($result = $stmt->get_result())) {
            return null;
        }
        elseif ($mode === self::FETCH_ONE) {
            $data = $result->fetch_assoc() ?: null;
        }
        elseif ($mode === self::FETCH_ALL) {
            $buffer = null;

            while ($buffer = $result->fetch_assoc()) {
                $data[] = $buffer;
            }

            if ($buffer === false) {
                return null;
            }
        }
        else {
            $data["affected_rows"] = ($stmt->affected_rows ?: 0);
        }

        return $data;
    }

    public static function destroy(): void {
        if (self::$instance !== null) {
            self::$instance->close();
        }

        self::$instance = null;
    }
}
