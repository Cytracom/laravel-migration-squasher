<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 11:05 AM
 */

namespace Cytracom\Squasher\Database;

class Column
{
    public $name;
    public $type;
    public $unsigned;
    public $size;

    public function __construct($type, $name = null, $unsigned = false, $size = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->unsigned = $unsigned;
        $this->size = $size;
    }



}