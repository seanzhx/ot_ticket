<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Log;

class Ticket extends Model
{
    protected $table = 'uf_meal_ticket';
    protected $connection = 'oracle';

    protected $fillable = [
        'record_id', 'user_id', 'user_name', 'workcode', 'sign_date', 'sign_time', 'title', 'lock_status', 'give_status', 'ModeDataCreater'
    ];
    protected $attributes = [
        'lock_status' => false,
        'give_status' => false,
        'FormModeId' => 301,
        'ModeDataCreaterType' => 0,
    ];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->ModeDataCreateDate = Carbon::now()->toDateString();
            $model->ModeDataCreateTime = Carbon::now()->toTimeString();
        });
    }

    protected static function queryExistTicket($begin_date, $end_date)
    {
        //获取OA已有数据并存入表中
        $map_tickets = [];
        $tickets = self::where([['sign_date', '>=', $begin_date], ['sign_date', '<=', $end_date]])
            ->get();
        foreach ($tickets as $t)
        {
            if(empty($t->sign_date) || empty($t->workcode))
            {
                Log::warning("data incomplete id:{$t->id}");
                continue;
            }
            $map_tickets[$t->sign_date][$t->workcode] = $t;
        }

        return $map_tickets;
    }

    protected static function createBy(Attendance $a)
    {
        $ticket = new self;
        $ticket->sign_id = $a->sign_id;
        $ticket->user_id = OaUser::queryId($a->workcode);
        $ticket->user_name = $a->user_name;
        $ticket->workcode = $a->workcode;
        $ticket->sign_date = $ticket->title = $a->sign_date;
        $ticket->sign_time = substr($a->sign_out,0,5);
        $ticket->ModeDataCreater = $ticket->user_id;

        return $ticket;
    }


    public static function sync($begin_date='2020-06-19', $end_date='2020-06-29')
    {
        //TODO: 参数处理
        // $begin_date = Carbon::createFromFormat('Y-m-d', begin_date);
        // $end_date = Carbon::now();
        $retData = ['abnormal'=>[], 'new'=>[]];

        $map_tickets = self::queryExistTicket($begin_date, $end_date);

        //获取所有加班数据
        $attendances = Attendance::queryRewards($begin_date, $end_date);

        //找出新数据
        $new_tickets = [];
        $debug = ['repeat'=>0, 'new'=>0, 'no_id'=>0];
        foreach ($attendances as $a)
        {
            //检查Ticket表中是否已有对应加班数据
            if (isset($map_tickets[$a->sign_date]) && isset($map_tickets[$a->sign_date][$a->workcode]))
            {
                // Log::debug("re:{$a->user_name}/{$a->sign_date}/{$a->workcode}");
                $debug['repeat']++;
                continue;
            }
            else
            {
                $ticket = self::createBy($a);

                //写入哈希表可以实现去重效果
                $new_tickets[$ticket->sign_date][$ticket->workcode] = $ticket;

                Log::debug("new:{$ticket->user_name}/{$ticket->sign_date}/{$ticket->workcode}/{$ticket->user_id}");
                $debug['new']++;
                if (!$ticket->user_id)
                {
                    $debug['no_id']++;
                    $retData['abnormal'][] = [$ticket->user_name, $ticket->sign_date, $ticket->workcode];
                }
                else
                {
                    $retData['new'][] = [$ticket->user_name, $ticket->sign_date, $ticket->workcode];
                }
            }
        }
        Log::debug("---Summary:new(no_id):{$debug['new']}({$debug['no_id']})---re:{$debug['repeat']}---");

        //保存到数据库
        foreach($new_tickets as $date_tickets)
        {
            foreach($date_tickets as $ticket)
            {
                $ticket->save();

                $tmds = new TicketModDataSet;
                $tmds->sourceid = $ticket->id;
                $tmds->save();

                $tmd = new TicketModData;
                $tmd->sourceid = $ticket->id;
                $tmd->content = $ticket->user_id;
                $tmd->opuser = $ticket->user_id;
                $tmd->setid = $tmds->id;
                $tmd->save();
            }
        }

        return $retData;

    }
}
