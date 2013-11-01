<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 9:46 AM
 */

namespace Cytracom\Squasher;

use Squasher\Database\Table;
use Squasher\Database\Relationship;
use Squasher\Database\Column;

class MigrationSquasher
{

    protected $migrationPath;
    protected $migrations;
    protected $tables;

    public function __construct($pathToMigrations)
    {
        $this->migrationPath = trim($pathToMigrations, '/');
        $this->migrations = scandir($pathToMigrations);
        $this->tables = [];
    }

    public function squash($outputFile)
    {
        echo "Beginning migration squash\n";
        $this->parseMigrations();
        foreach ($this->tables as $table) {
            echo "Squashing $table";
            file_put_contents("app/database/migrations/".TableBuilder::$built."_squashed_" . $table->name . "table.php", TableBuilder::build($table));
        }

        echo "Squash complete! Old migrations have been moved to app/storage/migrations.";
    }

    protected function parseMigrations()
    {
        if (!is_dir(app_path().'/storage/migrations')) {
            mkdir(app_path().'/storage/migrations');
        }

        foreach ($this->migrations as $migration) {
            echo "Parsing migration $migration\n";
            $this->parseMigration($migration);
            rename($migration, app_path().'/storage/migrations/'.basename($migration));
        }
    }

    protected function parseMigration($filePath)
    {
        $fileLines = explode(PHP_EOL, file_get_contents('/' . $this->migrationPath . '/' . $filePath));
        $this->parseFile($fileLines);
    }

    /**
     * @internal null|Table $table
     * @param $fileLines
     */
    protected function parseFile($fileLines)
    {
        $table = null;
        foreach ($fileLines as $line) {
            if (preg_match('/public function down\(\)/', $line)) {
                break;
            }

            if (preg_match('/Schema::[^(]*\((\'|")(.*)(\'|"), f/', $line, $matches)) {
                $table = $this->parseTable($matches);
            }
            elseif($table !== null) {
                $this->parseLine($table, $line);
            }
        }
    }

    /**
     * @param $matches
     * @return null|Table
     */
    protected function parseTable($matches)
    {
        preg_match('/(\'|").*(\'|")/', $matches[0], $tableMatch);
        $tableMatch = preg_replace("/'|\"/", "", $tableMatch[0]);
        return isset($this->tables[$tableMatch]) ? $this->tables[$tableMatch] :
            $this->tables[$tableMatch] = new Table($tableMatch);
    }

    /**
     * @param Table $table
     * @param $line
     */
    protected function parseLine(Table $table, $line)
    {
        if (preg_match('/\$[^->]*->engine/', $line)) {
            $table->setEngine(preg_replace("/(('*)|(;*)|( *))*/", "", explode("=", $line)[1]));
            return;
        }
        elseif ($matches = $this->lineContainsFunctionCall($line)) {
            $this->createMigrationFunctionCall($table, $line, $matches);
        }
    }


    /**
     * @param Table $table
     * @param $line
     * @param $matches
     */
    protected function createMigrationFunctionCall(Table $table, $line, $matches)
    {
        $segments = explode("'", $line);
        $line = str_replace('/"/', "'", $line);
        $matches[0] = preg_replace('/>| |,/', '', $matches[0]);
        switch ($matches[0]) {
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
            default :
                $table->addColumn($this->createStandardColumn($line, $matches, $segments));
                break;
        }
    }

    /**
     * @param $line
     * @param $matches
     * @param $segments
     * @return \Squasher\Column
     */
    protected function createStandardColumn($line, $matches, $segments)
    {
        $col = new Column($matches[0], isset($segments[1]) ? $segments[1] : null);
        foreach ($matches as $match) {
            if (str_contains($line, 'unsigned')) {
                $col->unsigned = true;
                break;
            };
        }

        $col->size = preg_match('/,( *)\d*/', $line, $lineSize) ? (int)preg_replace('/[^\d]/','',$lineSize[0]) : null;
        return $col;
    }

    /**
     * @param $line
     * @return array|bool
     */
    protected function lineContainsFunctionCall($line)
    {
        if (preg_match('/([^->]*>[^(]*)/', $line, $match)) {
            return $match;
        }
        return false;
    }
}

