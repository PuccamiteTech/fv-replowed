<?php
require_once AMFPHP_ROOTPATH . "Helpers/database.php";
require_once AMFPHP_ROOTPATH . "Helpers/general_functions.php";

function getCraftTypeFromCottageName(?string $itemName): ?string
{
    if ($itemName === null || $itemName === '') {
        return null;
    }

    $mapping = [
        'craftingwinery' => 'winery',
        'craftingbakery' => 'bakery',
        'craftingspa' => 'spa',
        'craftingcreamery' => 'creamery',
        'craftingfirework' => 'firework',
        'craftingsauna' => 'sauna',
        'craftingicecream' => 'icecream',
        'craftingtailor' => 'tailor',
        'craftingtoy' => 'toy',
        'craftingcarousel' => 'carousel',
        'craftingcandle' => 'candle',
        'craftingperfume' => 'perfume',
        'craftingcake' => 'cake',
        'craftingjewelry' => 'jewelry',
        'craftingdye' => 'dye',
        'craftingink' => 'ink',
        'craftingflower' => 'flower',
    ];

    $lowerName = strtolower($itemName);

    if (isset($mapping[$lowerName])) {
        return $mapping[$lowerName];
    }

    if (strpos($lowerName, 'crafting') === 0) {
        return substr($lowerName, 8);
    }

    return null;
}

function getRecipeQueueForCraftType(int $uid, string $craftType): array
{
    $queue = [];

    $rows = Database::query("SELECT * FROM crafting_queue WHERE uid = ? AND craft_type = ? AND status = 'active' ORDER BY start_ts ASC",
        [$uid, $craftType], "ss", Database::FETCH_ALL);

    foreach ($rows as $row) {
        $queue[] = [
            "recipeId" => $row["recipe_id"],
            "craftType" => $row["craft_type"],
            "ovenSlot" => (int) $row["oven_slot"],
            "startTS" => (int) $row["start_ts"],
            "finishTS" => (int) $row["finish_ts"],
            "worldType" => $row["world_type"]
        ];
    }

    return $queue;
}

function getCraftLevelForType(int $uid, string $craftType): int
{
    $skill = Database::query("SELECT * FROM crafting_skills WHERE uid = ? AND craft_type = ? LIMIT 1",
        [$uid, $craftType], "ss", Database::FETCH_ONE);

    return $skill ? (int) $skill["level"] : 1;
}

function getRecipeById($recipeId) {
    static $recipes = null;

    if ($recipes === null) {
        $xmlPath = $_SERVER['DOCUMENT_ROOT'] . "/farmville/xml/gz/v855038/crafting.xml";
        if (!file_exists($xmlPath)) {
            return null;
        }

        $xml = simplexml_load_file($xmlPath);
        if (!$xml) {
            return null;
        }

        $recipes = array();
        foreach ($xml->CraftingRecipe as $recipe) {
            $id = (string) $recipe['id'];
            $r = array(
                'id' => $id,
                'name' => (string) $recipe->name,
                'craft' => (string) $recipe->craft,
                'SkillLevelRequired' => (int) $recipe->SkillLevelRequired,
                'InitialRecipeLevel' => (int) $recipe->InitialRecipeLevel,
                'MinutesToCook' => (int) $recipe->MinutesToCook,
                'RushCostCoins' => (int) $recipe->RushCostCoins,
                'RushCostCash' => (int) $recipe->RushCostCash,
                'Deprecated' => (int) $recipe->Deprecated,
            );

            if (isset($recipe->Reward)) {
                $reward = $recipe->Reward;
                $r['OnMake'] = array(
                    'recipeXp' => (int) ($reward->OnMake['recipeXp'] ?? 0),
                    'playerXp' => (int) ($reward->OnMake['playerXp'] ?? 0),
                );
                if (isset($reward->OnFinish)) {
                    $r['OnFinish'] = array(
                        'itemCode' => (string) ($reward->OnFinish['itemCode'] ?? ''),
                        'sellQty' => (int) ($reward->OnFinish['sellQty'] ?? 0),
                        'giftQty' => (int) ($reward->OnFinish['giftQty'] ?? 0),
                    );
                }
                if (isset($reward->OnSell)) {
                    $r['OnSell'] = array(
                        'recipeXp' => (int) ($reward->OnSell['recipeXp'] ?? 0),
                    );
                }
            }

            $r['Ingredients'] = array();
            if (isset($recipe->Ingredients)) {
                foreach ($recipe->Ingredients->Ingredient as $ing) {
                    $r['Ingredients'][] = array(
                        'itemCode' => (string) $ing['itemCode'],
                        'quantityRequired' => (int) $ing['quantityRequired'],
                    );
                }
            }

            $recipes[$id] = $r;
        }
    }

    return $recipes[$recipeId] ?? null;
}

function getCraftTypeLevels() {
    static $levels = null;

    if ($levels === null) {
        $xmlPath = $_SERVER['DOCUMENT_ROOT'] . "/farmville/xml/gz/v855038/crafting.xml";
        if (!file_exists($xmlPath)) return array();

        $xml = simplexml_load_file($xmlPath);
        if (!$xml || !isset($xml->craftTypeLevels)) return array();

        $levels = array();
        foreach ($xml->craftTypeLevels->level as $lvl) {
            $num = (int) $lvl['num'];
            $levels[$num] = array(
                'xp' => (int) $lvl['xp'],
                'gold' => (int) $lvl['gold'],
                'cash' => (int) $lvl['cash'],
                'recipeSlots' => (int) $lvl['recipeSlots'],
            );
        }
    }

    return $levels;
}

function getCraftingInventory($uid, $storageType = null) {
    $items = array();
    if (!is_numeric($uid)) {
        return $items;
    }

    $baseQuery = "SELECT * FROM crafting_inventory WHERE uid = ? AND quantity = 0";

    $rows = ($storageType === null) ?
        Database::query($baseQuery, [$uid], "s", Database::FETCH_ALL) :
        Database::query($baseQuery . " AND storage_type = ?", [$uid, $storageType], "ss", Database::FETCH_ALL);

    foreach ($rows as $row) {
        $items[] = array(
            "itemCode" => $row["item_code"],
            "quantity" => (int) $row["quantity"],
            "price" => null
        );
    }

    return $items;
}

function addToInventory($uid, $itemCode, $quantity, $storageType = "silo") {
    if (!is_numeric($uid) || $quantity <= 0) {
        return false;
    }

    $result = Database::query("INSERT INTO crafting_inventory (uid, item_code, storage_type, quantity) VALUES (?, ?, ?, ?) AS new ON DUPLICATE KEY UPDATE quantity = COALESCE(crafting_inventory.quantity, 0) + new.quantity",
        [$uid, $itemCode, $storageType, $quantity], "sssi");

    return ($result !== null);
}

function removeFromInventory($uid, $itemCode, $quantity, $storageType = "silo") {
    if (!is_numeric($uid) || $quantity <= 0) {
        return false;
    }

    $result = Database::query("UPDATE crafting_inventory SET quantity = (quantity - ?) WHERE uid = ? AND item_code = ? AND storage_type = ? AND quantity >= ?",
        [$quantity, $uid, $itemCode, $storageType, $quantity], "isssi");

    return (($result["affected_rows"] ?? 0) > 0);
}

function getRecipeQueue($uid) {
    $queue = array();
    if (!is_numeric($uid)) {
        return $queue;
    }

    $rows = Database::query("SELECT * FROM crafting_queue WHERE uid = ? AND status = 'active' ORDER BY start_ts ASC", [$uid], "s", Database::FETCH_ALL);

    foreach ($rows as $row) {
        $entry = array(
            "recipeId" => $row["recipe_id"],
            "craftType" => $row["craft_type"],
            "ovenSlot" => (int) $row["oven_slot"],
            "startTS" => (int) $row["start_ts"],
            "finishTS" => (int) $row["finish_ts"],
            "worldType" => $row["world_type"],
        );

        $ct = $row["craft_type"];
        if (!isset($queue[$ct])) {
            $queue[$ct] = array();
        }
        $queue[$ct][] = $entry;
    }

    return $queue;
}

function getCraftingSkillState($uid) {
    $state = array(
        "craftTypeStates" => array(),
        "recipeStates" => array(),
    );
    if (!is_numeric($uid)) {
        return $state;
    }

    $skills = Database::query("SELECT * FROM crafting_skills WHERE uid = ?", [$uid], "s", Database::FETCH_ALL);
    foreach ($skills as $row) {
        $state["craftTypeStates"][$row["craft_type"]] = array(
            "level" => (int) $row["level"],
            "xp" => (int) $row["xp"],
        );
    }

    $recipeStates = Database::query("SELECT * FROM crafting_recipe_states WHERE uid = ?", [$uid], "s", Database::FETCH_ALL);
    foreach ($recipeStates as $row) {
        $state["recipeStates"][$row["recipe_id"]] = array(
            "level" => (int) $row["level"],
            "xp" => (int) $row["xp"],
            "isUnlocked" => (int) $row["is_unlocked"],
        );
    }

    return $state;
}

function addCraftSkillXp($uid, $craftType, $xpAmount) {
    if (!is_numeric($uid) || $xpAmount <= 0) {
        return;
    }

    Database::query("INSERT INTO craft_skills (uid, craft_type, level, xp) VALUES (?, ?, 1, ?) AS new
        ON DUPLICATE KEY UPDATE level = COALESCE(craft_skills.level, 1), xp = COALESCE(craft_skills.xp, 0) + new.xp",
        [$uid, $craftType, $xpAmount], "ssi");
}

function addRecipeXp($uid, $recipeId, $xpAmount) {
    if (!is_numeric($uid) || $xpAmount <= 0) {
        return;
    }

    Database::query("INSERT INTO crafting_recipe_states (uid, recipe_id, level, xp, is_unlocked) VALUES (?, ?, 1, ?, 1) AS new
        ON DUPLICATE KEY UPDATE level = COALESCE(level, 1), xp = COALESCE(xp, 0) + new.xp, is_unlocked = 1",
        [$uid, $recipeId, $xpAmount], "ssi");
}

function getStallsByUids($uids) {
    if (empty($uids)) {
        return array();
    }

    $stalls = [];

    foreach ($uids as $uid){
        $result = getStallsForUser($uid);

        if ($result !== null) {
            $stalls = array_merge($stalls, $result);
        }
    }

    return $stalls;
}

function getStallByObjectId($uid, $stallObjectId) {
    $stall = Database::query("SELECT * FROM market_stalls WHERE uid = ? AND stall_object_id = ? LIMIT 1", [$uid, $stallObjectId], "si", Database::FETCH_ONE);

    if ($stall) {
        $stall['inventory'] = json_decode($stall['inventory'], true) ?: [];
        return $stall;
    }

    return null;
}

function getStallsForUser($uid) {
    $now = time();
    $stalls = [];

    $rows = Database::query("SELECT * FROM market_stalls WHERE uid = ? AND is_configured = 1 AND date_closed > ?",
            [$uid, $now], "si", Database::FETCH_ALL);

    foreach ($rows as $stall) {
        $stall['inventory'] = json_decode($stall['inventory'], true) ?: [];
        $stalls[] = $stall;
    }
    

    foreach ($rows as $row) {
        $stall = $row->toArray();
        $stall['inventory'] = json_decode($stall['inventory'], true) ?: [];
        $stalls[] = $stall;
    }

    return $stalls;
}

function configureStall($uid, $stallObjectId, $bushelItemCode) {
    $stallDuration = 86400;
    $dateClosed = time() + $stallDuration;

    $playerBushels = getCraftingInventory($uid, "silo");
    $bushelQty = 0;
    foreach ($playerBushels as $item) {
        if ($item['itemCode'] === $bushelItemCode) {
            $bushelQty = $item['quantity'];
            break;
        }
    }

    $inventory = array();
    $toMove = min($bushelQty, 25);
    for ($i = 0; $i < $toMove; $i++) {
        $inventory[] = array("ic" => $bushelItemCode, "ts" => $dateClosed);
    }

    if ($toMove > 0) {
        removeFromInventory($uid, $bushelItemCode, $toMove, "silo");
    }

    $inventoryJson = json_encode($inventory);

    Database::query("INSERT INTO market_stalls (uid, stall_object_id, bushel_item_code, is_configured, date_closed, inventory) VALUES (?, ?, ?, 1, ?, ?) AS new
        ON DUPLICATE KEY UPDATE bushel_item_code = new.bushel_item_code, is_configured = new.is_configured, date_closed = new.date_closed, inventory = new.inventory",
        [$uid, $stallObjectId, $bushelItemCode, $dateClosed, $inventoryJson], "sisis");

    return true;
}

function closeStall($uid, $stallObjectId) {
    Database::query("UPDATE market_stalls SET is_configured = 0, inventory = NULL WHERE uid = ? AND stall_object_id = ?",
        [$uid, $stallObjectId], "si");

    return true;
}

function claimStallItem($claimerUid, $stallOwnerUid, $bushelItemCode) {
    $neighbors = get_meta($claimerUid, 'current_neighbors');
    $neighborUids = $neighbors ? (@unserialize($neighbors) ?: []) : [];
    if (!in_array($stallOwnerUid, $neighborUids)) {
        return 2;
    }

    $stalls = getStallsForUser($stallOwnerUid);
    $targetStall = null;
    foreach ($stalls as $stall) {
        foreach ($stall['inventory'] as $item) {
            if ($item['ic'] === $bushelItemCode) {
                $targetStall = $stall;
                break 2;
            }
        }
    }

    if (!$targetStall) {
        return 3;
    }

    $now = time();
    $found = false;
    $newInventory = array();
    foreach ($targetStall['inventory'] as $item) {
        if (!$found && $item['ic'] === $bushelItemCode) {
            if ($item['ts'] < $now) {
                return 1;
            }
            $found = true;
            continue;
        }
        $newInventory[] = $item;
    }

    if (!$found) {
        return 3;
    }

    $inventoryJson = json_encode($newInventory);
    $stallId = (int) $targetStall['stall_object_id'];
    
    Database::query("UPDATE market_stalls SET inventory = ? WHERE uid = ? AND stall_object_id = ?",
        [$inventoryJson, $stallOwnerUid, $stallId], "ssi");

    addToInventory($claimerUid, $bushelItemCode, 1);

    return 0;
}
