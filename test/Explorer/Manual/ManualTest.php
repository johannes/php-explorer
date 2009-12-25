<?php
namespace Test\Explorer\Manual;

ini_set('include_path', __DIR__.'/../../../src'.PATH_SEPARATOR.ini_get('include_path'));
require_once 'PHPUnit/Framework.php';

require_once 'Explorer/Manual/Manual.php';

/**
 * Test class for Explorer\Manual\Manual.
 * Generated by PHPUnit on 2009-08-09 at 03:06:15.
 */
class ManualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var    Explorer\Manual\Manual
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->object = new \Explorer\Manual\Manual(__DIR__.'/../../data/', 'en');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    public function testGetLoadedManuals()
    {
        $data = $this->object->getLoadedManuals();
	
	$this->assertEquals(2, sizeof($data));
	$this->assertArrayHasKey(0, $data);
	$this->assertArrayHasKey(1, $data);

	for ($i = 0; $i < 2; $i++) {
	    $this->assertEquals(3, sizeof($data[$i]));
      	    $this->assertArrayHasKey('title', $data[$i]);
  	    $this->assertArrayHasKey('filename', $data[$i]);
	    $this->assertArrayHasKey('archive', $data[$i]);
	    
	    $this->assertType('PharData', $data[$i]['archive']);
	}
    }

    /**
     * @todo Implement testGet().
     */
    public function testGet()
    {
	if (extension_loaded('bcmath')) {
	    $result = $this->object->get(new \ReflectionExtension('bcmath'));
	    $this->assertType('PharFileInfo', $result);
	}
	if (extension_loaded('bz2')) {
	    $result = $this->object->get(new \ReflectionExtension('bcmath'));
	    $this->assertType('PharFileInfo', $result);
	}
	$result = $this->object->get(new \ReflectionExtension('spl'));
	$this->assertType('PharFileInfo', $result);
	// Remove the following lines when you implement this test.
    }

    /**
     * @todo Implement testSearchFulltext().
     */
    public function testSearchFulltext()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
?>
