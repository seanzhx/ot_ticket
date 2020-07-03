<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketModDataSet extends Model
{
    protected $table = 'ModeDataShare_301_SET';
    protected $connection = 'oracle';

    public $timestamps = false;

    /**
    INSERT INTO openquery(OA, 'SELECT * FROM ModeDataShare_301_SET')
        (sourceid, righttype, sharetype, showlevel, isdefault, layoutid, layoutorder, rightid, requestid, hrmcompanyvirtualtype)
        SELECT remote_id, 1, 80, 0, 1, -1, -1, 821, NULL, 0 FROM [dbo].[oa_tmp_meal_ticket_2] AS l
            WHERE NOT EXISTS(SELECT 1 FROM openquery(OA,'SELECT * FROM ModeDataShare_301_SET') AS r WHERE r.SourceId = l.remote_id);
    **/

    protected $fillable = [
        'sourceid'
    ];

    protected $attributes = [
        'righttype'=>1,
        'sharetype'=>80,
        'showlevel'=>0,
        'isdefault'=>1,
        'layoutid'=>-1,
        'layoutorder'=>-1,
        'rightid'=>821,
        'requestid'=>-321,
        'hrmcompanyvirtualtype'=>0
    ];
}
