<?php
declare(strict_types=1);

namespace classes\core\security;

use classes\core\StackQuestion;
use classes\core\version\StackVersion;
use classes\platform\StackDatabase;

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
class StackQuestionSecurity
{
    private array $allowedWords = [];
    private array $forbiddenWords = [];

    /**
     * Checks the internal security of the question
     * Called at StackQuestion::initialise()
     * @param mixed $data
     * @return bool
     */
    public static function checkInternal(mixed $data): bool
    {
        return is_array($data);
    }

    /**
     * Checks the external security of the question
     * Called at StackQuestion::initialise()
     * @param string $json
     * @return bool
     */
    public static function checkExternal(string $json): bool
    {
        $decoded = json_decode($json, true);
        //JSON coding checks
        if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
            //TODO: Check student answer and other external interactions and saves it in cache
            return true;
        } else {
            return false;
        }
    }
    
    public function getQuestionInternalFromDB(StackVersion $version): array
    {
        return array();
    }

    public function getQuestionExternalJSONFromStudent(StackQuestion $question): string
    {
        if ($question->getStatus() === StackQuestion::STACK_QUESTION_STATUS_EVALUATED) {
            return '';
        } else {
            return StackQuestionStudentAnswer::getQuestionExternalJSON($question->getVersion());
        }
    }

    public function getQuestionExternalJSONFromTeacher(StackQuestion $question): string
    {
        return '';
    }

    /**
     * Sets the allowed words
     * @param array $words
     * @return void
     */
    public function setAllowedWords(array $words) :void {
        $this->allowedWords = $words;
    }

    /**
     * Sets the forbidden words
     * @param array $words
     * @return void
     */
    public function setForbiddenWords(array $words) :void {
        $this->forbiddenWords = $words;
    }
}