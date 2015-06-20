<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generic\Analyzer;

interface TypeRecognizerInterface
{

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isInteger($value);

    /**
     * Check if this value is a string. Scanned string values will be enclosed with quotes.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isString($value);

    /**
     * Check if this value is a string. Scanned string values will be enclosed with quotes.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isQuoted($value);


    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isBool($value);

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isNull($value);

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isArray($value);

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isNumeric($value);

    /**
     * @param $value
     * @return bool
     */
    public function isConstant($value);
}
