<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OaUser extends Model
{
    protected $table = 'HRMRESOURCE';
    protected $connection = 'oracle';

    protected static $map_ids;

    public static function queryId($workcode)
    {
        if(empty(self::$map_ids))
        {
            $users = self::select('id', 'workcode')->whereNotNull(['loginid', 'workcode'])->get();
            self::$map_ids = $users->pluck('id','workcode');

        }

        return isset(self::$map_ids[$workcode]) ? self::$map_ids[$workcode] : 0;
    }
}
