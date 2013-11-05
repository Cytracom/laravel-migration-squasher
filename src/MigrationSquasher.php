<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 9:46 AM
 */

namespace Cytracom\Squasher;

use Cytracom\Squasher\Database\Column;
use Cytracom\Squasher\Database\Relationship;
use Cytracom\Squasher\Database\Table;

class MigrationSquasher
{

    /**
     * The path to unsquashed migrations.
     *
     * @var string
     */
    protected $migrationPath;

    /**
     * The path to the generated migrations.
     *
     * @var string
     */
    protected $outputPath;

    /**
     * The path to move old migrations to.
     *
     * @var string
     */
    protected $moveToPath;

    /**
     * An array of strings containing the paths to each migration.
     *
     * @var array
     */
    protected $migrations;

    /**
     * An array of table objects.
     *
     * @var array
     */
    protected $tables;

    /**
     * Preps a new migration squasher.
     *
     * @param $pathToMigrations
     * @param $outputMigrations
     * @param $moveToPath
     */
    public function __construct($pathToMigrations, $outputMigrations, $moveToPath = null)
    {
        $this->migrationPath = trim(($pathToMigrations), '/') . '/';
        $this->outputPath = $this->setupFolder($outputMigrations);
        $this->moveToPath = $moveToPath == null ? null : $this->setupFolder($moveToPath);
        $this->migrations = scandir($this->migrationPath);
        $this->tables = [];
    }

    /**
     * Begin squashing all migrations in the migration path.
     */
    public function squash()
    {
        echo "Beginning migration squash\n";

        $this->parseMigrations();

        $sortedTableNames = $this->resolveTableDependencies();
        $date = date('Y_m_d');
        foreach ($sortedTableNames as $key => $table) {
            echo "Squashing $table\n";

            file_put_contents($this->outputPath . $date . '_' . str_pad($key, 6, '0', STR_PAD_LEFT) .
                "_squashed_" . $table .
                "_table.php", TableBuilder::build($this->tables[$table]));
        }

        echo "Squash complete!" . (trim($this->moveToPath, '/') === trim($this->migrationPath, '/') ? '' :
                " Old migrations have been moved to " . $this->moveToPath) . "\n";
        echo "New migrations are located in $this->outputPath\n";
    }

    /**
     * Begin parsing each file.
     */
    protected function parseMigrations()
    {
        foreach ($this->migrations as $migration) {
            if (!is_dir($migration)) {
                echo "Parsing migration $migration\n";
                if ($this->parseFile($migration) && $this->moveToPath !== null) {
                    rename($this->migrationPath . $migration, base_path($this->moveToPath . $migration));
                }
            }
        }
    }

    /**
     * Parse the given file.
     *
     * @param $filePath
     * @return bool true/false if the file was a migration
     */
    protected function parseFile($filePath)
    {
        $fileLines = explode(PHP_EOL, file_get_contents($this->migrationPath . $filePath));
        return $this->parseLines($fileLines);
    }

    /**
     * Parse each string from the given array of strings
     *
     * @param $fileLines
     * @return bool true/false if the file was a migration
     */
    protected function parseLines($fileLines)
    {
        $table = null;
        $migration = false;
        foreach ($fileLines as $line) {
            if (preg_match('/public function down\(\)/', $line)) {
                break;
            }

            if (str_contains($line, "}")) {
                $table = null;
            }

            if (preg_match('/Schema::(d|c|t|[^(]*\((\'|")(.*)(\'|"))*/', $line, $matches)) {
                $table = $this->parseTable($matches);
                $migration = true;
            }
            elseif ($table !== null) {
                $this->parseField($table, $line);
            }
        }
        return $migration;
    }

    /**
     * Pull the table out of the given regex matches.
     *
     * @param $matches
     * @return null|Table
     */
    protected function parseTable($matches)
    {
        preg_match('/(\'|").*(\'|")/', $matches[0], $tableMatch);
        $tableMatch = preg_replace("/'|\"/", "", $tableMatch[0]);

        if (str_contains($matches[0], '::drop')) {
            unset($this->tables[$tableMatch]);
            return null;
        }

        return isset($this->tables[$tableMatch]) ? $this->tables[$tableMatch] :
            $this->tables[$tableMatch] = new Table($tableMatch);
    }

    /**
     * Parse the given line and set the values in the given table.
     *
     * @param Table $table
     * @param $line
     */
    protected function parseField(Table $table, $line)
    {
        if (preg_match('/\$[^->]*->engine/', $line)) {
            $table->setEngine(preg_replace("/(('*)|(;*)|( *))*/", "", explode("=", $line)[1]));
            return;
        }
        elseif ($matches = $this->lineContainsFunctionCall($line)) {
            $this->createMigrationFunctionCall($table, $line, $matches[0]);
        }
    }


    /**
     * Create the function call based on the column on the line.
     *
     * @param Table $table
     * @param $line
     * @param $matches
     */
    protected function createMigrationFunctionCall(Table $table, $line, $matches)
    {
        $line = str_replace('"', "'", $line);
        $segments = explode("'", $line);
        $matches[0] = preg_replace('/>| |,/', '', $matches[0]);
        switch ($matches[0]) {
            case 'primary' :
                $table->setPrimaryKey($segments[1]);
                break;
            case 'unique' :
                $table->getColumn($segments[1])->unique = true;
                break;
            case 'renameColumn':
                $table->alterColumn($segments[1], "name", $segments[3]);
                break;
            case 'foreign':
                $table->addRelationship(new Relationship($segments[1], $segments[3], $segments[5]));
                break;
            case 'dropColumn':
            case 'dropIfExists' :
                $table->dropColumn($segments[1]);
                break;
            case 'dropForeign':
                $table->dropRelationship($segments[1]);
                break;
            case 'dropSoftDeletes' :
                $table->dropColumn('softDeletes');
                break;
            case 'dropTimestamps' :
                $table->dropColumn('timestamps');
                break;
            case 'timestamps' :
            case 'softDeletes' :
            case 'nullableTimestamps' :
                $segments[1] = $matches[0];
            case 'string' :
            case 'integer' :
            case 'increments' :
            case 'bigIncrements' :
            case 'bigInteger' :
            case 'smallInteger' :
            case 'float' :
            case 'double' :
            case 'decimal' :
            case 'boolean' :
            case 'date' :
            case 'dateTime' :
            case 'time' :
            case 'timestamp' :
            case 'text' :
            case 'binary' :
            case 'default' :
            case 'morphs' :
            case 'mediumText' :
            case 'longText' :
            case 'mediumInteger' :
            case 'tinyInteger' :
            case 'unsignedBigInteger' :
            case 'unsignedInteger' :
                $table->addColumn($this->createStandardColumn($matches, $segments));
                break;
        }
        $matches = null;
    }

    /**
     * A generic function for creating a plain old column.
     *
     * @param $matches
     * @param $segments
     * @return \Cytracom\Squasher\Database\Column
     */
    protected function createStandardColumn($matches, $segments)
    {
        $col = new Column($matches[0], isset($segments[1]) ? $segments[1] : null);
        foreach ($matches as $key => $match) {
            if ($key === 0) {
                continue;
            }
            if (str_contains($match, 'unsigned')) {
                $col->unsigned = true;
            }
            elseif (str_contains($match, 'unique')) {
                $col->unique = true;
            }
            elseif (str_contains($match, 'nullable')) {
                $col->nullable = true;
            }
        }
        if (isset($segments[2])) {
            $col->size =
                preg_match('/,( *)[\dtrue]*/', $segments[2], $lineSize) ?
                    preg_replace('/[^\dtrue]*/', '', $lineSize[0]) :
                    null;
        }
        return $col;
    }

    /**
     * Return an array of function calls on the given line, or false if there are none.
     *
     * @param $line
     * @return array|bool
     */
    protected function lineContainsFunctionCall($line)
    {
        if (preg_match_all('/[^->]*>[^(]*/', $line, $match)) {
            return $match;
        }
        return false;
    }

    /**
     * Create the given folder recursively, and return the correctly formatted folder path.
     *
     * @param $folder
     * @return string
     */
    protected function setupFolder($folder)
    {
        $folder = trim($folder, '/');
        if (!is_dir($folder)) {
            echo "Creating output folder $folder\n";
            mkdir($folder, 0777, true);
        }
        $folder .= '/';
        return $folder;
    }

    /**
     * Return an array that is the correct order that tables should be created.
     *
     * @return array
     */
    protected function resolveTableDependencies()
    {
        echo "Resolving foreign key relationships...\n";
        $sortedTables = [];
        $count = count($this->tables);
        while (count($sortedTables) !== $count) {
            {
                foreach ($this->tables as $table) {
                    if (in_array($table->name, $sortedTables)) {
                        continue;
                    }

                    $resolved = true;
                    foreach ($table->getRelationships() as $relationship) {
                        if (!in_array($relationship->relationshipTable, $sortedTables)) {
                            $resolved = false;
                            break;
                        }
                    }
                    if ($resolved) {
                        array_push($sortedTables, $table->name);
                    }
                }
            }
        }
        echo "Done!\n";
        return $sortedTables;
    }
}

