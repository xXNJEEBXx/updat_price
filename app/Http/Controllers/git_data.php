<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class git_data extends Controller
{
    static function heders()
    {
        return [
            'authority' => 'p2p.binance.com',
            'accept' => '*/*',
            'accept-language' => 'ar,en-US;q=0.9,en;q=0.8,ar-SA;q=0.7,en-GB;q=0.6',
            'bnc-uuid' => '678765ba-dc29-4e44-8f0d-a303a4fe3a63',
            'c2ctype' => 'c2c_merchant',
            'clienttype' => 'web',
            'content-type' => 'application/json',
            'cookie' => "cid=PD61qZJT; showBlockMarket=false; sys_mob=no; videoViewed=yes; noticeCache={'USD':true}; __BINANCE_USER_DEVICE_ID__={'23f687f5fc2dac80e8261d5308ae0737':{'date':1644675999650,'value':'1644676000015QIx7WFEMex0PM1vaIKx'}}; bnc-uuid=2cddd976-5125-4310-ad38-e8c4b392f4ea; campaign=accounts.binance.com; source=referral; _gcl_au=1.1.1246986891.1652876106; _ga=GA1.2.264223172.1652876106; fiat-prefer-currency=SAR; OptanonAlertBoxClosed=2022-05-18T12:15:09.645Z; BNC_FV_KEY=324abb49409eabdba08ffeb52bcdcdcde24728f7; common_fiat=SAR; sensorsdata2015jssdkcross=%7B%22distinct_id%22%3A%22359692082%22%2C%22first_id%22%3A%22180d717d895c7c-0b26e090f98876-26021851-2073600-180d717d896109d%22%2C%22props%22%3A%7B%22%24latest_traffic_source_type%22%3A%22%E7%9B%B4%E6%8E%A5%E6%B5%81%E9%87%8F%22%2C%22%24latest_search_keyword%22%3A%22%E6%9C%AA%E5%8F%96%E5%88%B0%E5%80%BC_%E7%9B%B4%E6%8E%A5%E6%89%93%E5%BC%80%22%2C%22%24latest_referrer%22%3A%22%22%7D%2C%22%24device_id%22%3A%22180d717d895c7c-0b26e090f98876-26021851-2073600-180d717d896109d%22%7D; se_sd=RQGDxVRkSBOEhcasZWxEgZZUgD1IAEWWlQWVfUEB1JVVwElNXV5d1; se_gd=FERGVUw8aGYFBUXwFAQYgZZFgFRpVBWWltUVfUEB1JVVwEFNXVMU1; se_gsd=RSQiLydvIzsnFloCJTI2JCY3FQJWBgQHU1RAV1ZVVlJTNFNS1; userPreferredCurrency=SAR_USD; bncLocation=; _gid=GA1.2.850064235.1653966178; BNC_FV_KEY_EXPIRE=1654103315747; lang=ar; logined=y; BNC-Location=; cr00=7E071A7F8E476E7DED022BB83FEB50A9; d1og=web.359692082.6763CF7C72DF8B4EB170724BB8D73D4C; r2o1=web.359692082.3458E6775AB78E55A80807994190448C; f30l=web.359692082.9A76F155E799A3DB99549E331EFBBFFC; OptanonConsent=isGpcEnabled=0&datestamp=Wed+Jun+01+2022+11%3A30%3A05+GMT%2B0300+(%D8%A7%D9%84%D8%AA%D9%88%D9%82%D9%8A%D8%AA+%D8%A7%D9%84%D8%B9%D8%B1%D8%A8%D9%8A+%D8%A7%D9%84%D8%B1%D8%B3%D9%85%D9%8A)&version=6.34.0&isIABGlobal=false&hosts=&consentId=3bfb9638-76bc-4a14-8140-f32a644cfc78&interactionCount=1&landingPath=NotLandingPage&groups=C0001%3A1%2CC0003%3A1%2CC0004%3A1%2CC0002%3A1&geolocation=SA%3B04&AwaitingReconsent=false; _uetsid=2cca3220e08e11ec9874bd8aac9cd585; _uetvid=c5d542e0a60711ecb4b813e7ea6fb7be; p20t=web.359692082.763C659C29DC1A85D877C75B8C22D309",
            'csrftoken' => "921fd0878d0cc0b7d75557ba0d9aa154",
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
    function ad_data()
    {
        $ad_data = Http::withHeaders(
            self::heders()
        )->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/adv/list-by-page", ['inDeal' => 1, 'rows' => 10, 'page' => 1]);
        return $ad_data["data"];
    }
    static function ads_list()
    {
        $ads_list = Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v2/friendly/c2c/adv/search", ["page" => 1, "rows" => 10, "payTypes" => [], "countries" => [], "asset" => "BTC", "tradeType" => "BUY", "fiat" => "SAR", "publisherType" => null]);
        return $ads_list["data"];
    }
}