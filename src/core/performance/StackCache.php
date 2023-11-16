<?php
declare(strict_types=1);

namespace src\core\performance;

use src\core\security\StackQuestionStudentAnswer;

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
class StackCache
{
    /**
     * Make sure the cache is valid for the given response. If not, clear it.
     * @param StackQuestionStudentAnswer $student_answer
     * @param bool|null $accept_valid_state if this is true, then we will grade things even
     * if the corresponding inputs are only VALID, and not SCORE
     * @return void
     */
    protected function validateCacheForStudentAnswer(StackQuestionStudentAnswer $student_answer, ?bool $accept_valid_state = null): void
    {

    }

}