<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 2:31 PM
 */

namespace Cytracom\Squasher\Database;

/**
 * A relationship (foreign key) class for use with the Cytracom Squasher pseudoschema.
 *
 * @package Cytracom\Squasher\Database
 */
class Relationship {

    /**
     * The column name on the table that the foreign key belongs to.
     *
     * @var string
     */
    public $tableColumn;

    /**
     * The name of the column on the other table.
     *
     * @var string
     */
    public $relationshipColumn;

    /**
     * The name of the relating table.
     *
     * @var string
     */
    public $relationshipTable;

    /**
     * @param string $tblCol The column name on the table that the foreign key belongs to.
     * @param string $relCol The name of the column on the other table.
     * @param string $relTbl The name of the relating table.
     */
    public function __construct($tblCol, $relCol, $relTbl){
        $this->tableColumn = $tblCol;
        $this->relationshipColumn = $relCol;
        $this->relationshipTable = $relTbl;
    }
}