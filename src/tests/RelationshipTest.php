<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 11/5/13
 * Time: 1:15 PM
 */

namespace Cytracom\Squasher\tests;


class RelationshipTest extends \PHPUnit_Framework_TestCase
{

    public function testInstantiateColumn()
    {
        require __DIR__ .'/../database/Relationship.php';

        $rel = new \Cytracom\Squasher\Database\Relationship('person_id', 'id', 'person');
        $this->assertEquals('person_id', $rel->tableColumn);
        $this->assertEquals('id', $rel->relationshipColumn);
        $this->assertEquals('person', $rel->relationshipTable);
    }
}







