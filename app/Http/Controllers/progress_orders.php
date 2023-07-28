<?php

namespace App\Http\Controllers;

use App\Models\progress_order;
use App\Models\sms_notification;
use Illuminate\Http\Request;

class progress_orders extends Controller
{
    public function chack_orders($my_data)
    {
        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return  "You need to log in";
        }

        $progress_orders = git_data::progress_orders();
        $finshed_progress_orders = progress_order::all();

        if (chack_list::chack_progress_orders($progress_orders, $finshed_progress_orders)) {
            proces::send_progress_orders($progress_orders, $finshed_progress_orders);
            return "order request sended";
        }
        return "no orders to chack";
    }

    //states types 0 wating for requests 1 wating for finsh requests
    public function git_progress_order()
    {
        return ["data" => self::git_progress_order_text()];
    }

    public function git_progress_order_text()
    {
        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return  "You need to log in";
        }
        $finshed_progress_orders = progress_order::where('status', 0)->get();
        if (count($finshed_progress_orders) > 0) {
            $progress_orders = git_data::full_orders([1], 0);
        }

        foreach ($finshed_progress_orders as $finshed_progress_order) {
            foreach ($progress_orders as $order) {
                if ($order["orderNumber"] == $finshed_progress_order->order_id) {
                    return $finshed_progress_order;
                }
            }
        }
        return "no progress order";
    }

    public function update_progress_order(Request $my_data)
    {
        $finshed_progress_orders = progress_order::where('order_id', $my_data->order_id)->get();
        $finshed_progress_orders->status = $my_data["data"]["status"];
        $finshed_progress_orders->save();
        return "orders update successfully";
    }


    public function new_sms_massage($name, $number, $message)
    {
        $table = new  sms_notification;
        $table->name = $name;
        $table->number = $number;
        $table->massage =  $message;
        $table->save();
        return "sms saved successfully";
    }
}
