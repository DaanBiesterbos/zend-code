<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generic\Analyzer;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Code\Generic\Analyzer\TypeRecognizer;

class TypeRecognizerTest extends TestCase
{
    /**
     * Test if the value scanner detects the correct data types
     */
    public function testShouldDetectDataType()
    {
        $recognizer = new TypeRecognizer();
        $this->assertTrue($recognizer->isBool('true'));
        $this->assertTrue($recognizer->isBool('false'));
        $this->assertFalse($recognizer->isBool('fapse'));
        $this->assertTrue($recognizer->isNumeric('10.5'));
        $this->assertTrue($recognizer->isNumeric(33.333));
        $this->assertFalse($recognizer->isNumeric('33.f3'));
        $this->assertTrue($recognizer->isInteger(100));
        $this->assertTrue($recognizer->isInteger('-200'));
        $this->assertTrue($recognizer->isInteger('0'));
        $this->assertTrue($recognizer->isInteger('-0'));
        $this->assertFalse($recognizer->isInteger('33.0'));
        $this->assertTrue($recognizer->isArray('[]'));
        $this->assertTrue($recognizer->isArray('array()'));
        $this->assertTrue($recognizer->isArray('array("bar", "foo")'));
        $this->assertTrue($recognizer->isArray('["foo", "bar", ["sub_foo"]]'));
        $this->assertTrue($recognizer->isNull('null'));
        $this->assertTrue($recognizer->isNull('NULL'));
        $this->assertTrue($recognizer->isString('"This is a string"'));
        $this->assertTrue($recognizer->isString("'this is another string'"));
        $this->assertTrue($recognizer->isString("this is another string"));
    }

    /**
     * Test if the value scanner can handle quotes properly
     */
    public function testShouldHandleQuotes()
    {
        $recognizer = new TypeRecognizer();
        $this->assertTrue($recognizer->isQuoted('"true"'));
    }

}
