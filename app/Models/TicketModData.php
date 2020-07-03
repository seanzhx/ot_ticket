<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketModData extends Model
{
    protected $table = 'ModeDataShare_301';
    protected $connection = 'oracle';

    public $timestamps = false;

    /**
        INSERT INTO openquery(OA, 'SELECT * FROM ModeDataShare_301')
        (sourceid, type, content, seclevel, sharelevel, srcfrom, opuser, isdefault, layoutid, layoutid1, layoutorder, setid, rightid, requestid, joblevel)
        SELECT remote_id, 1, user_id, 0, 1, 80, user_id, 1, -1, -1, -1, remote_set_id, 821, 0, 0 FROM [dbo].[oa_tmp_meal_ticket_2] AS l
        WHERE NOT EXISTS(SELECT 1 FROM openquery(OA,'SELECT * FROM ModeDataShare_301') AS r WHERE r.SourceId = l.remote_id);
    **/

    protected $fillable = [
        'sourceid',
        'content', //=>user_id
        'opuser',  //=>user_id
        'setid'    //=>set_id
    ];

    protected $attributes = [
        'type'=>1,
        'seclevel'=>0,
        'sharelevel'=>1,
        'srcfrom'=>80,
        'isdefault'=>1,
        'layoutid'=>-1,
        'layoutid1'=>-1,
        'layoutorder'=>-1,
        'rightid'=>821,
        'requestid'=>-321,
        'joblevel'=>0
    ];
}
