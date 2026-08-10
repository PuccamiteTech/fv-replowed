<?php

require_once AMFPHP_ROOTPATH . "Helpers/general_functions.php";
require_once AMFPHP_ROOTPATH . "Helpers/constants.php";

function addWater($uid, $amount, $worldType = 'farm') {
    $data = getIrrigationData($uid);

    if (!isset($data['waterPlots'][$worldType])) {
        $data['waterPlots'][$worldType] = ['amount' => IRRIGATION_DEFAULT_WATER];
    }

    $current = (int) ($data['waterPlots'][$worldType]['amount'] ?? 0);
    $newAmount = min($current + $amount, IRRIGATION_MAX_WATER);
    $data['waterPlots'][$worldType]['amount'] = $newAmount;

    setIrrigationData($uid, $data);
    return $newAmount;
}

function useWater($uid, $amount, $worldType = 'farm') {
    $data = getIrrigationData($uid);

    if (!isset($data['waterPlots'][$worldType])) {
        $data['waterPlots'][$worldType] = ['amount' => IRRIGATION_DEFAULT_WATER];
    }

    $current = (int) ($data['waterPlots'][$worldType]['amount'] ?? 0);

    if ($current < $amount) {
        return false;
    }

    $data['waterPlots'][$worldType]['amount'] = $current - $amount;
    setIrrigationData($uid, $data);
    return true;
}

function getIrrigationData($uid) {
    $default = [
        'waterPlots' => [
            'farm' => ['amount' => IRRIGATION_DEFAULT_WATER]
        ]
    ];

    $meta = get_meta($uid, IRRIGATION_META_KEY);

    if ($meta) {
        $data = @unserialize($meta);
        if (is_array($data)) {
            if (!isset($data['waterPlots'])) {
                $data['waterPlots'] = $default['waterPlots'];
            }
            
            if (!isset($data['waterPlots']['farm'])) {
                $data['waterPlots']['farm'] = ['amount' => IRRIGATION_DEFAULT_WATER];
            }
            
            if (!isset($data['waterPlots']['farm']['amount'])) {
                $data['waterPlots']['farm']['amount'] = IRRIGATION_DEFAULT_WATER;
            }

            return $data;
        }
    }

    return $default;
}

function setIrrigationData($uid, $data) {
    set_meta($uid, IRRIGATION_META_KEY, serialize($data));
}

function getWaterAmount($uid, $worldType = 'farm') {
    $data = getIrrigationData($uid);
    if (isset($data['waterPlots'][$worldType]['amount'])) {
        return (int) $data['waterPlots'][$worldType]['amount'];
    }
    return IRRIGATION_DEFAULT_WATER;
}
