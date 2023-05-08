<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\status;



class chack_list extends Controller
{
    static function chack_ad_status($my_ad_data)
    {
        if ($my_ad_data["advStatus"] == 1) {
            return false;
        } else {
            return true;
        }
    }


    static function chack_ad($ad, $price, $ad_amount)
    {
        if ($ad["advertiser"]["nickName"] == "NJEEB") {
            return false;
        }
        if ($ad["adv"]["minSingleTransAmount"] > git_data::ad_msta($ad_amount)) {
            return false;
        }
        // chack if the diffrince is biger than 100
        if (($ad["adv"]["maxSingleTransAmount"] - $ad["adv"]["minSingleTransAmount"]) < 100) {
            return false;
        }
        // chack if he has at lees 1000
        if (($ad["adv"]["surplusAmount"] * $ad["adv"]["price"]) < git_data::ad_msta($ad_amount)) {
            return false;
        }
        if (($ad["adv"]["price"]) < $price) {
            return false;
        }
        return true;
    }

    static function chack_full_list($ads_list, $my_data, $ad_amount)
    {
        // print_r($ads_list);
        foreach ($ads_list as $ad) {
            if (self::chack_ad($ad, $my_data["price"], $ad_amount)) {
                return false;
            }
        }
        return true;
    }

    static function chack_amount($ad_amount)
    {
        if ($ad_amount < 60) {
            return true;
        }
        return false;
    }


    static function chack_up_njeeb($ads_list, $my_data, $ad_amount)
    {
        foreach ($ads_list as $ad) {
            if ($ad["advertiser"]["nickName"] != "NJEEB") {
                if (self::chack_ad($ad, $my_data["price"], $ad_amount)) {
                    return true;
                }
            } else {
                return false;
            }
        }
    }



    public static function chack_down_njeeb($ads_list, $my_data, $ad_amount)
    {
        $flag = false;
        $NJEEB = 0;
        for ($i = 0; $i < sizeof($ads_list); $i++) {
            if ($flag) {
                if (self::chack_ad($ads_list[$i], $my_data["price"], $ad_amount)) {
                    if (round($ads_list[$i]["adv"]["price"] - 0.01, 2) == round($ads_list[$NJEEB]["adv"]["price"], 2)) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
            if ($ads_list[$i]["advertiser"]["nickName"] == "NJEEB") {
                $flag = true;
                $NJEEB = $i;
            }
        }
    }

    static function chack_the_best($ads_list, $my_data, $ad_amount)
    {
        if (!self::chack_up_njeeb($ads_list, $my_data["price"], $ad_amount) && !self::chack_down_njeeb($ads_list, $my_data["price"], $ad_amount)) {
            return true;
        } else {
            return false;
        }
    }









    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ chack tracks @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    static function chack_track_status()
    {
        $track_status = git_data::track_status();
        if ($track_status == 1) {
            return false;
        } else {
            return true;
        }
    }

    static function chack_ad_for_track($my_data, $ad)
    {
        if ($my_data["trade_type"] == "BUY") {
            if ($ad["adv"]["price"] > $my_data["price"]) {
                return false;
            }
            if ($ad["adv"]["minSingleTransAmount"] > git_data::total_amount($my_data, $ad)) {
                return false;
            }
            if (!$ad["adv"]["isTradable"]) {
                return false;
            }
        } else {

            if ($ad["adv"]["price"] < $my_data["price"]) {
                return false;
            }
            if ($ad["adv"]["minSingleTransAmount"] > git_data::total_amount($my_data, $ad)) {
                return false;
            }
            if (!$ad["adv"]["isTradable"]) {
                return false;
            }
        }

        return true;
    }



    static function chack_ads($my_data, $ads_list)
    {
        foreach ($ads_list as $ad) {
            if (self::chack_ad_for_track($my_data, $ad)) {
                return true;
            }
        }
        return false;
    }

    static function price_type_and_amount($my_data)
    {

        if ($my_data["price_type"] == "auto") {
            $my_data = git_data::orginal_price($my_data);
        }
        $my_data["track_amount"] = git_data::track_amount($my_data);
        return $my_data;
    }
}
