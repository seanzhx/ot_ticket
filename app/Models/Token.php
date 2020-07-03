<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Exception;
use App\DaoYiApi;

class Token extends Model
{
    //坑1：保存进去的也必须是数组，否则取出的将是字符串
    protected $casts = [
        'response' => 'array',
    ];

    public static function get()
    {
        $token = self::orderBy('id','desc')->first();

        //查询数据库缓存是否有效
        if ($token && $token->response)
        {
            $c = Carbon::createFromTimestamp(time() - $token->response['expiresIn']);
            if($token->created_at->gt($c))
            {
                return $token->response['token'];
            }
        }

        //无缓存或失效则重新申请
        $token = new self();
        $token->response = DaoYiApi::token(env('DEVELOPER_ID'), env('DEVELOPER_KEY'));
        $token->save();

        return $token->response['token'];
    }
}
