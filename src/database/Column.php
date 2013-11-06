<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 10/31/13
 * Time: 11:05 AM
 */

namespace Cytracom\Squasher\Database;

/**
 * A column class for use with the Cytracom Squasher pseudoschema.
 *
 * @package Cytracom\Squasher\Database
 */
class Column
{
    /**
     * The name of this column.
     *
     * @var null|string
     */
    public $name;

    /**
     * The datatype (according to laravel's schema builder) of this column.
     *
     * @var string
     */
    public $type;

    /**
     * Are the values in this column unsigned?
     *
     * @var bool
     */
    public $unsigned;

    /**
     * The size/length of this column.
     *
     * @var null|string|int
     */
    public $parameters;

    /**
     * Are the rows in this column unique?
     *
     * @var bool
     */
    public $unique;

    /**
     * Can the values in this column be null?
     *
     * @var bool
     */
    public $nullable;

    /**
     * The default value for this column.
     */
    public $default;

    /**
     * Creates a new column with the given parameters.
     *
     * @param $type
     * @param null $name
     * @param bool $unsigned
     * @param null $size
     * @param bool $nullable
     * @param bool $unique
     * @param null|mixed $default
     */
    public function __construct($type, $name = null, $unsigned = false, $size = null, $nullable = false, $unique = false, $default = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->unsigned = $unsigned;
        $this->parameters = $size;
        $this->unique = $unique;
        $this->nullable = $nullable;
        $this->default = $default;
    }
}