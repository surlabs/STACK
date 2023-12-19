<?php
declare(strict_types=1);

namespace src\core\filters;

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
}