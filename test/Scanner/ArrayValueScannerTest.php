<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Code\Scanner\ArrayValueScanner;

class ArrayValueScannerTest extends TestCase
{
    const CONSTANT_FOR_TESTING = 123;

    /**
     * Test if the scanner can handle simple arrays
     */
    public function testShouldParseSimpleArrays()
    {
        $scanner = new ArrayValueScanner();
        $this->assertEquals([1,2,3,4,5], $scanner->scan('[1,2,3,4,5]'));
        $this->assertEquals([1,2,3,4,5], $scanner->scan('array(1,2,3,4,5)'));
    }

    /**
     * Test if the scanner can handle associative arrays
     */
    public function testShouldParseAssociativeArrays()
    {
        $scanner = new ArrayValueScanner();
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $scanner->scan("['foo' => 'bar', 'bar' => 'foo']"));
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $scanner->scan("array('foo' => 'bar', 'bar' => 'foo')"));
    }

    /**
     * Test if the scanner can handle multi dimensional arrays
     */
    public function testShouldHandleMultiDimensionalArrays()
    {
        $scanner = new ArrayValueScanner();
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo', 'foobar' => [1,2,3]], $scanner->scan("['foo' => 'bar', 'bar' => 'foo', 'foobar' => [1,2,3]]"));
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo', 'foobar' => [1,2,3]], $scanner->scan("array('foo' => 'bar', 'bar' => 'foo', 'foobar' => array(1,2,3))"));
    }

    /**
     * Test if the scanner can handle constants
     */
    public function testShouldHandleConstants()
    {
        $scanner = new ArrayValueScanner();
        $this->assertEquals(['bar' => E_ERROR], $scanner->scan("['bar' => E_ERROR]"));

        // This does not work, it seems like we must account for this in the generator itself. The same goes for undefined constants.
        //$this->assertEquals(['foo' => 'bar', 'bar' => self::CONSTANT_FOR_TESTING], $scanner->scan("array('foo' => 'bar', 'bar' => self::CONSTANT_FOR_TESTING)"));
    }

}
