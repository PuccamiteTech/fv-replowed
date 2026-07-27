<?php

require_once AMFPHP_ROOTPATH . "Helpers/player.php";
require_once AMFPHP_ROOTPATH . "Helpers/general_functions.php";

class AvatarService{
    
    public static function saveAvatar($playerObj, $request){
        $uid = $playerObj->getUid();
        $items = $request->params[0];
        $gender = $request->params[1] ?? "female";

        $avatar = [
            "gender" => $gender,
            "version" => "fv_1",
            "items" => $items
        ];

        $playerObj->setAvatar($avatar);
        self::updateConfigurations($uid, $gender, $items);

        return [];
    }

    
    public static function buyAvatarItem($playerObj, $request){
        $itemId = (string) ($request->params[0] ?? "");

        if (empty($itemId)) {
            return ["data" => ["success" => false, "error" => "Invalid item"]];
        }

        self::unlockItem($playerObj, $itemId);

        return ["data" => ["success" => true, "itemId" => $itemId]];
    }

    
    // no associated request, helpers?
    public static function getUnlockedItems($playerObj){
        $unlocksRaw = $playerObj->getAvatarUnlocks();
        $unlocks = new \stdClass();

        if (is_array($unlocksRaw)) {    
            foreach ($unlocksRaw as $itemId) {
                $key = (string) $itemId;
                $unlocks->$key = true;
            }
        }

        return $unlocks;
    }

    
    public static function isItemUnlocked($playerObj, $itemId){
        $unlocks = $playerObj->getAvatarUnlocks();

        return is_array($unlocks) && in_array((string) $itemId, $unlocks, true);
    }

    
    public static function unlockItem($playerObj, $itemId){
        $unlocks = $playerObj->getAvatarUnlocks() ?? array();

        if (!in_array((string) $itemId, $unlocks, true)) {
            $unlocks[] = $itemId;
        }

        $playerObj->setAvatarUnlocks($unlocks);
        return true;
    }

    
    public static function getConfigurations($uid){
        $raw = get_meta($uid, 'avatar_configurations');
        if ($raw) {
            $configs = @unserialize($raw, ["allowed_classes" => false]);
            if (is_array($configs)) {
                return $configs;
            }
        }
        return [
            "male" => new stdClass(),
            "female" => new stdClass()
        ];
    }

    
    public static function updateConfigurations($uid, $gender, $params){
        $configs = self::getConfigurations($uid);

        if (!isset($configs[$gender]) || !is_array($configs[$gender])) {
            $configs[$gender] = [];
        }

        if (is_object($params) || is_array($params)) {
            foreach ($params as $category => $itemData) {
                if (is_object($itemData) && isset($itemData->itemId)) {
                    $configs[$gender][$category] = (string) $itemData->itemId;
                } elseif (is_array($itemData) && isset($itemData['itemId'])) {
                    $configs[$gender][$category] = (string) $itemData['itemId'];
                }
            }
        }

        set_meta($uid, 'avatar_configurations', serialize($configs));
        return true;
    }
}
