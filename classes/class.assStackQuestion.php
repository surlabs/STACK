<?php
declare(strict_types=1);

use classes\core\StackQuestion;
use classes\core\version\StackVersion;
use classes\platform\StackConfig;
use classes\platform\StackPlatform;
use classes\platform\ilias\StackPlatformIlias;

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
class assStackQuestion extends assQuestion implements iQuestionCondition, ilObjQuestionScoringAdjustable
{
    /**
     * @var ilPlugin Contains ilPlugin derived object
     * like ilLanguage
     */
    private ilPlugin $plugin;
    public function getPlugin(): ilPlugin
    {
        return $this->plugin;
    }
    public function setPlugin(ilPlugin $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @var array Contains the platform data
     */
    private array $platform_data = [];
    public function getPlatformData(): array
    {
        return $this->platform_data;
    }
    public function setPlatformData(array $platform_data): void
    {
        $this->platform_data = $platform_data;
    }

    /**
     * @var array Contains the question data
     */
    private array $stack_question_data = [];
    public function getStackQuestionData(): array
    {
        return $this->stack_question_data;
    }
    public function setStackQuestionData(array $stack_question_data): void
    {
        $this->stack_question_data = $stack_question_data;
    }

    /**
     * @var StackQuestion Contains the stack question object
     */
    private StackQuestion $stack_question;
    public function getStackQuestion(): StackQuestion
    {
        return $this->stack_question;
    }
    public function setStackQuestion(StackQuestion $stack_question): void
    {
        $this->stack_question = $stack_question;
    }

    /**
     * ILIAS STACK QUESTION CONSTRUCTOR
     * @param string $title
     * @param string $comment
     * @param string $author
     * @param int $owner
     * @param string $question
     */
    function __construct(
        string $title = "",
        string $comment = "",
        string $author = "",
        int    $owner = -1,
        string $question = ""
    )
    {
        parent::__construct($title, $comment, $author, $owner, $question);

        try{
            StackPlatform::initialize('ilias');
            //Set plugin object
            $this->setPlugin(StackPlatformIlias::getPlugin());
            //Get stored settings from the platform database
            $this->setPlatformData(StackConfig::getAll());
            //Get stack version from question_id
            $stack_version = new StackVersion($this->getId());
            //Creates and sets stack question object with minimal data
            $stack_question = new StackQuestion($stack_version);
            $this->setStackQuestion($stack_question);
        } catch (Exception $e) {
            //TODO ERROR MESSAGE
        }
    }

    //TEST
    //This methods are called from the test when the user is answering the question
    //and when the test is being evaluated or to show the results

    /**
     * Saves user data, evaluates the question and stores the results
     * in the tst_solutions table
     * @param int $active_id
     * @param null $pass
     * @param bool $authorized
     * @return bool
     */
    public function saveWorkingData(int $active_id, $pass = null, bool $authorized = true): bool
    {
        //TODO Esto tiene que rehacerse entero mágicamente
        //mientras tanto
        return true;
    }

    /**
     * Removes an existing solution without removing the variables
     * (specific for STACK question: don't delete seeds)
     * Called at resetting user answer in tests
     * @param int $activeId
     * @param int $pass
     * @return int
     */
    public function removeExistingSolutions(int $activeId, int $pass): int
    {
        //TODO Esto tiene que rehacerse entero mágicamente
        //mientras tanto
        return 1;
    }

    /*
     * Para obtener los valores de la solución de ilias se usa el metodo de la clase padre
     *
    public function getSolutionValues($active_id, $pass = null, bool $authorized = true): array
    */

    /**
     * Calculates points reached in Test
     * @param int $active_id
     * @param null $pass
     * @param bool $authorizedSolution
     * @param false $returndetails
     * @return float|int
     */
    public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false)
    {
        //TODO Esto tiene que rehacerse entero mágicamente
        //mientras tanto
        return 1;
    }

    //PREVIEW
    //This methods are called from the question pool/test preview
    /**
     * Saves user data, evaluates the question and stores the results
     *  in the tst_solutions table
     * @param ilAssQuestionPreviewSession $previewSession
     * @return void
     */
    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        //TODO Esto tiene que rehacerse entero mágicamente
    }

    /**
     * Returns the user response given per $_POST
     * Used in Question Preview
     * @return array
     */
    public function getSolutionSubmit(): array
    {
        //TODO se llama para la preview de la pregunta en el question pool
        return [];
    }

    /**
     * Calculate the points a user has reached in a preview session
     * @param ilAssQuestionPreviewSession $previewSession
     * @return float
     */
    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession): float
    {
        //TODO Esto tiene que rehacerse entero mágicamente
        //mientras tanto
        return 1.0;
    }

    //Save to DB
    //Authoring Interface

    /**
     * Saves a assStackQuestion object to the database
     *
     * @param string $original_id
     *
     */
    public function saveToDb(string $original_id = ""): void
    {
        //TODO Esto tiene que rehacerse entero mágicamente
    }

    /**
     * Saves the STACK related parameters of the questions
     * @return void
     */
    public function saveAdditionalQuestionDataToDb()
    {
        //TODO Esto tiene que rehacerse entero mágicamente
    }

    /**
     * Checks if question has minimum requirements
     * @return bool
     */
    function isComplete(): bool
    {
        //TODO Esto tiene que rehacerse entero mágicamente
        return true;
    }

    /**
     * Deletes the question from the DB
     * @param int $question_id
     */
    public function delete(int $question_id): void
    {
        //TODO Esto tiene que rehacerse entero mágicamente
    }


    //COPY, MOVE, DUPLICATE
    //These methods are called when transferring questions within the platform

    /**
     * Duplicates the question in the same directory
     * @param bool $for_test
     * @param string $title
     * @param string $author
     * @param string|int $owner
     * @param null $testObjId
     * @return int the duplicated question id
     */
    public function duplicate(bool $for_test = true, string $title = "", string $author = "", $owner = "", $testObjId = null): int
    {
        //TODO Esto tiene que rehacerse entero mágicamente
        //mientras tanto
        return 1;
    }

    /**
     * Copies an assStackQuestion object into the Clipboard
     *
     * @param int $target_questionpool_id
     * @param string $title
     *
     * @return int Id of the clone or nothing.
     */
    function copyObject(int $target_questionpool_id, string $title = ""): int
    {
        //TODO Esto tiene que rehacerse entero mágicamente
        //mientras tanto
        return 1;
    }

    /**
     * Copies the question into a question pool
     * @param $targetParentId
     * @param string $targetQuestionTitle
     * @return int
     */
    public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = ""): int
    {
        //TODO Esto tiene que rehacerse entero mágicamente
        //mientras tanto
        return 1;
    }

    //assQuestion

    /**
     * Gets all the data of an assStackQuestion from the DB
     * Called by assStackQuestionGUI Constructor
     * For new questions, loads the standard values from xqcas_configuration.
     *
     * @param integer $question_id A unique key which defines the question in the database
     */
    public function loadFromDb(int $question_id): void
    {
        //TODO 300 lineas fuera, mucho de esto ahora se hace en StackQuestion
    }

    //Import and Export

    /**
     * Creates a question from a QTI file
     *
     * Receives parameters from a QTI parser and creates a valid ILIAS question object
     *
     * @param object $item The QTI item object
     * @param integer $questionpool_id The id of the parent questionpool
     * @param int|null $tst_id The id of the parent test if the question is part of a test
     * @param object $tst_object A reference to the parent test object
     * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
     * @param array $import_mapping An array containing references to included ILIAS objects
     * @param array $solutionhints
     * @return array
     */
    public function fromXML(
        $item,
        int $questionpool_id,
        ?int $tst_id, &$tst_object,
        int &$question_counter,
        array $import_mapping,
        array &$solutionhints = []
    ): array
    {
        //TODO Import from XML QTI ILIAS Valid
        return [];
    }

    /**
     * Returns a QTI xml representation of the question and sets the internal
     * domxml variable with the DOM XML representation of the QTI xml representation
     * @param bool $a_include_header
     * @param bool $a_include_binary
     * @param bool $a_shuffle
     * @param bool $test_output
     * @param bool $force_image_references
     * @return string The QTI xml representation of the question
     */
    public function toXML(
        bool $a_include_header = true,
        bool $a_include_binary = true,
        bool $a_shuffle = false,
        bool $test_output = false,
        bool $force_image_references = false
    ): string
    {
        //TODO Export to XML QTI ILIAS Valid
        return '';
    }

    //Question Points



    /* ILIAS OVERWRITTEN METHODS END */

    /* ILIAS SPECIFIC METHODS BEGIN */

    /**
     * Evaluates the question
     * @param array $user_response
     * @return bool
     */
    public function evaluateQuestion(array $user_response): bool
    {
        global $tpl;

        try {

            $evaluation_data = array();
            $total_weight = 0.0;

            foreach ($this->prts as $prt_name => $prt) {

                if (!$this->hasNecessaryPrtInputs($prt, $user_response, true)) {
                    global $tpl;
                    $tpl->setOnScreenMessage('failure', 'The PRT ' . $prt_name . ' wasnt evaluated because not all inputs were answered.', true);
                    return false;
                }

                //User answers for PRT Evaluation
                $prt_input = $this->getPrtInput($prt_name, $user_response, true);

                //PRT Results
                if (is_array($prt_input) && !empty($prt_input)) {
                    $evaluation_data['prts'][$prt_name] = $this->prts[$prt_name]->evaluate_response(
                        $this->session, $this->options, $prt_input, $this->seed
                    );
                } else {
                    $evaluation_data['prts'][$prt_name] = new stack_potentialresponse_tree_state(
                        $this->prts[$prt_name]->get_value(), false, 0, 0
                    );
                }

                //Sum weights
                $total_weight = $total_weight + (float)$evaluation_data['prts'][$prt_name]->_weight;

                //Accept valid
                //if ($evaluation_data['prts'][$prt_name]->_valid) {
                //}
            }
            $points_obtained = 0.0;

            //Calculate Points per PRT
            foreach (array_keys($this->prts) as $prt_name) {

                //Calculate prt value in points
                if ($total_weight != 0.0) {
                    $relative_prt_weight_in_points = (((float)$evaluation_data['prts'][$prt_name]->_weight / $total_weight) * $this->getMaximumPoints());
                } else {
                    $tpl->setOnScreenMessage('failure', "PRT: " . $prt_name . " Value invalid", true);
                    $relative_prt_weight_in_points = 0.0;
                }

                $relative_prt_points = ((float)$evaluation_data['prts'][$prt_name]->_score * $relative_prt_weight_in_points);

                //PRT Weight in Points
                $evaluation_data['points'][$prt_name]['prt_weight'] = $relative_prt_weight_in_points;

                //PRT Received points
                $evaluation_data['points'][$prt_name]['prt_points'] = $relative_prt_points;

                //Set Feedback type
                if ($relative_prt_points <= 0.0) {
                    $evaluation_data['points'][$prt_name]['status'] = 'incorrect';
                } elseif ($relative_prt_points == $relative_prt_weight_in_points) {
                    $evaluation_data['points'][$prt_name]['status'] = 'correct';
                } elseif ($relative_prt_points < $relative_prt_weight_in_points) {
                    $evaluation_data['points'][$prt_name]['status'] = 'partially_correct';
                } else {
                    $evaluation_data['points'][$prt_name]['status'] = null;
                    $tpl->setOnScreenMessage('failure', 'Error calculating PRT points in evaluateQuestion', true);
                }

                //Count points
                $points_obtained = $points_obtained + $relative_prt_points;
            }

            if ($points_obtained > $this->getMaximumPoints()) {
                $tpl->setOnScreenMessage('failure', 'Error calculating points in evaluateQuestion, trying to give more than existing, set to Max Points.', true);
            }

            //Manage Inputs and Validation
            $evaluation_data['inputs']['states'] = $this->getInputStates();
            $evaluation_data['inputs']['validation'] = array();

            foreach ($evaluation_data['inputs']['states'] as $input_name => $input) {
                if (isset($evaluation_data['inputs']['states'][$input_name]) and is_a($evaluation_data['inputs']['states'][$input_name], 'stack_input_state')) {
                    $evaluation_data['inputs']['validation'][$input_name] = $this->inputs[$input_name]->render_validation($input, $input_name);
                }
            }

            //Mark as evaluated
            $this->setEvaluation($evaluation_data);

        } catch (stack_exception $e) {

            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);

        }

        return true;
    }

    /**
     * This function loads the standard values from xqcas_configuration to the question object
     * @throws stack_exception
     */
    public function loadStandardQuestion()
    {
        $standard_question = array();

        //load options
        require_once __DIR__ . '/model/configuration/class.assStackQuestionConfig.php';
        $standard_options = assStackQuestionConfig::_getStoredSettings('options');
        $options_array = array();

        $options_array['simplify'] = ((int)$standard_options['options_question_simplify']);
        $options_array['assumepos'] = ((int)$standard_options['options_assume_positive']);
        $options_array['assumereal'] = ((int)$standard_options['options_assume_real']);
        $options_array['multiplicationsign'] = ($standard_options['options_multiplication_sign']);
        $options_array['sqrtsign'] = ((int)$standard_options['options_sqrt_sign']);
        $options_array['complexno'] = ($standard_options['options_complex_numbers']);
        $options_array['inversetrig'] = ($standard_options['options_inverse_trigonometric']);
        $options_array['matrixparens'] = ($standard_options['options_matrix_parents']);
        $options_array['logicsymbol'] = ($standard_options['options_logic_symbol']);

        try {
            $options = new stack_options($options_array);

            //Set Options
            $this->options = $options;
        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
        }

        $this->question_variables = '';
        $this->question_note = '';

        //We add the feedback for the first prt to the specific feedback section.
        $this->prt_correct = $standard_options['options_prt_correct'];
        $this->prt_correct_format = 1;
        $this->prt_partially_correct = $standard_options['options_prt_partially_correct'];
        $this->prt_partially_correct_format = 1;
        $this->prt_incorrect = $standard_options['options_prt_incorrect'];
        $this->prt_incorrect_format = 1;

        $this->variants_selection_seed = '';

        //Stack version TODO CONFIG
        $this->stack_version = '2021120900';

        //load standard input
        $this->loadStandardInput('ans1');
        $this->setQuestion('[[input:ans1]] [[validation:ans1]]');

        //load standard PRT
        $this->loadStandardPRT('prt1');
        $this->specific_feedback = ('[[feedback:prt1]]');
        $this->specific_feedback_format = 1;

        //load seeds
        $this->deployed_seeds = array();

        $this->setPoints(1);

        //load extra info
        $this->general_feedback = '';
        $this->penalty = 0.0;
        $this->hidden = false;
    }

    /**
     * @throws stack_exception
     */
    public function loadStandardInput(string $input_name)
    {
        //Ensure input doesn't exists
        if (!isset($this->inputs[$input_name])) {
            //load standard input
            $standard_input = assStackQuestionConfig::_getStoredSettings('inputs');

            $required_parameters = stack_input_factory::get_parameters_used();

            $all_parameters = array(
                'boxWidth' => $standard_input['input_box_size'],
                'strictSyntax' => $standard_input['input_strict_syntax'],
                'insertStars' => $standard_input['input_insert_stars'],
                'syntaxHint' => $standard_input['input_syntax_hint'],
                'syntaxAttribute' => $standard_input['input_syntax_attribute'],
                'forbidWords' => $standard_input['input_forbidden_words'],
                'allowWords' => $standard_input['input_allow_words'],
                'forbidFloats' => $standard_input['input_forbid_float'],
                'lowestTerms' => $standard_input['input_require_lowest_terms'],
                'sameType' => $standard_input['input_check_answer_type'],
                'mustVerify' => $standard_input['input_must_verify'],
                'showValidation' => $standard_input['input_show_validation'],
                'options' => $standard_input['input_extra_options'],
            );

            $parameters = array();
            foreach ($required_parameters[$standard_input['input_type']] as $parameter_name) {
                if ($parameter_name == 'inputType') {
                    continue;
                }
                $parameters[$parameter_name] = $all_parameters[$parameter_name];
            }

            //Create Input
            $input = stack_input_factory::make($standard_input['input_type'], $input_name, 1, $this->options, $parameters);
            //Load input to the question.
            $this->inputs[$input_name] = $input;
        } else {
            global $tpl;
            $tpl->setOnScreenMessage('info', 'The new input ' . $input_name . ' was already created', true);
        }
    }

    /**
     * @param string $prt_name
     * @param bool $return_standard_node
     * @return void || stack_potentialresponse_node
     */
    public function loadStandardPRT(string $prt_name, bool $return_standard_node = false)
    {
        //load PRTs and PRT nodes
        $standard_prt = assStackQuestionConfig::_getStoredSettings('prts');

        //Values
        $total_value = 1;

        //in ILIAS all attempts are graded
        $grade_all = true;

        if ($standard_prt && $grade_all && $total_value < 0.0000001) {
            try {
                throw new stack_exception('There is an error authoring your question. ' .
                    'The $totalvalue, the marks available for the question, must be positive in question ' .
                    $this->getTitle());
            } catch (stack_exception $e) {
                global $tpl;
                $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            }
        }

        //get PRT and PRT Nodes from DB

        $nodes = array();

        $sans = stack_ast_container::make_from_teacher_source('PRSANS1:ans1', '', new stack_cas_security());
        $tans = stack_ast_container::make_from_teacher_source('PRTANS1:1', '', new stack_cas_security());

        //Penalties management, penalties are not an ILIAS Feature
        if (is_null($standard_prt['prt_neg_penalty']) || $standard_prt['prt_neg_penalty'] === '') {
            $false_penalty = 0;
        } else {
            $false_penalty = $standard_prt['prt_neg_penalty'];
        }

        if (is_null(($standard_prt['prt_pos_penalty']) || $standard_prt['prt_pos_penalty'] === '')) {
            $true_penalty = 0;
        } else {
            $true_penalty = $standard_prt['prt_pos_penalty'];
        }

        try {
            //Create Node and add it to the
            $node = new stack_potentialresponse_node($sans, $tans, $standard_prt['prt_node_answer_test'], $standard_prt['prt_node_options'], (bool)$standard_prt['prt_node_quiet'], '', 1, 'ans1', '1');

            $node->add_branch(0, $standard_prt['prt_neg_mod'], $standard_prt['prt_neg_score'], $false_penalty, -1, '', 1, $standard_prt['prt_neg_answernote']);
            $node->add_branch(1, $standard_prt['prt_pos_mod'], $standard_prt['prt_pos_score'], $true_penalty, -1, '', 1, $standard_prt['prt_pos_answernote']);

            if ($return_standard_node) {
                return $node;
            }

            $nodes[1] = $node;
        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
        }

        $feedback_variables = null;

        $prt_value = 1.0;
        try {
            $this->prts[$prt_name] = new stack_potentialresponse_tree($prt_name, '', (bool)$standard_prt['prt_simplify'], $prt_value, $feedback_variables, $nodes, '1', 1);
        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
        }
    }

    /* ILIAS SPECIFIC METHODS END */

    /* STACK CORE METHODS BEGIN */

    /**
     * Make sure the cache is valid for the current response. If not, clear it.
     *
     * @param array $response the response.
     * @param bool|null $accept_valid if this is true, then we will grade things even
     * if the corresponding inputs are only VALID, and not SCORE.
     */
    public function validateCache(array $response, bool $accept_valid = null)
    {
        if (is_null($this->getLastResponse())) {
            $this->setLastResponse($response);
            $this->setLastAcceptValid($accept_valid);
            return;
        }

        // We really need the PHP === here, as "0.040" == "0.04", even as strings.
        // See https://stackoverflow.com/questions/80646/ for details.
        if ($this->getLastResponse() === $response && ($this->getLastAcceptValid() === null || $accept_valid === null || $this->getLastAcceptValid() === $accept_valid)) {
            if ($this->getLastAcceptValid() === null) {
                $this->setLastAcceptValid($accept_valid);
            }
            return; // Cache is good.
        }

        // Clear the cache.
        $this->setLastResponse($response);
        $this->setLastAcceptValid($accept_valid);
        $this->setInputStates(array());
        $this->setPrtResults(array());
    }

    /* make_behaviour() not required as behaviours are only Moodle relevant */

    /**
     * start_attempt(question_attempt_step $step, $variant) method
     * Transferred to ILIAS as questionInitialisation();
     * @param int|null $variant
     * @param bool $force_variant
     * @param bool $deployed_seeds_view true only in authoring mode / deployed seeds view
     */
    public function questionInitialisation(?int $variant, bool $force_variant = false, bool $deployed_seeds_view = false)
    {
        //Initialize Options
        if (!is_a($this->options, 'stack_options')) {
            $this->options = new stack_options();
        }

        // @codingStandardsIgnoreStart
        // Work out the right seed to use.
        if (is_null($this->seed) or $deployed_seeds_view) {
            if ($force_variant) {
                $this->seed = $variant;
            } else if (!$this->hasRandomVariants()) {
                // Randomisation not used.
                $this->seed = 1;
            } else if (!empty($this->deployed_seeds)) {
                // Question has a fixed number of variants.
                $this->seed = $this->deployed_seeds[$variant - 1] + 0;
                // Don't know why this is coming out as a string. + 0 converts to int.
            } else {
                // This question uses completely free randomisation.
                $this->seed = $variant;
            }
        }

        $this->initialiseQuestionFromSeed();
    }

    /**
     * INITIALISATION MAIN METHOD
     * initialise_question_from_seed() Method in Moodle
     * Once we know the random seed, we can initialise all the other parts of the question.
     */
    public function initialiseQuestionFromSeed()
    {
        try {
            // Build up the question session out of all the bits that need to go into it.
            // 1. question variables.
            $session = new stack_cas_session2([], $this->options, $this->seed);
            if ($this->getCached('preamble-qv') !== null) {
                $session->add_statement(new stack_secure_loader($this->getCached('preamble-qv'), 'preamble'));
            }
            // Context variables should be first.
            if ($this->getCached('contextvariables-qv') !== null) {
                $session->add_statement(new stack_secure_loader($this->getCached('contextvariables-qv'), 'qv'));
            }
            if ($this->getCached('statement-qv') !== null) {
                $session->add_statement(new stack_secure_loader($this->getCached('statement-qv'), 'qv'));
            }

            // Construct the security object.
            $units = (boolean)$this->getCached('units');

            // If we have units we might as well include the units declaration in the session.
            // To simplify authors work and remove the need to call that long function.
            // TODO: Maybe add this to the preamble to save lines, but for now documented here.
            if ($units) {
                $session->add_statement(stack_ast_container_silent::make_from_teacher_source('stack_unit_si_declare(true)', 'automatic unit declaration'), false);
            }
            // Note that at this phase the security object has no "words".
            // The student's answer may not contain any of the variable names with which
            // the teacher has defined question variables. Otherwise when it is evaluated
            // in a PRT, the student's answer will take these values.   If the teacher defines
            // 'ta' to be the answer, the student could type in 'ta'!  We forbid this.

            // TODO: shouldn't we also protect variables used in PRT logic? Feedback vars
            // and so on?
            $forbidden_keys = array();
            if ($this->getCached('forbiddenkeys') !== null) {
                $forbidden_keys = $this->getCached('forbiddenkeys');
            }
            $this->setSecurity(new stack_cas_security($units, '', '', $forbidden_keys));

            // Add the context to the security, needs some unpacking of the cached.
            if ($this->getCached('security-context') === null || count($this->getCached('security-context')) === 0) {
                $this->getSecurity()->set_context([]);
            } else {
                // Combine to a single statement to keep the parser cache small.
                // We need to turn a set of code-fragments into ASTs.
                $tmp = '[';
                foreach ($this->getCached('security-context') as $key => $values) {
                    $tmp .= '[';
                    $tmp .= implode(',', $values);
                    $tmp .= '],';
                }
                $tmp = mb_substr($tmp, 0, -1);
                $tmp .= ']';
                $ast = maxima_parser_utils::parse($tmp)->items[0]->statement->items;
                $ctx = [];
                $i = 0;
                foreach ($this->getCached('security-context') as $key => $values) {
                    $ctx[$key] = [];
                    $j = 0;
                    foreach ($values as $k) {
                        $ctx[$key][$k] = $ast[$i]->items[$j];
                        $j = $j + 1;
                        if ($k === -1 || $k === -2) {
                            $ctx[$key][$k] = $k;
                        }
                    }
                    $i = $i + 1;
                }
                $this->getSecurity()->set_context($ctx);
            }

            // The session to keep. Note we do not need to reinstantiate the teachers answers.
            $session_to_keep = new stack_cas_session2($session->get_session(), $this->options, $this->seed);

            // 2. correct answer for all inputs.
            foreach ($this->inputs as $name => $input) {
                $cs = stack_ast_container::make_from_teacher_source($input->get_teacher_answer(), '', $this->getSecurity());
                $session->add_statement($cs);
                $this->setTas($cs, $name);
            }

            // 3. CAS bits inside the question text.
            //Get the question String of the assQuestion object
            $question_text = $this->prepareCASText($this->getQuestion(), $session);

            // 4. CAS bits inside the specific feedback.
            $feedback_text = $this->prepareCASText($this->specific_feedback, $session);

            // 5. CAS bits inside the question note.
            $note_text = $this->prepareCASText($this->question_note, $session);

            // 6. The standard PRT feedback.
            $prt_correct = $this->prepareCASText($this->prt_correct, $session);
            $prt_partially_correct = $this->prepareCASText($this->prt_partially_correct, $session);
            $prt_incorrect = $this->prepareCASText($this->prt_incorrect, $session);

            // 7. The General feedback.
            if (isset($this->general_feedback)) {
                $general_feedback = $this->prepareCASText($this->general_feedback, $session);
            } else {
                $general_feedback = $this->prepareCASText('', $session);
            }

            // Now instantiate the session.
            if ($session->get_valid()) {
                try {
                    $session->instantiate();
                } catch (Exception $e) {
                    global $tpl;
                    //Maxima is not running, show information to the user.
                    $tpl->setOnScreenMessage('failure', $this->getPlugin()->txt('hc_connection_status_display_error'), true);
                }
            }

            if ($session->get_errors()) {
                // In previous versions we threw an exception here.
                // Upgrade and import stops errors being caught during validation when the question was edited or deployed.
                // This breaks bulk testing in a nasty way.
                $this->runtime_errors[$session->get_errors(true)] = true;
            }

            // Finally, store only those values really needed for later.
            //#35924 $question_text->get_display_castext() being null
            if (is_string($question_text->get_display_castext())) {
                $question_text_text = $question_text->get_display_castext();
            } else {
                $question_text_text = "Error Rendering Text, question might be malformed";
            }

            $this->question_text_instantiated = assStackQuestionUtils::_getLatex($question_text_text);

            if ($question_text->get_errors()) {
                $s = stack_string('runtimefielderr', array('field' => stack_string('questiontext'), 'err' => $question_text->get_errors()));
                $this->runtime_errors[$s] = true;
            }
            $this->specific_feedback_instantiated = assStackQuestionUtils::_getLatex($feedback_text->get_display_castext());
            if ($feedback_text->get_errors()) {
                $s = stack_string('runtimefielderr', array('field' => stack_string('specificfeedback'), 'err' => $feedback_text->get_errors()));
                $this->runtime_errors[$s] = true;
            }
            $this->question_note_instantiated = assStackQuestionUtils::_getLatex($note_text->get_display_castext());
            if ($note_text->get_errors()) {
                $s = stack_string('runtimefielderr', array('field' => stack_string('questionnote'), 'err' => $note_text->get_errors()));
                $this->runtime_errors[$s] = true;
            }


            $this->prt_correct_instantiated = assStackQuestionUtils::_getLatex($prt_correct->get_display_castext());
            $this->prt_partially_correct_instantiated = assStackQuestionUtils::_getLatex($prt_partially_correct->get_display_castext());
            $this->prt_incorrect_instantiated = assStackQuestionUtils::_getLatex($prt_incorrect->get_display_castext());
            $this->general_feedback = assStackQuestionUtils::_getLatex($general_feedback->get_display_castext());

            $this->session = $session_to_keep;
            $this->addQuestionVarsToSession($session);

            if ($session_to_keep->get_errors()) {
                $s = stack_string('runtimefielderr', array('field' => stack_string('questionvariables'), 'err' => $session_to_keep->get_errors(true)));
                $this->runtime_errors[$s] = true;
            }

            if ($this->getCached('contextvariables-qv') !== null) {
                foreach ($this->prts as $prt) {
                    $prt->add_contextsession(new stack_secure_loader($this->getCached('contextvariables-qv'), 'qv'));
                }
            }

            // Allow inputs to update themselves based on the model answers.
            $this->adaptInputs();
            if ($this->runtime_errors) {
                // It is quite possible that questions will, legitimately, throw some kind of error.
                // For example, if one of the question variables is 1/0.
                // This should not be a show stopper.
                if (trim($this->getQuestion()) !== '' && trim($this->question_text_instantiated) === '') {
                    // Something has gone wrong here, and the student will be shown nothing.
                    $s = html_writer::tag('span', stack_string('runtimeerror'), array('class' => 'stackruntimeerrror'));
                    $error_message = '';
                    foreach ($this->runtime_errors as $key => $val) {
                        $error_message .= html_writer::tag('li', $key);
                    }
                    $s .= html_writer::tag('ul', $error_message);
                    //$this->question_text_instantiated .= $s;
                }
            }

            //Question has been properly instantiated
            $this->setInstantiated(true);

        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
        }
    }

    /**
     * Helper method used by initialise_question_from_seed.
     * And Render Specific PRT Feedback (not initialised by default)
     * prepare_cas_text($text, $session) method from Moodle
     * @param string $text a textual part of the question that is CAS text.
     * @param stack_cas_session2 $session the question's CAS session.
     * @return stack_cas_text|false the CAS text version of $text.
     */
    public function prepareCASText(string $text, stack_cas_session2 $session): stack_cas_text
    {
        try {
            $cas_text = new stack_cas_text($text, $session, $this->seed);
            if ($cas_text->get_errors()) {
                $this->runtime_errors[$cas_text->get_errors()] = true;
            }
            return $cas_text;
        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            return false;
        }
    }

    /**
     * adapt_inputs() method in Moodle
     * Give all the input elements a chance to configure themselves given the
     * teacher's model answers.
     */
    protected function adaptInputs()
    {
        try {
            foreach ($this->inputs as $name => $input) {
                // TODO: again should we give the whole thing to the input.
                $teacher_answer = '';
                if ($this->getTas($name)->is_correctly_evaluated()) {
                    $teacher_answer = $this->getTas($name)->get_value();
                }
                $input->adapt_to_model_answer($teacher_answer);
                if ($this->getCached('contextvariables-qv') !== null) {
                    $input->add_contextsession(new stack_secure_loader($this->getCached('contextvariables-qv'), 'qv'));
                }
            }
        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
        }
    }

    /**
     * get_hint_castext(question_hint $hint) from Moodle
     * Get the castext for a hint, instantiated within the question's session.
     * @param string $hint the hint.
     * @return stack_cas_text|false the castext.
     */
    public function getHintCASText(string $hint): stack_cas_text
    {
        try {
            $hint_text = new stack_cas_text($hint, $this->session, $this->seed);
            if ($hint_text->get_errors()) {
                $this->runtime_errors[$hint_text->get_errors()] = true;
            }
            return $hint_text;
        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            return false;
        }
    }

    /**
     * format_correct_response($qa) in Moodle
     * We need to make sure the inputs are displayed in the order in which they
     * occur in the question text. This is not necessarily the order in which they
     * are listed in the array $this->inputs.
     * @return false|stack_cas_text
     */
    public function formatCorrectResponse()
    {
        try {
            $feedback = '';
            $inputs = stack_utils::extract_placeholders($this->question_text_instantiated, 'input');
            foreach ($inputs as $name) {
                $input = $this->inputs[$name];
                $feedback .= html_writer::tag('p', $input->get_teacher_answer_display($this->getTas($name)->get_dispvalue(), $this->getTas($name)->get_latex()));
            }
            //TODO
            //return stack_ouput_castext($feedback);

            return new stack_cas_text($feedback);
        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            return false;
        }
    }

    /* get_expected_data() not required as it is only Moodle relevant */

    /* get_question_summary() not required as it is only Moodle relevant */

    /* summarise_response(array $response) not required as it is only Moodle relevant */
    //TODO FEATURE

    /* summarise_response_data(array $response) not required as it is only Moodle relevant */
    //TODO FEATURE

    /**
     * get_correct_response() in Moodle
     * @return array|string
     */
    public function getCorrectResponse()
    {
        $teacher_answer = array();
        foreach ($this->inputs as $name => $input) {
            $teacher_answer = array_merge($teacher_answer, $input->get_correct_response($this->getTas($name)->get_dispvalue()));
        }
        return $teacher_answer;
    }

    /* is_same_response(array $prevresponse, array $newresponse) not required as it is only Moodle relevant */
    //TODO FEATURE?

    /* is_same_response_for_part($index, array $prevresponse, array $newresponse) not required as it is only Moodle relevant */
    //TODO FEATURE?

    /**
     * get_input_state($name, $response, $rawinput=false) in Moodle
     * Get the results of validating one of the input elements.
     * @param string $name the name of one of the input elements.
     * @param array $response the response, in Maxima format.
     * @param bool $raw_input the response in raw form. Needs converting to Maxima format by the input.
     * @return stack_input_state|bool the result of calling validate_student_response() on the input.
     */
    public function getInputState(string $name, array $response, bool $raw_input = false, bool $sets_question_object = true)
    {
        try {
            $this->validateCache($response);

            if (array_key_exists($name, $this->getInputStates())) {
                return $this->getInputStates($name);
            }

            // TODO: we should probably give the whole ast_container to the input.
            // Direct access to LaTeX and the AST might be handy.
            $teacher_answer = '';

            //Get Teacher answer
            if (array_key_exists($name, $this->getTas())) {
                if ($this->getTas($name)->is_correctly_evaluated()) {
                    $teacher_answer = $this->getTas($name);
                }
            }

            //Validate student response
            if (array_key_exists($name, $this->inputs)) {
                if ($sets_question_object) {
                    $this->setInputStates($this->inputs[$name]->validate_student_response($response, $this->options, $teacher_answer, $this->security, false), $name);
                    return $this->getInputStates($name);
                } else {
                    return $this->inputs[$name]->validate_student_response($response, $this->options, $teacher_answer, $this->security, false);
                }
            }

            return true;

        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            return false;
        }
    }

    /**
     * is_any_input_blank(array $response) in Moodle
     * @param array $response the current response being processed.
     * @return boolean whether any of the inputs are blank.
     */
    public function isAnyInputBlank(array $response): bool
    {
        foreach ($this->inputs as $name => $input) {
            if (stack_input::BLANK == $this->getInputState($name, $response)->status) {
                return true;
            }
        }
        return false;
    }

    /**
     * is_any_part_invalid(array $response) in Moodle
     * @param array $response
     * @return bool
     */
    public function isAnyPartInvalid(array $response): bool
    {
        // Invalid if any input is invalid, ...
        foreach ($this->inputs as $name => $input) {
            if (stack_input::INVALID == $this->getInputState($name, $response)->status) {
                //$this->runtime_errors[] = $this->getInputState($name, $response)->errors;
                return true;
            }
        }

        // ... or any PRT gives an error.
        foreach ($this->prts as $index => $prt) {
            $result = $this->getPrtResult($index, $response, false);
            if ($result->errors) {
                //$this->runtime_errors[] = $result->errors;
                return true;
            }
        }

        return false;
    }

    /* is_complete_response(array $response) not required as it is only Moodle relevant */
    //TODO FEATURE?

    /* is_gradable_response(array $response) not required as it is only Moodle relevant */
    //TODO FEATURE?

    /**
     * get_validation_error(array $response)
     * @param array $response
     * @return array|mixed|string|string[]
     */
    public function getValidationError(array $response)
    {
        if ($this->isAnyPartInvalid($response)) {
            // There will already be a more specific validation error displayed.
            //TODO text variable
            $error_message = '';
            foreach ($this->runtime_errors as $error) {
                $error_message .= $error . '</br>';
            }
            return $error_message;

        } else if ($this->isAnyInputBlank($response)) {
            return stack_string('pleaseananswerallparts');
        }
    }

    /* grade_response(array $response) not required as it is only Moodle relevant */
    //TODO FEATURE MANUAL GRADING

    /**
     * @param $current_prt_name
     * @param $last_input
     * @param $prt_input
     * @return bool
     */
    public function isSamePRTInput($current_prt_name, $last_input, $prt_input): bool
    {
        //Not yet cached, this method has been adapted
        foreach ($this->getCached('required')[$this->prts[$current_prt_name]->get_name()] as $name) {
            if (!assStackQuestionUtils::arrays_same_at_key_missing_is_blank($last_input, $prt_input, $name)) {
                return false;
            }
        }
        return true;
    }

    /* get_parts_and_weights() not required as it is only Moodle relevant */
    //TODO FEATURE

    /* grade_parts_that_can_be_graded(array $response, array $lastgradedresponses, $finalsubmit) not required as it is only Moodle relevant */

    /* compute_final_grade($responses, $totaltries) not required as it is only Moodle relevant */

    /**
     * has_necessary_prt_inputs(stack_potentialresponse_tree $prt, $response, $acceptvalid)
     * Do we have all the necessary inputs to execute one of the potential response trees?
     * @param stack_potentialresponse_tree $prt the tree in question.
     * @param array $response the response.
     * @param bool $accept_valid if this is true, then we will grade things even if the corresponding inputs are only VALID, and not SCORE.
     * @return bool can this PRT be executed for that response.
     */
    public function hasNecessaryPrtInputs(stack_potentialresponse_tree $prt, array $response, bool $accept_valid): bool
    {
        // Some kind of time-time error in the question, so bail here.
        if ($this->getCached('required') === null) {
            return false;
        }

        foreach ($this->getCached('required')[$prt->get_name()] as $name) {
            $this->getInputState($name, $response);
        }

        return true;
    }

    /**
     * can_execute_prt(stack_potentialresponse_tree $prt, $response, $acceptvalid) in Moodle
     * Do we have all the necessary inputs to execute one of the potential response trees?
     * @param stack_potentialresponse_tree $prt the tree in question.
     * @param array $response the response.
     * @param bool $accept_valid if this is true, then we will grade things even if the corresponding inputs are only VALID, and not SCORE.
     * @return bool can this PRT be executed for that response.
     */
    protected function canExecutePrt(stack_potentialresponse_tree $prt, array $response, bool $accept_valid): bool
    {
        // The only way to find out is to actually try evaluating it. This calls
        // has_necessary_prt_inputs, and then does the computation, which ensures
        // there are no CAS errors.

        $result = $this->getPrtResult($prt->get_name(), $response, $accept_valid);
        return null !== $result->valid && !$result->errors;
    }

    /**
     * get_prt_input($index, $response, $acceptvalid) in Moodle
     * Extract the input for a given PRT from a full response.
     * @param string $index the name of the PRT.
     * @param array $response the full response data.
     * @param bool $accept_valid if this is true, then we will grade things even if the corresponding inputs are only VALID, and not SCORE.
     * @return array|false
     */
    protected function getPrtInput(string $index, array $response, bool $accept_valid)
    {
        $input_states = array();
        try {
            if (!array_key_exists($index, $this->prts)) {
                $msg = '"' . $this->getTitle() . '" (' . $this->getId() . ') seed = ' . $this->seed . ' and STACK version = ' . $this->stack_version;
                throw new stack_exception ("get_prt_input called for PRT " . $index . " which does not exist in question " . $msg);
            }
            $prt = $this->prts[$index];
            $prt_input = array();
            foreach ($this->getCached('required')[$prt->get_name()] as $name) {

                $state = $this->getInputState($name, $response);

                $input_states[$name] = $state;
                if (stack_input::SCORE == $state->status || ($accept_valid && stack_input::VALID == $state->status)) {
                    $val = $state->contentsmodified;
                    if ($state->simp === true) {
                        $val = 'ev(' . $val . ',simp)';
                    }
                    $prt_input[$name] = $val;
                }
            }

            return $prt_input;
        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            return false;
        }
    }

    /**
     * get_prt_result($index, $response, $acceptvalid) in Moodle
     * Evaluate a PRT for a particular response.
     * @param string $prt_name the name of the PRT to evaluate.
     * @param array $response the response to process.
     * @param bool $accept_valid if this is true, then we will grade things even if the corresponding inputs are only VALID, and not SCORE.
     * @return stack_potentialresponse_tree_state|string
     */
    public function getPrtResult(string $prt_name, array $response, bool $accept_valid)
    {
        try {
            $this->validateCache($response, $accept_valid);

            if (array_key_exists($prt_name, $this->getPrtResults())) {
                return $this->getPrtResults($prt_name);
            }

            // We can end up with a null prt at this point if we have question tests for a deleted PRT.
            if (!array_key_exists($prt_name, $this->prts)) {
                // Bail here with an empty state to avoid a later exception which prevents question test editing.
                return new stack_potentialresponse_tree_state(null, null, null, null);
            }
            $prt = $this->prts[$prt_name];

            if (!$this->hasNecessaryPrtInputs($prt, $response, $accept_valid)) {
                $this->setPrtResults(new stack_potentialresponse_tree_state($prt->get_value(), null, null, null), $prt_name);
                return $this->getPrtResults($prt_name);
            }

            //EVALUATE PRT
            $prt_input = $this->getPrtInput($prt_name, $response, $accept_valid);

            $this->setPrtResults($prt->evaluate_response($this->session, $this->options, $prt_input, $this->seed), $prt_name);

            return $this->getPrtResults($prt_name);
        } catch (stack_exception $e) {
            return $e->getMessage();
        }
    }

    /* set_value_in_nested_arrays($arrayorscalar, $newvalue) not required as it is only Moodle relevant */

    /* setup_fake_feedback_and_input_validation() not required as it is only Moodle relevant */

    /**
     * has_random_variants in Moodle
     * @return bool whether this question uses randomisation.
     */
    public function hasRandomVariants(): bool
    {
        if (isset($this->question_variables)) {
            return preg_match('~\brand~', $this->question_variables) || preg_match('~\bmultiselqn~', $this->question_variables);
        } else {
            return false;
        }
    }

    /**
     * get_num_variants() in Moodle
     * @return int
     */
    public function getNumVariants(): int
    {
        if (!$this->hasRandomVariants()) {
            // This question does not use randomisation. Only declare one variant.
            return 1;
        }

        if (!empty($this->deployed_seeds)) {
            // Fixed number of deployed variants, declare that.
            return count($this->deployed_seeds);
        }

        // Random question without fixed variants.
        return 1000000;
    }

    /* check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) not required as it is only Moodle relevant */
    //TODO FEATURE ROLES

    /* get_context() not required as it is only Moodle relevant */

    /* has_question_capability($type) not required as it is only Moodle relevant */

    /* user_can_view() not required as it is only Moodle relevant */

    /* user_can_edit() not required as it is only Moodle relevant */

    /* get_question_session_keyval_representation() not required as it is only Moodle relevant */
    //TODO FEATURE SHOW QUESTION VARIABLES USED IN TEST RUN

    /**
     * add_question_vars_to_session(stack_cas_session2 $session) in Moodle
     * Add all the question variables to a give CAS session. This can be used to
     * initialise that session, so expressions can be evaluated in the context of
     * the question variables.
     * @param stack_cas_session2 $session the CAS session to add the question variables to.
     */
    public function addQuestionVarsToSession(stack_cas_session2 $session)
    {
        // Question vars will always get added to the beginning of whatever session you give.
        $this->session->prepend_to_session($session);
    }

    /**
     * get_ta_for_input(string $vname) in Moodle
     * Enable the renderer to access the teacher's answer in the session.
     * TODO: should we give the whole thing?
     * @param string $input_name
     */
    public function getTeacherAnswerForInput(string $input_name): string
    {
        if (array_key_exists($input_name, $this->getTas())) {
            return $this->getTas($input_name)->get_value();
        } else {
            return '';
        }

    }

    /* classify_response(array $response) not required as it is only Moodle relevant */
    //TODO FEATURE CLASSIFY RESPONSE

    /**
     * deploy_variant($seed) in Moodle
     * Deploy a variant of this question.
     * @param int $seed the seed to deploy.
     */
    public function deployVariant(int $seed)
    {
        //TODO COPY
    }

    /**
     * undeploy_variant($questionid, $seed) in Moodle
     * Deploy a variant of this question.
     * @param int $question_id
     * @param int $seed
     */
    public function undeployVariant(int $question_id, int $seed)
    {
        //TODO COPY
    }

    /* validate_against_stackversion() not required as it is only Moodle relevant */
    //TODO FEATURE BULK TEST

    /* validate_warnings($errors = false) not required as it is only Moodle relevant */
    //TODO FEATURE BULK TEST

    /**
     * Cache management.
     * get_cached(string $key) method in Moodle
     *
     * Returns named items from the cache and rebuilds it if the cache
     * has been cleared.
     * @param string $key
     * @return array|null
     */
    private function getCached(string $key)
    {
        // Do we have that particular thing in the cache?
        if ($this->compiled_cache === null || !array_key_exists($key, $this->compiled_cache)) {
            // If not do the compilation.
            try {
                $this->compiled_cache = assStackQuestion::compile($this->question_variables, $this->inputs, $this->prts, $this->options);
                //TODO CREATE NEW QUESTION CACHE DB ENTRY
            } catch (exception $e) {
                // TODO: what exactly do we use here as the key
                // and what sort of errors does the compilation generate.
                $this->runtime_errors[$e->getMessage()] = true;
            }
        }

        // A run-time error means we don't have the $key in the cache.
        // We don't want an error here, we want to degrade gracefully.*/
        $ret = null;
        if (is_array($this->compiled_cache) && array_key_exists($key, $this->compiled_cache)) {
            $ret = $this->compiled_cache[$key];
        }

        return $ret;
    }

    /* STACK CORE METHODS END */

    /* GETTERS AND SETTERS BEGIN */




    /**
     * @return int|null
     */
    public function getSeed(): ?int
    {
        return $this->seed;
    }

    /**
     * @param int|null $seed
     */
    public function setSeed(?int $seed): void
    {
        $this->seed = $seed;
    }

    /**
     * @return array
     */
    public function getUnitTests(): array
    {
        return $this->unit_tests;
    }

    /**
     * @param array $unit_tests
     */
    public function setUnitTests(array $unit_tests): void
    {
        $this->unit_tests = $unit_tests;
    }

    /**
     * @return stack_cas_session2
     */
    public function getSession(): stack_cas_session2
    {
        return $this->session;
    }

    /**
     * @param stack_cas_session2 $session
     */
    public function setSession(stack_cas_session2 $session): void
    {
        $this->session = $session;
    }

    /**
     * SPECIAL GETTER
     * @param null|string $name
     * @return stack_ast_container[]|stack_ast_container
     */
    public function getTas(string $name = null)
    {
        if ($name) {
            return $this->tas[$name];
        } else {
            return $this->tas;
        }
    }

    /**
     * SPECIAL SETTER
     * @param array|stack_ast_container $tas
     * @param null|string $name
     */
    public function setTas($tas, $name = null): void
    {
        if ($name) {
            $this->tas[$name] = $tas;
        } else {
            $this->tas = $tas;
        }
    }

    /**
     * @return stack_cas_security
     */
    public function getSecurity(): stack_cas_security
    {
        return $this->security;
    }

    /**
     * @param stack_cas_security $security
     */
    public function setSecurity(stack_cas_security $security): void
    {
        $this->security = $security;
    }

    /**
     * @return array|null
     */
    public function getLastResponse(): ?array
    {
        return $this->last_response;
    }

    /**
     * @param array|null $last_response
     */
    public function setLastResponse(?array $last_response): void
    {
        $this->last_response = $last_response;
    }

    /**
     * @return bool|null
     */
    public function getLastAcceptValid(): ?bool
    {
        return $this->last_accept_valid;
    }

    /**
     * @param bool|null $last_accept_valid
     */
    public function setLastAcceptValid(?bool $last_accept_valid): void
    {
        $this->last_accept_valid = $last_accept_valid;
    }

    /**
     * SPECIAL GETTER
     * @param false|string $name
     * @return stack_input_state[]|stack_input_state
     */
    public function getInputStates($name = false)
    {
        if ($name) {
            return $this->input_states[$name];
        } else {
            return $this->input_states;
        }
    }

    /**
     * SPECIAL SETTER
     * @param stack_input_state[]|stack_input_state $input_states
     * @param false|string $name
     */
    public function setInputStates($input_states, $name = false): void
    {
        if ($name) {
            $this->input_states[$name] = $input_states;
        } else {
            $this->input_states = $input_states;
        }
    }

    /**
     * SPECIAL GETTER
     * @param false|string $index
     * @return array|stack_potentialresponse_tree_state
     */
    public function getPrtResults($index = false)
    {
        if ($index) {
            return $this->prt_results[$index];
        } else {
            return $this->prt_results;
        }
    }

    /**
     * SPECIAL SETTER
     * @param array|stack_potentialresponse_tree_state $prt_results
     * @param false|string $index
     */
    public function setPrtResults($prt_results, $index = false): void
    {
        if ($index) {
            $this->prt_results[$index] = $prt_results;
        } else {
            $this->prt_results = $prt_results;
        }
    }

    /**
     * @return int
     */
    public function getHidden(): int
    {
        return $this->hidden;
    }

    /**
     * @param int $hidden
     */
    public function setHidden(int $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @return float
     */
    public function getPenalty()
    {
        return $this->penalty;
    }

    /**
     * @param float $penalty
     */
    public function setPenalty(float $penalty): void
    {
        $this->penalty = $penalty;
    }

    /**
     * @return bool|stack_cas_session2|string
     */
    public function getQuestionNoteInstantiated()
    {
        return $this->question_note_instantiated;
    }

    /**
     * @param bool|stack_cas_session2|string $question_note_instantiated
     */
    public function setQuestionNoteInstantiated($question_note_instantiated): void
    {
        $this->question_note_instantiated = $question_note_instantiated;
    }

    /**
     * SPECIAL GETTER
     * @param false|string $input_name
     * @return array|string
     */
    public function getUserResponse($input_name = false)
    {
        if ($input_name) {
            return $this->user_response[$input_name];
        } else {
            return $this->user_response;
        }
    }

    /**
     * SPECIAL SETTER
     * @param array $user_response
     * @param false|string $input_name
     */
    public function setUserResponse(array $user_response, $input_name = false)
    {
        if ($input_name) {
            $this->user_response[$input_name] = $user_response[$input_name];
        } else {
            $this->user_response = $user_response;
        }
    }

    /**
     * Not get because that function exists in assQuestion with a different purpose
     * @return float|null
     */
    public function obtainReachedPoints(): ?float
    {
        return $this->reached_points;
    }

    /**
     * @param float|null $reached_points
     */
    public function setReachedPoints(?float $reached_points): void
    {
        $this->reached_points = $reached_points;
    }

    /**
     * @return bool
     */
    public function isInstantiated(): bool
    {
        return $this->instantiated;
    }

    /**
     * @param bool $instantiated
     */
    public function setInstantiated(bool $instantiated): void
    {
        $this->instantiated = $instantiated;
    }

    /**
     * @return array
     */
    public function getEvaluation(): array
    {
        return $this->evaluation;
    }

    /**
     * @param array $evaluation
     */
    public function setEvaluation(array $evaluation): void
    {
        $this->evaluation = $evaluation;
    }

    /* GETTERS AND SETTERS END */

    /* QUESTIONTYPE METHODS BEGIN */

    /* rename_input($questionid, $from, $to) not required as it is only Moodle relevant */
    //TODO FEATURE RENAME INPUT

    /* rename_prt($questionid, $from, $to) not required as it is only Moodle relevant */
    //TODO FEATURE RENAME PRT

    /* rename_prt_node($questionid, $prtname, $from, $to) not required as it is only Moodle relevant */
    //TODO FEATURE RENAME PRT NODE

    /* notify_question_edited($questionid) not required as it is only Moodle relevant */
    //TODO FEATURE NOTIFY QUESTION EDITED

    /* load_question_tests($questionid) not required as it is only Moodle relevant */
    //TODO FEATURE BULK UNIT TESTS

    /* load_question_test($questionid, $testcase) not required as it is only Moodle relevant */
    //TODO

    /* delete_question_tests($questionid) not required as it is only Moodle relevant */
    //TODO FEATURE BULK UNIT TESTS

    /* delete_question_test($questionid, $testcase) not required as it is only Moodle relevant */
    //TODO

    /**
     * Helper method for "compiling" a question, validates and finds all the things
     * that do not change unless the question changes and stores them in a dictionary.
     *
     * Note that does throw exceptions about validation details.
     *
     * Currently the cache contaisn the following keys:
     *  'units' for declaring the units-mode.
     *  'forbiddenkeys' for the lsit of those.
     *  'contextvariable-qv' the pre-validated question-variables which are context variables.
     *  'statement-qv' the pre-validated question-variables.
     *  'preamble-qv' the matching blockexternals.
     *  'required' the lists of inputs required by given PRTs an array by PRT-name.
     *
     * In the future expect the following:
     *  'castext-qt' for the question-text as compiled CASText2.
     *  'castext-qn' for the question-note as compiled CASText2.
     *  'castext-...' for the model-solution and prtpartiallycorrect etc.
     *  'prt' the compiled PRT-logics in an array.
     *  'security-config' extended logic for cas-security, e.g. custom-units.
     *
     * @param string the questionvariables
     * @param array inputs as objects, keyed by input name
     * @param array PRTs as objects
     * @param stack_options the options in use, if they would ever matter
     * @return array|false
     */
    public static function compile($questionvariables, $inputs, $prts, $options)
    {
        // NOTE! We do not compile during question save as that would make
        // import actions slow. We could compile during fromform-validation
        // but we really should look at refactoring that to better interleave
        // the compilation.
        //
        // As we currently compile at the first use things start slower than they could.

        try {
            // The cache will be a dictionary with many things.
            $cc = [];
            // Some details are globals built from many sources.
            $units = false;
            $forbiddenkeys = [];

            // First handle the question variables.
            if ($questionvariables === null || trim($questionvariables) === '') {
                $cc['statement-qv'] = null;
                $cc['preamble-qv'] = null;
                $cc['contextvariable-qv'] = null;
                $cc['security-context'] = [];
            } else {
                $kv = new stack_cas_keyval($questionvariables, $options);
                if (!$kv->get_valid()) {
                    throw new stack_exception('Error(s) in question-variables: ' . implode('; ', $kv->get_errors()));
                }
                $c = $kv->compile('question-variables');
                // Store the pre-validated statement representing the whole qv.
                $cc['statement-qv'] = $c['statement'];
                // Store any contextvariables, e.g. assume statements.
                $cc['contextvariables-qv'] = $c['contextvariables'];
                // Store the possible block external features.
                $cc['preamble-qv'] = $c['blockexternal'];
                // Finally extend the forbidden keys set if we saw any variables written.
                if (isset($c['references']['write'])) {
                    $forbiddenkeys = array_merge($forbiddenkeys, $c['references']['write']);
                }
                // Collect type information and condense it.
                $ti = $kv->get_security()->get_context();
                $si = [];
                foreach ($ti as $key => $value) {
                    // We should not directly serialize the ASTs they have too much context in them.
                    // Unfortunately that means we need to parse them back on every init.
                    $si[$key] = array_keys($value);
                }

                // Mark all inputs. To let us know that they have special types.
                foreach ($inputs as $key => $value) {
                    if (!isset($si[$key])) {
                        $si[$key] = [];
                    }
                    $si[$key][-2] = -2;
                }
                $cc['security-context'] = $si;
            }

            // Then do some basic detail collection related to the inputs and PRTs.
            foreach ($inputs as $input) {
                if (is_a($input, 'stack_units_input')) {
                    $units = true;
                    break;
                }
            }
            $cc['required'] = [];
            foreach ($prts as $prt) {
                if ($prt->has_units()) {
                    $units = true;
                }
                // This is surprisingly expensive to do, simpler to extract from compiled.
                $cc['required'][$prt->get_name()] = $prt->get_required_variables(array_keys($inputs));
            }

            // Note that instead of just adding the unit loading to the 'preamble-qv'
            // and forgetting about units we do keep this bit of information stored
            // as it may be used in input configuration at some later time.
            $cc['units'] = $units;
            $cc['forbiddenkeys'] = $forbiddenkeys;

            return $cc;
        } catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            return array();
        }
    }

    /**
     * Collects all text in the question which could contain media objects
     * These were created with the Rich Text Editor
     * The collection is needed to delete unused media objects
     */
    protected function getRTETextWithMediaObjects(): string
    {

        // question text, suggested solutions etc
        $collected = parent::getRTETextWithMediaObjects();

        if (isset($this->options)) {
            $collected .= $this->specific_feedback;
            $collected .= $this->prt_correct;
            $collected .= $this->prt_partially_correct;
            $collected .= $this->prt_incorrect;
        }

        if (isset($this->general_feedback)) {
            $collected .= $this->general_feedback;
        }

        foreach ($this->prts as $prt) {
            foreach ($prt->getNodes() as $node) {
                $node_feedback = $node->getFeedbackFromNode();
                $collected .= $node_feedback['true_feedback'];
                $collected .= $node_feedback['false_feedback'];
            }
        }

        return $collected;
    }

    /* QUESTIONTYPE METHODS END */

    /**
     * @return bool
     */
    public function checkMaximaConnection(): bool
    {
        try {
            list($message, $genuinedebug, $result) = stack_connection_helper::stackmaxima_genuine_connect();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getStackVersion(): string
    {
        return $this->stack_version;
    }

    /**
     * @param string $stack_version
     */
    public function setStackVersion(string $stack_version): void
    {
        $this->stack_version = $stack_version;
    }

    /**
     * @return string|null
     */
    public function getQuestionVariables(): ?string
    {
        return $this->question_variables;
    }

    /**
     * @param string|null $question_variables
     */
    public function setQuestionVariables(?string $question_variables): void
    {
        $this->question_variables = $question_variables;
    }

    /**
     * @return string|null
     */
    public function getQuestionNote(): ?string
    {
        return $this->question_note;
    }

    /**
     * @param string|null $question_note
     */
    public function setQuestionNote(?string $question_note): void
    {
        $this->question_note = $question_note;
    }

    /**
     * @return string|null
     */
    public function getSpecificFeedback(): ?string
    {
        return $this->specific_feedback;
    }

    /**
     * @param string|null $specific_feedback
     */
    public function setSpecificFeedback(?string $specific_feedback): void
    {
        $this->specific_feedback = $specific_feedback;
    }

    /**
     * @return int|null
     */
    public function getSpecificFeedbackFormat(): ?int
    {
        return $this->specific_feedback_format;
    }

    /**
     * @param int|null $specific_feedback_format
     */
    public function setSpecificFeedbackFormat(?int $specific_feedback_format): void
    {
        $this->specific_feedback_format = $specific_feedback_format;
    }

    /**
     * @return string|null
     */
    public function getPrtCorrect(): ?string
    {
        return $this->prt_correct;
    }

    /**
     * @param string|null $prt_correct
     */
    public function setPrtCorrect(?string $prt_correct): void
    {
        $this->prt_correct = $prt_correct;
    }

    /**
     * @return int|null
     */
    public function getPrtCorrectFormat(): ?int
    {
        return $this->prt_correct_format;
    }

    /**
     * @param int|null $prt_correct_format
     */
    public function setPrtCorrectFormat(?int $prt_correct_format): void
    {
        $this->prt_correct_format = $prt_correct_format;
    }

    /**
     * @return string|null
     */
    public function getPrtPartiallyCorrect(): ?string
    {
        return $this->prt_partially_correct;
    }

    /**
     * @param string|null $prt_partially_correct
     */
    public function setPrtPartiallyCorrect(?string $prt_partially_correct): void
    {
        $this->prt_partially_correct = $prt_partially_correct;
    }

    /**
     * @return int|null
     */
    public function getPrtPartiallyCorrectFormat(): ?int
    {
        return $this->prt_partially_correct_format;
    }

    /**
     * @param int|null $prt_partially_correct_format
     */
    public function setPrtPartiallyCorrectFormat(?int $prt_partially_correct_format): void
    {
        $this->prt_partially_correct_format = $prt_partially_correct_format;
    }

    /**
     * @return string|null
     */
    public function getPrtIncorrect(): ?string
    {
        return $this->prt_incorrect;
    }

    /**
     * @param string|null $prt_incorrect
     */
    public function setPrtIncorrect(?string $prt_incorrect): void
    {
        $this->prt_incorrect = $prt_incorrect;
    }

    /**
     * @return int|null
     */
    public function getPrtIncorrectFormat(): ?int
    {
        return $this->prt_incorrect_format;
    }

    /**
     * @param int|null $prt_incorrect_format
     */
    public function setPrtIncorrectFormat(?int $prt_incorrect_format): void
    {
        $this->prt_incorrect_format = $prt_incorrect_format;
    }

    /**
     * @return string|null
     */
    public function getVariantsSelectionSeed(): ?string
    {
        return $this->variants_selection_seed;
    }

    /**
     * @param string|null $variants_selection_seed
     */
    public function setVariantsSelectionSeed(?string $variants_selection_seed): void
    {
        $this->variants_selection_seed = $variants_selection_seed;
    }

    /**
     * @return array
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    /**
     * @param array $inputs
     */
    public function setInputs(array $inputs): void
    {
        $this->inputs = $inputs;
    }

    /**
     * @return array
     */
    public function getPrts(): array
    {
        return $this->prts;
    }

    /**
     * @param array $prts
     */
    public function setPrts(array $prts): void
    {
        $this->prts = $prts;
    }

    /**
     * @return stack_options
     */
    public function getOptions(): stack_options
    {
        return $this->options;
    }

    /**
     * @param stack_options $options
     */
    public function setOptions(stack_options $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getDeployedSeeds(): array
    {
        return $this->deployed_seeds;
    }

    /**
     * @param array $deployed_seeds
     */
    public function setDeployedSeeds(array $deployed_seeds): void
    {
        $this->deployed_seeds = $deployed_seeds;
    }

    /**
     * @return string|null
     */
    public function getQuestionTextInstantiated(): ?string
    {
        return $this->question_text_instantiated;
    }

    /**
     * @param string|null $question_text_instantiated
     */
    public function setQuestionTextInstantiated(?string $question_text_instantiated): void
    {
        $this->question_text_instantiated = $question_text_instantiated;
    }

    /**
     * @return string|null
     */
    public function getSpecificFeedbackInstantiated(): ?string
    {
        return $this->specific_feedback_instantiated;
    }

    /**
     * @param string|null $specific_feedback_instantiated
     */
    public function setSpecificFeedbackInstantiated(?string $specific_feedback_instantiated): void
    {
        $this->specific_feedback_instantiated = $specific_feedback_instantiated;
    }

    /**
     * @return string|null
     */
    public function getPrtCorrectInstantiated(): ?string
    {
        return $this->prt_correct_instantiated;
    }

    /**
     * @param string|null $prt_correct_instantiated
     */
    public function setPrtCorrectInstantiated(?string $prt_correct_instantiated): void
    {
        $this->prt_correct_instantiated = $prt_correct_instantiated;
    }

    /**
     * @return string|null
     */
    public function getPrtPartiallyCorrectInstantiated(): ?string
    {
        return $this->prt_partially_correct_instantiated;
    }

    /**
     * @param string|null $prt_partially_correct_instantiated
     */
    public function setPrtPartiallyCorrectInstantiated(?string $prt_partially_correct_instantiated): void
    {
        $this->prt_partially_correct_instantiated = $prt_partially_correct_instantiated;
    }

    /**
     * @return string|null
     */
    public function getPrtIncorrectInstantiated(): ?string
    {
        return $this->prt_incorrect_instantiated;
    }

    /**
     * @param string|null $prt_incorrect_instantiated
     */
    public function setPrtIncorrectInstantiated(?string $prt_incorrect_instantiated): void
    {
        $this->prt_incorrect_instantiated = $prt_incorrect_instantiated;
    }

    /**
     * @return array
     */
    public function getRuntimeErrors(): array
    {
        return $this->runtime_errors;
    }

    /**
     * @param array $runtime_errors
     */
    public function setRuntimeErrors(array $runtime_errors): void
    {
        $this->runtime_errors = $runtime_errors;
    }

    /**
     * @return array
     */
    public function getCompiledCache(): array
    {
        return $this->compiled_cache;
    }

    /**
     * @param array $compiled_cache
     */
    public function setCompiledCache(array $compiled_cache): void
    {
        $this->compiled_cache = $compiled_cache;
    }

    /**
     * @return string|null
     */
    public function getGeneralFeedback(): ?string
    {
        return $this->general_feedback;
    }

    /**
     * @param string|null $general_feedback
     */
    public function setGeneralFeedback(?string $general_feedback): void
    {
        $this->general_feedback = $general_feedback;
    }


    public function getAdditionalTableName()
    {
        // TODO: Implement getAdditionalTableName() method.
    }

    public function getAnswerTableName()
    {
        // TODO: Implement getAnswerTableName() method.
    }

    /**
     * Now uses ilassStackQuestionPlugin getQuestionType() method
     * @return string ILIAS question type name
     */
    public function getQuestionType(): string
    {
        return $this->plugin->getQuestionType();
    }

    //iQuestionCondition methods

    /**
     * Get all available operations for a specific question
     *
     * @param $expression
     *
     * @return array
     * @internal param string $expression_type
     */
    public function getOperators($expression): array
    {
        //TODO Esto hay que saber donde se usa
        //mientras tanto
        return [];
    }

    /**
     * Get all available expression types for a specific question
     *
     * @return array
     */
    public function getExpressionTypes(): array
    {
        //TODO Esto hay que saber donde se usa
        //mientras tanto
        return [];
    }

    /**
     * Get the user solution for a question by active_id and the test pass
     *
     * @param int $active_id
     * @param int $pass
     *
     * @return ilUserQuestionResult
     */
    public function getUserQuestionResult($active_id, $pass): ilUserQuestionResult
    {
        //TODO Modificar esto a como se hace en otras clases, buscar directamente en tst_solutions
        //mientras tanto

        $result = new ilUserQuestionResult($this, $active_id, $pass);
        $points = (float)$this->calculateReachedPoints($active_id, $pass);
        $max_points = (float)$this->getMaximumPoints();
        $result->setReachedPercentage(($points / $max_points) * 100);

        return $result;
    }

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
     */
    public function getAvailableAnswerOptions($index = null): array
    {
        return array();
    }

}