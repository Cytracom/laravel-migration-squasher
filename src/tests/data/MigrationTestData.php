<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UnsquashedTestSquash extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("droppedSchema", function (Blueprint $table) {
            $table->increments('thisWillBeDropped');
        });
        Schema::drop("droppedSchema");
        Schema::create("test", function (Blueprint $table) {
            $table->string("test");
            $table->string("size",2);
            $table->integer("int");
            $table->smallInteger("smallInt");
            $table->bigInteger("bigInt");
            $table->increments("inc");
            $table->bigIncrements("bigInc");
            $table->binary("bin");
            $table->nullableTimestamps();
            $table->softDeletes();
            $table->dropSoftDeletes();
            $table->softDeletes();
            $table->boolean("bool")->default(true);
            $table->date("dte");
            $table->double("doub");
            $table->decimal("deci");
            $table->engine = "TestEngine";
            $table->float("flat");
            $table->float("unsigned and unique")->unsigned()->unique();
            $table->integer("to_be_dropped");
            $table->unsignedBigInteger('ubigInt', true)->unique();
            $table->unsignedInteger('uint');
            $table->text('txt');
            $table->mediumText('medtext');
            $table->mediumInteger('medint');
            $table->longText('longText');
            $table->dropColumn("to_be_dropped");

            DB::update('ALTER TABLE `test` MODIFY COLUMN `longText` int(11);');
            DB::update('ALTER TABLE test MODIFY COLUMN `bin` BLOB(5000);');
            DB::update('ALTER TABLE `test` MODIFY COLUMN doub double(11,12);');
            DB::update('ALTER TABLE test MODIFY COLUMN bigInc bigint(11)  AUTO_INCREMENT;');
            DB::update('ALTER TABLE `test` MODIFY COLUMN `test` string(255);');
            DB::update('ALTER TABLE `test` MODIFY COLUMN `dte` datetime NULL AUTO_INCREMENT;');
        });
    }
}