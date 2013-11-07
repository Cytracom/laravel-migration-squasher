<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SquashedRenamedTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("renamed", function (Blueprint $table) {
            $table->string('test', 255)->nullable();
            $table->string('size', 2);
            $table->integer('int');
            $table->smallInteger('smallInt');
            $table->bigInteger('bigInt');
            $table->increments('inc');
            $table->bigIncrements('bigInc')->nullable();
            $table->binary('bin')->nullable();
            $table->boolean('bool')->default(true);
            $table->datetime('dte')->nullable();
            $table->double('doub', 11,12)->nullable();
            $table->decimal('deci');
            $table->float('flat');
            $table->float('unsigned and unique')->unsigned()->unique();
            $table->unsignedBigInteger('ubigInt', true)->unique();
            $table->unsignedInteger('uint');
            $table->text('txt');
            $table->mediumText('medtext');
            $table->mediumInteger('medint');
            $table->integer('longText')->nullable();
            $table->nullableTimestamps();
            $table->softDeletes();
            $table->engine = 'TestEngine';
        });
    }
}