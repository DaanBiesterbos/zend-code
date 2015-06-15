<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Scanner;

class ValueScanner
{
    /** @var array  */
    protected $arrayTokens = [];

    /**
     * @param array $tokenArray
     */
    public function __construct(array $tokenArray)
    {
        $this->arrayTokens = $tokenArray;
    }


    /**
     * @return mixed
     */
    public function scan()
    {
        if($this->isArray($this->toString())) {

            // Delegate to array value scanner
            $scanner = new ArrayValueScanner($this->arrayTokens);

            return $scanner->scan();

        }

        // Delegate to atomic value scanner
        $scanner = new AtomicValueScanner($this->arrayTokens);

        return $scanner->scan();
    }

    /**
     * @return string
     */
    public function toString()
    {
        $string = '';
        foreach($this->arrayTokens as $token) {
            $string .= trim((is_string($token)) ? $token : $token[1]);
        }

        return $string;
    }


    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isInteger($value)
    {
        return (bool) preg_match('/^-?(0|[1-9][0-9]*)$/', $value);
    }

    /**
     * Check if this value is a string. Scanned string values will be enclosed with quotes.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isString($value)
    {
        // Raw strings are quoted. We will trim the value but this function should still return true for values enclosed with quotes.
        if($this->isQuoted($value)) {
            return true;
        }

        // To make it easy for ourselves threat any value that does not match another datatype as string.
        // The other types are much easier to recognize on a reliable way, using this is good for stability.
        // I agree that there are prettier solutions. But for now this will do.
        return !$this->isBool($value) and !$this->isNull($value) and !$this->isArray($value) and !$this->isNumeric($value);
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
     * Check if this value is a string. Scanned string values will be enclosed with quotes.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isQuoted($value)
    {
        return (bool) preg_match('/^["\'].*["\']$/', $value);
    }


    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isBool($value)
    {
        return in_array(strtolower($value), ['true', 'false']);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isNull($value)
    {
        return strtolower($value) === 'null';
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isArray($value)
    {
        return (bool) preg_match('/^(\[|array\().*(\]|\))$/', $value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function parseAtomic($value)
    {
        // If the parameter type is a string than it will be enclosed with quotes
        if($this->isString($value)) {
            // Is (already) a string
            if(defined($value)) {
                // Is constant!
                return constant($value);
            }
            return $value;
        }

        // Parse integer
        if($this->isInteger($value)) {
            return (int) $value;
        }

        // Parse other sorts of numeric values (floats, scientific notation etc)
        if($this->isNumeric($value)) {
            return  (float) $value;
        }

        // Parse bool
        if($this->isBool($value)) {
            return ($value == 'true') ? true : false;
        }

        // Parse null
        if($this->isNull($value)) {
            return null;
        }

        // Return unsupported type as string.
        return $value;
    }
}
