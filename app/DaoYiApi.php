<?php

namespace App;

use GuzzleHttp\Client;

class DaoYiApi
{
    /*---------- Singleton Start ----------*/
    private static $_instance;

    private function __construct() {}

    private function __clone() {}

    public static function getInstance()
    {
        if(! (self::$_instance instanceof self) )
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /*---------- Singleton End ----------*/

    const API_BASE_URI = 'https://qwif.do1.com.cn';

    const API_PATH_TOKEN = '/qwcgi/portal/api/qwsecurity!getToken.action';
    const API_PATH_ATTENDANCE = '/qwcgi/api/checkwork!getCheckFixedRuleDataList.action';

    const REQUEST_TIMEOUT = 30;

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    private $httpClient;

    protected function get($apiPath, $query)
    {
        return $this->request(self::METHOD_GET, $apiPath, $query);
    }

    protected function post($apiPath, $query, $contents)
    {
        return $this->request(self::METHOD_POST, $apiPath, $query, $contents);
    }

    protected function request($method, $apiPath, $query, $contents=null)
    {
        if(empty($this->httpClient))
        {
            $this->httpClient = new Client([
                'base_uri' => self::API_BASE_URI,
                'timeout'  => self::REQUEST_TIMEOUT,
            ]);
        }

        $params['query'] = $query;

        if($method == self::METHOD_GET)
        {
            //DO NOTHING
        }
        elseif ($method == self::METHOD_POST)
        {
            $params['multipart'] = [[
                'name' => 'data',
                'contents' => json_encode($contents)
            ]];
        }
        else
        {
            //UNDEFINED METHOD
            //TODO: THROWING EXCEPTION
        }

        $response = $this->httpClient->request($method, $apiPath, $params);

        //TODO: HANDLE EXCEPTION

        $ret = json_decode($response->getBody());

        if($ret->code != 0)
        {
            throw new \Exception($ret->desc.'-'.$ret->code);
        }

        return $ret->data;
    }


    public static function attendance($token, $corpId, $startDate, $endDate, $currentPage, $pageSize=200)
    {
        $startTime = $startDate.'000000';
        $endTime = $endDate.'235959';
        $checkType = 0;

        return self::getInstance()->post(self::API_PATH_ATTENDANCE,
            compact('token', 'corpId'),
            compact('checkType','startTime', 'endTime', 'currentPage', 'pageSize')
        );
    }

    public static function token($developerId, $developerKey)
    {
        return self::getInstance()->get(self::API_PATH_TOKEN, compact('developerId', 'developerKey'));
    }

}
