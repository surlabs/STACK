<?php
declare(strict_types=1);

namespace classes\core\filters;

use classes\platform\StackPlatform;

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 * This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 * originally created by Chris Sangwin.
 *
 * The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "STACK Question" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/STACK
 *
 * If you need support, please contact the maintainer of this software at:
 * stack@surlabs.es
 *
 *********************************************************************/

class StackParser
{
    /**
     * Parse option separated by : to get option and argument
     * @param string $option
     * @return array
     */
    public static function parseOption(string $option): array
    {
        $arg = '';

        if (str_contains($option, ':')) {
            $ops = explode(':', $option);
            $option = $ops[0];
            $arg = trim($ops[1]);
        }

        return(array($option, $arg));
    }

    /**
     * Converts a PHP string object to a PHP string object containing the Maxima code that would generate a similar
     * string in Maxima.
     * @param $str string
     * @return string string that contains ""-quotes around the content.
     */
    public static function phpStringToMaximaString(string $str) :string {
        $converted = str_replace("\\", "\\\\", $str);
        $converted = str_replace("\"", "\\\"", $converted);
        return '"' . $converted . '"';
    }

    /**
     * Converts a PHP string object containing a Maxima string as presented by the grind command to a PHP string object.
     * @param $str string
     * @return string string that contains ""-quotes around the content.
     */
    public static function maximaStringToPhpString(string $str) :string {
        $converted = str_replace("\\\\", "\\", $str);
        $converted = str_replace("\\\"", '"', $converted);
        return substr($converted, 1, -1);
    }

    /**
     * Translate a string from Maxima to a more user-friendly string.
     * @param string $string
     * @return string|null
     */
    public static function maximaTranslateString(string $string): ?string {
        $fixed = $string;
        if (str_contains($string, '0 to a negative exponent')) {
            $fixed = StackPlatform::getTranslation('Maxima_DivisionZero', null);
        } else if (str_contains($string, 'args: argument must be a non-atomic expression;')) {
            $fixed = StackPlatform::getTranslation('Maxima_Args', null);
        }
        return $fixed;
    }

    /**
     * Returns the substring between the first occurrence of $left and the first occurrence of $right.
     * @param string $string
     * @param string $left
     * @param string $right
     * @param int $start
     * @return array
     */
    public static function substringBetween(string $string, string $left, string $right, int $start = 0): array
    {

        $start = strpos($string, $left, $start);
        if ($start === false) {
            return array('', -1, 0);
        }

        if ($left == $right) {
            // Left and right are the same.
            $end = strpos($string, $right, $start + 1); // Just go for the next one.
            if ($end === false) {
                return array('', $start, -1);
            }
            $end += 1;

        } else {
            $length = strlen($string);
            $nesting = 1;
            $end = $start + 1;

            while ($nesting > 0 && $end < $length) {
                if ($string[$end] == $left) {
                    $nesting += 1;
                } else if ($string[$end] == $right) {
                    $nesting -= 1;
                }
                $end++;
            }

            if ($nesting > 0) {
                return array('', -1, -1);
            }
        }

        return array(substr($string, $start, $end - $start), $start, $end - 1);
    }

    /**
     * Converts a CSV string into an array, removing empty entries.
     *
     * @param string $string
     * @param string $token
     * @return array
     * @access public
     */
    public static function csvToArray(string $string, string $token = ','): array {
        $toreturn = array();
        $exploded = explode($token, $string);
        // Remove any null entries.
        for ($i = 0; $i < count($exploded); $i++) {
            $trim = trim($exploded[$i]);
            if (!empty($trim)) {
                $toreturn[] = $exploded[$i];
            }
        }
        return $toreturn;
    }

    /**
     * Converts windows style paths to unix style with forward slashes
     *
     * @param string $string
     * @return string|null
     */
    public static function convertSlashPaths(string $string): ?string {
        $in = trim($string);
        $length = strlen($in);
        $lastchar = $in[($length - 1)];
        $trailingslash = false;
        if ($lastchar == '\\') {
            $trailingslash = true;
        }
        $patharray = self::csvToArray($string, "\\");
        if (!empty($patharray)) {
            $newpath = $patharray[0];
            for ($i = 1; $i < count($patharray); $i++) {
                $newpath .= "/".$patharray[$i];
            }
            if ($trailingslash) {
                return $newpath.'/';
            } else {
                return $newpath;
            }
        } else {
            return null;
        }
    }

    /**
     * Extracts double-quoted strings with \-escapes, extracts only the content
     * not the quotes.
     *
     * @param string $string
     * @return array
     */
    public static function allSubstringStrings(string $string): array
    {
        $strings = array();
        $i = 0;
        $lastslash = false;
        $instring = false;
        $stringentry = -1;
        while ($i < strlen($string)) {
            $c = $string[$i];
            $i++;
            if ($instring) {
                if ($c == '"' && !$lastslash) {
                    $instring = false;
                    // Last -1 to drop the quote.
                    $s = substr($string, $stringentry, ($i - $stringentry) - 1);
                    $strings[] = $s;
                } else if ($c == "\\") {
                    $lastslash = !$lastslash;
                } else if ($lastslash) {
                    $lastslash = false;
                }
            } else if ($c == '"') {
                $instring = true;
                $lastslash = false;
                $stringentry = $i;
            }
        }
        return $strings;
    }

    /**
     * Replaces all Maxima strings with zero length strings to eliminate string
     * contents for validation tasks.
     *
     * @param string $string
     * @return string
     */
    public static function eliminateStrings(string $string): string
    {
        $cleared = '';
        $i = 0;
        $lastslash = false;
        $instring = false;
        $laststringexit = 0;
        while ($i < strlen($string)) {
            $c = $string[$i];
            $i++;
            if ($instring) {
                if ($c == '"' && !$lastslash) {
                    $instring = false;
                    $laststringexit = $i - 1;
                } else if ($c == "\\") {
                    $lastslash = !$lastslash;
                } else if ($lastslash) {
                    $lastslash = false;
                }
            } else if ($c == '"') {
                $instring = true;
                $lastslash = false;
                $cleared .= substr($string, $laststringexit, $i - $laststringexit);
            }
        }
        $cleared .= substr($string, $laststringexit);
        return $cleared;
    }

    /**
     * Handles complex (comma-containing) list elements,
     * i.e. sets {}, functions() and nested lists[[]]
     * Strict checking on nesting.
     * Helper for list_to_array_workhorse()
     *
     * @param string $list
     * @return string|null
     */
    private static function nextElement(string $list): ?string
    {
        if ($list == '') {
            return null;
        }
        // Delimited by next comma at same degree of nesting.
        $startdelimiter = "[({";
        $enddelimiter   = "])}";
        $nesting = array(0 => 0, 1 => 0, 2 => 0); // Stores nesting for delimiters above.
        for ($i = 0; $i < strlen($list); $i++) {
            $startchar = strpos($startdelimiter, $list[$i]); // Which start delimiter.
            $endchar = strpos($enddelimiter, $list[$i]); // Which end delimiter (if any).

            // Change nesting for delimiter if specified.
            if ($startchar !== false) {
                $nesting[$startchar]++;
            } else if ($endchar !== false) {
                $nesting[$endchar]--;
            } else if ($list[$i] == ',' && $nesting[0] == 0 && $nesting[1] == 0 &&$nesting[2] == 0) {
                // Otherwise, return element if all nestings are zero.
                return substr($list, 0, $i);
            }
        }

        // End of list reached.
        if ($nesting[0] == 0 && $nesting[1] == 0 &&$nesting[2] == 0) {
            return $list;
        } else {
            return null;
        }
    }

    /**
     * Returns the next element in a list, set or function.
     * Handles nested lists, sets and functions.
     *
     * @param string $list
     * @param bool $rec
     * @return array
     */
    private static function listToArrayWorkhorse(string $list, bool $rec = true): array
    {
        $array = array();
        $list = trim($list);
        $list = substr($list, 1, strlen($list) - 2); // Trims outermost [] only.
        $e = self::nextElement($list);
        while ($e !== null) {
            if ($e[0] == '[') {
                if ($rec) {
                    $array[] = self::listToArrayWorkhorse($e, $rec);
                } else {
                    $array[] = $e;
                }
            } else {
                $array[] = $e;
            }
            $list = substr($list, strlen($e) + 1);
            $e = self::nextElement($list);
        }
        return $array;
    }

    /**
     * Converts a list structure into an array.
     * Handles nested lists, sets and functions with help from next_element().
     *
     * @param string $string
     * @param bool $rec
     * @return array
     */
    public static function listToArray(string $string, bool $rec = true): array
    {
        return self::listToArrayWorkhorse($string, $rec);
    }

    /**
     * Check whether the number of left and right substrings match, for example
     * whether every 'left' has a matching 'right'.
     * Returns true if equal, 'left' left is missing, 'right' if right is missing.
     *
     * @param string $string the string to test.
     * @param string $left the left delimiter.
     * @param string $right the right delimiter.
     * @return bool|string true if they match; 'left' if there are left delimiters
     *      missing; or 'right' if there are right delimiters missing.
     */
    public static function checkBookends(string $string, string $left, string $right): bool|string
    {
        $leftcount = substr_count($string, $left);
        $rightcount = substr_count($string, $right);

        if ($leftcount == $rightcount) {
            return true;
        } else if ($leftcount > $rightcount) {
            return 'right';
        } else {
            return 'left';
        }
    }
}