<?php

require_once AMFPHP_ROOTPATH . "Helpers/user_resources.php";
require_once AMFPHP_ROOTPATH . "Helpers/general_functions.php";

class PresentService
{
    
    private static function getValue($data, $key, $default = null)
    {
        if (is_object($data) && isset($data->$key)) {
            return $data->$key;
        }
        if (is_array($data) && isset($data[$key])) {
            return $data[$key];
        }
        return $default;
    }

    
    public static function buyAndSend($playerObj, $request, $market = null)
    {
        $uid = $playerObj->getUid();
        $itemName = $request->params[0] ?? null;
        $recipientUids = $request->params[1] ?? [$uid];
        $itemContext = $request->params[2] ?? "normal";
        $extraItemData = $request->params[3] ?? null;

        if (!$itemName) {
            return ["data" => ["complete" => false, "error" => "no_item"]];
        }

        if (!is_array($recipientUids)) {
            $recipientUids = [$recipientUids];
        }

        $numRecipients = count($recipientUids);
        if ($numRecipients == 0) {
            return ["data" => ["complete" => false, "error" => "no_recipients"]];
        }

        $item = getItemByName($itemName, "db");
        if (!$item) {
            return ["data" => ["complete" => false, "error" => "item_not_found"]];
        }

        $market_type = $item["market"] ?? "coins";
        $cashCost = (int) ($item["cash"] ?? 0);
        $goldCost = (int) ($item["cost"] ?? 0);

        $totalCashCost = $cashCost * $numRecipients;
        $totalGoldCost = $goldCost * $numRecipients;

        if ($market_type === "cash" && $cashCost > 0) {
            if (!UserResources::removeCash($uid, $totalCashCost)) {
                return ["data" => ["complete" => false, "error" => "insufficient_cash"]];
            }
        } else if ($goldCost > 0) {
            if (!UserResources::removeGold($uid, $totalGoldCost)) {
                return ["data" => ["complete" => false, "error" => "insufficient_gold"]];
            }
        }

        $buyXp = (int) ($item["buyXp"] ?? 0);
        if ($buyXp > 0) {
            UserResources::addXp($uid, $buyXp * $numRecipients);
        }

        $giftExtraData = [
            "sender" => $uid
        ];

        if ($extraItemData) {
            $ringType = self::getValue($extraItemData, 'ringType');
            $message = self::getValue($extraItemData, 'message');
            $world = self::getValue($extraItemData, 'world');
            $opened = self::getValue($extraItemData, 'opened');

            if ($ringType !== null) {
                $giftExtraData["ringType"] = $ringType;
            }
            if ($message !== null) {
                $giftExtraData["message"] = $message;
            }
            if ($world !== null) {
                $giftExtraData["world"] = $world;
            }
            if ($opened !== null) {
                $giftExtraData["opened"] = $opened;
            }
        }

        $itemCode = $item["code"] ?? null;
        if (!$itemCode) {
            return ["data" => ["complete" => false, "error" => "no_item_code"]];
        }

        $numSent = 0;
        foreach ($recipientUids as $recipientUid) {
            addGiftByCode($recipientUid, $itemCode, 1, $uid, $giftExtraData);
            $numSent++;
        }

        return [
            "data" => [
                "complete" => true,
                "numSent" => $numSent,
                "totalCost" => ($market_type === "cash") ? $totalCashCost : $totalGoldCost
            ]
        ];
    }
}
