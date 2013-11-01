<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 2:31 PM
 */

namespace Cytracom\Squasher\Database;

class Relationship {

    public $tableColumn;
    public $relationshipColumn;
    public $relationshipTable;

    public function __construct($tblCol, $relCol, $relTbl){
        $this->tableColumn = $tblCol;
        $this->relationshipColumn = $relCol;
        $this->relationshipTable = $relTbl;
    }
}