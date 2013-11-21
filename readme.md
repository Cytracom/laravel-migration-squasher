Aggregate your incremental Laravel migration files into single migration for each table. This can be beneficial when needing to compress large migration sets into a single migration for each table. 

This package also eliminates all alter columns since all migartions are aggregated into a single file making testing via sqlite a possibility.

[![Build Status](https://travis-ci.org/Cytracom/laravel-migration-squasher.png)](https://travis-ci.org/Cytracom/laravel-migration-squasher)

To install simply require 
```
"cytracom/squasher": "dev-master"
```
Then, add the service provider to your app/config/app.php to enable artisan functionality:
```
'Cytracom\Squasher\SquasherServiceProvider'
```
NOTE: this is not required if you do not with to have the commandline interface.  If you want to use the squasher just for testing, then you can ignore this service provider, and call the squasher directly.  This way, the squasher can be in your require-dev and not be a part of your production stack.



Commandline usage:
```
php artisan migrate:squash [-p|--path[="..."]] [-o|--output[="..."]] [-mv|--move-to[="..."]]                                                      
                                                                                                                                  
Options:                                                                                                                 
 --path (-p)           The path to the migrations folder (default: "app/database/migrations")                             
 --output (-o)         The path to the output folder of squashes (default: "app/tests/migrations")
 --move-to (-mv)       The path where old migrations will be moved. (default: "app/database/migrations")      
```


Usage in php: 
```php
$squasher = new \Cytracom\Squasher\MigrationSquasher($pathToMigrations, $outputForSquashedMigrations [, $moveOldToThisPath = null]);
$squasher->squash();
```


The squasher does not currently support composite keys, or indexes.  If you find anything else I missed, please raise an issue! Or, even better, attempt to integrate it!

The migration squasher will take several migrations and create a single, final migration that reflects what the database schema should be after all migrations have run.

Keep in mind that the squasher was made for testing, not for incremental database changes.  Using the squasher will drop any non-migration related functionality in your code.  The goal is to get rid of all alter columns, to enable sqlite testing.

The table squasher can handle simple migration statements, written in a normal, not insane way. Like this:

```php
Schema::create('my_table', function (Blueprint $table) {
    $table->integer("my_int",10)->unsigned()->unique();
    $table->increments("id");
    $table->string("test",255);
    $table->myEnum("oldArrayInit", array("val1","val2"));
    $table->myEnum("newArrayInit", ["val1","val2"]);

    DB::update('ALTER TABLE `my_table` MODIFY COLUMN `test` blob(500);');
    //etc;
});
```
This also works for dropping and modifying schemas.  For a more detailed view on what it can handle, look at the sample test data in src/tests/data/MigrationTestData.php

The table squasher will NOT handle things like
```php
$myStringColumns = ["col1","col2","col3"];
foreach($myStringColumns as $column){
    $table->string($column);
}
```
And it never ever will.  Migrations shouldn't be written this way, and writing a php parser in php is no small task.


Here is how you can use this for your tests

While setting up the test case, we run

```php
recursiveDelete(base_path('app/tests/migrations'));
$squash = new \Cytracom\Squasher\MigrationSquasher("app/database/migrations", "app/tests/migrations");
$squash->squash();
\Artisan::call('migrate', ['--path' => 'app/tests/migrations']);

/**
 * Delete a file or recursively delete a directory
 *
 * @param string $str Path to file or directory
 * @return bool
 */
function recursiveDelete($str){
    if(is_file($str)){
        return @unlink($str);
    }
    elseif(is_dir($str)){
        $scan = glob(rtrim($str,'/').'/*');
        foreach($scan as $index=>$path){
            recursiveDelete($path);
        }
        return @rmdir($str);
    }
}
```
We delete all of the migrations before squashing again, to get rid of old squashed migrations that may be there.

Again, please raise an issue if you find one, and feel free to make pull requests for review!  Our goal is to make testing with sqlite much more of a possibility, to enable fast testing.  Help from the community is always appreciated.
