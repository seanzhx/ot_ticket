<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('response');
            $table->string('sign_id');
            $table->string('work_code');
            $table->string('user_name');
            $table->string('dept_name');
            $table->date('sign_date');
            $table->time('sign_in')->nullable();
            $table->time('sign_out')->nullable();
            $table->double('work_hour')->nullable();
            $table->boolean('ot_reward')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
