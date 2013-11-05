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
    public $unique;
    public $nullable;

    public function __construct($type, $name = null, $unsigned = false, $size = null, $nullable = false, $unique = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->unsigned = $unsigned;
        $this->size = $size;
        $this->unique = $unique;
        $this->nullable = $nullable;
    }
}