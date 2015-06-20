<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generic\Analyzer;

class TypeRecognizer implements TypeRecognizerInterface
{

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isInteger($value)
    {
        return is_int($value) or preg_match('/^-?(0|[1-9][0-9]*)$/', $value);
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
        if ($this->isQuoted($value)) {
            return true;
        }

        // To make it easy for ourselves threat any value that does not match another datatype as string.
        // The other types are much easier to recognize on a reliable way, using this is good for stability.
        // I agree that there are prettier solutions. But for now this will do.
        return !$this->isBool($value) and !$this->isNull($value) and !$this->isArray($value) and !$this->isNumeric($value);
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
        return is_bool($value) or in_array(strtolower($value), ['true', 'false']);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isNull($value)
    {
        return $value === null or strtolower($value) === 'null';
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isArray($value)
    {
        return is_array($value) or preg_match('/^(\[|array\().*(\]|\))$/', $value);
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
     * @return bool
     */
    public function isConstant($value)
    {
        return defined($value);
    }
}
