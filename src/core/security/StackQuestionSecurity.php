<?php
declare(strict_types=1);

namespace src\core\security;
use src\core\version\StackVersion;

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

    /**
     * Checks the internal security of the question
     * Called at StackQuestion::initialise()
     * @param string $json
     * @return bool
     */
    public static function checkInternal(string $json): bool
    {
        $decoded = json_decode($json, true);
        //JSON coding checks
        if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
            //TODO: Check internal status of the json values
            return true;
        } else {
            return false;
        }
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

    public function getQuestionExternalJSONFromStudent(StackVersion $version): string
    {
        return '';
    }

    public function getQuestionExternalJSONFromTeacher(StackVersion $version): string
    {
        return '';
    }

}