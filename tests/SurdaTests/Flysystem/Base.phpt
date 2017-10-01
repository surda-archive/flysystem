<?php
/**
 * Test: Surda\Flysystem\Base.
 *
 * @testCase SurdaTests\Flysystem\BaseTest
 * @package  Surda\Flysystem
 */

namespace SurdaTests\Flysystem;


use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class BaseTest extends TestCase
{

    public function testFoo()
    {
        Assert::false(false);
    }

    public function testBar()
    {
        Assert::true(true);
    }

}

(new BaseTest())->run();

