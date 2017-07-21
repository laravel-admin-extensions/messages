<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $table = config('admin.extensions.messages.table', 'admin_messages');

        Schema::connection($connection)->create($table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from');
            $table->integer('to');
            $table->string('title');
            $table->text('message');
            $table->timestamp('read_at');
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
        $connection = config('admin.database.connection') ?: config('database.default');

        $table = config('admin.extensions.messages.table', 'admin_messages');

        Schema::connection($connection)->dropIfExists($table);
    }
}
