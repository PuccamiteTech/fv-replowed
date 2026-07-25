<?php

class WatchToEarnRewardGrantService{
    public static function generateDailyTokens($playerObj, $request){
        $data["data"] = array(
            "tokens" => 0
        );
        return $data;
    }

    public static function getUserZid($playerObj = null, $request = null, $market = null){
        $zid = $playerObj ? (string) $playerObj->getUid() : "0";
        return array(
            "success" => (bool) $playerObj,
            "zid" => $zid,
            "data" => array(
                "zid" => $zid
            )
        );
    }
}
