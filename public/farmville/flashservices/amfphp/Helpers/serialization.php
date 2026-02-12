<?php

/**
 * Safely unserialize data. Returns null for invalid payloads.
 *
 * @param mixed $value
 * @param array|false $allowedClasses
 * @return mixed
 */
function safe_unserialize($value, $allowedClasses = ['stdClass']) {
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
 *
 * @param mixed $value
 * @param array|false $allowedClasses
 * @return array
 */
function safe_unserialize_array($value, $allowedClasses = ['stdClass']) {
    $data = safe_unserialize($value, $allowedClasses);

    return is_array($data) ? $data : [];
}
