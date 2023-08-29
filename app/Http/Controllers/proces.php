<?php

namespace App\Http\Controllers;

use App\Models\finshed_orders;
use Illuminate\Http\Request;
use App\Models\status;
use App\Models\progress_order;
use PhpParser\Node\Expr\Isset_;

class proces extends Controller
{

    static function add_defult_ad_amount($my_data, $my_ad_data)
    {
        $my_data["track_amount"] = 0;
        if ($my_ad_data["tradeType"] == "SELL") {
            $my_data["track_amount"] = $my_ad_data["tradableQuantity"] * $my_ad_data["price"];
        }

        return $my_data;
    }
    static function add_crupto_amount($my_data, $my_ad_data)
    {
        if (isset($my_data["max_amount"]) && ($my_data["max_amount"] < $my_data["track_amount"])) {
            $my_data["crupto_amount"] = $my_data["max_amount"] / $my_data["orginal_price"];
        } else {
            $my_data["crupto_amount"] = $my_data["free_amount"] + $my_ad_data["tradableQuantity"];
        };
        return $my_data;
    }



    static function difference_value($my_data)
    {
        if ((($my_data["asset"] == "USDT") || ($my_data["asset"] == "BUSD")) && ($my_data["fiat"] = "USD")) {
            if ($my_data["trade_type"] == "SELL") {
                return -0.001;
            } else {
                return 0.001;
            }
        } else {
            if ($my_data["trade_type"] == "SELL") {
                return -0.01;
            } else {
                return 0.01;
            }
        }
    }
    static function difference_index($my_data)
    {
        if (($my_data["asset"] == "USDT") && ($my_data["fiat"] = "USD")) {
            return 3;
        } else {
            return 2;
        }
    }
    static function change_price($ads_list, $my_ad_data, $my_data)
    {

        $enemy_ad = git_data::enemy_ad($ads_list, $my_data, $my_ad_data);
        return git_data::change_price_req($enemy_ad, $my_ad_data, $my_data);
        return "success";
    }

    static function make_order($my_data, $ads_list)
    {
        $traked_ad = git_data::traked_ad($my_data, $ads_list);
        git_data::open_order_req($my_data, $traked_ad);

        //send massge telegram
        $telegram_massge = self::make_new_order_massge_countent($my_data, $traked_ad);
        git_data::send_massge($telegram_massge);
    }


    static function update_amount($my_data)
    {
        if (isset($my_data["trade_type"]) && $my_data["trade_type"] == "SELL") {
            return "STOP";
        }
        $full_orders = git_data::full_orders([4], 0);
        $track_table = status::where('name', "track_amount")->first();
        $finshed_orders = finshed_orders::all();

        foreach ($full_orders as $order) {
            if ($track_table->updated_at->valueOf() < $order["createTime"]) {
                if (self::array_any($finshed_orders, $order["orderNumber"])) {
                    $table = new  finshed_orders;
                    $table->order_id = $order["orderNumber"];
                    $table->save();
                    $selled_amount = $order["totalPrice"];
                    $track_table = status::where('name', "track_amount")->first();
                    $track_table->value = $track_table->value - $selled_amount;
                    $track_table->save();
                    echo "update_amount\n";
                }
            }
        }
        return "work";
    }

    static function array_any($array, $id)
    {
        foreach ($array as $key) {
            if ($key["order_id"] == $id) {
                return false;
            }
        }
        return true;
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

    static function send_progress_orders($order)
    {
        $status = 0;
        if ($order["orderStatus"] == 5) {
            $status = 7;
        }
        $payment = self::git_payment($order);
        $name = self::git_name($payment);
        $email = self::git_email($payment);
        $pay_id = $payment["payMethodId"];
        $table = new  progress_order;
        $table->type = $order["tradeType"];
        $table->order_id = $order["orderNumber"];
        $table->payment = $payment["tradeMethodName"];
        $table->status = $status;
        $table->binace_name = $name;
        if (!$email) {
            $email = "no email";
        }
        $table->email = $email;
        $table->pay_id = $pay_id;
        $table->value = $order["totalPrice"];
        $table->save();
        echo "progress order added\n";
    }

    static function get_orders_dones($progress_orders, $finshed_progress_orders)
    {
        $orders_dones = [];
        foreach ($finshed_progress_orders as $finshed_order) {
            $flag = true;
            foreach ($progress_orders as $order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    $flag = false;
                }
            }
            if ($flag) {
                $orders_dones[] = $finshed_order;
            }
        }
        return $orders_dones;
    }

    static function update_orders_status($order)
    {
        $table = progress_order::where('order_id', $order["order_id"])->first();
        $table->status = $order["status"];
        $table->save();

        echo "order closed\n";
    }

    static function git_name($payment)
    {
        foreach ($payment["fields"] as $field) {
            if ($field["fieldName"]  == "Name") {
                return $field["fieldValue"];
            }
        }
    }


    static function git_payment($order)
    {
        foreach ($order["payMethods"] as $payMethod) {
            if ($payMethod["identifier"] == "Wise") {
                return $payMethod;
            }
        }
    }

    static function git_email($payment)
    {
        foreach ($payment["fields"] as $field) {
            if ($field["fieldName"]  == "Email Address") {
                return $field["fieldValue"];
            }
        }
    }

    static function orders_need_to_store_it($progress_orders, $finshed_progress_orders)
    {
        $orders_need_to_store_it = [];
        foreach ($progress_orders as $order) {
            $flag = true;
            foreach ($finshed_progress_orders as $finshed_order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    $flag = false;
                }
            }
            if ($flag) {
                $orders_need_to_store_it[] = $order;
            }
        }
        return $orders_need_to_store_it;
    }

    static function orders_finshed($progress_orders, $finshed_progress_orders)
    {
        $orders_need_to_store_it = [];
        foreach ($progress_orders as $order) {
            $flag = false;
            foreach ($finshed_progress_orders as $finshed_order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    $flag = true;
                }
            }
            if ($flag) {
                $orders_need_to_store_it[] = $order;
            }
        }
        return $orders_need_to_store_it;
    }

    static function real_progress_orders($progress_orders, $finshed_progress_orders)
    {
        $real_progress_orders = [];
        foreach ($finshed_progress_orders as $finshed_order) {
            $flag = false;
            foreach ($progress_orders as $order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    $flag = true;
                }
            }
            if ($flag) {
                $real_progress_orders[] = $order;
            }
        }
        return $real_progress_orders;
    }

    static function get_finshed_progress_orders()
    {
        $finshed_progress_orders = progress_order::all();
        $finshed_progress_orders_array = [];

        foreach ($finshed_progress_orders as $finshed_order) {
            if ($finshed_order->status != 1) {
                $finshed_progress_orders_array[] = $finshed_order;
            }
        }
        return $finshed_progress_orders_array;
    }
}
