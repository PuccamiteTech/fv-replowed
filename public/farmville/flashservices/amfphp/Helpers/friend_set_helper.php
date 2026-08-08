<?php
require_once AMFPHP_ROOTPATH . "Helpers/database.php";
require_once AMFPHP_ROOTPATH . "Helpers/user_resources.php";
require_once AMFPHP_ROOTPATH . "Helpers/json_helper.php";

// TODO: delegate to updateFriendSet

function getFriendSet($uid, $code) {
    if (!is_numeric($uid) || !is_string($code)) {
        return null;
    }

    $row = Database::query("SELECT * FROM friend_sets WHERE uid = ? AND code = ? ORDER BY fs_index DESC LIMIT 1", [$uid, $code], "ss", Database::FETCH_ONE);
    return $row;
}

function getFriendSetByIndex($uid, $code, $fsIndex) {
    if (!is_numeric($uid) || !is_string($code) || !is_int($fsIndex)) {
        return null;
    }

    $row = Database::query("SELECT * FROM friend_sets WHERE uid = ? AND code = ? AND fs_index = ? LIMIT 1", [$uid, $code, $fsIndex], "ssi", Database::FETCH_ONE);
    return $row;
}

function createFriendSet($uid, $code, $worldCode, $totalRequired = 5) {
    if (!is_numeric($uid) || !is_string($code) || !is_string($worldCode) || !is_int($totalRequired)) {
        return null;
    }

    $existing = getFriendSet($uid, $code);
    $nextIndex = $existing ? ((int) $existing['fs_index'] + 1) : 1;

    $neighbors = get_meta($uid, 'current_neighbors');
    $neighborUids = $neighbors ? (@unserialize($neighbors) ?: []) : [];

    $friends = new \stdClass();
    $count = 0;
    foreach ($neighborUids as $nUid) {
        if ($count >= $totalRequired) {
            break;
        }
        $friends->{"_" . $nUid} = "0";
        $count++;
    }

    $friendsJson = JsonHelper::safeEncode($friends);
    $pendingJson = JsonHelper::safeEncode([]);
    $startTime = time();

    Database::query("INSERT INTO friend_sets (uid, code, fs_index, friends, pending, bought_count, progress_state, start_time, world_code, reward_link) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$uid, $code, $nextIndex, $friendsJson, $pendingJson, 0, 0, $startTime, $worldCode, ''], "ssissiiiss");

    return getFriendSetByIndex($uid, $code, $nextIndex);
}

function updateFriendSet($data) {
    if (!is_array($data) || !isset($data['uid']) || !isset($data['code']) || !isset($data['fs_index'])) {
        return false;
    }

    $fallback = getFriendSetByIndex($data['uid'], $data['code'], $data['fs_index']);

    if ($fallback === null) {
        return false;
    }

    $friends = $data['friends'] ?? $fallback['friends'];
    $pending = $data['pending'] ?? $fallback['pending'];
    $boughtCount = $data['bought_count'] ?? $fallback['bought_count'];
    $progressState = $data['progress_state'] ?? $fallback['progress_state'];
    $id = $fallback['id'];

    $result = Database::query("UPDATE friend_sets SET friends = ?, pending = ?, bought_count = ?, progress_state = ? WHERE id = ?",
        [$friends, $pending, $boughtCount, $progressState, $id], "ssiii");

    return (($result["affected_rows"] ?? 0) > 0);
}

function updateProgressState($uid, $code, $fsIndex, $newState) {
    if (!is_numeric($uid) || !is_string($code) || !is_int($fsIndex) || !is_int($newState)) {
        return false;
    }

    $result = Database::query("UPDATE friend_sets SET progress_state = ? WHERE uid = ? AND code = ? AND fs_index = ?",
        [$newState, $uid, $code, $fsIndex], "issi");

    return (($result["affected_rows"] ?? 0) > 0);
}

function completeFriendSetWithCash($uid, $code, $fsIndex, $totalRequired = 5, $costPerFriend = 4) {
    $fs = getFriendSetByIndex($uid, $code, $fsIndex);
    if (!$fs) {
        return ["status" => 2, "cost" => 0, "data" => null, "gifts" => []];
    }

    $friends = JsonHelper::safeDecode($fs['friends'], true, []);
    $boughtCount = (int) $fs['bought_count'];
    $worldCode = $fs['world_code'] ?? "2dvd";
    $progressState = (int) ($fs['progress_state'] ?? 0);

    $item = getItemByCode($worldCode);
    $worldName = $item ? ($item['name'] ?? $worldCode) : $worldCode;

    $completedCount = 0;
    foreach ($friends as $val) {
        if ((int) $val > 0) {
            $completedCount++;
        }
    }
    $completedCount += $boughtCount;

    if ($completedCount >= $totalRequired) {
        if ($progressState < 2) {
            updateProgressState($uid, $code, $fsIndex, 2);
            addGiftByCode($uid, $worldCode);

            $updatedFs = getFriendSetByIndex($uid, $code, $fsIndex);
            return [
                "status" => 1,
                "cost" => 0,
                "data" => buildFriendSetResponse($updatedFs),
                "gifts" => [$worldName]
            ];
        }

        return [
            "status" => 2,
            "cost" => 0,
            "data" => buildFriendSetResponse($fs),
            "gifts" => []
        ];
    }

    $missing = $totalRequired - $completedCount;
    $cashCost = $missing * $costPerFriend;

    if (!UserResources::removeCash($uid, $cashCost)) {
        return ["status" => 2, "cost" => 0, "data" => buildFriendSetResponse($fs), "gifts" => []];
    }

    $newBought = $boughtCount + $missing;

    Database::query("UPDATE friend_sets SET bought_count = ?, progress_state = 2 WHERE uid = ? AND code = ? AND fs_index = ?",
        [$newBought, $uid, $code, $fsIndex], "issi");

    $updatedFs = getFriendSetByIndex($uid, $code, $fsIndex);
    addGiftByCode($uid, $worldCode);

    return [
        "status" => 1,
        "cost" => $cashCost,
        "data" => buildFriendSetResponse($updatedFs),
        "gifts" => [$worldName]
    ];
}

function buildFriendSetResponse($row) {
    if (!$row) {
        return [];
    }

    $uids = JsonHelper::safeDecode($row['friends'], true, []);
    if (empty($uids)) $uids = new \stdClass();

    $pending = JsonHelper::safeDecode($row['pending'], true, []);

    return [
        "uids"              => $uids,
        "pending"           => $pending,
        "boughtFriendCount" => (int) $row['bought_count'],
        "startTime"         => (int) $row['start_time'],
        "rewardLink"        => $row['reward_link'] ?? "",
    ];
}

function recordFriendHelp($hostUid, $helperUid, $code = "FS06", $totalRequired = 5) {
    if (!is_numeric($hostUid) || !is_numeric($helperUid) || !is_string($code) || !is_int($totalRequired) || $hostUid == $helperUid) {
        return false;
    }

    $fs = Database::query("SELECT * FROM friend_sets WHERE uid = ? AND code = ? AND progress_state < 2 ORDER BY fs_index DESC LIMIT 1",
        [$hostUid, $code], "ss", Database::FETCH_ONE);

    if ($fs === null) {
        return false;
    }

    $friends = JsonHelper::safeDecode($fs['friends'], true, []);
    $boughtCount = (int) $fs['bought_count'];
    $helperKey = "_" . $helperUid;

    if (isset($friends[$helperKey]) && (int) $friends[$helperKey] > 0) {
        return false;
    }

    $helpedCount = 0;
    foreach ($friends as $val) {
        if ((int) $val > 0) {
            $helpedCount++;
        }
    }

    $totalCompleted = $helpedCount + $boughtCount;
    if ($totalCompleted >= $totalRequired) {
        return false;
    }

    $friends[$helperKey] = "1";
    $helpedCount++;
    $totalCompleted++;

    $fs['friends'] = JsonHelper::safeEncode($friends);

    if ($totalCompleted >= $totalRequired) {
        $fs['progress_state'] = 2;
        updateFriendSet($fs);

        $worldCode = $fs['world_code'] ?? "2dvd";
        addGiftByCode($hostUid, $worldCode);
    } else {
        updateFriendSet($fs);
    }

    return true;
}
