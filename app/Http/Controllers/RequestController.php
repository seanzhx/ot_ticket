<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Token;
use App\Models\Attendance;


class RequestController extends Controller
{

    protected function request($url, $query, $form_params)
    {

    }

    protected function getToken()
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

        return $response->getBody();
    }

    protected function getAttendance()
    {
        //Y   "checkType":"0",
        //N   "checkWorkId":"8c87bfa6-8c60-4c6f-9014-7da25b2c5787",
        //Y   "currentPage":"1",
        //Y   "endTime":"20161129103700",
        //N   "pageSize":"100",
        //Y   "startTime":"20161029103700"
        $checkType = 0;
        $currentPage = 1;
        $pageSize = 10;
        $startTime = '20200302000000';
        $endTime = '20200302120000';

        //$token = 'ODlmZmEzYjItZmU1Yy00MzVlLWJlODQtYmU1NmRmNzM4NTA0';
        $token = Token::get();
        $corpId = env('CORP_ID');

        $client = new Client([
            'base_uri' => 'https://qwif.do1.com.cn',
            'timeout'  => 5.0,
        ]);

        // dd(json_encode(compact('checkType', 'currentPage', 'pageSize', 'startTime', 'endTime')));

        //'https://qwif.do1.com.cn/qwcgi/api/checkwork!getCheckFixedRuleDataList.action?token=token&corpId=corpId';
        $response = $client->request('POST', '/qwcgi/api/checkwork!getCheckFixedRuleDataList.action', [
            'query' => compact('token', 'corpId'),
            'multipart' => [
                [
                    'name' => 'data',
                    'contents' => json_encode(compact('checkType', 'startTime', 'endTime', 'currentPage', 'pageSize'))
                ]
            ]
        ]);

        $r = $response->getBody();
        dd(json_decode((string)$r));
    }


    public function token(Request $request)
    {
        return Token::get();
    }

    public function attendance(Request $request)
    {
        Attendance::synchronize();
        return 'attendance';
    }
}
