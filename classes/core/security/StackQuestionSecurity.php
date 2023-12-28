<?php
declare(strict_types=1);

namespace classes\core\security;

use classes\core\external\cas\stack_cas_session2;
use classes\core\maxima\StackSession;
use classes\core\options\StackOptions;
use classes\core\StackQuestion;
use classes\core\version\StackVersion;
use classes\platform\StackConfig;
use classes\platform\StackDatabase;
use stdClass;

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

    /**
     * Get a raw array of the question from the database
     *
     * @throws StackException
     */
    public function getQuestionInternalFromDB(StackVersion $version): array
    {
        $question = StackDatabase::select('qpl_questions', ['question_id' => $version->getId()], array('title', 'question_text', 'description'));

        if (empty($question)) {
            throw new StackException('StackQuestionSecurity->getQuestionInternalFromDB: Question not found for question id ' . $version->getId());
        } else {
            $question = $question[0];
        }

        $options = StackDatabase::select('xqcas_options', ['question_id' => $version->getId()]);

        if (empty($options)) {
            throw new StackException('StackQuestionSecurity->getQuestionInternalFromDB: Options not found for question id ' . $version->getId());
        } else {
            $options = $options[0];
        }

        $extra_info = StackDatabase::select('xqcas_extra_info', ['question_id' => $version->getId()], array('general_feedback'));

        if (empty($extra_info)) {
            throw new StackException('StackQuestionSecurity->getQuestionInternalFromDB: Extra info not found for question id ' . $version->getId());
        } else {
            $extra_info = $extra_info[0];
        }

        $inputs = StackDatabase::select('xqcas_inputs', ['question_id' => $version->getId()]);

        if (empty($inputs)) {
            throw new StackException('StackQuestionSecurity->getQuestionInternalFromDB: Inputs not found for question id ' . $version->getId());
        } else {
            $tmp_inputs = array();

            foreach ($inputs as $input) {
                $tmp_inputs[$input['id']] = $input;
            }

            $inputs = $tmp_inputs;
        }

        $prts = StackDatabase::select('xqcas_prts', ['question_id' => $version->getId()]);

        if (empty($prts)) {
            throw new StackException('StackQuestionSecurity->getQuestionInternalFromDB: PRTs not found for question id ' . $version->getId());
        } else {
            $tmp_prts = array();

            foreach ($prts as $prt) {
                $tmp_prts[$prt['name']] = [
                    'id' => $prt['id'],
                    'name' => $prt['name'],
                    'simplify' => $prt['auto_simplify'],
                    'feedback_style' => 1,
                    'value' => $prt['value'],
                    'feedback_variables' => $prt['feedback_variables'],
                    'nodes' => array(),
                    'first_node' => $prt['first_node_name'],
                ];
            }

            $prts = $tmp_prts;

            $nodes = StackDatabase::select('xqcas_prt_nodes', ['question_id' => $version->getId()]);

            foreach ($nodes as $node) {
                $prts[$node['prt_name']]['nodes'][$node['node_name']] = $node;
            }
        }

        return array(
            'title' => $question['title'],
            'text' => $question['question_text'],
            'description' => $question['description'],
            'specific_feedback' => $options['specific_feedback'],
            'options' => array(
                'multiplicationsign' => $options['multiplication_sign'],
                'complexno' => $options['complex_no'],
                'inversetrig' => $options['inverse_trig'],
                'logicsymbol' => $options['logic_symbol'],
                'sqrtsign' => $options['sqrt_sign'],
                'simplify' => $options['question_simplify'],
                'assumepos' => $options['assume_positive'],
                'assumereal' => $options['assume_real'],
                'matrixparens' => $options['matrix_parens'],
            ),
            'variables' => $options['question_variables'],
            'default_feedback_for_fully_correct_prt' => $options['prt_correct'],
            'default_feedback_for_partially_correct_prt' => $options['prt_partially_correct'],
            'default_feedback_for_fully_incorrect_prt' => $options['prt_incorrect'],
            'general_feedback_text' => $extra_info['general_feedback'],
            'hint' => '',
            'inputs' => $inputs,
            'potential_response_trees' => $prts,
        );
    }

    /**
     * Set a raw array of the question to the database
     *
     * @throws StackException
     */
    public function setQuestionInternalToDB(array $data) :bool {
        dump($data);

        return true;
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