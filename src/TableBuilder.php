<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 3:48 PM
 */

namespace Cytracom\Squasher;

use Cytracom\Squasher\Database\Table;

class TableBuilder
{

    /**
     * The table to build a migration for.
     *
     * @var Table
     */
    protected $table;

    /**
     * The string in the table.
     *
     * @var string
     */
    public $content;

    /**
     * Create a new builder for the given table.
     *
     * @param Table $table
     */
    protected function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * Build a migration file using a given table.
     *
     * @param Table $table
     * @return mixed
     */
    public static function build(Table $table)
    {
        $squasher = new self($table);
        $squasher->content = $squasher->init();
        $squasher->fillInTableData();
        $squasher->content .= $squasher->close();
        return str_replace("\n", PHP_EOL, $squasher->content);
    }

    /**
     * Fill out the table data.
     */
    public function fillInTableData()
    {
        $this->createColumns();
        $this->createPrimaryKey();
        $this->createRelationships();
        $this->content .= "            \$table->engine = '" . $this->table->getEngine() . "';\n";

    }

    /**
     * Create all of the column for the given table, and put nameless columns last (timestamps, softDeletes).
     */
    protected function createColumns()
    {
        $doLater = [];
        foreach ($this->table->getColumns() as $column) {
            //if it is a generic column such as timestamps, soft deletes, etc; put at the end of the column list.
            if ($column->name === '' || $column->name === null || $column->name === $column->type) {
                array_push($doLater, $column);
            }
            else {
                $this->content .= $this->createColumn($column);
            }
        }

        foreach ($doLater as $column) {
            $column->name = null;
            $this->content .= $this->createColumn($column);
        }
    }

    /**
     * Set the primary key if this tables PK is specified.
     */
    protected function createPrimaryKey()
    {
        if ($this->table->getPrimaryKey() !== null) {
            $this->content .= "            \$table->primary('" . $this->table->getPrimaryKey() . "');\n";
        }
    }

    /**
     * Create all of the tables relationships.
     */
    protected function createRelationships()
    {
        foreach ($this->table->getRelationships() as $relationship) {
            $this->content .= "            \$table->foreign('$relationship->tableColumn')->" .
                "references('$relationship->relationshipColumn')->on('$relationship->relationshipTable');\n";
        }
    }

    /**
     * Create the given column and apply it's attributes.
     *
     * @param $column
     * @return string
     */
    public function createColumn($column)
    {
        $line = "            \$table->$column->type(";

        $line .= $this->appendColumnName($column);
        $line .= $this->appendColumnSize($column) . ')';
        $line .= $this->appendColumnSign($column);
        $line .= $this->appendColumnUnique($column);

        $line .= ";\n";
        return $line;
    }

    /**
     * Add the column size if specified.
     *
     * @param $column
     * @return string
     */
    protected function appendColumnSize($column)
    {
        if ($column->size !== null) {
            return ", " . $column->size;
        }
        return '';
    }

    /**
     * Add the column name if specified.
     *
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
     * Add the column sign if specified.
     *
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
     * Mark if the column is unique or not.
     *
     * @param $column
     * @return string
     */
    protected function appendColumnUnique($column)
    {
        if ($column->unique) {
            return "->unique()";
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

    /**
     * Closes out the migration file.
     *
     * @return string
     */
    public function close()
    {
        return "       });\n    }\n}";
    }
}