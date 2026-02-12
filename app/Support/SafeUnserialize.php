<?php

namespace App\Support;

final class SafeUnserialize
{
    /**
     * Safely unserialize data. Returns null for invalid payloads.
     *
     * @param mixed $value
     * @param array|false $allowedClasses
     * @return mixed
     */
    public static function value($value, $allowedClasses = false)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $data = @unserialize($value, ['allowed_classes' => $allowedClasses]);

        if ($data === false && $value !== 'b:0;') {
            return null;
        }

        return $data;
    }

    /**
     * Safely unserialize into an array, or return an empty array.
     */
    public static function arrayOrEmpty($value, $allowedClasses = false): array
    {
        $data = self::value($value, $allowedClasses);

        return is_array($data) ? $data : [];
    }

    /**
     * Safely unserialize into an array, or return null.
     */
    public static function arrayOrNull($value, $allowedClasses = false): ?array
    {
        $data = self::value($value, $allowedClasses);

        return is_array($data) ? $data : null;
    }
}
