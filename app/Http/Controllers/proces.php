<?php

namespace App\Http\Controllers;

use App\Models\finshed_orders;
use Illuminate\Http\Request;
use App\Models\status;
use App\Models\progress_order;
use App\Models\wise_transaction;
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
        $table = new  progress_order;
        $payment = self::git_payment($order);
        $table->order_id = $order["orderNumber"];
        $table->payment = $payment["tradeMethodName"];
        $table->type = $order["tradeType"];
        $table->status = 0;
        $table->value = $order["totalPrice"];
        if ($order["tradeType"] == "BUY") {
            $name = self::git_name($payment);
            $table->binace_name = $name;
            $email = self::git_email($payment);
            if (!$email) {
                $email = "no email";
            }
            $table->email = $email;
            $pay_id = $payment["payMethodId"];
            $table->pay_id = $pay_id;
        } else {
            $table->binace_name = git_data::get_binance_sell_order_name($order);
            $table->status = 10;
        }
        if ($order["orderStatus"] == 5) {
            $table->status = 7;
        }
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

    static function chack_if_thare_is_orders_just_updated($progress_orders, $finshed_progress_orders)
    {
        foreach ($progress_orders as $order) {
            foreach ($finshed_progress_orders as $finshed_order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    if (($order["orderStatus"] == 2) && ($finshed_order["status"] == 9)) {
                        $finshed_order["status"] = 10;
                        self::update_orders_status($finshed_order);
                        echo "the buyer marked the order as paid\n";
                    }
                }
            }
        }
    }
    //no need delete it
    static function update_transactions($finshed_progress_orders)
    {
        $wise_transactions = wise_transaction::where("status", 0)->get();
        echo "here\n";

        foreach ($wise_transactions as $wise_transaction) {
            if (self::chack_value($wise_transaction, $finshed_progress_orders)) {
                $wise_transaction["status"] = 1;
                $wise_transaction->save();
                echo "transaction closed\n";
            }
        }
    }
    static function chack_value($wise_transaction, $finshed_progress_orders)
    {
        foreach ($finshed_progress_orders as $finshed_order) {
            if ($wise_transaction["value"] == $finshed_order["value"]) {
                return true;
            }
        }
        return false;
    }
    static function chack_transaction($order)
    {
        $wise_transactions = wise_transaction::where("status", 0)->get();
        foreach ($wise_transactions as $wise_transaction) {
            if ($wise_transaction["value"] == $order["value"]) {
                return true;
            }
        }
        return false;
    }

    static function close_transaction($order)
    {
        $wise_transactions = wise_transaction::where("status", 0)->get();
        foreach ($wise_transactions as $wise_transaction) {
            if ($wise_transaction["value"] == $order["value"]) {
                $wise_transaction["status"] = 1;
                $wise_transaction->save();
                echo "transaction closed\n";
            }
        }
    }
}
