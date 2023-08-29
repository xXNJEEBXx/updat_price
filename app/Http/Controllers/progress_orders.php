<?php

namespace App\Http\Controllers;

use App\Models\progress_order;
use App\Models\sms_notification;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Carbon\Carbon;
//Google2FA
use PragmaRX\Google2FA\Google2FA;

class progress_orders extends Controller
{
    public function chack_orders($my_data)
    {
        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return  "You need to log in";
        }
        $finshed_progress_orders = proces::get_finshed_progress_orders();

        $progress_orders = git_data::get_progress_orders();


        do {
            $orders_need_to_store_it = proces::orders_need_to_store_it($progress_orders, $finshed_progress_orders);
            foreach ($orders_need_to_store_it as $order) {
                proces::send_progress_orders($order);
            }
            if (count($orders_need_to_store_it) > 0) {
                $finshed_progress_orders = proces::get_finshed_progress_orders();
            }
        } while (count($orders_need_to_store_it) > 0);

        $orders_dones = proces::get_orders_dones($progress_orders, $finshed_progress_orders);

        foreach ($orders_dones as $order) {
            $order["status"] = 1;
            proces::update_orders_status($order);
        }
        $finshed_progress_orders = proces::get_finshed_progress_orders();
        if (count($finshed_progress_orders) == 0) {
            return "no orders to chack";
        }
        $updates = Telegram::getUpdates();
        foreach ($finshed_progress_orders as $order) {
            if ($order->status == 2) {
                echo "wroung email\n";
                $massege = "Thare is wrong email for the order id=" . $order["order_id"] . " amount=" . $order["value"];
                Telegram::sendMessage([
                    'chat_id' => "438631667",
                    'text' => $massege
                ]);
                $order["status"] = 7;
                proces::update_orders_status($order);
            }
            if ($order->status == 3) {
                self::telgram_send_Validat_massge($order, $updates);
                $order["status"] = 4;
                proces::update_orders_status($order);
                echo "waiting Validat name\n";
            }
            if ($order->status == 4) {
                if (self::chack_vote_no($updates, $order)) {
                    $order["status"] = 7;
                    proces::update_orders_status($order);
                    echo "bad name cancel order\n";
                } else {
                    $now = Carbon::now();
                    if (self::chack_vote_yes($updates, $order) || $order->updated_at->diffInMinutes($now) >= 5) {
                        $order["status"] = 5;
                        proces::update_orders_status($order);
                        echo "order just accepted\n";
                    }
                }
            }
            if ($order->status == 6) {
                $order["status"] = 7;
                proces::update_orders_status($order);
                git_data::mark_order_as_paid($order);
                echo "order marked as payid\n";
            }
        }
        return "orders chack successfully";
    }

    //states types 0 wating for requests 1 wating for finsh requests



    public function telgram_send_Validat_massge($order)
    {
        $question = "are you accept about this transaction?
id :" . $order["order_id"] . "
binace name :" . $order["binace_name"] . "
wise name :" . $order["wise_name"];

        Telegram::sendPoll([
            'chat_id' => "438631667",
            'question' => $question,
            'options' => ['Yes', 'No']
        ]);
    }

    public function chack_vote_no($updates, $order)
    {
        foreach ($updates as $update) {
            if ($update->poll) {
                $poll = $update->poll;
                $id = self::getId($poll->question);
                if ($order["order_id"] == $id) {
                    if ($poll->options[1]["voter_count"] > 0) {
                        Telegram::markUpdateAsRead($update->getUpdateId());
                        return true;
                    }
                }
            }
        }
        return false;
    }


    public function chack_vote_yes($updates, $order)
    {
        foreach ($updates as $update) {
            if ($update->poll) {
                $poll = $update->poll;
                $id = self::getId($poll->question);
                if ($order["order_id"] == $id) {
                    if ($poll->options[0]["voter_count"] > 0) {
                        Telegram::markUpdateAsRead($update->getUpdateId());
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getId($message)
    {
        preg_match("/id\s*:\s*(\d+)/", $message, $matches);
        return $matches[1];
    }

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
        $finshed_progress_orders = self::git_orders_for_wise();
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

    public function update_progress_order(Request $order)
    {
        $order = $order->json()->all();
        $finshed_progress_orders = progress_order::where('order_id', $order["order_id"])->get()->first();
        $finshed_progress_orders->status = $order["status"];
        if (isset($order["wise_name"])) {
            $finshed_progress_orders->wise_name = $order["wise_name"];
        }
        $finshed_progress_orders->save();
        return ["data" => "orders update successfully"];
    }




    public function new_sms_massage($name, $number, $message)
    {
        $table = new  sms_notification;
        $table->name = $name;
        $table->number = $number;
        $table->massage =  $message;
        $table->status = false;
        $table->save();
        return "sms saved successfully";
    }
    public function git_order_otp()
    {
        return ["data" => self::git_order_otp_text()];
    }
    public function git_order_otp_text()
    {
        $sms_notifications = sms_notification::where('name', "TW Team")->get();
        foreach ($sms_notifications as $sms_notification) {
            $now = Carbon::now();
            if ($sms_notification->created_at->diffInMinutes($now) <= 3) {
                if ($sms_notification->status == false) {
                    $sms_notification->status = true;
                    $sms_notification->save();
                    return  self::extractCode($sms_notification->massage);
                }
            }
        }
        return "no sms massage yet";
    }
    public function git_wise_login_otp()
    {
        $_g2fa = new Google2FA();
        $current_otp = $_g2fa->getCurrentOtp("BMAUAAWP2ZLPBYGMKO2GAZP6FMJCMRQQ");
        return ["data" => $current_otp];
    }

    public function git_orders_for_wise()
    {
        $orders_for_wise = progress_order::all();
        $array = [];
        foreach ($orders_for_wise as $order_for_wise) {
            if ($order_for_wise->status == "0" || $order_for_wise->status == "5") {
                $array[] = $order_for_wise;
            }
        }

        return $array;
    }

    function extractCode($text)
    {
        // The regular expression matches any six-digit number at the end of the text
        $regex = "/(\d{6})/";
        // The preg_match() function returns 1 if the pattern matches, or 0 if it does not
        $match = preg_match($regex, $text, $matches);
        // If there is a match, return the first element of the matches array, which is the code
        if ($match) {
            return $matches[0];
        }
        // Otherwise, return null
        else {
            return null;
        }
    }
}
