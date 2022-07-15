<?php


namespace App\Http\Controllers;



use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\cookie;
use App\Models\status;


class ApiController extends Controller
{

    // function updat_price_api($ad_data, $ads_list, $states)
    // {
    //     if ($states == "reduction") {
    //         $data = ["asset" => "BTC", "fiatUnit" => "SAR", "priceType" => 1, "fiatScale" => 2, "assetScale" => 8, "priceScale" => 2, "advNo" => "11329712394179661824", "autoReplyMsg" => $ad_data['data'][1]["autoReplyMsg"], "initAmount" => $ad_data['data'][1]["initAmount"], "payTimeLimit" => 30, "price" => $ads_list["data"][0]["adv"]["price"] - 0.01, "priceFloatingRatio" => "", "minSingleTransAmount" => $ad_data['data'][1]["minSingleTransAmount"], "maxSingleTransAmount" => "90000", "remarks" => $ad_data['data'][1]["remarks"], "tradeMethods" => $ad_data['data'][1]["tradeMethods"], "tradeType" => "SELL"];
    //     } else {
    //         $data = ["asset" => "BTC", "fiatUnit" => "SAR", "priceType" => 1, "fiatScale" => 2, "assetScale" => 8, "priceScale" => 2, "advNo" => "11329712394179661824", "autoReplyMsg" => $ad_data['data'][1]["autoReplyMsg"], "initAmount" => $ad_data['data'][1]["initAmount"], "payTimeLimit" => 30, "price" => $ads_list["data"][1]["adv"]["price"] - 0.01, "priceFloatingRatio" => "", "minSingleTransAmount" => $ad_data['data'][1]["minSingleTransAmount"], "maxSingleTransAmount" => "90000", "remarks" => $ad_data['data'][1]["remarks"], "tradeMethods" => $ad_data['data'][1]["tradeMethods"], "tradeType" => "SELL"];
    //     }
    //     $ads_list = Http::withHeaders($this->heders())->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/adv/update", $data);
    //     return $ads_list;
    // }

    public function changprics()
    {
        $ad_data = git_data::ads_list();
        if (!chack_list::chack_njeeb($ad_data)) {
            return  "ad is not exist";
        } else {
            // ad is exist
            // if (condition) {
            //     # code...
            // }
        }

        return chack_list::chack_njeeb($ad_data);
        if ($ad_data->status() !== 200) {
            return  "You need to log in";
        } else {
            $ads_list = git_data::ads_list();
            if ($this->chack_the_best($ads_list)) {
                # code...
            }
            if ($ads_list["data"][0]["adv"]["price"] > 126998) {
                if ($ads_list["data"][0]["advertiser"]["nickName"] !== "NJEEB") {
                    if ((($ads_list["data"][0]["adv"]["price"] - 0.01) * $ad_data['data'][1]["tradableQuantity"]) > 60) {
                        $updat_price_response = $this->updat_price_api($ad_data, $ads_list, "reduction");
                        if ($updat_price_response->status() !== 200) {
                            return  "You need to log in";
                        } else {
                            return  "price reduction from " . $ad_data['data'][1]["price"] . " to " . ($ads_list["data"][0]["adv"]["price"] - 0.01);
                        }
                        return  $ad_data->status();
                    } else {
                        return  "no amunt";
                    }
                } else {
                    if ((($ads_list["data"][1]["adv"]["price"] - 0.01) * $ad_data['data'][1]["tradableQuantity"]) > 60) {
                        $price = $ads_list["data"][1]["adv"]["price"] - 0.01;
                        if ($price == $ad_data['data'][1]["price"]) {
                            return "you are the best";
                        } else {
                            $updat_price_response = $this->updat_price_api($ad_data, $ads_list, "incresed");
                            if ($updat_price_response->status() !== 200) {
                                return  "You need to log in";
                            } else {
                                return  "price incresed from " . $ad_data['data'][1]["price"] . " to " . ($ads_list["data"][1]["adv"]["price"] - 0.01);
                            }
                        }
                    }
                }
            } else {
                return "price is too low:" . $ads_list["data"][0]["adv"]["price"];
            }
        }
    }

    public function postcookies(Request $request)
    {
        $data = cookie::get()->first();
        if ($data == null) {
            $cookie_table = new  cookie;
            $cookie_table->cookies = $request["cookies"];
            $cookie_table->csrftoken = $request["csrftoken"];
            $cookie_table->save();
        } else {
            $cookie_table = cookie::where('id', "1")->first();
            $cookie_table->cookies = $request["cookies"];
            $cookie_table->csrftoken = $request["csrftoken"];
            $cookie_table->save();
        }
        return  $request["cookies"];
    }

    public function poststatus(Request $request)
    {
        $data = status::get()->first();
        if ($data == null) {
            $status_table = new  status;
            $status_table->status = $request["status"];
            $status_table->save();
        } else {
            $status_table = status::where('id', "1")->first();
            $status_table->status = $request["status"];
            $status_table->save();
        }
        return  $request["status"];
    }
}