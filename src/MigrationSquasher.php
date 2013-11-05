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

    protected $migrationPath;
    protected $outputPath;
    protected $moveToPath;


    protected $migrations;
    protected $tables;

    public function __construct($pathToMigrations, $outputMigrations, $moveToPath)
    {
        $this->migrationPath = trim(($pathToMigrations), '/').'/';
        $this->outputPath = $this->setupFolder($outputMigrations);
        $this->moveToPath = $this->setupFolder($moveToPath);
        $this->migrations = scandir($this->migrationPath);
        $this->tables = [];
    }

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

    protected function parseMigrations()
    {
        foreach ($this->migrations as $migration) {
            if (!is_dir($migration)) {
                echo "Parsing migration $migration\n";
                if ($this->parseFile($migration)) {
                    rename($this->migrationPath . $migration, base_path($this->moveToPath . $migration));
                }
            }
        }
    }

    protected function parseFile($filePath)
    {
        $fileLines = explode(PHP_EOL, file_get_contents($this->migrationPath . $filePath));
        return $this->parseLines($fileLines);
    }

    /**
     * @param $fileLines
     * @return bool
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
                $table->dropColumn($segments[1]);
                break;
            case 'dropForeign':
                $table->dropRelationship($segments[1]);
                break;
            case 'timestamps' :
            case 'softDeletes' :
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
                $table->addColumn($this->createStandardColumn($matches, $segments));
                break;
        }
    }

    /**
     * @param $matches
     * @param $segments
     * @return \Cytracom\Squasher\Database\Column
     */
    protected function createStandardColumn($matches, $segments)
    {
        $col = new Column($matches[0], isset($segments[1]) ? $segments[1] : null);
        foreach ($matches as $match) {

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
                preg_match('/,( *)\d*/', $segments[2], $lineSize) ?
                    (int) preg_replace('/[^\d]*/', '', $lineSize[0]) :
                    null;
            echo json_encode($lineSize) . "\n";
        }
        return $col;
    }

    /**
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
     * @param $folder
     * @return string
     */
    protected function setupFolder($folder)
    {
        $folder = trim($folder, '/');
        if (!is_dir($folder)) {
            echo "Creating output folder $folder";
            mkdir($folder, 0777, true);
        }
        $folder .= '/';
        return $folder;
    }

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

