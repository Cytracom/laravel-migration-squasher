<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 10:02 AM
 */

namespace Cytracom\Squasher\Database;

class Table
{

    public $name;
    protected $columns;
    protected $engine;
    protected $relationships;
    protected $primary = null;

    public function __construct($tableName, $engine = "InnoDB")
    {
        $this->name = $tableName;
        $this->columns = [];
        $this->relationships = [];
        $this->engine = "InnoDB";
    }

    public function addColumn(Column $col)
    {
        $this->columns[$col->name] = $col;
    }

    public function alterColumn($columnName, $key, $value)
    {
        $this->columns[$columnName]->{$key} = $value;
    }

    public function dropColumn($columnName)
    {
        unset($this->columns[$columnName]);
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    public function getColumn($columnName)
    {
        return $this->columns[$columnName];
    }

    public function hasColumn($columnName)
    {
        return isset($this->columns[$columnName]) ? true : false;
    }

    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function addRelationship(Relationship $rel)
    {
        $this->relationships[$this->getRelationshipName($rel)] = $rel;
    }

    protected function getRelationshipName(Relationship $rel)
    {
        return $this->name . "_" . $rel->tableColumn . "_foreign";
    }

    public function dropRelationship($fkName)
    {
        unset($this->relationships[$fkName]);
    }

    public function getRelationships()
    {
        return $this->relationships;
    }

    public function setPrimaryKey($columnName)
    {
        $this->primary = $columnName;
    }

    public function getPrimaryKey()
    {
        return $this->primary;
    }
} 