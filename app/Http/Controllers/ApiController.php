<?php


namespace App\Http\Controllers;



use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\cookie;
use App\Models\status;


class ApiController extends Controller
{

    public function changprics_api()
    {
        return $this->changprics("11329712394179661824");
    }

    public function changprics($my_data)
    {
        // if (self::getstatus()) {
        //     return  "program turn off in database";
        // }
        // $ad_data["asset"], $ad_data["fiat"]
        $ads_list = git_data::ads_list($my_data);
        $ads_data = git_data::ads_data();


        if ($ads_data->status() !== 200) {
            return  "You need to log in";
        }

        $my_ad_data = git_data::ad_data($ads_data, $my_data);

        $ad_amount = git_data::ad_amount($my_ad_data);


        if (chack_list::chack_full_list($ads_list, $my_data, $ad_amount)) {
            return  "all ads bad";
        }


        if (chack_list::chack_ad_status($my_ad_data)) {
            return  "ad is turn off in binance";
        }
        if (chack_list::chack_amount($ad_amount)) {
            return  "ad out of amount";
        }

        if (chack_list::chack_up_njeeb($ads_list, $my_data, $ad_amount)) {
            //Ad price need to reduction
            proces::change_price($ads_list, $my_ad_data, $my_data, $ad_amount);
            return  "ad price reduction from " . $my_ad_data["price"] . " to " . git_data::new_price(git_data::enemy_ad($ads_list, $my_data, $ad_amount));
        }
        // return chack_list::chack_down_njeeb($ads_list);
        if (chack_list::chack_down_njeeb($ads_list, $my_data, $ad_amount)) {
            //Ad price need to incress
            proces::change_price($ads_list, $my_ad_data, $my_data, $ad_amount);
            return  "ad price increesed from " . $my_ad_data["price"] . " to " . git_data::new_price(git_data::enemy_ad($ads_list, $my_data, $ad_amount));
        }
        if (chack_list::chack_the_best($ads_list, $my_data, $ad_amount)) {
            return  "ad have best price";
        }
        return  "test";
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
        return  $this->getdate($cookie_table->updated_at);
    }


    public function getlastupdate()
    {
        $cookie_table = cookie::where('id', "1")->first();
        if ($cookie_table != null) {
            return  $this->getdate($cookie_table->updated_at);
        }
        return "test";
    }

    public function getdate($date)
    {
        $secands = (time() - strtotime($date));
        function loop($secands, $minets, $hours)
        {
            if ($secands >= 3600) {
                $secands2 = $secands % 3600;
                $hours = ($secands - $secands2) / 3600;
                return loop($secands2, $minets, $hours);
            }
            if ($secands >= 60) {
                $secands2 = $secands % 60;
                $minets  = ($secands - $secands2) / 60;
                return loop($secands2, $minets, $hours);
            }
            if ($hours) {
                $hours = $hours . " hours ";
            } else {
                $hours = "";
            }
            if ($minets) {
                $minets = $minets . " minets ";
            } else {
                $minets = "";
            }
            if ($secands) {
                $secands = $secands . " secands ";
            } else {
                $secands = "";
            }
            if (!$secands && !$minets && !$hours) {
                $secands = 1;
            }
            return $hours . $minets . $secands;
        }
        return loop($secands, 0, 0);
    }

    public function poststatus(Request $request)
    {
        $data = status::get()->first();
        if ($data == null) {
            $status_table = new  status;
            $status_table->name = $request["name"];
            $status_table->status = 1;
            $status_table->save();
        } else {
            $status_table = status::where('name', $request["name"])->first();
            $status_table->name = $request["name"];
            if ($status_table->status) {
                $status_table->status = 0;
            } else {
                $status_table->status = 1;
            }
            $status_table->save();
        }
        return  $status_table->status;
    }

    public function getstatus()
    {
        $data = status::get()->first();
        if ($data != null) {
            return  $data->status;
        }
    }
}
