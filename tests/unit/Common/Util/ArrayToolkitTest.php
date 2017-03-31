<?php

namespace Honeybee\Tests\Common\Util;

use Honeybee\Tests\TestCase;
use Honeybee\Common\Util\ArrayToolkit;

class ArrayToolkitTest extends TestCase
{
    public function testIsAssoc()
    {
        $this->assertTrue(ArrayToolkit::isAssoc(['a' => 'b']));
        $this->assertTrue(ArrayToolkit::isAssoc([1 => 2]));
        $this->assertFalse(ArrayToolkit::isAssoc([]));
        $this->assertFalse(ArrayToolkit::isAssoc(['b']));
        $this->assertFalse(ArrayToolkit::isAssoc([3]));
        $this->assertFalse(ArrayToolkit::isAssoc([0]));
    }

    public function testFlatten()
    {
        $this->assertSame(['foo.bar' => 42], ArrayToolkit::flatten(['foo' => ['bar' => 42]]));
    }

    public function testMoveToTop()
    {
        $a = ['b' => 2, 'c' => 3, 'a' => 1];
        ArrayToolkit::moveToTop($a, 'a');
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $a);
    }

    public function testMoveToBottom()
    {
        $a = ['b' => 2, 'c' => 3, 'a' => 1];
        ArrayToolkit::moveToBottom($a, 'b');
        ArrayToolkit::moveToBottom($a, 'c');
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $a);
    }

    public function testGetUrlQueryInRequestFormat()
    {
        $this->assertEquals(
            [ "limit" => 2, "foo[0]" => 1, "foo[1]" => 2, "foo[2]" => 3 ],
            ArrayToolkit::getUrlQueryInRequestFormat('http://some.tld?limit=2&foo[0]=1&foo[1]=2&foo[2]=3')
        );
    }

    public function testAnyInArray()
    {
        $this->assertTrue(ArrayToolkit::anyInArray(['a','b'], ['c','b','d']));
        $this->assertTrue(ArrayToolkit::anyInArray(['a','b'], ['a','b']));
        $this->assertFalse(ArrayToolkit::anyInArray(['a','b'], ['c','d']));
    }

    public function testAllInArray()
    {
        $this->assertTrue(ArrayToolkit::allInArray(['c','a','b'], ['a','b','c']));
        $this->assertTrue(ArrayToolkit::allInArray(['c','a','b'], ['a','b','c','d']));
        $this->assertFalse(ArrayToolkit::allInArray(['c','a','b'], ['a','d','c']));
    }
}
