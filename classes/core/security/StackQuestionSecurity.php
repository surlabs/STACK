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

        $deployedseeds = StackDatabase::select('xqcas_deployed_seeds', ['question_id' => $version->getId()], array('seed'));

        if (empty($deployedseeds)) {
            throw new StackException('StackQuestionSecurity->getQuestionInternalFromDB: Deployed seeds not found for question id ' . $version->getId());
        } else {
            $tmp_seeds = array();

            foreach ($deployedseeds as $deployedseed) {
                $tmp_seeds[] = $deployedseed['seed'];
            }

            $deployedseeds = $tmp_seeds;
        }

        $quests = StackDatabase::select('xqcas_qtests', ['question_id' => $version->getId()], array('test_case'));

        if (empty($quests)) {
            throw new StackException('StackQuestionSecurity->getQuestionInternalFromDB: QTests not found for question id ' . $version->getId());
        } else {
            $tmp_quests = array();

            foreach ($quests as $quest) {
                $tmp_quests[$quest['test_case']] = [
                    'id' => $quest['id'],
                    'test_case' => $quest['test_case'],
                    'inputs' => array(),
                    'expected' => array(),
                ];
            }

            $quests = $tmp_quests;

            $qtest_inputs = StackDatabase::select('xqcas_qtest_inputs', ['question_id' => $version->getId()]);

            foreach ($qtest_inputs as $qtest_input) {
                $quests[$qtest_input['test_case']]['inputs'][$qtest_input['input_name']] = $qtest_input['input_value'];
            }

            $qtest_expected = StackDatabase::select('xqcas_qtest_expected', ['question_id' => $version->getId()]);

            foreach ($qtest_expected as $qtest_expect) {
                $quests[$qtest_expect['test_case']]['expected'][$qtest_expect['prt_name']] = [
                    'score' => $qtest_expect['expected_score'],
                    'penalty' => $qtest_expect['expected_penalty'],
                    'answernote' => $qtest_expect['expected_answer_note'],
                ];
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
            'stackversion' => $options['stack_version'],
            'deployedseeds' => $deployedseeds,
            'qtests' => $quests,
        );
    }

    /**
     * Set a raw array of the question to the database
     *
     * @throws StackException
     */
    public function setQuestionInternalToDB(array $data) :bool {
        try {
            StackDatabase::insert('xqcas_options', array(
                'id' => StackDatabase::nextId('xqcas_options'),
                'question_id' => $data['question_id'],
                'question_variables' => $data['question_variables'],
                'specific_feedback' => $data['specific_feedback'],
                'specific_feedback_format' => $data['specific_feedback_format'],
                'question_note' => $data['question_note'],
                'question_simplify' => $data['options']['simplify'],
                'assume_positive' => $data['options']['assumepos'],
                'prt_correct' => $data['prt_correct'],
                'prt_correct_format' => $data['prt_correct_format'],
                'prt_partially_correct' => $data['prt_partially_correct'],
                'prt_partially_correct_format' => $data['prt_partially_correct_format'],
                'prt_incorrect' => $data['prt_incorrect'],
                'prt_incorrect_format' => $data['prt_incorrect_format'],
                'multiplication_sign' => $data['options']['multiplicationsign'],
                'sqrt_sign' => $data['options']['sqrtsign'],
                'complex_no' => $data['options']['complexno'],
                'inverse_trig' => $data['options']['inversetrig'],
                'variants_selection_seed' => $data['variants_selection_seed'],
                'matrix_parens' => $data['options']['matrixparens'],
                'assume_real' => $data['options']['assumereal'],
                'logic_symbol' => $data['options']['logicsymbol'],
                'stack_version' => $data['stackversion'],
            ));

            StackDatabase::insert('xqcas_extra_info', array(
                'id' => StackDatabase::nextId('xqcas_extra_info'),
                'question_id' => $data['question_id'],
                'general_feedback' => $data['general_feedback'],
                'penalty' => $data['penalty'],
                'hidden' => $data['hidden'],
            ));

            foreach ($data['inputs'] as $key => $value) {
                StackDatabase::insert('xqcas_inputs', array(
                    'id' => StackDatabase::nextId('xqcas_inputs'),
                    'question_id' => $data['question_id'],
                    'name' => $key,
                    'tans' => $value['tans'],
                    'box_size' => $value['boxWidth'],
                    'strict_syntax' => $value['strictSyntax'],
                    'insert_stars' => $value['insertStars'],
                    'syntax_hint' => $value['syntaxHint'],
                    'forbid_words' => $value['forbidWords'],
                    'require_lowest_terms' => $value['lowestTerms'],
                    'check_answer_type' => $value['checkanswertype'],
                    'must_verify' => $value['mustVerify'],
                    'show_validation' => $value['showValidation'],
                    'options' => $value['options'],
                    'allow_words' => $value['allowWords'],
                    'syntax_attribute' => $value['syntaxAttribute'],
                ));
            }

            foreach ($data['prts'] as $key => $value) {
                StackDatabase::insert('xqcas_prts', array(
                    'id' => StackDatabase::nextId('xqcas_prts'),
                    'question_id' => $data['question_id'],
                    'name' => $key,
                    'value' => $value['value'],
                    'auto_simplify' => $value['simplify'],
                    'feedback_variables' => $value['feedback_variables'],
                    'first_node_name' => $value['first_node'],
                ));

                foreach ($value['nodes'] as $node_name => $node) {
                    StackDatabase::insert('xqcas_prt_nodes', array(
                        'id' => StackDatabase::nextId('xqcas_prt_nodes'),
                        'question_id' => $data['question_id'],
                        'prt_name' => $key,
                        'node_name' => $node_name,
                        'answer_test' => $node['answertest'],
                        'sans' => $node['sans'],
                        'tans' => $node['tans'],
                        'test_options' => $node['testoptions'],
                        'quiet' => $node['quiet'],
                        'true_score_mode' => $node['truescoremode'],
                        'true_score' => $node['truescore'],
                        'true_penalty' => $node['truepenalty'],
                        'true_next_node' => $node['truenextnode'],
                        'true_answer_note' => $node['trueanswernote'],
                        'true_feedback' => $node['truefeedback'],
                        'true_feedback_format' => $node['truefeedback_format'],
                        'false_score_mode' => $node['falsescoremode'],
                        'false_score' => $node['falsescore'],
                        'false_penalty' => $node['falsepenalty'],
                        'false_next_node' => $node['falsenextnode'],
                        'false_answer_note' => $node['falseanswernote'],
                        'false_feedback' => $node['falsefeedback'],
                        'false_feedback_format' => $node['falsefeedback_format'],
                    ));
                }
            }

            foreach ($data['deployedseeds'] as $key => $value) {
                StackDatabase::insert('xqcas_deployed_seeds', array(
                    'id' => StackDatabase::nextId('xqcas_deployed_seeds'),
                    'question_id' => $data['question_id'],
                    'seed' => $value,
                ));
            }

            foreach ($data['qtests'] as $key => $value) {
                StackDatabase::insert('xqcas_qtests', array(
                    'id' => StackDatabase::nextId('xqcas_qtests'),
                    'question_id' => $data['question_id'],
                    'test_case' => $key,
                ));

                foreach ($value['inputs'] as $input_name => $input_value) {
                    StackDatabase::insert('xqcas_qtest_inputs', array(
                        'id' => StackDatabase::nextId('xqcas_qtest_inputs'),
                        'question_id' => $data['question_id'],
                        'test_case' => $key,
                        'input_name' => $input_name,
                        'value' => $input_value,
                    ));
                }

                foreach ($value['expected'] as $prt_name => $expected) {
                    StackDatabase::insert('xqcas_qtest_expected', array(
                        'id' => StackDatabase::nextId('xqcas_qtest_expected'),
                        'question_id' => $data['question_id'],
                        'test_case' => $key,
                        'prt_name' => $prt_name,
                        'expected_score' => $expected['score'],
                        'expected_penalty' => $expected['penalty'],
                        'expected_answer_note' => $expected['answer_note'],
                    ));
                }
            }

            return true;
        } catch (StackException $e) {
            // If the question could not be saved to xqcas tables, delete the inserted data
            StackDatabase::delete('qpl_questions', ['question_id' => $data['question_id']]);

            StackDatabase::delete('xqcas_options', ['question_id' => $data['question_id']]);

            StackDatabase::delete('xqcas_extra_info', ['question_id' => $data['question_id']]);

            StackDatabase::delete('xqcas_inputs', ['question_id' => $data['question_id']]);

            StackDatabase::delete('xqcas_prts', ['question_id' => $data['question_id']]);

            StackDatabase::delete('xqcas_prt_nodes', ['question_id' => $data['question_id']]);

            StackDatabase::delete('xqcas_deployed_seeds', ['question_id' => $data['question_id']]);

            StackDatabase::delete('xqcas_qtests', ['question_id' => $data['question_id']]);

            StackDatabase::delete('xqcas_qtest_inputs', ['question_id' => $data['question_id']]);

            StackDatabase::delete('xqcas_qtest_expected', ['question_id' => $data['question_id']]);

            return false;
        }
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