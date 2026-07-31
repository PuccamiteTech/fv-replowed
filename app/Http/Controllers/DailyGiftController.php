<?php

namespace App\Http\Controllers;

use App\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyGiftController extends Controller
{
    private const MIN_CASH = 1;
    private const MAX_CASH = 5;
    private const MIN_GOLD = 100;
    private const MAX_GOLD = 1000;

    public function checkStatus()
    {
        $uid = Auth::user()->uid;
        $meta = UserMeta::select('next_free_gift_at')->where('uid', $uid)->first();

        return response()->json([
            'canClaim' => ($meta && $meta->next_free_gift_at->lte(Carbon::now()))
        ]);
    }

    public function claim()
    {
        $uid = Auth::user()->uid;
        $now = Carbon::now();
        $meta = UserMeta::select('id', 'cash', 'gold', 'next_free_gift_at')->where('uid', $uid)->first();

        if (!$meta || $meta->next_free_gift_at->gt($now)) {
            return response()->json(['success' => false, 'message' => 'Already claimed today'], 400);
        }

        $cashAmount = random_int(self::MIN_CASH, self::MAX_CASH);
        $goldAmount = random_int(self::MIN_GOLD, self::MAX_GOLD);
        $meta->next_free_gift_at = $now->addDay();
        $meta->cash = min($meta->cash + $cashAmount, 99_999); // copied because inaccessible
        $meta->gold = min($meta->gold + $goldAmount, 999_999_999); // also copied
        $meta->save();

        return response()->json([
            'success' => true,
            'cashAmount' => $cashAmount,
            'goldAmount' => $goldAmount,
            'message' => "You received {$cashAmount} Farm Cash and {$goldAmount} Coins!"
        ]);
    }
}
