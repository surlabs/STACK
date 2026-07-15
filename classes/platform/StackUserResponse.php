<?php
declare(strict_types=1);

namespace classes\platform;

use assStackQuestionDB;


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
class StackUserResponse
{

    /**
     * Returns the stack user response from different sources depending on the purpose.
     * @throws StackException
     */
    public static function getStackUserResponse(string $purpose, int $question_id, int $id, int $pass = 0): array
    {

        $stack_user_response = match ($purpose) {
            'post' => self::getPostStackUserResponse(),
            'preview' => self::getPreviewStackUserResponse($question_id, $id),
            'test' => self::getTestStackUserResponse($question_id, $id, $pass),
            'unit_test' => self::getUnitTestStackUserResponse(),
            'correct' => self::getCorrectStackUserResponse(),
            default => throw new StackException('Invalid purpose selected: ' . $purpose . '.'),
        };

        if ($stack_user_response === null) {
            return [];
        }
        if (!self::checkStackUserResponse($stack_user_response)) {
            throw new StackException('Invalid stack user response.');
        } else {
            return $stack_user_response;
        }
    }

    protected static function checkStackUserResponse(array $stack_user_response): bool {
        //TODO: SUR
        if(is_array($stack_user_response)) {
            return true;
        } else {
            return false;
        }
    }

    public function saveStackUserResponse(array $stack_user_response, string $purpose): void
    {

        $stack_user_response = match ($purpose) {
            'preview' => $this->getPreviewStackUserResponse(),
            'test' => $this->getTestStackUserResponse(),
            default => throw new StackException('Invalid purpose selected: ' . $purpose . '.'),
        };

    }

    private static function getPostStackUserResponse(): array
    {
        $stack_user_response = array();
        return $stack_user_response;
    }

    private static function getPreviewStackUserResponse(int $question_id, int $user_id): ?array
    {
        return assStackQuestionDB::_readPreviewSolution($question_id, $user_id);
    }

    private static function getTestStackUserResponse(int $question_id, int $active_id, int $pass = 0): array
    {
        return assStackQuestionDB::_readTestSolution($question_id, $active_id, $pass);
    }

    private static function getCorrectStackUserResponse(): array
    {
        $stack_user_response = array();
        return $stack_user_response;
    }

    private static function getUnitTestStackUserResponse(): array
    {
        $stack_user_response = array();
        return $stack_user_response;
    }

}