<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Token;
use App\Models\Attendance;
use App\Models\Ticket;
use DB;

class RequestController extends Controller
{
    public function token(Request $request)
    {
        //phpinfo();
        // $d = DB::connection('oracle')->select('SELECT * FROM uf_meal_ticket WHERE ROWNUM < 10');
        // dd($d);

        // $t = Ticket::first();
        // dd($t->id,$t->responce);


        return Token::get();
    }

    public function attendance(Request $request)
    {
        Attendance::get();
        return 'attendance';
    }

    public function ticket(Request $request)
    {
        Ticket::sync();
        // Ticket::create();
        return 'ticket';
    }
}
