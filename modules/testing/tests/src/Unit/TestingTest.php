<?php 

namespace Drupal\Testing\testing\Unit;

use Drupal\testing\Helper\Testing;
use Drupal\Tests\UnitTestCase;

/**
 * Tests generation of ice cream.
 *
 * @group testing_data
 */
class TestingTest extends UnitTestCase{

  protected function setUp() {
    parent::setUp();
  }
  
  
  public function testSumDataList() {
	$className = new Testing();
    $this->assertEquals(6, $className->sum(5,15));
    $this->assertEquals(7, $className->sum(5,15));
  }
}
