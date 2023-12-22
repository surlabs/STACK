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

class StackUtils {
    const FORMAT_HTML = 0;
    const FORMAT_MARKDOWN = 1;
    const FORMAT_MOODLE = 2;
    const FORMAT_PLAIN = 3;


    /**
     * This function get translated strings from Maxima.
     * @return array
     */
    private static function getStackMaximaLatexReplacements(): array {
        return array(
            'QMCHAR' => '?',
            '!LEFTSQ!' => '\left[',
            '!LEFTR!' => '\left(',
            '!RIGHTSQ!' => '\right]',
            '!RIGHTR!' => '\right)',
            '!ANDOR!' => StackPlatform::getTranslation('equiv_ANDOR', null),
            '!SAMEROOTS!' => StackPlatform::getTranslation('equiv_SAMEROOTS', null),
            '!MISSINGVAR!' => StackPlatform::getTranslation('equiv_MISSINGVAR', null),
            '!ASSUMEPOSVARS!' => StackPlatform::getTranslation('equiv_ASSUMEPOSVARS', null),
            '!ASSUMEPOSREALVARS!' => StackPlatform::getTranslation('equiv_ASSUMEPOSREALVARS', null),
            '!LET!' => StackPlatform::getTranslation('equiv_LET', null),
            '!AND!' => StackPlatform::getTranslation('equiv_AND', null),
            '!OR!' => StackPlatform::getTranslation('equiv_OR', null),
            '!NOT!' => StackPlatform::getTranslation('equiv_NOT', null),
            '!NAND!' => StackPlatform::getTranslation('equiv_NAND', null),
            '!NOR!' => StackPlatform::getTranslation('equiv_NOR', null),
            '!XOR!' => StackPlatform::getTranslation('equiv_XOR', null),
            '!XNOR!' => StackPlatform::getTranslation('equiv_XNOR', null),
            '!IMPLIES!' => StackPlatform::getTranslation('equiv_IMPLIES', null),
            '!BOOLTRUE!' => StackPlatform::getTranslation('true', null),
            '!BOOLFALSE!' => StackPlatform::getTranslation('false', null),
        );
    }

    /**
     * This function takes a string and replaces all the Maxima tags with their translations.
     * @param string $latex
     * @return string
     * @noinspection PhpUnnecessaryLocalVariableInspection
     */
    public static function stackMaximaLatexTidy(string $latex) :string {
        $replacements = self::getStackMaximaLatexReplacements();
        $latex = str_replace(array_keys($replacements), array_values($replacements), $latex);

        // Also previously some spaces have been eliminated and line changes dropped.
        // Apparently returning verbatim LaTeX was not a thing.
        $latex = str_replace("\n ", '', $latex);
        $latex = str_replace("\n", '', $latex);
        // Just don't want to use regexp.
        $latex = str_replace('    ', ' ', $latex);
        $latex = str_replace('   ', ' ', $latex);
        $latex = str_replace('  ', ' ', $latex);

        return $latex;
    }

    /**
     * This function takes a feedback string from Maxima and unpacks and translates it.
     * @param string $rawfeedback
     * @return string
     */
    public static function stackMaximaTranslate(string $rawfeedback): string {

        if (!str_contains($rawfeedback, 'stack_trans')) {
            return trim(self::stackMaximaLatexTidy($rawfeedback));
        } else {
            $rawfeedback = str_replace('[[', '', $rawfeedback);
            $rawfeedback = str_replace(']]', '', $rawfeedback);
            $rawfeedback = str_replace("\n", '', $rawfeedback);
            $rawfeedback = str_replace('\n', '', $rawfeedback);
            $rawfeedback = str_replace('!quot!', '"', $rawfeedback);

            $translated = array();
            preg_match_all('/stack_trans\(.*?\);/', $rawfeedback, $matches);
            $feedback = $matches[0];
            foreach ($feedback as $fb) {
                $fb = substr($fb, 12, -2);
                if (!str_contains($fb, "' , \"")) {
                    // We only have a feedback tag, with no optional arguments.
                    $translated[] = trim(StackPlatform::getTranslation(substr($fb, 1, -1), null));
                } else {
                    // We have a feedback tag and some optional arguments.
                    $tag = substr($fb, 1, strpos($fb, "' , \"") - 1);
                    $arg = substr($fb, strpos($fb, "' , \"") + 5, -2);
                    $args = explode('"  , "', $arg);

                    $a = array();
                    for ($i = 0; $i < count($args); $i++) {
                        $a["m$i"] = $args[$i];
                    }
                    $translated[] = trim(StackPlatform::getTranslation($tag, array($a)));
                }
            }

            return self::stackMaximaLatexTidy(implode(' ', $translated));
        }
    }
}