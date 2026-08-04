<?php

require_once AMFPHP_ROOTPATH . "Helpers/general_functions.php";
require_once AMFPHP_ROOTPATH . "Helpers/player.php";

class FarmExpressZMCService {
    public static function sendGiftsFromFlash($playerObj, $request, $market = null){
        $senderUid = $playerObj->getUid();
        $giftCode = $request->params[0] ?? null;
        $requestIds = $request->params[1] ?? [];
        $ref = $request->params[2] ?? '';
        $source = $request->params[3] ?? '';

        if (!$giftCode) {
            return ["data" => ["success" => false, "error" => "no_gift_code"]];
        }

        if (!is_array($requestIds) || empty($requestIds)) {
            return ["data" => ["success" => false, "error" => "no_recipients"]];
        }

        $currNeighbors = get_meta($senderUid, 'current_neighbors');
        $validNeighbors = $currNeighbors ? (@unserialize($currNeighbors) ?: []) : [];
        $validNeighbors = array_map('intval', $validNeighbors);

        $item = getItemByCode($giftCode);
        if (!$item) {
            $item = getItemByName($giftCode, "db");
        }

        if (!$item) {
            return ["data" => ["success" => false, "error" => "item_not_found"]];
        }

        $itemCode = $item['code'] ?? $giftCode;
        
        $sentKey = "flash_gift_sent_" . $itemCode;
        $alreadySentRaw = get_meta($senderUid, $sentKey);
        $alreadySent = $alreadySentRaw ? (@unserialize($alreadySentRaw) ?: []) : [];
        $alreadySent = array_map('intval', $alreadySent);
        
        $giftsSent = [];
        $skippedAlreadySent = [];

        foreach ($requestIds as $recipientUid) {
            if (!is_numeric($recipientUid)) {
                continue;
            }
            
            $recipientUid = (int) $recipientUid;
            
            if (!in_array($recipientUid, $validNeighbors)) {
                continue;
            }
            
            if (in_array($recipientUid, $alreadySent)) {
                $skippedAlreadySent[] = (string) $recipientUid;
                continue;
            }
            
            $extraData = [
                "sender" => $senderUid,
                "source" => $source,
                "ref" => $ref
            ];

            addGiftByCode($recipientUid, $itemCode, 1, $senderUid, $extraData);
            $giftsSent[] = (string) $recipientUid;
            $alreadySent[] = $recipientUid;
        }

        if (!empty($giftsSent)) {
            set_meta($senderUid, $sentKey, serialize($alreadySent));
        }

        return [
            "data" => [
                "success" => true,
                "giftsSent" => $giftsSent,
                "numSent" => count($giftsSent),
                "skippedAlreadySent" => $skippedAlreadySent
            ]
        ];
    }
}
