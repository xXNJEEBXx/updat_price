<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\cookie;
use App\Models\status;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;


class git_data extends Controller
{

    static function catch_errors($callback)
    {
        do {
            try {
                $falg = false;
                $res = $callback();
            }
            //catch exception
            catch (QueryException $error) {
                if (!isset($error_alarm)) {
                    $error_alarm = true;
                    echo "QueryException error \n";
                }
                $falg = true;
                sleep(2);
            } catch (ConnectionException $error) {
                if (!isset($error_alarm)) {
                    $error_alarm = true;
                    echo "ConnectionExceptiong error \n";
                }
                $falg = true;
                sleep(2);
            } catch (RequestException $error) {
                if (!isset($error_alarm)) {
                    $error_alarm = true;
                    echo "RequestException error \n";
                }
                $falg = true;
                sleep(2);
            }
        } while ($falg);

        return $res;
    }
    static function heders()
    {

        $cookies = self::catch_errors(function () {
            return cookie::all()->first();
        });


        return [
            'authority' => 'p2p.binance.com',
            'accept' => '*/*',
            'accept-language' => 'ar,en-US;q=0.9,en;q=0.8,ar-SA;q=0.7,en-GB;q=0.6',
            'bnc-uuid' => '678765ba-dc29-4e44-8f0d-a303a4fe3a63',
            'c2ctype' => 'c2c_merchant',
            'clienttype' => 'web',
            'content-type' => 'application/json',
            'cookie' => $cookies->cookies,
            'csrftoken' => $cookies->csrftoken,
            'device-info' => 'eyJzY3JlZW5fcmVzb2x1dGlvbiI6IjE5MjAsMTA4MCIsImF2YWlsYWJsZV9zY3JlZW5fcmVzb2x1dGlvbiI6IjE5MjAsMTA0MCIsInN5c3RlbV92ZXJzaW9uIjoiV2luZG93cyAxMCIsImJyYW5kX21vZGVsIjoidW5rbm93biIsInN5c3RlbV9sYW5nIjoiYXIiLCJ0aW1lem9uZSI6IkdNVCszIiwidGltZXpvbmVPZmZzZXQiOi0xODAsInVzZXJfYWdlbnQiOiJNb3ppbGxhLzUuMCAoV2luZG93cyBOVCAxMC4wOyBXaW42NDsgeDY0KSBBcHBsZVdlYktpdC81MzcuMzYgKEtIVE1MLCBsaWtlIEdlY2tvKSBDaHJvbWUvMTAwLjAuNDg5Ni42MCBTYWZhcmkvNTM3LjM2IiwibGlzdF9wbHVnaW4iOiJQREYgVmlld2VyLENocm9tZSBQREYgVmlld2VyLENocm9taXVtIFBERiBWaWV3ZXIsTWljcm9zb2Z0IEVkZ2UgUERGIFZpZXdlcixXZWJLaXQgYnVpbHQtaW4gUERGIiwiY2FudmFzX2NvZGUiOiJhNDBkZGEzMiIsIndlYmdsX3ZlbmRvciI6Ikdvb2dsZSBJbmMuIChOVklESUEpIiwid2ViZ2xfcmVuZGVyZXIiOiJBTkdMRSAoTlZJRElBLCBOVklESUEgR2VGb3JjZSBHVFggMTA2MCA2R0IgRGlyZWN0M0QxMSB2c181XzAgcHNfNV8wLCBEM0QxMSkiLCJhdWRpbyI6IjEyNC4wNDM0NzUyNzUxNjA3NCIsInBsYXRmb3JtIjoiV2luMzIiLCJ3ZWJfdGltZXpvbmUiOiJBc2lhL1JpeWFkaCIsImRldmljZV9uYW1lIjoiQ2hyb21lIFYxMDAuMC40ODk2LjYwIChXaW5kb3dzKSIsImZpbmdlcnByaW50IjoiNzhhYjQ0MjRiMDQ4M2MwNmU4M2Q5NjcyNWIxODBjYzkiLCJkZXZpY2VfaWQiOiIiLCJyZWxhdGVkX2RldmljZV9pZHMiOiIifQ==',
            'fvideo-id' => '324abb49409eabdba08ffeb52bcdcdcde24728f7',
            'lang' => 'ar',
            'origin' => 'https://p2p.binance.com',
            'referer' => 'https://p2p.binance.com/ar/myads',
            'sec-ch-ua' => '" Not A;Brand";v="99", "Chromium";v="100", "Google Chrome";v="100"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.60 Safari/537.36',
            'x-trace-id' => 'ee96a687-d600-4fa3-bc1c-a674b88ad426',
            'x-ui-request-trace' => 'ee96a687-d600-4fa3-bc1c-a674b88ad426'
        ];
    }
    static function ads_data()
    {
        $ads_data = self::catch_errors(function () {
            return Http::withHeaders(
                self::heders()
            )->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/adv/list-by-page", ['inDeal' => 1, 'rows' => 10, 'page' => 1]);
        });

        return $ads_data;
    }
    static function ads_list($my_data)
    {
        if ($my_data["track_type"] == "choce_best_price") {
            $my_data["trade_type"] = $my_data["trade_type"] == "SELL" ? "BUY" : "SELL";
        }
        $payTypes = [];
        if (isset($my_data["payTypes"]) > 0) {
            array_push($payTypes, $my_data["payTypes"]);
        }

        $ads_list = self::catch_errors(function () use (&$payTypes, &$my_data) {
            return  Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v2/friendly/c2c/adv/search", ["page" => 1, "rows" => 10, "payTypes" => $payTypes, "countries" => [], "asset" => $my_data["asset"], "tradeType" => $my_data["trade_type"], "fiat" => $my_data["fiat"], "proMerchantAds" => false, "publisherType" => null]);
        });
        return $ads_list["data"];
    }

    static function change_price_req($enemy_ad, $my_ad_data, $my_data)
    {
        $res = self::catch_errors(function () use ($enemy_ad, $my_ad_data, $my_data) {
            return  Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/adv/update", self::paylode_for_change_price($enemy_ad, $my_ad_data, $my_data));
        });
    }

    static function ad_data($ads_data, $my_data)
    {
        foreach ($ads_data["data"] as $my_ad_data) {
            if ($my_ad_data["advNo"] == $my_data["id"]) {
                return $my_ad_data;
            }
        }
    }

    static function enemy_ad($ads_list, $my_data, $my_ad_data)
    {
        foreach ($ads_list as $ad_data) {
            if (chack_list::chack_ad($ad_data, $my_data, $my_ad_data)) {
                return $ad_data;
            }
        }
    }

    static function new_price($my_data, $enemy_ad)
    {
        return $enemy_ad["adv"]["price"] + proces::difference_value($my_data);
    }

    static function paylode_for_change_price($enemy_ad, $my_ad_data, $my_data)
    {

        $paylode = ["advNo" => $my_ad_data["advNo"], "asset" => $my_ad_data["asset"], "assetScale" => $my_ad_data["assetScale"], "autoReplyMsg" => $my_ad_data["autoReplyMsg"], "buyerBtcPositionLimit" => $my_ad_data["buyerBtcPositionLimit"], "buyerRegDaysLimit" => $my_ad_data["buyerRegDaysLimit"], "fiatScale" => $my_ad_data["fiatScale"], "fiatUnit" => $my_ad_data["fiatUnit"], "initAmount" => self::total_initAmount($enemy_ad, $my_ad_data, $my_data), "launchCountry" => $my_ad_data["launchCountry"], "maxSingleTransAmount" => $my_ad_data["maxSingleTransAmount"], "minSingleTransAmount" => $my_ad_data["minSingleTransAmount"], "payTimeLimit" => $my_ad_data["payTimeLimit"], "price" => self::new_price($my_data, $enemy_ad), "priceFloatingRatio" => $my_ad_data["priceFloatingRatio"], "priceScale" => $my_ad_data["priceScale"], "priceType" => $my_ad_data["priceType"], "remarks" => $my_ad_data["remarks"], "tradeMethods" => $my_ad_data["tradeMethods"], "tradeType" => $my_ad_data["tradeType"]];
        return $paylode;
    }


    static function total_initAmount($enemy_ad, $my_ad_data, $my_data)
    {
        if ($my_data["trade_type"] == "BUY") {
            $my_data["crupto_amount"] = $my_data["track_amount"] / self::new_price($my_data, $enemy_ad);
        }
        $my_data["crupto_amount"] += self::total_transefer_amount($my_ad_data);
        $my_data["crupto_amount"] = $my_data["crupto_amount"] * 0.999;
        if ($my_ad_data["asset"] == "USDT") {
            return  round($my_data["crupto_amount"], 2, PHP_ROUND_HALF_DOWN);
        } else {
            return  round($my_data["crupto_amount"], 8, PHP_ROUND_HALF_DOWN);
        }
    }
    static function total_transefer_amount($my_ad_data)
    {
        return   $my_ad_data["initAmount"] - $my_ad_data["tradableQuantity"];
    }


    static function ad_amount($my_ad_data)
    {
        return   round($my_ad_data["tradableQuantity"] * $my_ad_data["price"], 2, PHP_ROUND_HALF_DOWN);
    }


    static function ad_msta($ad_amount)
    {
        if ($ad_amount <= 350) {
            return 200;
        }


        if ($ad_amount <= 500) {
            return 300;
        }

        if ($ad_amount <= 1000) {
            return 500;
        }

        if ($ad_amount <= 1900) {
            return 1000;
        }

        if ($ad_amount <= 2500) {
            return 1500;
        }


        if ($ad_amount <= 5000) {
            return 2000;
        }


        if ($ad_amount <= 7000) {
            return 3000;
        }


        if ($ad_amount <= 12000) {
            return 4000;
        }
    }

    static function ad_own($ad_amount)
    {
        if ($ad_amount <= 500) {
            return 60;
        }
        if ($ad_amount <= 1000) {
            return 400;
        }

        if ($ad_amount <= 1900) {
            return 900;
        }

        if ($ad_amount <= 2500) {
            return 1000;
        }

        if ($ad_amount <= 5000) {
            return 1000;
        }

        if ($ad_amount <= 7000) {
            return 1000;
        }

        if ($ad_amount <= 12000) {
            return 2500;
        }
    }
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@track orders& closes orders@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    static function git_track_data()
    {

        $data = self::catch_errors(function () {
            return status::where('name', "track_amount")->first();
        });
        if ($data == null) {
            $track_table = new  status;
            $track_table->name = "track_amount";
            $track_table->value = 0;
            self::catch_errors(function () use ($track_table) {
                $track_table->save();
            });
            $track_table = new  status;
            $track_table->name = "track_status";
            $track_table->value = 0;
            self::catch_errors(function () use ($track_table) {
                $track_table->save();
            });
        } else {
            $data3 = self::catch_errors(function () {
                return  status::where('name', "track_status")->first();
            });
            return ["amount" => $data["value"], "status" => $data3["value"]];
        }
    }

    static function track_status()
    {
        $data = status::where('name', "track_status")->first();
        return  $data["value"];
    }



    static function orginal_price($my_data)
    {

        $data = self::catch_errors(function () {
            return Http::withHeaders(self::heders())->get("https://www.binance.com/bapi/composite/v1/public/marketing/symbol/list");
        });
        foreach ($data["data"] as $element) {
            if ($element["name"] == $my_data["asset"]) {
                $my_data["price"] = $element["price"] * $my_data["price_multiplied"];
                $my_data["orginal_price"] = $element["price"];
            }
        }
        return  $my_data;
    }

    static function traked_ad($my_data, $ads_list)
    {
        foreach ($ads_list as $ad) {
            if (chack_list::chack_ad_for_track($my_data, $ad)) {
                $traked_ad = self::catch_errors(function () use ($ad) {
                    return  Http::withHeaders(self::heders())->get("https://p2p.binance.com/bapi/c2c/v2/public/c2c/adv/selected-adv/" . $ad["adv"]["advNo"]);
                });
                return $traked_ad["data"];
            }
        }
    }

    static function open_order_req($my_data, $traked_ad)
    {
        print_r(self::paylode_for_open_order($my_data, $traked_ad));
        self::catch_errors(function () use ($my_data, $traked_ad) {
            Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/order-match/makeOrder", self::paylode_for_open_order($my_data, $traked_ad));
        });
    }

    static function paylode_for_open_order($my_data, $traked_ad)
    {
        $pay_methed = self::pay_methed($my_data, $traked_ad);
        if ($my_data["trade_type"] == "SELL") {
            if ($pay_methed["identifier"] == "Wise") {
                $pay_methed["payId"] = 33677319;
            }
        }

        return ["advOrderNumber" => $traked_ad["adv"]["advNo"], "asset" => $my_data["asset"], "matchPrice" => $traked_ad["adv"]["price"], "fiatUnit" => $my_data["fiat"], "buyType" => "BY_MONEY", "totalAmount" => self::total_amount($my_data, $traked_ad), "tradeType" => $my_data["trade_type"], "origin" => "MAKE_TAKE", "payId" => $pay_methed["payId"], "payType" => $pay_methed["identifier"]];
    }

    static function total_amount($my_data, $traked_ad)
    {
        $buy_amount = ($my_data["orginal_price"] * $my_data["track_amount"]) / $traked_ad["adv"]["price"];
        if ($buy_amount >= self::MSA($traked_ad)) {
            $amount = self::MSA($traked_ad);
        } else {
            $amount = $buy_amount;
        }
        if (isset($my_data["buy_the_lowist"]) && $my_data["buy_the_lowist"]) {
            if ($amount > $traked_ad["adv"]["minSingleTransAmount"]) {
                $amount = $traked_ad["adv"]["minSingleTransAmount"];
            }
        }
        return round($amount, 2, PHP_ROUND_HALF_DOWN);
    }

    static function selled_amount($my_data, $traked_ad)
    {
        $buy_amount = ($my_data["orginal_price"] * $my_data["track_amount"]) / $traked_ad["adv"]["price"];
        if ($buy_amount >= self::MSA($traked_ad)) {

            $selled_amount = ($traked_ad["adv"]["price"] * self::MSA($traked_ad)) / $my_data["orginal_price"];
        } else {
            $selled_amount = $my_data["track_amount"];
        }
        return round($selled_amount, 2, PHP_ROUND_HALF_DOWN);
    }


    static function pay_methed($my_data, $traked_ad)
    {
        foreach ($traked_ad["adv"]["tradeMethods"] as $pay_methed) {
            if ($pay_methed["identifier"] == $my_data["payTypes"]) {
                return $pay_methed;
            }
        }
    }

    static function send_massge($telegram_massge)
    {
        self::catch_errors(function () use ($telegram_massge) {
            Http::withHeaders(self::heders())->get("https://api.telegram.org/bot5546910942:AAFWrAYCeosx1x3x2K9HE5tpKagTwE-M0bI/sendMessage?chat_id=438631667,&text=" . $telegram_massge);
        });
    }

    static function MSA($traked_ad)
    {
        if ($traked_ad["adv"]["maxSingleTransAmount"] > $traked_ad["adv"]["surplusAmount"] * $traked_ad["adv"]["price"]) {
            return $traked_ad["adv"]["surplusAmount"] * $traked_ad["adv"]["price"];
        } else {
            return $traked_ad["adv"]["maxSingleTransAmount"];
        }
    }

    static function track_amount($my_data)
    {
        //may need to convert to sar
        $my_amount =  0;
        $my_data["free_amount"] = 0;
        $wallet_amount = self::git_wallet_amount();
        if ($my_data["trade_type"] == "BUY") {
            $my_data = self::set_track_buy_amount($my_data);
            $my_data = self::set_progress_orders_amount($my_data);
        } else {
            //Sell
            $my_data = self::set_free_amount_and_track_amount($my_data, $wallet_amount);
        }
        $my_data = self::set_max_amount($my_data, $wallet_amount);
        $my_data["track_amount"] = round($my_data["track_amount"], 2, PHP_ROUND_HALF_DOWN);
        return $my_data;
    }
    static function set_track_buy_amount($my_data)
    {
        $track_table = self::catch_errors(function () {
            return   status::where('name', "track_amount")->first();
        });
        $my_data["track_amount"] = $track_table["value"];
        return $my_data;
    }
    static function git_wallet_amount()
    {
        return self::catch_errors(function () {
            return  Http::withHeaders(self::heders())->post("https://www.binance.com/bapi/asset/v3/private/asset-service/asset/get-ledger-asset", ["needBtcValuation" => true, "quoteAsset" => "BTC"]);
        });
    }

    static function set_free_amount_and_track_amount($my_data, $wallet_amount)
    {
        foreach ($wallet_amount["data"] as $crupto) {
            if ($crupto["asset"] == $my_data["asset"]) {
                //convert crupto to usd amount
                if (isset($my_data["track_amount"])) {
                    $my_data["track_amount"] += $crupto["free"] * $my_data["orginal_price"];
                } else {
                    $my_data["track_amount"] = $crupto["free"] * $my_data["orginal_price"];
                }
                $my_data["free_amount"] = $crupto["free"];
                return $my_data;
            }
        }
    }

    static function set_max_amount($my_data, $wallet_amount)
    {
        if (isset($my_data["max_amount"])) {
            foreach ($wallet_amount["data"] as $crupto) {
                if ($crupto["asset"] == $my_data["asset"]) {
                    $my_data["free_amount"] = $crupto["free"];
                    $my_data["max_amount"] -= ($crupto["free"] + $crupto["freeze"]) * $my_data["orginal_price"];
                }
            }
            if ($my_data["track_amount"] > $my_data["max_amount"]) {
                $my_data["track_amount"] = $my_data["max_amount"];
            }
        }
        return $my_data;
    }

    static function set_progress_orders_amount($my_data)
    {

        $progress_orders = self::progress_orders();
        foreach ($progress_orders as $order) {
            if ($order["tradeType"] == "BUY") {
                $my_data["track_amount"] += $order["totalPrice"];
            }
        }

        return $my_data;
    }

    static function full_orders($orderStatusList, $tradetype)
    {
        $GMT_time = time() - (3600 * 3);
        $end_time = (((($GMT_time - ($GMT_time % 86400)) + 86400 * 1) - (3600 * 3)) * 1000) - 1;
        $start_time = (($end_time + 1) - 86400 * 1000);
        $carbon = Carbon::createFromTimestamp($start_time / 1000);
        $start_time = $carbon->subMonths(3);
        $start_time = strtotime($start_time) * 1000;
        $full_orders = self::catch_errors(function () use ($start_time, $end_time, $orderStatusList, $tradetype) {
            return Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v1/private/c2c/order-match/order-list-archived-involved", ["page" => 1, "rows" => 10, "orderStatusList" => $orderStatusList, "startDate" => $start_time, "endDate" => $end_time, "tradeType" => $tradetype]);
        });
        return $full_orders["data"];
    }





    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ progress_orders @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


    static function progress_orders()
    {
        $processing_ads_list = self::catch_errors(function () {
            return Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/order-match/order-list", ["page" => 1, "rows" => 10, "orderStatusList" => [0, 1, 2, 3, 5]]);
        });

        return $processing_ads_list["data"];
    }

    static function mark_order_as_paid($order)
    {
        self::catch_errors(function () use ($order) {
            Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v1/private/c2c/order-match/notifyOrderPayed", ["orderNumber" => $order["order_id"], "payId" => $order["pay_id"]]);
        });
        return "done";
    }
}
