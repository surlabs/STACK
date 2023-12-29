<?php
declare(strict_types=1);

use classes\core\security\StackException;
use classes\platform\StackDatabase;

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 *  This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 *  originally created by Chris Sangwin.
 *
 *  The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 *  For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 *  To report bugs or participate in discussions, visit the Mantis system and filter by
 *  the category "STACK Question" at https://mantis.ilias.de.
 *
 *  More information and source code are available at:
 *  https://github.com/surlabs/STACK
 *
 *  If you need support, please contact the maintainer of this software at:
 *  stack@surlabs.es
 *
 */
class MoodleXmlImport
{
    /**
     * Plugin instance for language management
     * @var ilassStackQuestionPlugin
     */
    private ilassStackQuestionPlugin $plugin;

    /**
     * The current question
     * @var assStackQuestion
     */
    private assStackQuestion $question;

    /**
     * Question_id for the first question to import
     * (When only one question, use this as Question_Id)
     * @var int If first question this var is higher than 0.
     */
    private int $first_question;


    /**
     * @var string    allowed html tags, e.g. "<em><strong>..."
     */
    private string $rte_tags = "";

    /**
     * media objects created for an imported question
     * This list will be cleared for every new question
     * @var array    id => object
     */
    private array $media_objects = array();

    /**
     * Set all the parameters for this question, including the creation of
     * the first assStackQuestion object.
     * @param ilassStackQuestionPlugin $plugin
     * @param int $first_question_id the question_id for the first question to import.
     * @param assStackQuestion $question
     */
    function __construct(ilPlugin $plugin, int $first_question_id, assStackQuestion $question)
    {
        //Set Plugin and first question id.
        $this->setPlugin($plugin);
        $this->setFirstQuestion($first_question_id);
        $this->setQuestion($question);
    }

    /* MAIN METHODS BEGIN */

    /**
     * ### MAIN METHOD OF THIS CLASS ###
     * This method is called from assStackQuestion to import the questions from an MoodleXML file.
     * @param $xml_file
     * @return bool
     * @throws StackException
     */
    public function import($xml_file): bool
    {
        //Step 1: Get data from XML.
        //LIBXML_NOCDATA Merge CDATA as Textnodes
        $xml = simplexml_load_file($xml_file, null, LIBXML_NOCDATA);

        $number_of_questions_created = 0;


        //Step 2: Iterate over all questions in the XML file.
        foreach ($xml->question as $xmlQuestion) {
            $type = (string) $xmlQuestion->attributes()['type'];

            if ($type == "stack") {
                //New list of media objects for each question
                $this->media_objects = array();

                //Set current question Id to -1 if we have created already one question, to ensure creation of the others
                if ($number_of_questions_created > 0) {
                    $this->getQuestion()->setId(-1);
                }

                //Delete predefined inputs and prts
                $this->getQuestion()->getStackQuestion()->setInputs(array());
                $this->getQuestion()->getStackQuestion()->setPotentialResponseTrees(array());

                //Step 3: Set the basic data for the question.
                $raw_data = array(
                    'question_title' => (string) $xmlQuestion->name->text,
                    'question_text' => (string) $xmlQuestion->questiontext->text,
                    'points' => (float) $xmlQuestion->defaultgrade,
                );

                //Step 4: Set the xqcas_options fields

                //question variables
                if (isset($xmlQuestion->questionvariables->text)) {
                    $raw_data["question_variables"] = (string)$xmlQuestion->questionvariables->text;
                }

                //specific feedback
                if (isset($xmlQuestion->specificfeedback->text)) {
                    $raw_data["specific_feedback"] = (string)$xmlQuestion->specificfeedback->text;
                    $raw_data["specific_feedback_format"] = 1;
                }

                //question note
                if (isset($xmlQuestion->questionnote->text)) {
                    $raw_data["question_note"] = (string)$xmlQuestion->questionnote->text;
                }

                //prt correct feedback
                $raw_data["prt_correct"] = (string) $xmlQuestion->prtcorrect->text;
                $raw_data["prt_correct_format"] = 1;

                //prt partially correct
                $raw_data["prt_partially_correct"] = (string) $xmlQuestion->prtpartiallycorrect->text;
                $raw_data["prt_partially_correct_format"] = 1;

                //prt incorrect
                $raw_data["prt_incorrect"] = (string) $xmlQuestion->prtincorrect->text;
                $raw_data["prt_incorrect_format"] = 1;

                //variants selection seeds
                $raw_data["variants_selection_seed"] = (string) $xmlQuestion->variantsselectionseed;

                //options
                $raw_data["options"] = array();
                $raw_data["options"]['simplify'] = (int) $xmlQuestion->questionsimplify;
                $raw_data["options"]['assumepos'] = (int) $xmlQuestion->assumepositive;
                $raw_data["options"]['assumereal'] = (int) $xmlQuestion->assumereal;
                $raw_data["options"]['multiplicationsign'] = (string) $xmlQuestion->multiplicationsign;
                $raw_data["options"]['sqrtsign'] = (int) $xmlQuestion->sqrtsign;
                $raw_data["options"]['complexno'] = (string) $xmlQuestion->complexno;
                $raw_data["options"]['inversetrig'] = (string) $xmlQuestion->inversetrig;
                $raw_data["options"]['matrixparens'] = (string) $xmlQuestion->matrixparens;
                $raw_data["options"]['logicsymbol'] = (string) $xmlQuestion->logicsymbol;

                if (isset($xmlQuestion->stackversion->text)) {
                    $raw_data["stackversion"] = (string) $xmlQuestion->stackversion->text;
                } else {
                    $raw_data["stackversion"] = "";
                }

                //Step 5: Set the xqcas_inputs fields
                $raw_data["inputs"] = array();

                foreach ($xmlQuestion->input as $input) {
                    $raw_data["inputs"][(string) $input->name] = array(
                        'tans' => (string) $input->tans,
                        'inputType' => (string) $input->type,
                        'boxWidth' => (string) $input->boxsize,
                        'strictSyntax' => (string) $input->strictsyntax,
                        'insertStars' => (int) $input->insertstars,
                        'syntaxHint' => (string) $input->syntaxhint,
                        'syntaxAttribute' => (string) $input->syntaxattribute,
                        'forbidWords' => (string) $input->forbidwords,
                        'allowWords' => (string) $input->allowwords,
                        'forbidFloats' => (string) $input->forbidfloat,
                        'lowestTerms' => (string) $input->requirelowestterms,
                        'sameType' => (string) $input->checkanswertype,
                        'mustVerify' => (string) $input->mustverify,
                        'showValidation' => (string) $input->showvalidation,
                        'options' => (string) $input->options,
                        'checkanswertype' => (int) $input->checkanswertype,
                    );
                }

                //Step 6: Set the xqcas_prt & xqcas_prt_nodes fields
                $raw_data["prts"] = array();

                foreach ($xmlQuestion->prt as $prt) {
                    $prt_name = (string) $prt->name;
                    $nodes = array();
                    $first_node = false;

                    foreach ($prt->node as $xml_node) {
                        if (!$first_node) {
                            $first_node = (string) $xml_node->name;
                        }

                        $nodes[(string) $xml_node->name] = array(
                            'sans' => (string) $xml_node->sans,
                            'tans' => (string) $xml_node->tans,
                            'falsepenalty' => (string) $xml_node->falsepenalty,
                            'truepenalty' => (string) $xml_node->truepenalty,
                            'answertest' => (string) $xml_node->answertest,
                            'testoptions' => (string) $xml_node->testoptions,
                            'quiet' => (string) $xml_node->quiet,
                            'falsefeedback' => (string) $xml_node->falsefeedback->text,
                            'falsefeedback_format' => 1,
                            'truefeedback' => (string) $xml_node->truefeedback->text,
                            'truefeedback_format' => 1,
                            'truenextnode' => (string) $xml_node->truenextnode,
                            'falsenextnode' => (string) $xml_node->falsenextnode,
                            'trueanswernote' => (string) $xml_node->trueanswernote,
                            'falseanswernote' => (string) $xml_node->falseanswernote,
                            'truescoremode' => (string) $xml_node->truescoremode,
                            'falsescoremode' => (string) $xml_node->falsescoremode,
                            'truescore' => (string) $xml_node->truescore,
                            'falsescore' => (string) $xml_node->falsescore,
                        );
                    }

                    $raw_data["prts"][$prt_name] = array(
                        'name' => $prt_name,
                        'simplify' => (int) $prt->autosimplify,
                        'feedback_style' => 1,
                        'value' => (float) $prt->value,
                        'feedback_variables' => (string) $prt->feedbackvariables->text,
                        'nodes' => $nodes,
                        'first_node' => $first_node,
                    );
                }

                //deployedseeds
                $raw_data["deployedseeds"] = array();
                if (isset($xmlQuestion->deployedseed)) {
                    foreach ($xmlQuestion->deployedseed as $seed) {
                        $raw_data["deployedseeds"][] = (int) $seed;
                    }
                }

                //Step 7: Set the xqcas_extra_info fields
                if (isset($xmlQuestion->generalfeedback->text)) {
                    $raw_data["general_feedback"] = (string) $xmlQuestion->generalfeedback->text;
                }

                //Penalty
                if (isset($xmlQuestion->penalty)) {
                    $raw_data["penalty"] = (float) $xmlQuestion->penalty;
                }

                //Hidden
                if (isset($xmlQuestion->hidden)) {
                    $raw_data["hidden"] = (int) $xmlQuestion->hidden;
                }

                //Unit tests
                if (isset($xmlQuestion->qtest)) {
                    $raw_data["qtests"] = array();

                    foreach ($xmlQuestion->qtest as $testcase) {
                        $testcase_name = (string)$testcase->testcase;
                        $raw_data["qtests"][$testcase_name] = array();

                        foreach ($testcase->testinput as $testcase_input) {
                            $raw_data["qtests"][$testcase_name]['inputs'][(string)$testcase_input->name] = (string) $testcase_input->value;;
                        }

                        foreach ($testcase->expected as $testcase_expected) {
                            $prt_name = (string)$testcase_expected->name;

                            $raw_data["qtests"][$testcase_name]['expected'][$prt_name]['score'] = (string) $testcase_expected->expectedscore;
                            $raw_data["qtests"][$testcase_name]['expected'][$prt_name]['penalty'] = (string) $testcase_expected->expectedpenalty;
                            $raw_data["qtests"][$testcase_name]['expected'][$prt_name]['answer_note'] = (string) $testcase_expected->expectedanswernote;
                        }
                    }
                }

                //Step 9: Set basic data for the question.
                $this->getQuestion()->setTitle($raw_data['question_title']);
                $this->getQuestion()->setPoints($raw_data['points']);

                $this->getQuestion()->setQuestion($raw_data['question_text']);
                $this->getQuestion()->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());

                $this->getQuestion()->saveQuestionDataToDb();

                $raw_data['question_id'] = $this->getQuestion()->getId();

                if ($this->getQuestion()->getStackQuestion()->getSecurity()->setQuestionInternalToDB($raw_data)) {
                    $number_of_questions_created++;
                }
            } else {
                throw new StackException("MoodleXmlImport: Question type not supported: " . $type . ", expected: stack");
            }
        }

        return $number_of_questions_created > 0;
    }

    /**
     * @param ilassStackQuestionPlugin $plugin
     */
    public
    function setPlugin(ilassStackQuestionPlugin $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @return ilassStackQuestionPlugin
     */
    public
    function getPlugin(): ilassStackQuestionPlugin
    {
        return $this->plugin;
    }

    /**
     * @param assStackQuestion $question
     */
    public
    function setQuestion(assStackQuestion $question): void
    {
        $this->question = $question;
    }

    /**
     * @return assStackQuestion
     */
    public
    function getQuestion(): assStackQuestion
    {
        return $this->question;
    }

    /**
     * @param int $first_question
     */
    public
    function setFirstQuestion(int $first_question): void
    {
        $this->first_question = $first_question;
    }

    /**
     * @return int
     */
    public
    function getFirstQuestion(): int
    {
        return $this->first_question;
    }

    /**
     * @param $tags
     */
    public
    function setRTETags($tags): void
    {
        $this->rte_tags = $tags;
    }

    /**
     * @return string    allowed html tags, e.g. "<em><strong>..."
     */
    public
    function getRTETags(): string
    {
        return $this->rte_tags;
    }

    /* GETTERS AND SETTERS END */
}