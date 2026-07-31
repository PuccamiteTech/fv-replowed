<?php

require_once AMFPHP_ROOTPATH . "Helpers/general_functions.php";

class OrganicFertilizerService
{
    public static function executeOrganicFertilizer($playerObj, $request, $market = null)
    {
        $uid = $playerObj->getUid();
        $currentWorldType = getCurrentWorldType($uid);
        $world = getWorldByType($uid, $currentWorldType);
        $modified = false;

        if (!empty($world["objectsArray"])) {
            foreach ($world["objectsArray"] as &$obj) {
                if (isset($obj->className) && $obj->className === 'Plot'
                    && isset($obj->state) && $obj->state === 'planted'
                    && isset($obj->itemName)) {
                    $obj->isJumbo = true;
                    $modified = true;
                }
            }
            unset($obj);

            if ($modified && !saveWorld($uid, $world, $currentWorldType)) {
                return ["data" => ["success" => false, "error" => "Failed to save organic fertilizer for uid=$uid"]];
            }
        }

        return ["data" => []];
    }
}
