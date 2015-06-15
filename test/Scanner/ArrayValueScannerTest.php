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
use Zend\Code\NameInformation;
use Zend\Code\Scanner\ArrayValueScanner;

class ArrayValueScannerTest extends TestCase
{
    const CONSTANT_FOR_TESTING = 123;

    /**
     * Test if the scanner can handle simple arrays
     */
    public function testShouldParseSimpleArrays()
    {
        $this->assertEquals([1,2,3,4,5], ArrayValueScanner::createFromString('[1,2,3,4,5]')->scan());
        $this->assertEquals([1,2,3,4,5], ArrayValueScanner::createFromString('array(1,2,3,4,5)')->scan());
    }

    /**
     * Test if the scanner can handle associative arrays
     */
    public function testShouldParseAssociativeArrays()
    {
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], ArrayValueScanner::createFromString("['foo' => 'bar', 'bar' => 'foo']")->scan());
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], ArrayValueScanner::createFromString("array('foo' => 'bar', 'bar' => 'foo')")->scan());
    }

    /**
     * Test if the scanner can handle multi dimensional arrays
     */
    public function testShouldHandleMultiDimensionalArrays()
    {
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo', 'foobar' => [1,2,3]], ArrayValueScanner::createFromString("['foo' => 'bar', 'bar' => 'foo', 'foobar' => [1,2,3]]")->scan());
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo', 'foobar' => [1,2,3]], ArrayValueScanner::createFromString("array('foo' => 'bar', 'bar' => 'foo', 'foobar' => array(1,2,3))")->scan());
    }

    /**
     * Test if the scanner can handle constants
     */
    public function testShouldHandleConstants()
    {
        $this->assertEquals(['bar' => E_ERROR], ArrayValueScanner::createFromString("['bar' => E_ERROR]")->scan());
        $this->assertEquals(['bar' => \PDO::PARAM_INT], ArrayValueScanner::createFromString("['bar' => PDO::PARAM_INT]")->scan());

        // This does not work, it seems like we must account for this in the generator itself. The same goes for undefined constants.
        //$this->assertEquals(['foo' => 'bar', 'bar' => self::CONSTANT_FOR_TESTING], $scanner->scan("array('foo' => 'bar', 'bar' => self::CONSTANT_FOR_TESTING)"));
    }

}
