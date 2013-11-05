Aggregate your incremental Laravel migration files into single migration for each table. This eliminates all alter columns and makes testing via sqlite a possibility.

[![Build Status](https://travis-ci.org/Cytracom/laravel-migration-squasher.png)](https://travis-ci.org/Cytracom/laravel-migration-squasher)


To install simply require 
```
"cytracom/squasher": "dev-master"
```

Then, add the service provider to your app/config/app.php
```
'Cytracom\Squasher\SquasherServiceProvider'
```

Commandline usage:
```
 migrate:squash [-p|--path[="..."]] [-o|--output[="..."]] [-mv|--move-to[="..."]]                                                      
                                                                                                                                  
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

The squasher does not currently support composite keys, enumerations, or indexes.  If you find anything else I missed, please raise an issue! Or, even better, attempt to integrate it!

Keep in mind that the squasher was made for testing, not for incrememtal database changes.  Using the squasher will drop any non-table related functionality in your code.

The table squasher can handle simple migration statements, written in a normal, not insane way. Like this:
```php
Schema::create('my_table', function (Blueprint $table) {
    $table->integer("my_int",10)->unsigned()->unique();
    $table->increments("id");
    $table->string("test",255);
    //etc;
});
```

The table squasher will NOT hanlde things like
```php
$myStringColumns = ["col1","col2","col3"];
foreach($myStringColumns as $column){
    $table->string($column);
}
```
And I don't think it ever will.  I do not believe migrations were intended to work that way, and it's a pain to parse anyway.


