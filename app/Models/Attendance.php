<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;
use Log;

use App\DaoYiApi;

class Attendance extends Model
{
    protected $casts = [
        'response' => 'array',
    ];

    public static function sync($startDate='20200618', $endDate='20200618', $output=null)
    {
        $progressBar = null;
        $abnormalData = [];

        DB::table('attendances')->truncate();

        $maxPage = 1;
        for ($currentPage=1; $currentPage <= $maxPage ; $currentPage++)
        {
            $result = DaoYiApi::attendance(Token::get(), env('CORP_ID'), $startDate, $endDate, $currentPage);

            if($result->totalRows==0) return;

            if(empty($progressBar) && $output)
            {
                $progressBar = $output->createProgressBar($result->totalRows);
                $progressBar->start();
            }

            $maxPage = $result->maxPage;

            foreach($result->pageData as $pd)
            {
                $attendance = self::createBy($pd);
                $attendance->save();

                //检查收集工号异常数据
                if(!preg_match('/J\d{4}/', $attendance->workcode))
                {
                    $abnormalData[$attendance->workcode] = [
                        'workcode'=>$attendance->workcode,
                        'user_name'=>$attendance->user_name
                    ];
                }

                if($progressBar) $progressBar->advance();
            }
        }

        if(!empty($progressBar)) $progressBar->finish();

        return $abnormalData;

    }

    public static function queryRewards($begin_date, $end_date)
    {
        //获取所有加班数据
        return Attendance::where([['ot_reward', true],
            ['sign_date', '>=', $begin_date],
            ['sign_date', '<=', $end_date],
            ['check_name', '金斧子考勤']])
            ->get();
    }

    protected static function createBy($pd)
    {
        //删除空白属性
        foreach($pd as $k => $v)
        {
            if(!$v) unset($pd->$k);
        }

        $attendance = new self();
        $attendance->response = $pd;

        $attendance->workcode = $pd->wxUserId;
        $attendance->user_name = $pd->personName;
        $attendance->dept_name = $pd->departmentName;
        $attendance->sign_id = $pd->id;
        $attendance->sign_date = $pd->signDate;
        $attendance->sign_in = isset($pd->oneSigninTime) ? $pd->oneSigninTime : null;
        $attendance->sign_out = isset($pd->oneSignoutTime) ? $pd->oneSignoutTime : null;
        $attendance->check_name = $pd->checkWorkName;

        if($attendance->sign_in && $attendance->sign_out)
        {
            //计算工作时长
            $attendance->work_hour = round(Carbon::parse($attendance->sign_in)->floatDiffInHours($attendance->sign_out),2);

            //计算是否加班：工作时长>9H && 最后一次打卡超过20:30
            $attendance->ot_reward = $attendance->work_hour > 9
                    && Carbon::parse($attendance->sign_out)->gt(Carbon::parse("20:30:00"));
        }

        return $attendance;
    }
}
