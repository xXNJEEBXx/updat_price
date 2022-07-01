<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class chack_list extends Controller
{
    static function chack_ad($ad)
    {
        return true;
        if ($ad["adv"]["minSingleTransAmount"] > 300) {
            return false;
        }
        if (($ad["adv"]["surplusAmount"] * $ad["adv"]["price"]) < 300) {
            return false;
        }
        if (($ad["adv"]["price"]) < 87787.86) {
            return false;
        }
        return true;
    }

    static function chack_the_best($ads_list)
    {
        foreach ($ads_list as $ad) {
            if ($ad["advertiser"]["nickName"] != "NJEEB") {
                if (self::chack_ad($ad)) {
                    return true;
                }
            } else {
                return false;
            }
        }
    }

    static function chack_njeeb($ads_list)
    {
        foreach ($ads_list as $ad) {
            if ($ad["advertiser"]["nickName"] == "NJEEB") {
                return true;
            }
        }
        return false;
    }

    public function chack_the_second($ads_list)
    {
        $flag = false;
        $NJEEB = 0;
        for ($i = 0; $i < sizeof($ads_list); $i++) {
            if ($flag) {
                if ($this->chack_ad($ads_list[$i])) {
                    if ($ads_list[$i]["price"] - 0.01 == $ads_list[$NJEEB]["price"]) {
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
}