<?php

namespace App\Http\Controllers;

use App\Models\status;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Print_;

class track_controller extends Controller
{
    public function track_orders($my_data)
    {
        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return  "You need to log in";
        }
        $my_data = chack_list::price_type_and_amount($my_data);

        $ads_list = git_data::ads_list($my_data);
        if (chack_list::chack_ads($my_data, $ads_list)) {
            proces::make_order($my_data, $ads_list);
            return "New order opened";
        };
        return "Thare is no good price";
    }



    public function post_track_amount_and_price(Request $my_data)
    {
        $data = status::where('name', "track_amount")->first();
        if ($data == null) {
            $track_table1 = new  status;
            $track_table1->name = "track_amount";
            $track_table1->value = $my_data["amount"];
            $track_table1->save();
            $track_table2 = new  status;
            $track_table2->name = "track_price";
            $track_table2->value = $my_data["price"];
            $track_table2->save();
        } else {
            $track_table1 = status::where('name', "track_amount")->first();
            $track_table1->name = "track_amount";
            $track_table1->value = $my_data["amount"];
            $track_table1->save();
            $track_table2 = status::where('name', "track_price")->first();
            $track_table2->name = "track_price";
            $track_table2->value = $my_data["price"];
            $track_table2->save();
        }
        return ["amount" => $track_table1->value, "price" => $track_table2->value];
    }

    public function post_track_status(Request $my_data)
    {
        $data = status::where('name', "track_status")->first();
        if ($data == null) {
            $track_table = new  status;
            $track_table->value = 0;
        } else {
            $track_table = status::where('name', "track_status")->first();
            $track_table->value = ($track_table->value == 0 ? 1 : 0);
        }
        $track_table->name = "track_status";
        $track_table->save();

        return ["status" => $track_table->value];
    }


    public function git_track_data()
    {
        return git_data::git_track_data();
    }
}
