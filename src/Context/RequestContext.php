<?php

namespace SoftHouse\MonitoringService\Context;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Request;

class RequestContext
{
    public static function getIP()
    {

        $ip = Request::ip();

        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        return $ip;
    }

    public static function getInfoIP($ip = null)
    {

        try {
            if ($ip === null) {
                $ip = self::getIP();
            }

            $client = new Client();

            $promise = $client->requestAsync("GET", "http://ip-api.com/json/${ip}?fields=25")->then(function ($response) {
                return $response->getBody();
            }, function ($exception) {
                return $exception->getMessage();
            });

            $response = $promise->wait();
            $_response = json_decode($response, true);
            $_response['ip'] = $ip;
            return $_response;
        } catch (\Exception $exception) {
            return [];
        }
    }
}
