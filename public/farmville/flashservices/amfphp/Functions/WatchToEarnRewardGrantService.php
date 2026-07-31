<?php

class WatchToEarnRewardGrantService{
    public static function generateDailyTokens($playerObj, $request){
        return ["data" => ["tokens" => 0]];
    }

    public static function getUserZid($playerObj = null, $request = null, $market = null){
        $zid = $playerObj ? (string) $playerObj->getUid() : "0";
        return ["data" => ["success" => (bool) $playerObj, "zid" => $zid]];
    }
}
