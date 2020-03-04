<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Exception;

class Token extends Model
{
    //坑1：保存进去的也必须是数组，否则取出的将是字符串
    protected $casts = [
        'response' => 'array',
    ];

    public static function get()
    {
        $token = self::orderBy('id','desc')->first();

        if ($token && $token->response)
        {
            $data = $token->response['data'];
            $c = Carbon::createFromTimestamp(time() - $data['expiresIn']);
            if($token->created_at->gt($c))
            {
                return $data['token'];
            }
        }

        return self::requestFromApi();
    }


    protected static function requestFromApi()
    {
        //{"code":"0","desc":"成功","data":{"token":"ODlmZmEzYjItZmU1Yy00MzVlLWJlODQtYmU1NmRmNzM4NTA0","expiresIn":7200}}
        //'https://qwif.do1.com.cn/qwcgi/portal/api/qwsecurity!getToken.action?developerId=id&developerKey=key'
        $developerId = env('DEVELOPER_ID');
        $developerKey = env('DEVELOPER_KEY');

        $client = new Client([
            'base_uri' => 'https://qwif.do1.com.cn',
            'timeout'  => 5.0,
        ]);

        $response = $client->request('GET', '/qwcgi/portal/api/qwsecurity!getToken.action', [
            'query' => compact('developerId', 'developerKey')
        ]);



        $token = new self();
        $token->response = json_decode($response->getBody());

        if($token->response['code'] != 0)
        {
            throw new Exception($token->response['desc'], $token->response['code']);
        }

        $token->save();

        return $token->response['data']['token'];
    }
}
