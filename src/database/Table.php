<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 10:02 AM
 */

namespace Cytracom\Squasher\Database;

/**
 * A table class for the Cytracom Squasher database pseudoschema
 *
 * @package Cytracom\Squasher\Database
 */
class Table
{
    /**
     * The name of the table.
     *
     * @var string
     */
    public $name;

    /**
     * An array of column objects.
     *
     * @var array
     */
    protected $columns;

    /**
     * The engine to be used with this table.
     *
     * @var string
     */
    protected $engine;

    /**
     * An array of relationship objects.
     *
     * @var array
     */
    protected $relationships;

    /**
     * The name of the primary key column.
     *
     * @var null|string
     */
    protected $primary = null;

    /**
     * Instantiate a new table with a given table name and optionally a database engine.
     *
     * @param $tableName
     * @param string $engine
     */
    public function __construct($tableName, $engine = "InnoDB")
    {
        $this->name = $tableName;
        $this->columns = [];
        $this->relationships = [];
        $this->engine = $engine;
    }

    /**
     * Inserts the given column into the table.
     *
     * @param Column $col
     */
    public function addColumn(Column $col)
    {
        $this->columns[$col->name] = $col;
    }

    /**
     * Changes the given columns attribute (key) to the given value.
     *
     * @param $columnName
     * @param $key
     * @param $value
     */
    public function alterColumn($columnName, $key, $value)
    {
        $this->columns[$columnName]->{$key} = $value;
    }

    /**
     * Removes the column from the table.
     *
     * @param $columnName
     */
    public function dropColumn($columnName)
    {
        unset($this->columns[$columnName]);
    }

    /**
     * Returns an array of all of the columns in the table.  Columns are indexed by their column name.
     * TODO: Make a better system for indexing columns (affects any operations on columns)
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Returns the column with the given column name.
     *
     * @param $columnName
     * @return mixed
     */
    public function getColumn($columnName)
    {
        return $this->columns[$columnName];
    }

    /**
     * Returns true or false if the column exists or not.
     *
     * @param $columnName
     * @return bool
     */
    public function hasColumn($columnName)
    {
        return isset($this->columns[$columnName]) ? true : false;
    }

    /**
     * Set this table's database engine.
     *
     * @param string $engine
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * Get this table's database engine.
     *
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Add the relationship to the table.
     *
     * @param Relationship $rel
     */
    public function addRelationship(Relationship $rel)
    {
        $this->relationships[$this->getRelationshipName($rel)] = $rel;
    }

    /**
     * Get the relationship from the table.
     * Relationships follow laravel's FK naming convention ('table_name'_'column_name'_foreign).
     *
     * @param Relationship $rel
     * @return string
     */
    protected function getRelationshipName(Relationship $rel)
    {
        return $this->name . "_" . $rel->tableColumn . "_foreign";
    }

    /**
     * Drop a foreign key using the foreign key name.
     *
     * @param $fkName
     */
    public function dropRelationship($fkName)
    {
        unset($this->relationships[$fkName]);
    }

    /**
     * Get an array of all of the relationship objects for this table.
     *
     * @return array
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * Retrieve the relationship object with the given foreign key.
     *
     * @param $relName
     * @return Relationship
     */
    public function getRelationship($relName)
    {
        return $this->relationships[$relName];
    }

    /**
     * Set the primary key for this table.
     *
     * @param $columnName
     */
    public function setPrimaryKey($columnName)
    {
        $this->primary = $columnName;
    }

    /**
     * Get the primary key for this table.
     *
     * @return null|string
     */
    public function getPrimaryKey()
    {
        return $this->primary;
    }
} 