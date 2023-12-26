<?php
declare(strict_types=1);

namespace classes\core\maths;

use classes\core\security\StackException;
use classes\platform\StackConfig;
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
class StackMaths {
    protected static array $outputs = array();

    /**
     * Do the necessary processing on equations in a language string, before it
     * is output. Rather than calling this method directly, you should probably
     * use the stack_string method in locallib.php.
     *
     * @param string $string the language string, as loaded by get_string.
     * @return string the string, with equations rendered to HTML.
     */
    public static function processLangString(string $string) :string {
        return self::getOutput()->processLangString($string);
    }

    /**
     * Do the necessary processing on content that came from the user, for example
     * the question text or general feedback. The result of calling this method is
     * then passed to Moodle's {@link format_text()} function.
     * @param string $text the content to process.
     * @return string the content ready to pass to format text.
     * @throws StackException
     */
    public static function processDisplayCastext(string $text) :string {
        return self::getOutput()->processDisplayCastext($text, StackConfig::get("replace_dollars") == 1);
    }

    /**
     * Do the necessary processing on documentation page before the content is
     * passed to Markdown.
     * @param string $docs content of the documentation file.
     * @return string the documentation content ready to pass to Markdown.
     */
    public static function preProcessDocsPage(string $docs) :string {
        return self::getOutput()->preProcessDocsPage($docs);
    }

    /**
     * Do the necessary processing on documentation page after the content has been rendered by Markdown.
     * @param string $html rendered version of the documentation page.
     * @return string rendered version of the documentation page with equations inserted.
     */
    public static function postProcessCocsPage(string $html) :string {
        return self::getOutput()->postProcessDocsPage($html);
    }

    /**
     * Replace dollar delimiters ($...$ and $$...$$) in text with the safer \(...\) and \[...\].
     * @param string $text the original text.
     * @param bool $markup surround the change with <ins></ins> tags.
     * @return string the text with delimiters replaced.
     */
    public static function replaceDollars(string $text, bool $markup = false) :string {
        return self::getOutput()->replaceDollars($text, $markup);
    }

    /**
     * @return string the name of the currently configured output method.
     */
    public static function configuredOutputName(): string {
        return StackPlatform::getTranslation('settingmathsdisplay_' . StackConfig::get('maths_filter'));
    }

    /**
     * @return StackMathsOutput the output method that has been set in the configuration options.
     */
    protected static function getOutput(): StackMathsOutput {
        return self::getOutputInstance(StackConfig::get('maths_filter') ?? "mathjax");
    }

    /**
     * @param string $method
     * @return StackMathsOutput instance of the output class for this method.
     */
    protected static function getOutputInstance(string $method): StackMathsOutput {
        if (!array_key_exists($method, self::$outputs)) {
            switch ($method) {
                case 'mathjax':
                    self::$outputs[$method] = new StackMathsMathjax();
                    break;
                case 'maths':
                    self::$outputs[$method] = new StackMathsMaths();
                    break;
                case 'text':
                    self::$outputs[$method] = new StackMathsText();
                    break;
            }
        }
        return self::$outputs[$method];
    }
}