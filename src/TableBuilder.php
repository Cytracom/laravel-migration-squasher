<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 3:48 PM
 */

namespace Cytracom\Squasher;

use Squasher\Database\Table;

class TableBuilder
{

    public static $built = 0;

    /**
     * @var Table
     */
    protected $table;
    public $content;

    protected function __construct(Table $table)
    {
        $this->table = $table;
    }

    public static function build(Table $table)
    {
        $squasher = new self($table);
        $squasher->content = $squasher->init();
        $squasher->fillInTableData();
        $squasher->content .= $squasher->close();
        return str_replace("\n", PHP_EOL, $squasher->content);
    }

    public function fillInTableData()
    {
        $this->createColumns();
        $this->createRelationships();
    }

    protected function createColumns()
    {
        $doLater = [];
        foreach ($this->table->getColumns() as $column) {
            //if it is a generic column such as timestamps, soft deletes, etc; put at the end of the column list.
            if ($column->name === '') {
                array_push($doLater, $column);
            }
            else {
                $this->content .= $this->createColumn($column);
            }
        }

        foreach ($doLater as $column) {
            $this->content .= $this->createColumn($column);
        }
    }

    protected function createRelationships()
    {
        foreach ($this->table->getRelationships() as $relationship) {
            $this->content .= "            \$table->foreign('$relationship->tableColumn')->" .
                "references('$relationship->relationshipColumn')->on('$relationship->relationshipTable');\n";
        }
    }

    public function createColumn($column)
    {
        $line = "            \$table->$column->type(";

        $line .= $this->appendColumnName($column);
        $line .= $this->appendColumnSize($column) . ')';
        $line .= $this->appendColumnSign($column, $line) . ";\n";

        return $line;
    }

    /**
     * @param $column
     * @return string
     */
    protected function appendColumnSize($column)
    {
        if ($column->size !== null) {
            return ", " . (int) $column->size;
        }
        return '';
    }

    /**
     * @param $column
     * @return string
     */
    protected function appendColumnName($column)
    {
        if ($column->name !== null) {
            return "'$column->name'";
        }
        return '';
    }

    /**
     * @param $column
     * @return string
     */
    protected function appendColumnSign($column)
    {
        if ($column->unsigned) {
            return "->unsigned()";
        }
        return '';
    }

    /**
     * Creates the base template for a migration file.
     *
     * @return string
     */
    public function init()
    {
        self::$built++;
        return
            "<?php\n" .
            "\n" .
            "use Illuminate\\Database\\Migrations\\Migration;\n" .
            "use Illuminate\\Database\\Schema\\Blueprint;\n" .
            "\n" .
            "class Squashed" . studly_case($this->table->name) . "Table extends Migration\n" .
            "{\n" .
            "\n" .
            "    /**\n" .
            "     * Run the migrations.\n" .
            "     *\n" .
            "     * @return void\n" .
            "     */\n" .
            "    public function up()\n" .
            "    {\n" .
            "       Schema::create(\"{$this->table->name}\", function (Blueprint \$table) {\n";
    }

    public function close()
    {
        return "       });\n    }\n}";
    }
}