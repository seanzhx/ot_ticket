<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Token;
use App\Models\Attendance;
use App\Models\Ticket;
use App\DaoYiApi;
use DB;


class CallApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:call
                            {interface : token|attend|ticket}
                            {startDate? : YYYY-MM-DD}
                            {endDate? : YYYY-MM-DD}
                            {--singleStep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $interface = $this->argument('interface');
        $startDate = $this->argument('startDate');
        $endDate = $this->argument('endDate') ? $this->argument('endDate') : $startDate;
        $isSingleStep = $this->option('singleStep');

        switch ($interface)
        {
            case 'token':
                $this->token();
                break;
            case 'attend':
                $this->attend($startDate, $endDate);
                break;
            case 'ticket':
                $this->ticket($startDate, $endDate, $isSingleStep);
                break;
            default:
                $this->error('Call unexists api: '.$interface);
                break;
        }
    }

    protected function token()
    {
        $this->info('Token: '.Token::get());
    }

    protected function attend($startDate, $endDate)
    {
        //参数格式验证，必须是YYYYMMDD的格式
        $this->validateDates($startDate, $endDate);
        $startDate = str_replace('-', '', $startDate);
        $endDate = str_replace('-', '', $endDate);

        if(!$this->confirm("开始同步从{$startDate}到{$endDate}的考勤数据"))
        {
            $this->info("  同步取消！");
            die();
        }

        $abnormalData = Attendance::sync($startDate, $endDate, $this->output);

        $this->info("  同步完成！");

        if(!empty($abnormalData))
        {
            $this->info("以下为异常数据：");
            $this->table(['workcode', 'user_name'], $abnormalData);
        }
    }

    protected function ticket($startDate, $endDate, $isSingleStep)
    {
        if(!$isSingleStep)
        {
            $this->attend($startDate, $endDate);
        }

        $this->validateDates($startDate, $endDate);

        if(!$this->confirm("开始同步从{$startDate}到{$endDate}的加班数据"))
        {
            $this->info("  同步取消！");
            die();
        }

        $retData = Ticket::sync($startDate, $endDate);

        $this->info("  同步完成！");

        if(!empty($retData['new']))
        {
            $this->info("以下为新增正常数据：");
            $this->table(['workcode', 'user_name'], $retData['new']);
        }

        if(!empty($retData['abnormal']))
        {
            $this->info("以下为新增异常数据：");
            $this->table(['user_name', 'sign_date', 'workcode'], $retData['abnormal']);
        }
    }

    protected function isDateValid($date)
    {
        return preg_match('/^20\d{2}-?(0[1-9]|1[0-2])-?(0[1-9]|[1-2][0-9]|3[0-1])$/', $date);
    }

    protected function validateDates($startDate, $endDate)
    {
        if(!$this->isDateValid($startDate))
        {
            $this->error('startDate 起始日期未输入或输入错误');
            die();
        }

        if(!$this->isDateValid($endDate))
        {
            $this->error('结束日期输入错误endDate:'.$endDate);
            die();
        }
    }
}
