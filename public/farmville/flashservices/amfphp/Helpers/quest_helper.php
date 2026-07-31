<?php

require_once AMFPHP_ROOTPATH . "Helpers/globals.php";
require_once AMFPHP_ROOTPATH . "Helpers/general_functions.php";
require_once AMFPHP_ROOTPATH . "Helpers/user_resources.php";

define('META_QUEST_ACTIVE', 'quest_active');
define('META_QUEST_COMPLETED', 'quest_completed');
define('META_QUEST_COMPLETED_REPLAYABLE', 'quest_completed_replayable');

function getQuestByName($questName) {
    global $db;
    static $questCache = [];

    if (!is_string($questName)) {
        return false;
    }
    elseif (isset($questCache[$questName])) {
        return $questCache[$questName];
    }
    
    $conn = $db->getDb();
    $stmt = $conn->prepare("SELECT * FROM quests WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $questName);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows <= 0) {
        $db->destroy();
        $questCache[$questName] = null;
        return null;
    }

    $row = $result->fetch_assoc();
    $db->destroy();

    $row['prereqs'] = $row['prereqs'] ? json_decode($row['prereqs'], true) : [];
    $row['children'] = $row['children'] ? json_decode($row['children'], true) : [];
    $row['tasks'] = $row['tasks'] ? json_decode($row['tasks'], true) : [];
    $row['rewards'] = $row['rewards'] ? json_decode($row['rewards'], true) : [];
    $row['frontend'] = $row['frontend'] ? json_decode($row['frontend'], true) : [];
    $row['friend_reward'] = $row['friend_reward'] ? json_decode($row['friend_reward'], true) : null;

    $questCache[$questName] = $row;
    return $row;
}

function getQuestsByCategory($category) {
    global $db;

    if (!is_string($category)){
        return [];
    }

    $conn = $db->getDb();
    $stmt = $conn->prepare("SELECT name FROM quests WHERE category = ? ORDER BY priority");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $questNames = ($result) ? array_column($result->fetch_all(MYSQLI_ASSOC), 'name') : [];
    $quests = [];

    foreach ($questNames as $name) {
        if ($row = getQuestByName($name)) {
            $quests[] = $row;
        }
    }

    $db->destroy();

    return $quests;
}

function getActiveQuests($uid) {
    $raw = get_meta($uid, META_QUEST_ACTIVE);
    if ($raw) {
        $data = @unserialize($raw);
        if (is_array($data)) {
            return $data;
        }
    }
    return [];
}

function setActiveQuests($uid, $quests) {
    set_meta($uid, META_QUEST_ACTIVE, serialize($quests));
}

function getActiveQuestIds($uid) {
    $activeQuests = getActiveQuests($uid);
    return array_keys($activeQuests);
}

function getCompletedQuests($uid) {
    $raw = get_meta($uid, META_QUEST_COMPLETED);
    if ($raw) {
        $data = @unserialize($raw);
        if (is_array($data)) {
            return $data;
        }
    }
    return [];
}

function addCompletedQuest($uid, $questName) {
    $completed = getCompletedQuests($uid);
    if (!in_array($questName, $completed)) {
        $completed[] = $questName;
        set_meta($uid, META_QUEST_COMPLETED, serialize($completed));
    }
}

function getCompletedReplayableQuests($uid) {
    $raw = get_meta($uid, META_QUEST_COMPLETED_REPLAYABLE);
    if ($raw) {
        $data = @unserialize($raw);
        if (is_array($data)) {
            return $data;
        }
    }
    return [];
}

function addCompletedReplayableQuest($uid, $questName) {
    $completed = getCompletedReplayableQuests($uid);
    if (!in_array($questName, $completed)) {
        $completed[] = $questName;
        set_meta($uid, META_QUEST_COMPLETED_REPLAYABLE, serialize($completed));
    }
}

function hasCompletedQuest($uid, $questName) {
    $completed = getCompletedQuests($uid);
    return in_array($questName, $completed);
}

function startQuest($uid, $questName) {
    $row = getQuestByName($questName);
    if (!$row) {
        return null;
    }

    $activeQuests = getActiveQuests($uid);

    if (isset($activeQuests[$questName])) {
        return $activeQuests[$questName];
    }

    $taskCount = count($row['tasks']);
    $progress = array_fill(0, $taskCount, 0);

    $questState = [
        'name' => $questName,
        'startedAt' => time(),
        'progress' => $progress,
        'taskCount' => $taskCount,
        'removed' => false,
        'expired' => false,
        'completed' => false,
    ];

    $activeQuests[$questName] = $questState;
    setActiveQuests($uid, $activeQuests);

    return $questState;
}

function updateQuestProgress($uid, $questName, $taskIndex, $amount = 1) {
    $activeQuests = getActiveQuests($uid);

    if (!isset($activeQuests[$questName])) {
        return null;
    }

    $row = getQuestByName($questName);
    if (!$row || $taskIndex < 0 || $taskIndex >= count($row['tasks'])) {
        return null;
    }

    $task = $row['tasks'][$taskIndex];
    $total = isset($task['total']) ? (int)$task['total'] : 1;

    $currentProgress = $activeQuests[$questName]['progress'][$taskIndex];
    $activeQuests[$questName]['progress'][$taskIndex] = min($currentProgress + $amount, $total);

    setActiveQuests($uid, $activeQuests);

    return $activeQuests[$questName];
}

function checkAndCompleteQuest($uid, $questName) {
    $activeQuests = getActiveQuests($uid);

    if (!isset($activeQuests[$questName])) {
        return false;
    }

    $row = getQuestByName($questName);
    if (!$row) {
        return false;
    }

    foreach ($row['tasks'] as $idx => $task) {
        $total = isset($task['total']) ? (int)$task['total'] : 1;
        $progress = $activeQuests[$questName]['progress'][$idx];

        if ($progress < $total) {
            return false;
        }
    }

    $activeQuests[$questName]['completed'] = true;
    setActiveQuests($uid, $activeQuests);

    return true;
}

function completeQuest($uid, $questName, $worldType = 'farm') {
    $activeQuests = getActiveQuests($uid);
    $row = getQuestByName($questName);

    if (!$row || !isset($activeQuests[$questName])) {
        return ['success' => false, 'error' => 'Quest not found or not active'];
    }

    $rewardsGranted = grantQuestRewards($uid, $row['rewards'], $worldType);

    unset($activeQuests[$questName]);
    setActiveQuests($uid, $activeQuests);

    if ($row['replay']) {
        addCompletedReplayableQuest($uid, $questName);
    } else {
        addCompletedQuest($uid, $questName);
    }

    $childrenStarted = [];
    if (!empty($row['children'])) {
        foreach ($row['children'] as $child) {
            if ($child['type'] === 'Quest') {
                $childQuest = startQuestIfEligible($uid, $child['value']);
                if ($childQuest) {
                    $childrenStarted[] = $child['value'];
                }
            }
        }
    }

    return [
        'success' => true,
        'rewards' => $rewardsGranted,
        'childrenStarted' => $childrenStarted,
    ];
}

function grantQuestRewards($uid, $rewards, $worldType = 'farm') {
    $granted = [];

    foreach ($rewards as $reward) {
        $type = $reward['type'];
        $value = $reward['value'];
        $quantity = isset($reward['quantity']) ? (int)$reward['quantity'] : 1;

        switch ($type) {
            case 'xp':
                UserResources::addXp($uid, (int)$value);
                $granted[] = ['type' => 'xp', 'amount' => (int)$value];
                break;

            case 'coins':
                UserResources::addGold($uid, (int)$value);
                $granted[] = ['type' => 'coins', 'amount' => (int)$value];
                break;

            case 'cash':
                UserResources::addCash($uid, (int)$value);
                $granted[] = ['type' => 'cash', 'amount' => (int)$value];
                break;

            case 'item_grant':
                addGiftByCode($uid, $value, $quantity);
                $granted[] = ['type' => 'item_grant', 'itemCode' => $value, 'quantity' => $quantity];
                break;

            case 'world_score':
                addWorldScore($uid, $worldType, (int)$value);
                $granted[] = ['type' => 'world_score', 'amount' => (int)$value];
                break;

            case 'avatar_item':
                unlockAvatarItem($uid, $value);
                $granted[] = ['type' => 'avatar_item', 'item' => $value];
                break;

            case 'seen_flag':
                setUserSeenFlag($uid, $value);
                $granted[] = ['type' => 'seen_flag', 'flag' => $value];
                break;

            default:
                $granted[] = ['type' => $type, 'value' => $value, 'skipped' => true];
                break;
        }
    }

    return $granted;
}

function addWorldScore($uid, $worldType, $amount) {
    $key = "world_score_$worldType";
    $current = (int)get_meta($uid, $key) ?: 0;
    set_meta($uid, $key, (string)($current + $amount));
}

function unlockAvatarItem($uid, $itemCode) {
    $items = get_meta($uid, 'avatar_items');
    $itemArray = $items ? @unserialize($items) : [];

    if (!is_array($itemArray)) {
        $itemArray = [];
    }
    elseif (!in_array($itemCode, $itemArray)) {
        $itemArray[] = $itemCode;
        set_meta($uid, 'avatar_items', serialize($itemArray));
    }
}

function setUserSeenFlag($uid, $flag) {
    $flags = get_meta($uid, 'seen_flags');
    $flagArray = $flags ? @unserialize($flags) : [];

    if (!is_array($flagArray)) {
        $flagArray = [];
    }
    elseif (!isset($flagArray[$flag])) {
        $flagArray[$flag] = time();
        set_meta($uid, 'seen_flags', serialize($flagArray));
    }
}

function checkQuestPrereqs($uid, $prereqs, $playerLevel = 1) {
    if (empty($prereqs)) {
        return true;
    }

    $now = time();

    foreach ($prereqs as $prereq) {
        $type = $prereq['type'];
        $value = $prereq['value'];

        switch ($type) {
            case 'level_min':
                if ($playerLevel < (int)$value) {
                    return false;
                }
                break;

            case 'quest_complete':
                if (!hasCompletedQuest($uid, $value)) {
                    return false;
                }
                break;

            case 'start_time':
            case 'end_time':
                break;

        }
    }

    return true;
}

function startQuestIfEligible($uid, $questName, $playerLevel = 1) {
    $row = getQuestByName($questName);
    if (!$row) {
        return null;
    }

    $activeQuests = getActiveQuests($uid);
    if (isset($activeQuests[$questName])) {
        return null;
    }
    elseif (!$row['replay'] && hasCompletedQuest($uid, $questName)) {
        return null;
    }
    elseif (!checkQuestPrereqs($uid, $row['prereqs'], $playerLevel)) {
        return null;
    }

    return startQuest($uid, $questName);
}

function removeQuest($uid, $questName) {
    $activeQuests = getActiveQuests($uid);

    if (!isset($activeQuests[$questName])) {
        return false;
    }

    unset($activeQuests[$questName]);
    setActiveQuests($uid, $activeQuests);

    return true;
}

function skipQuestTask($uid, $questName, $taskIndex) {
    $row = getQuestByName($questName);
    $activeQuests = getActiveQuests($uid);

    if (!$row || !isset($activeQuests[$questName])) {
        return ['success' => false, 'error' => 'Quest not found or not active'];
    }
    elseif ($taskIndex < 0 || $taskIndex >= count($row['tasks'])) {
        return ['success' => false, 'error' => 'Invalid task index'];
    }

    $task = $row['tasks'][$taskIndex];
    $cashValue = isset($task['cashValue']) ? (int)$task['cashValue'] : 5;

    $userCash = UserResources::getCash($uid);
    if ($userCash < $cashValue) {
        return ['success' => false, 'error' => 'Not enough cash', 'required' => $cashValue];
    }

    UserResources::addCash($uid, -$cashValue);

    $total = isset($task['total']) ? (int)$task['total'] : 1;
    $activeQuests[$questName]['progress'][$taskIndex] = $total;
    setActiveQuests($uid, $activeQuests);

    return [
        'success' => true,
        'cashSpent' => $cashValue,
        'progress' => $activeQuests[$questName]['progress'],
    ];
}

function buildQuestComponent($uid) {
    $activeQuests = getActiveQuests($uid);
    $component = [];

    foreach ($activeQuests as $questName => $state) {
        $progressStrings = array_map('strval', $state['progress']);

        $component[] = [
            'name' => $questName,
            'progress' => $progressStrings,
            'removed' => $state['removed'] ?? false,
            'expired' => $state['expired'] ?? false,
            'completed' => $state['completed'] ?? false,
        ];
    }

    return $component;
}

function getAvailableQuests($uid, $playerLevel = 1, $limit = 20) {
    if (!is_string($uid) || !is_int($playerLevel) || !is_int($limit)){
        return [];
    }

    $activeQuests = getActiveQuests($uid);
    $completedQuests = getCompletedQuests($uid);
    $storyQuests = getQuestsByCategory('story');
    $availableQuests = [];

    foreach ($storyQuests as $quest) {
        if (isset($activeQuests[$quest['name']])) {
            continue;
        }
        elseif (!$quest['replay'] && in_array($quest['name'], $completedQuests)) {
            continue;
        }
        elseif (checkQuestPrereqs($uid, $quest['prereqs'], $playerLevel)) {
            $availableQuests[] = $quest;

            if (count($availableQuests) >= $limit) {
                break;
            }
        }
    }

    return $availableQuests;
}
