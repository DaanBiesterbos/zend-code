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
use Zend\Code\Scanner\ValueScanner;

class AtomicValueScannerTest extends TestCase
{
    /**
     * Test if the value scanner detects the correct data types
     */
    public function testShouldDetectDataType()
    {
        $scanner = new ValueScanner([]);
        $this->assertTrue($scanner->isBool('true'));
        $this->assertTrue($scanner->isBool('false'));
        $this->assertFalse($scanner->isBool('fapse'));
        $this->assertTrue($scanner->isNumeric('10.5'));
        $this->assertTrue($scanner->isNumeric(33.333));
        $this->assertFalse($scanner->isNumeric('33.f3'));
        $this->assertTrue($scanner->isInteger(100));
        $this->assertTrue($scanner->isInteger('-200'));
        $this->assertTrue($scanner->isInteger('0'));
        $this->assertTrue($scanner->isInteger('-0'));
        $this->assertFalse($scanner->isInteger('33.0'));

        $this->assertTrue($scanner->isArray('[]'));
        $this->assertTrue($scanner->isArray('array()'));
        $this->assertTrue($scanner->isArray('array("bar", "foo")'));
        $this->assertTrue($scanner->isArray('["foo", "bar", ["sub_foo"]]'));

        $this->assertTrue($scanner->isNull('null'));

        $this->assertTrue($scanner->isString('"This is a string"'));
        $this->assertTrue($scanner->isString("'this is another string'"));
        $this->assertTrue($scanner->isString("this is another string"));

    }

    /**
     * Test if the value scanner can handle quotes properly
     */
    public function testShouldHandleQuotes()
    {
        $scanner = new ValueScanner([]);
        $this->assertTrue($scanner->isQuoted('"true"'));
        $this->assertEquals('true', $scanner->trimQuotes('"true"'));
    }

}
