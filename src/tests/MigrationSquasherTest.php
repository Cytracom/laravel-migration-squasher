<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbarber
 * Date: 11/5/13
 * Time: 3:24 PM
 */

namespace Cytracom\Squasher\tests;


use Cytracom\Squasher\MigrationSquasher;

class MigrationSquasherTest extends \PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        require_once __DIR__ . '/../MigrationSquasher.php';
        require_once __DIR__ . '/../../bootstrap/autoload.php';
        parent::__construct();
    }

    public function testUnorthodoxTestThatNeedsToBeRevised()
    {
        date_default_timezone_set("UTC");
        $squash = new MigrationSquasher('src/tests/data',  'src/tests/data/output');
        $squash->squash();

        $date = date('Y_m_d');
        $file = preg_replace('/\n| /','',file_get_contents(__DIR__ . '/data/output/'.$date.'_000000_squashed_test_table.php'));
        $exp = preg_replace('/\n| /','',file_get_contents(__DIR__ . '/data/output/Expected.php'));
        $this->assertEquals($file, $exp);
    }

}