<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\status;


class proces extends Controller
{

    static function change_price($ads_list, $my_ad_data, $my_data, $ad_amount)
    {
        $enemy_ad = git_data::enemy_ad($ads_list, $my_data["price"], $ad_amount);
        git_data::change_price_req($enemy_ad, $my_ad_data);
    }

    static function make_order($my_data, $ads_list)
    {
        $traked_ad = git_data::traked_ad($my_data, $ads_list);
        git_data::open_order_req($my_data, $traked_ad);

        //send massge telegram
        $telegram_massge = self::make_new_order_massge_countent($my_data, $traked_ad);
        git_data::send_massge($telegram_massge);
        if ($my_data["trade_type"] == "BUY") {
            //update amount
            $selled_amount = git_data::selled_amount($my_data, $traked_ad);
            $track_table = status::where('name', "track_amount")->first();
            $track_table->value = $track_table->value - $selled_amount;
            $track_table->save();
        }
    }

    static function make_new_order_massge_countent($my_data, $traked_ad)
    {
        $telegram_massge = "
You have a new " . $my_data["trade_type"] . " P2P Order \n
time:" . $traked_ad["adv"]["payTimeLimit"] . " min\n
price:" . $traked_ad["adv"]["price"] . "\n
amount in usd:" . git_data::total_amount($my_data, $traked_ad) . " USD\n
remarks:" . $traked_ad["adv"]["remarks"];
        return $telegram_massge;
    }
}
