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
use Zend\Code\Scanner\AtomicValueScanner;
use Zend\Code\Scanner\ValueScanner;

class AtomicValueScannerTest extends TestCase
{
    /**
     * Test if the value scanner detects the correct data types
     */
    public function testShouldDetectDataType()
    {
        $this->assertInternalType('int', (new AtomicValueScanner(token_get_all('123')))->scan());
        $this->assertInternalType('float', (new AtomicValueScanner(token_get_all('10.5')))->scan());
        $this->assertInternalType('string', (new AtomicValueScanner(token_get_all('Hoi')))->scan());
        $this->assertInternalType('bool', (new AtomicValueScanner(token_get_all('false')))->scan());
        $this->assertInternalType('null', (new AtomicValueScanner(token_get_all('NULL')))->scan());

    }

}
