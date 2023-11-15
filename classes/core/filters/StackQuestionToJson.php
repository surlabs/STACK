<?php
declare(strict_types=1);

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
class StackQuestionToJson
{

    /**
     * Converts a StackQuestion object to a JSON string
     * Depending on the state of the question, different JSON strings are returned
     * @param StackQuestion $stack_question
     * @return string|null
     */
    public static function default(StackQuestion $stack_question): ?string
    {
        $json_string = '';

        try {
            //TODO: Convert the StackQuestion object to a JSON string
            switch ($stack_question->getState()) {
                case StackQuestion::STACK_QUESTION_STATE_UNINITIALIZED:
                    return $json_string;
                default:
                    //TODO: Log error, unknown state
                    break;
            }
        } catch (StackException $e) {
            //TODO: Log error at conversion
            return null;
        }

        return null;
    }

}