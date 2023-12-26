<?php
declare(strict_types=1);

namespace classes\core\maths;

use classes\core\security\StackException;
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
abstract class StackFactSheets {

    /**
     * This is the list of allowable facts tags. Each of these needs to have
     * two corresponding lines in the language file.
     * E.g. greek_alphabet_name and greek_alphabet_fact
     */
    protected static array $factsheets = array('greek_alphabet', 'alg_inequalities',
        'alg_indices', 'alg_logarithms', 'alg_quadratic_formula',
        'alg_partial_fractions', 'trig_degrees_radians', 'trig_standard_values',
        'trig_standard_identities', 'hyp_functions', 'hyp_identities',
        'hyp_inverse_functions', 'calc_diff_standard_derivatives',
        'calc_diff_linearity_rule', 'calc_product_rule', 'calc_quotient_rule',
        'calc_chain_rule', 'calc_rules', 'calc_int_standard_integrals',
        'calc_int_linearity_rule', 'calc_int_methods_substitution',
        'calc_int_methods_parts', 'calc_int_methods_parts_indefinite');

    /**
     * Check each facts tag actually corresponds to a valid fact sheet.
     * @param string $text the text to validate.
     * @return array any tags in the input that are not recognised.
     */
    public static function getUnrecognisedTags(string $text): array {
        $tags = self::getFactSheetTags($text);
        $errors = array();
        foreach ($tags as $val) {
            if (!in_array($val, self::$factsheets)) {
                $errors[] = $val;
            }
        }
        return $errors;
    }

    /**
     * Get all the tags present in a string.
     * @return array tags, if there are any. Empty array if none.
     */
    protected static function getFactSheetTags($text): array {
        if (preg_match_all('|\[\[facts:(\w*)]]|U', $text, $matches)) {
            return $matches[1];
        }
        return array();
    }

    /**
     * This function replaces tags with the HTML value.
     * Note, that at this point we assume we have already validated the text.
     * @param string $text the text in which to expand fact sheet tags.
     * @throws StackException
     */
    public static function display(string $text): array|string {
        $text = self::convertLegacyTags($text);

        $tags = self::getFactSheetTags($text);
        if (!$tags) {
            return $text;
        }

        foreach ($tags as $tag) {
            if (!in_array($tag, self::$factsheets)) {
                throw new StackException('StackFactSheets: the following facts tag does not exist: ' . $tag);
            }
        }

        //TODO: User renderers to render the fact sheets.

        return $text;
    }

    /**
     * This function converts the old style html tags to the new fact sheets system using square brackets.
     * @param string $text the text to convert.
     * @return string the converted text.
     */
    public static function convertLegacyTags(string $text) :string {
        if (!str_contains($text, '<hint>')) {
            return $text;
        }

        preg_match_all('|<hint>(.*)</hint>|U', $text, $matches);
        foreach ($matches[1] as $key => $val) {
            $old = $matches[0][$key];
            $new = '[[facts:' . trim($val) . ']]';
            $text = str_replace($old, $new, $text);
        }

        return $text;
    }

    /**
     * This function returns the html to insert into the documentaion.
     * It ensures that all/only the current tags are included in the docs.
     * Note, docs are usually in markdown, but we have html here because
     * fact sheets are part of castext.
     *
     * @return string HTML to insert into the docs page.
     */
    public static function generateDocs(): string {
        $doc = '';
        foreach (self::$factsheets as $tag) {
            $doc .= '### ' . StackPlatform::getTranslation($tag . '_name') . "\n\n<code>[[facts:" . $tag . "]]</code>\n\n";
            // Unusually we don't use stack_string here to make sure mathematics is not processed (yet).
            $doc .= StackPlatform::getTranslation($tag . '_fact', 'qtype_stack') . "\n\n\n";
        }
        return $doc;
    }
}