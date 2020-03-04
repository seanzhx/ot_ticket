<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;

class Attendance extends Model
{
    protected $casts = [
        'response' => 'array',
    ];

    public static function synchronize()
    {
        //Y   "checkType":"0",
        //N   "checkWorkId":"8c87bfa6-8c60-4c6f-9014-7da25b2c5787",
        //Y   "currentPage":"1",
        //Y   "endTime":"20161129103700",
        //N   "pageSize":"100",
        //Y   "startTime":"20161029103700"
        $checkType = 0;
        //$currentPage = 1;
        $pageSize = 10;
        $startTime = '20200303000000';
        $endTime = '20200304000000';

        $token = Token::get();
        $corpId = env('CORP_ID');

        $client = new Client([
            'base_uri' => 'https://qwif.do1.com.cn',
            'timeout'  => 5.0,
        ]);

        // dd(json_encode(compact('checkType', 'currentPage', 'pageSize', 'startTime', 'endTime')));

        //'https://qwif.do1.com.cn/qwcgi/api/checkwork!getCheckFixedRuleDataList.action?token=token&corpId=corpId';

        DB::table('attendances')->truncate();

        $maxPage = 1;
        for ($currentPage = 1; $currentPage <= $maxPage; $currentPage++)
        {
            $response = $client->request('POST', '/qwcgi/api/checkwork!getCheckFixedRuleDataList.action', [
                'query' => compact('token', 'corpId'),
                'multipart' => [
                    [
                        'name' => 'data',
                        'contents' => json_encode(compact('checkType', 'startTime', 'endTime', 'currentPage', 'pageSize'))
                    ]
                ]
            ]);

            $result = json_decode($response->getBody());

            if($result->code)
            {
                return '错误码：'.$result->code.'，错误信息：'. $result->desc;
            }

            $data = $result->data;
            $maxPage = $data->maxPage;

            foreach($data->pageData as $pd)
            {
                $attendance = new self();
                foreach($pd as $k => $v)
                {
                    if(!$v) unset($pd->$k);
                }

                $attendance->response = $pd;

                $attendance->work_code = $pd->wxUserId;
                $attendance->user_name = $pd->personName;
                $attendance->dept_name = $pd->departmentName;
                $attendance->sign_id = $pd->id;
                $attendance->sign_date = $pd->signDate;
                $attendance->sign_in = isset($pd->oneSigninTime) ? $pd->oneSigninTime : null;
                $attendance->sign_out = isset($pd->oneSignoutTime) ? $pd->oneSignoutTime : null;

                if($attendance->sign_in && $attendance->sign_out)
                {
                    $attendance->work_hour = Carbon::parse($attendance->sign_in)->floatDiffInHours($attendance->sign_out);

                    $attendance->ot_reward = $attendance->work_hour > 9
                            && Carbon::parse($attendance->sign_out)->gt(Carbon::parse("20:30:00"));
                }

                $attendance->save();
            }
        }
    }
}
