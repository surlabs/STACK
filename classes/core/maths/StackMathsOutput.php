<?php
declare(strict_types=1);

namespace classes\core\maths;

use classes\core\filters\StackParser;
use classes\core\security\StackException;

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
abstract class StackMathsOutput {
    /**
     * Do the necessary processing on equations in a language string, before it is output.
     * @param string $string the language string, as loaded by get_string.
     * @return string the string, with equations rendered to HTML.
     */
    public function processLangString(string $string) :string {
        return $string;
    }

    /**
     * Do the necessary processing on documentation page before the content is
     * passed to Markdown.
     * @param string $docs content of the documentation file.
     * @return string the documentation content ready to pass to Markdown.
     */
    public function preProcessDocsPage(string $docs): string {
        // Double all the \ characters, since Markdown uses it as an escape char,
        // but we use it for maths.
        $docs = str_replace('\\', '\\\\', $docs);

        // Re-double \ characters inside text areas, because we don't want maths
        // renderered there.
        return preg_replace_callback('~(<textarea[^>]*>)(.*?)(</textarea>)~s',
            function ($match) {
                return $match[1] . str_replace('\\', '\\\\', $match[2]) . $match[3];
            }, $docs);
    }

    /**
     * Do the necessary processing on documentation page after the content is
     * has been rendered by Markdown.
     * @param string $html rendered version of the documentation page.
     * @return string rendered version of the documentation page with equations inserted.
     */
    public function postProcessDocsPage(string $html) :string {
        // Now, undo the doubling of the \\ characters inside <code> and <textarea> regions.
        return preg_replace_callback('~(<code>|<textarea[^>]*>)(.*?)(</code>|</textarea>)~s',
            function ($match) {
                return $match[1] . str_replace('\\\\', '\\', $match[2]) . $match[3];
            }, $html);
    }

    /**
     * Do the necessary processing on content that came from the user, for example
     * the question text or general feedback. The result of calling this method is
     * then passed to Moodle's {@link format_text()} function.
     * @param string $text the content to process.
     * @param bool $replacedollars
     * @return string the content ready to pass to format_text.
     * @throws StackException
     */
    public function processDisplayCastext(string $text, bool $replacedollars): string {
        if ($replacedollars) {
            $text = $this->replaceDollars($text);
        }

        //TODO: $text = str_replace('!ploturl!', moodle_url::make_file_url('/question/type/stack/plot.php', '/'), $text);

        return StackFactSheets::display($text);
    }

    /**
     * Replace dollar delimiters ($...$ and $$...$$) in text with the safer
     * \(...\) and \[...\].
     * @param string $text the original text.
     * @param bool $markup surround the change with <ins></ins> tags.
     * @return string the text with delimiters replaced.
     */
    public function replaceDollars(string $text, bool $markup = false) :string {
        if ($markup) {
            $displaystart = '<ins>\[</ins>';
            $displayend   = '<ins>\]</ins>';
            $inlinestart  = '<ins>\(</ins>';
            $inlineend    = '<ins>\)</ins>';
            $v4start      = '<ins>{@</ins>';
            $v4end        = '<ins>@}</ins>';
        } else {
            $displaystart = '\[';
            $displayend   = '\]';
            $inlinestart  = '\(';
            $inlineend    = '\)';
            $v4start      = '{@';
            $v4end        = '@}';
        }
        $text = preg_replace('~(?<!\\\\)\$\$(.*?)(?<!\\\\)\$\$~', $displaystart . '$1' . $displayend, $text);
        $text = preg_replace('~(?<!\\\\)\$(.*?)(?<!\\\\)\$~', $inlinestart . '$1' . $inlineend, $text);

        $temp = StackParser::allSubstringStrings($text, '@', '@', true);
        $i = 0;
        foreach ($temp as $cmd) {
            $pos = strpos($text, '@', $i);
            $post = false;
            while (!$post) {
                $post = strpos($text, '@', $pos + 1);
                if (strpos($text, $cmd, $pos) > $post || trim(substr($text, $pos + 1, $post - $pos - 1)) != $cmd) {
                    $pos = $post;
                    $post = false;
                } else {
                    $post = $post + 1;
                }
            }
            $front = $pos > 0 && $text[$pos - 1] == '{';
            $back = $post < strlen($text) && $text[$post] == '}';
            if (!($front && $back)) {
                $text = substr($text, 0, $pos) . $v4start . trim($cmd) . $v4end . substr($text, $post);
            }
            $i = $pos + strlen($v4start);
        }

        return $text;
    }
}