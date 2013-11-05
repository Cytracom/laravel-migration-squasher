<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SquashedTestTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("test", function (Blueprint $table) {
            $table->string('test');
            $table->string('size', 2);
            $table->integer('int');
            $table->smallInteger('smallInt');
            $table->bigInteger('bigInt');
            $table->increments('inc');
            $table->bigIncrements('bigInc');
            $table->binary('bin');
            $table->boolean('bool');
            $table->date('dte');
            $table->double('doub');
            $table->decimal('deci');
            $table->float('flat');
            $table->float('unsigned and unique')->unsigned()->unique();
            $table->unsignedBigInteger('ubigInt', true)->unique();
            $table->unsignedInteger('uint');
            $table->text('txt');
            $table->mediumText('medtext');
            $table->mediumInteger('medint');
            $table->longText('longtext');
            $table->nullableTimestamps();
            $table->softDeletes();
            $table->engine = '"TestEngine"';
        });
    }
}