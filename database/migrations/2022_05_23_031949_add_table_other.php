<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTableOther extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('m_menu', function (Blueprint $table) {
            $table->id("id_menu");
            $table->string('nama_menu',100);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamp('created_date')->useCurrent();
            $table->timestamp('updated_date')->nullable();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_action', function (Blueprint $table) {
            $table->id("id_action");
            $table->integer('id_menu');
            $table->string('nama_role',100);
            $table->string('kode',100);
        });
        Schema::create('m_role', function (Blueprint $table) {
            $table->id("id_role");
            $table->string('nama_role',100);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamp('created_date')->useCurrent();
            $table->timestamp('updated_date')->nullable();
            $table->integer('deleted')->default(1);
        });
        Schema::create('map_role_action', function (Blueprint $table) {
            $table->id();
            $table->integer('id_role');
            $table->integer('id_action');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
