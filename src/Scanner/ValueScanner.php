<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Scanner;

use Zend\Code\Generic\Analyzer\TypeRecognizer;
use Zend\Code\Generic\Analyzer\TypeRecognizerInterface;
use Zend\Code\NameInformation;

class ValueScanner
{
    /** @var array  */
    protected $arrayTokens = [];

    /** @var TypeRecognizerInterface */
    protected $recognizer = null;

    /** @var null|NameInformation  */
    protected $nameInformation = null;

    /**
     * @param array $arrayTokens
     * @param NameInformation $nameInformation
     */
    public function __construct(array $arrayTokens, NameInformation $nameInformation = null)
    {
        $this->arrayTokens = $arrayTokens;
        $this->nameInformation = $nameInformation;
    }


    /**
     * Scan the tokens to get the extracted value.
     *
     * @return mixed
     */
    public function scan()
    {
        if ($this->getRecognizer()->isArray($this->toString())) {

            // Delegate to array value scanner
            $scanner = ($this->nameInformation) ? new ArrayValueScanner($this->arrayTokens, $this->nameInformation) : new ArrayValueScanner($this->arrayTokens);

            return $scanner->scan();
        }

        // Delegate to atomic value scanner
        $scanner = new AtomicValueScanner($this->arrayTokens);

        return $scanner->scan();
    }


    /**
     * @param string $value
     *
     * @return string
     */
    public function trimQuotes($value)
    {
        // Trim quotes
        return trim($value, '"\'');
    }


    /**
     * @return string
     */
    public function toString()
    {
        $string = '';
        foreach ($this->arrayTokens as $token) {
            $string .= trim((is_string($token)) ? $token : $token[1]);
        }

        return $string;
    }


    /**
     * @param $value
     *
     * @return mixed
     */
    protected function castType($value)
    {
        // Get recognizer
        $recognizer = $this->getRecognizer();

        // If the parameter type is a string than it will be enclosed with quotes
        if ($recognizer->isString($value)) {
            // Is (already) a string
            if ($recognizer->isConstant($value)) {
                // Is constant!
                return constant($value);
            }
            return $value;
        }

        // Parse integer
        if ($recognizer->isInteger($value)) {
            return (int) $value;
        }

        // Parse other sorts of numeric values (floats, scientific notation etc)
        if ($recognizer->isNumeric($value)) {
            return  (float) $value;
        }

        // Parse bool
        if ($recognizer->isBool($value)) {
            return ($value == 'true') ? true : false;
        }

        // Parse null
        if ($recognizer->isNull($value)) {
            return null;
        }

        // Return unsupported type as string.
        return $value;
    }

    /**
     * @return TypeRecognizerInterface
     */
    public function getRecognizer()
    {
        if (!$this->recognizer instanceof TypeRecognizerInterface) {
            $this->recognizer = new TypeRecognizer();
        }

        return $this->recognizer;
    }

    /**
     * @param TypeRecognizerInterface $recognizer
     * @return $this
     */
    public function setRecognizer($recognizer)
    {
        $this->recognizer = $recognizer;
        return $this;
    }
}
