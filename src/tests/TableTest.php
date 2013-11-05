<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 11/5/13
 * Time: 2:06 PM
 */

namespace Cytracom\Squasher\tests;


use Cytracom\Squasher\Database\Table;

class TableTest extends \PHPUnit_Framework_TestCase
{
    public function __construct(){
        require_once __DIR__ . '/../database/Table.php';
        parent::__construct();
    }

    public function testCreateTable()
    {

        $tbl = new Table("test_table", 'MyISAM');

        $this->assertEquals("test_table", $tbl->name);
        $this->assertEquals("MyISAM", $tbl->getEngine());
    }

    public function testAddColumn()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $tbl->addColumn($this->getMock('Cytracom\Squasher\Database\Column',[],['string','myColumn']));
        $cols = $tbl->getColumns();

        $this->assertEquals(1, count($cols));
    }

    public function testGetColumn()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $tbl->addColumn($this->getMock('Cytracom\Squasher\Database\Column',[],['string','myColumn']));
        $col = $tbl->getColumn("myColumn");

        $this->assertEquals('string', $col->type);
    }

    public function testGetColumnInvalidColumnName()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $error = false;
        try{
            $col = $tbl->getColumn("myBadColumn");
        }catch(\Exception $e){
            $error = true;
        }

        $this->assertTrue($error);
    }

    public function testDropColumn()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $tbl->addColumn($this->getMock('Cytracom\Squasher\Database\Column',[],['string','myColumn']));

        $cols = $tbl->getColumns();
        $this->assertEquals(1, count($cols));

        $tbl->dropColumn('myColumn');

        $cols = $tbl->getColumns();
        $this->assertEquals(0, count($cols));
    }

    public function testDropColumnInvalidColumnName()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $tbl->addColumn($this->getMock('Cytracom\Squasher\Database\Column',[],['string','myColumn']));

        $cols = $tbl->getColumns();
        $this->assertEquals(1, count($cols));

        $tbl->dropColumn('bad column name');

        $cols = $tbl->getColumns();
        $this->assertEquals(1, count($cols));
    }

    public function testAddRelationship()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $tbl->addRelationship($this->getMock('Cytracom\Squasher\Database\Relationship',[],['col_id','id','other_table']));

        $rels = $tbl->getRelationships();
        $this->assertEquals(1, count($rels));
    }

    public function testGetRelationship()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $tbl->addRelationship($this->getMock('Cytracom\Squasher\Database\Relationship',[],['col_id','id','other_table']));

        $rel = $tbl->getRelationship('test_table_col_id_foreign');
        $this->assertEquals('col_id', $rel->tableColumn);
        $this->assertEquals('id', $rel->relationshipColumn);
        $this->assertEquals('other_table', $rel->relationshipTable);
    }

    public function testGetRelationshipInvalidRelationshipName()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $error = false;
        try{
            $col = $tbl->getRelationship("myBadRelationship");
        }catch(\Exception $e){
            $error = true;
        }

        $this->assertTrue($error);
    }

    public function testDropRelationship()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $tbl->addRelationship($this->getMock('Cytracom\Squasher\Database\Relationship',[],['col_id','id','other_table']));
        $tbl->dropRelationship('test_table_col_id_foreign');
        $rels = $tbl->getRelationships();
        $this->assertEquals(0, count($rels));
    }

    public function testSetPrimaryKey()
    {
        $tbl = new Table("test_table", 'MyISAM');

        $tbl->setPrimaryKey("primary test");
        $this->assertEquals("primary test", $tbl->getPrimaryKey());
    }

}