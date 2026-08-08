<?php
    require_once AMFPHP_ROOTPATH . "Helpers/database.php";
    /**
     * Get User Metadata
     * 
     * @param string $uid User ID
     * @param string $meta_key Meta Key
     * @return string The value of the meta field
     *                False for invalid $uid of non found $meta_key
     */
    function get_meta($uid, $meta_key){
        $meta = [];

        if (is_numeric($uid) && is_string($meta_key) && $meta_key !== ""){
            $meta = Database::query("SELECT meta_value FROM playermeta WHERE meta_key = ? AND uid = ?", [$meta_key, $uid], "ss", Database::FETCH_ONE);
        }

        return ($meta !== null) ? $meta["meta_value"] : false;
    }


    /**
     * Set User Metadata
     * 
     * @param string $uid User ID
     * @param string $meta_key Meta Key
     * @param string $meta_value Meta Value to update or insert
     * 
     * @return string The value of the meta field
     *                False for incalid $uid of non found $meta_key
     */
    function set_meta($uid, $meta_key, $meta_value){
        $meta_rec = get_meta($uid, $meta_key); // params validated inside
        
        if (is_string($meta_value)){
            if ($meta_rec){
                Database::query("UPDATE playermeta SET meta_value = ? WHERE uid = ? AND meta_key = ?", [$meta_value, $uid, $meta_key], "sss");
            }else{
                Database::query("INSERT INTO playermeta (uid, meta_key, meta_value) VALUES (?, ?, ?)", [$uid, $meta_key, $meta_value], "sss");
            }
        }
    }

    function getCurrentWorldType($uid) {
        return get_meta($uid, "currentWorldType") ?: "farm";
    }

    function compressArray($array){

        // Convert the array to JSON (compatible with ActionScript)
        $jsonData = json_encode($array);

        // Compress the JSON string
        $compressedData = gzcompress($jsonData);

        // Encode to Base64
        $base64Encoded = base64_encode($compressedData);

        return $base64Encoded;
    }


    function getItemByName($itemName, $method = "json"){
        if (is_string($itemName) && $itemName !== ""){
            if ($method === "db"){
                $item = Database::query("SELECT * FROM items WHERE name = ?", [$itemName], "s", Database::FETCH_ONE);
                return unserialize($item["data"]);
            }

            $items_str = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/props/items.json");
            $items = json_decode($items_str);
            foreach ($items->settings->items->item as $item){
                if ($item->name == $itemName){
                    return (array) $item;
                }
            }
        }

        return false;
    }

    function getItemByCode($itemCode) {
        if (!is_string($itemCode) || $itemCode === "") {
            return false;
        }

        $item = Database::query("SELECT * FROM items WHERE code = ?", [$itemCode], "s", Database::FETCH_ONE);
        return unserialize($item["data"]);
    }

    /*
    function getWorldByUid($uid){
        return getWorldByType($uid);
    }
    */
    function getWorldByType($uid, $type = "farm"){
        $worldData = [];

        if (is_numeric($uid) && is_string($type) && $type !== ""){
            $row = Database::query("SELECT * FROM userworlds WHERE type = ? AND uid = ?", [$type, $uid], "ss", Database::FETCH_ONE);

            if ($row !== null){
                // no point in validating further
                // if the row's contents are invalid, loading SHOULD fail

                // populate objects if missing, retaining other data
                if (empty($worldData["objectsArray"] = unserialize($row["objects"]))){
                    $worldData["objectsArray"] = createWorldObjects();
                }

                $worldData["type"] = $row["type"];
                $worldData["sizeX"] = $row["sizeX"];
                $worldData["sizeY"] = $row["sizeY"];
                $worldData["creation"] = $row["created_at"];
                $worldData["messageManager"] = array();
            }else{
                $worldData = createWorldByType($uid, $type);
            }
        }
        
        return $worldData;
    }

    function createWorldObjects()
    {
        // Unix timestamp in milliseconds
        $plantTime = (float) ((time() * 1000) - 172800000); // pretend 2 days elapsed

        return array(
            0 => 
            (object) array(
                'plantTime' => $plantTime,
                'position' => 
                (object) array(
                'x' => 27,
                'z' => 0,
                'y' => 13,
                ),
                'isBigPlot' => false,
                'direction' => 0,
                'isJumbo' => true,
                'deleted' => false,
                'tempId' => -1,
                'className' => 'Plot',
                'state' => 'fallow',
                'instanceDataStoreKey' => NULL,
                'components' => 
                (object) array(
                ),
                'isProduceItem' => false,
                'id' => 1,
                'itemName' => NULL,
            ),
            1 => 
            (object) array(
                'plantTime' => $plantTime,
                'position' => 
                (object) array(
                'x' => 27,
                'z' => 0,
                'y' => 9,
                ),
                'isBigPlot' => false,
                'direction' => 0,
                'isJumbo' => true,
                'deleted' => false,
                'tempId' => -1,
                'className' => 'Plot',
                'state' => 'fallow',
                'instanceDataStoreKey' => NULL,
                'components' => 
                (object) array(
                ),
                'isProduceItem' => false,
                'id' => 2,
                'itemName' => NULL,
            ),
            2 => 
            (object) array(
                'plantTime' => $plantTime, // finish growing now
                'position' => 
                (object) array(
                'x' => 19,
                'z' => 0,
                'y' => 9,
                ),
                'isBigPlot' => false,
                'direction' => 0,
                'isJumbo' => false,
                'deleted' => false,
                'tempId' => -1,
                'className' => 'Plot',
                'state' => 'planted',
                'instanceDataStoreKey' => NULL,
                'components' => 
                (object) array(
                ),
                'isProduceItem' => false,
                'id' => 3,
                'itemName' => 'eggplant',
            ),
            3 => 
            (object) array(
                'plantTime' => $plantTime,
                'position' => 
                (object) array(
                'x' => 19,
                'z' => 0,
                'y' => 13,
                ),
                'isBigPlot' => false,
                'direction' => 0,
                'isJumbo' => false,
                'deleted' => false,
                'tempId' => -1,
                'className' => 'Plot',
                'state' => 'planted',
                'instanceDataStoreKey' => NULL,
                'components' => 
                (object) array(
                ),
                'isProduceItem' => false,
                'id' => 4,
                'itemName' => 'eggplant',
            ),
            4 => 
            (object) array(
                'plantTime' => NAN,
                'position' => 
                (object) array(
                'x' => 23,
                'z' => 0,
                'y' => 9,
                ),
                'isBigPlot' => false,
                'direction' => 0,
                'isJumbo' => false,
                'deleted' => false,
                'tempId' => -1,
                'className' => 'Plot',
                'state' => 'plowed',
                'instanceDataStoreKey' => NULL,
                'components' => 
                (object) array(
                ),
                'isProduceItem' => false,
                'id' => 5,
                'itemName' => NULL,
            ),
            5 => 
            (object) array(
                'plantTime' => NAN,
                'position' => 
                (object) array(
                'x' => 23,
                'z' => 0,
                'y' => 13,
                ),
                'isBigPlot' => false,
                'direction' => 0,
                'isJumbo' => false,
                'deleted' => false,
                'tempId' => -1,
                'className' => 'Plot',
                'state' => 'plowed',
                'instanceDataStoreKey' => NULL,
                'components' => 
                (object) array(
                ),
                'isProduceItem' => false,
                'id' => 6,
                'itemName' => NULL,
            ),
        );
    }

    function createWorldByType($uid, $type = "farm"){
        $size = 50; // matches the schema default
        $messageManager = "";
        
        $newWorld = serialize(createWorldObjects());
        
        // only checking if the serialization was successful JUST IN CASE
        if (is_numeric($uid) && is_string($type) && $type !== "" && is_string($newWorld)){
            Database::query("INSERT INTO userworlds (uid, type, sizeX, sizeY, objects, messageManager) VALUES (?, ?, ?, ?, ?, ?)",
                [$uid, $type, $size, $size, $newWorld, $messageManager], "ssiiss");
        }

        return array(
            "uid" => $uid,
            'type' => $type,
            'sizeX' => $size,
            'sizeY' => $size,
            'objectsArray' => unserialize($newWorld),
            'messageManager' => array(),
            'creation' => date("Y-m-d h:i:s")
        );
    }

    function saveWorld($uid, $newData, $type = "farm") {
        $oldData = getWorldByType($uid, $type);
        
        if (empty($oldData) || !is_array($newData) || !isset($newData["objectsArray"])) {
            return false;
        }

        $objects = serialize($newData["objectsArray"]);
        $sizeX = $newData["sizeX"] ?? $oldData["sizeX"];
        $sizeY = $newData["sizeY"] ?? $oldData["sizeY"];

        $result = Database::query("UPDATE userworlds SET sizeX = ?, sizeY = ?, objects = ? WHERE uid = ? AND type = ?", [$sizeX, $sizeY, $objects, $uid, $type], "iisss");
        return ($result !== null);
    }

    function getGiftBox($uid) {
        $raw = get_meta($uid, 'giftbox');
        if ($raw) {
            $data = @unserialize($raw);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }
    
    function saveGiftBox($uid, $giftbox) {
        set_meta($uid, 'giftbox', serialize($giftbox));
    }

    function addGiftByCode($uid, $itemCode, $quantity = 1, $senderId = null, $extraData = null) {
        $giftbox = getGiftBox($uid);
        
        $extraDataObj = null;
        if ($extraData !== null) {
            $extraDataObj = is_array($extraData) ? (object) $extraData : $extraData;
        }
        
        if (isset($giftbox[$itemCode])) {
            $giftbox[$itemCode][0] += $quantity;
            if ($senderId) {
                $giftbox[$itemCode][1][] = $senderId;
            }
            if ($extraDataObj !== null) {
                if (!isset($giftbox[$itemCode][2]) || !is_array($giftbox[$itemCode][2])) {
                    $giftbox[$itemCode][2] = [];
                }
                for ($i = 0; $i < $quantity; $i++) {
                    $giftbox[$itemCode][2][] = $extraDataObj;
                }
            }
        } else {
            $extraDataArray = [];
            if ($extraDataObj !== null) {
                for ($i = 0; $i < $quantity; $i++) {
                    $extraDataArray[] = $extraDataObj;
                }
            }
            $giftbox[$itemCode] = [
                $quantity,
                $senderId ? [$senderId] : [],
                $extraDataArray
            ];
        }
        saveGiftBox($uid, $giftbox);
    }
