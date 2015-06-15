<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Scanner;

class AtomicValueScanner extends ValueScanner
{
    /** @var array  */
    protected $arrayTokens = [];

    /**
     * @param array $arrayTokens
     */
    public function __construct(array $arrayTokens)
    {
        $this->arrayTokens = $arrayTokens;
    }

    /**
     * @return mixed
     */
    public function scan()
    {
        $tokens = $this->filter();
        $value = '';
        while($token = current($tokens)) {
            $tokenValue = trim($token[1]);
            if($token[0] & T_DNUMBER|T_NUM_STRING|T_LNUMBER|T_STRING|T_NUM_STRING|T_CONSTANT_ENCAPSED_STRING) {
                if($tokenValue !== '') {
                    $value .= $token[1];
                }
            }
            next($tokens);
        }

        return $this->castType($value);
    }

    /**
     * @return array|false
     */
    protected function filter()
    {
        if(is_array($this->arrayTokens)) {
            // Filter tokens
            return array_values(array_filter($this->arrayTokens, [$this, 'accept']));
        }

        return false;
    }


    /**
     * Method used to accept or deny tokens so that we only have to deal with the allowed tokens
     *
     * @param array|string $value    A token or syntax character
     * @return bool
     */
    protected function accept($value)
    {
        if(is_array($value) and $value[0] & T_DNUMBER|T_NUM_STRING|T_LNUMBER|T_STRING|T_NUM_STRING|T_CONSTANT_ENCAPSED_STRING) {
            // Token did not match requirement. The token is not listed in the collection above.
            return true;
        }
        // Token is not accepted.
        return false;
    }

}
