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
