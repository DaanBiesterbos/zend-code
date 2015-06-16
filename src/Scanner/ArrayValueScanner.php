<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Scanner;

use Zend\Code\Exception\InvalidArgumentException;
use Zend\Code\Exception\RuntimeException;
use Zend\Code\NameInformation;

/**
 * Array value scanner
 *
 * A class used convert an textual array from php source code to an array without the use of eval().
 * The intended use for the time being is the ability to properly parse default values for parameters.
 * For now this scanner will only contain logic we'll need to support in case of parameters.  The use of conditionals, executing functions or
 * instantiating -/ using objects is not supported.
 *
 * Highlights:
 * - Multidimensional arrays are supported
 * - Reuses the associative keys
 * - Short array syntax is supported
 * - The use of good old constants and class constants are both supported.
 * - Will fix data types on atomic values so that the generator is able to parse new source code from the same data.
 *
 *
 * Not supported
 * - Bitsets 0x2,1x2 etc
 * - Nested code such as conditions, execution of arbitrary code, object instantiation etc.
 * - Using objects and variables (We currently don't need this functionality)
 */
class ArrayValueScanner extends ValueScanner
{
    /** @var array  */
    protected $arrayKeys = [];

    /** @var array  */
    protected $arrayTokens = [];

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
     * @param string $string e.g. array('foo' => 123, 'bar' => [0 => 123, 1 => 12345])
     * @param NameInformation $nameInformation
     *
     * @return self
     */
    public static function createFromString($string, NameInformation $nameInformation = null)
    {
        // Remove whitespace and semi colons
        $sanitized = trim($string, " \t\n\r\0\x0B;");

        // Get tokens
        $tokens = token_get_all("<?php {$sanitized}");

        // Create scanner
        $scanner = new self($tokens, $nameInformation);
        if ($scanner->isArray($sanitized)) {
            return  $scanner;
        }

        throw new InvalidArgumentException("Invalid array format.");
    }

    /**
     * @return array
     */
    public function scan()
    {
        if ($tokens = $this->filter()) {
            $this->initialize($tokens);
            return $this->parse($tokens);
        }

        throw new RuntimeException("No tokens to process.");
    }

    /**
     * @param array $tokens
     */
    protected function initialize(array $tokens)
    {
        $this->arrayKeys = [];
        while ($current = current($tokens)) {
            $next = next($tokens);
            if ($next[0] === T_DOUBLE_ARROW) {
                $this->arrayKeys[] = $current[1];
            }
        }
    }

    /**
     * @param array $tokens
     * @return array
     */
    protected function parse(array &$tokens)
    {
        $array = [];
        $token = current($tokens);
        if (in_array($token[0], [T_ARRAY, T_BRACKET_OPEN])) {

            // Is array!
            $assoc = false;
            $index = 0;

            // Iterate until the end of this array
            while ($token = $this->until($tokens, ($token[0] === T_ARRAY) ? T_ARRAY_CLOSE : T_BRACKET_CLOSE)) {

                // Init next value up once up front
                $next = next($tokens);
                prev($tokens);

                // Skip arrow ( => )
                if (in_array($token[0], [T_DOUBLE_ARROW])) {
                    continue;
                }

                // Reset associative array key
                if ($token[0] === T_COMMA_SEPARATOR) {
                    $assoc = false;
                    continue;
                }

                // Handle array key
                if ($next[0] === T_DOUBLE_ARROW) {
                    // Is assoc key, trim quotes
                    $assoc = trim($token[1], '"\'');
                    if ($this->isInteger($assoc)) {
                        $index = $assoc = (int) $assoc;
                    }
                    continue;
                }

                // Parse array contents recursively
                if (in_array($token[0], [T_ARRAY, T_BRACKET_OPEN])) {
                    $array[($assoc !== false) ? $assoc : $this->createKey($index)] = $this->parse($tokens);
                    continue;
                }

                // Parse atomic string
                if (in_array($token[0], [T_STRING, T_NUM_STRING, T_CONSTANT_ENCAPSED_STRING])) {

                    // Parse string
                    $text = $this->trimQuotes($token[1]);
                    if ($next[0] === T_DOUBLE_COLON) {

                        // Resolve class name
                        if ($this->nameInformation and !in_array($text, ['self', 'parent', 'static'])) {
                            $text = $this->nameInformation->resolveName($text);
                        }

                        // Move pointer to double colon
                        next($tokens);

                        // Move pointer to constant and process token
                        $constantToken = next($tokens);

                        $text .= '::' . $this->trimQuotes($constantToken[1]);
                    }

                    // Parse string
                    $array[($assoc !== false) ? $assoc : $this->createKey($index)] = $this->castType($text);
                } elseif (in_array($token[0], [T_LNUMBER, T_DNUMBER])) {

                    // Parse atomic number

                    // Check if number is negative
                    $prev = prev($tokens);
                    $value = $token[1];
                    if ($prev[0] === T_MINUS) {
                        $value = "-{$value}";
                    }
                    next($tokens);

                    $array[($assoc !== false) ? $assoc : $this->createKey($index)] = $this->castType($value);
                }

                // Increment index unless a associative key is used. In this case we want too reuse the current value.
                if (!is_string($assoc)) {
                    $index++;
                }
            }

            return $array;
        }

        throw new InvalidArgumentException("Invalid token. The first token must either be 'T_ARRAY' or '['.");
    }

    /**
     * @param array $tokens
     * @param int|string $untilCharacter
     *
     * @return array|false
     */
    protected function until(array &$tokens, $untilCharacter)
    {
        $next = next($tokens);
        if ($next === false or $next[0] === $untilCharacter) {
            return false;
        }

        return $next;
    }

    /**
     * @param $index
     *
     * @return int
     */
    protected function createKey(&$index)
    {
        do {
            if (!in_array($index, $this->arrayKeys, true)) {
                return $index;
            }
        } while (++$index);
    }

    /**
     * @return array|false
     */
    protected function filter()
    {
        if (is_array($this->arrayTokens)) {

            // Filter tokens
            $tokens = array_values(array_filter($this->arrayTokens, [$this, 'accept']));

            // Normalize token format, make syntax characters look like tokens for consistent parsing
            return $this->normalize($tokens);
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
        if (is_string($value)) {
            // Allowed syntax characters: comma's and brackets.
            return in_array($value, [',', '[', ']', ')', '-']);
        }
        if (!in_array($value[0], [T_ARRAY, T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_ARROW, T_STRING, T_NUM_STRING, T_LNUMBER, T_DNUMBER, T_DOUBLE_COLON])) {
            // Token did not match requirement. The token is not listed in the collection above.
            return false;
        }
        // Token is accepted.
        return true;
    }

    /**
     * Normalize tokens so that each allowed syntax character looks like a token for consistent parsing.
     *
     * @param array $tokens
     *
     * @return array
     */
    protected function normalize(array $tokens)
    {
        // Define some constants for consistency. These characters are not "real" tokens.
        defined('T_MINUS')           ?: define('T_MINUS',           '-');
        defined('T_BRACKET_OPEN')    ?: define('T_BRACKET_OPEN',    '[');
        defined('T_BRACKET_CLOSE')   ?: define('T_BRACKET_CLOSE',   ']');
        defined('T_COMMA_SEPARATOR') ?: define('T_COMMA_SEPARATOR', ',');
        defined('T_ARRAY_CLOSE')     ?: define('T_ARRAY_CLOSE',     ')');

        // Normalize the token array
        return array_map(function ($token) {

            // If the token is a syntax character ($token[0] will be string) than use the token (= $token[0]) as value (= $token[1]) as well.
            return [
                0 => $token[0],
                1 => (is_string($token[0])) ? $token[0] : $token[1]
            ];

        }, $tokens);
    }
}
