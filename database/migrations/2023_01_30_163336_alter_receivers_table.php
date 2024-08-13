<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterReceiversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('receivers')) {
            Schema::table('receivers', function (Blueprint  $table) {
                $table->integer('user_id')->unsigned();
                $table->tinyInteger('is_archived')->default(0);
                $table->integer('branch_id')->unsigned()->nullable();
            });
         }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receivers');
    }
}
