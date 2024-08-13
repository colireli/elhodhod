<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponsibleMobileToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'responsible_mobile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('responsible_mobile')->nullable();
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
        if (Schema::hasColumn('users', 'responsible_mobile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('responsible_mobile');
            });
        }
    }
}
