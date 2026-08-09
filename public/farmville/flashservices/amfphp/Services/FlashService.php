<?php
require_once AMFPHP_ROOTPATH . "Helpers/player.php";
require_once AMFPHP_ROOTPATH . "Helpers/market_transactions.php";
require_once AMFPHP_ROOTPATH . "Helpers/quest_helper.php";

require_once AMFPHP_ROOTPATH . "Functions/AvatarService.php";
//require_once AMFPHP_ROOTPATH . "Functions/CraftingService.php";
require_once AMFPHP_ROOTPATH . "Functions/DailyStatsService.php";
//require_once AMFPHP_ROOTPATH . "Functions/EquipmentWorldService.php";
require_once AMFPHP_ROOTPATH . "Functions/FarmExpressZMCService.php";
require_once AMFPHP_ROOTPATH . "Functions/FarmQuestService.php";
//require_once AMFPHP_ROOTPATH . "Functions/FarmService.php";
require_once AMFPHP_ROOTPATH . "Functions/FBRequestService.php";
require_once AMFPHP_ROOTPATH . "Functions/FertilizerService.php";
require_once AMFPHP_ROOTPATH . "Functions/FleaMarketService.php";
require_once AMFPHP_ROOTPATH . "Functions/FriendListService.php";
require_once AMFPHP_ROOTPATH . "Functions/FriendSetService.php";
require_once AMFPHP_ROOTPATH . "Functions/FVV10AnniversaryBirthdayCardService.php";
//require_once AMFPHP_ROOTPATH . "Functions/IrrigationService.php";
require_once AMFPHP_ROOTPATH . "Functions/LeaderboardService.php";
require_once AMFPHP_ROOTPATH . "Functions/LonelyAnimalFriendSetService.php";
require_once AMFPHP_ROOTPATH . "Functions/LonelyCowService.php";
require_once AMFPHP_ROOTPATH . "Functions/NeighborActionService.php";
require_once AMFPHP_ROOTPATH . "Functions/OrganicFertilizerService.php";
require_once AMFPHP_ROOTPATH . "Functions/PresentService.php";
require_once AMFPHP_ROOTPATH . "Functions/SNPermissionsService.php";
require_once AMFPHP_ROOTPATH . "Functions/UserFeedService.php";
require_once AMFPHP_ROOTPATH . "Functions/UserService.php";
require_once AMFPHP_ROOTPATH . "Functions/WatchToEarnRewardGrantService.php";
require_once AMFPHP_ROOTPATH . "Functions/WorldService.php";
require_once AMFPHP_ROOTPATH . "Functions/ZAPIClientService.php";

class FlashService {
    public function dispatchBatch($userData, $reqData, $params3) {
        $data = array();
        $player = null;
        $market = null;
        $amf_debug = amfphp_debug_enabled();
        if ($amf_debug) {
            $count = is_array($reqData) ? count($reqData) : (is_object($reqData) ? count((array)$reqData) : 0);
            @file_put_contents(amfphp_debug_log_path('amf_calls.log'), "dispatchBatch start count={$count}\n", FILE_APPEND);
        }
        // Are we in init? if so, get masterId. If not, the id is in zy_user
        // We initialize the player object with our id.
        if (isset($userData->masterId) && $userData->masterId != ""){
            $player = new Player($userData->masterId);
            $market = new MarketTransactions($userData->masterId);
        }else{
            $player = new Player($userData->zy_user);
            $market = new MarketTransactions($userData->zy_user);
        }

        // Build QuestComponent once for all requests (same player)
        $questComponent = buildQuestComponent($player->getUid());

        foreach ($reqData as $key => $requ){
            $data[$key] = array(
                "errorType" => 0,
                "errorData" => null,
                "sequenceNumber" => $requ->sequence,
                "worldTime" => time()
            );
            $data[$key]["metadata"] = array(
                "QuestComponent" => $questComponent
            );

            try{
                $fn_details = explode(".", $requ->functionName);
                if ($amf_debug) {
                    @file_put_contents(amfphp_debug_log_path('amf_calls.log'), "AMF call: {$requ->functionName}\n", FILE_APPEND);
                }

                if (method_exists($fn_details[0], $fn_details[1])){
                    $result = call_user_func(array($fn_details[0], $fn_details[1]), $player, $requ, $market);
                    $data[$key] = array_merge($data[$key], $result);
                } else {
                    if ($amf_debug) {
                        @file_put_contents(amfphp_debug_log_path('amf_missing.log'), "Missing AMF method: {$requ->functionName}\n", FILE_APPEND);
                    }
                }
            }catch (Exception $e){
                if ($amf_debug) {
                    $msg = "AMF exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
                    $msg .= $e->getTraceAsString() . "\n";
                    @file_put_contents(amfphp_debug_log_path('amf_missing.log'), $msg, FILE_APPEND);
                }
                $data[$key]["errorType"] = 1;
                $data[$key]["errorData"] = "Server error: " . ($e->getMessage() ?: "Method not found");
            }
        }

        $data = array_values($data);

        return array(
            "errorType" => 0,
            "errorData" => null,
            "serverTime" => time(),
            "zySig" => array(
                "zy_user" => $player->getUid(),
                "zy_ts" => time(),
                "zy_session" => "thetestofthetime"
            ),
            "data" => $data
        );

    }
}
