<?php
declare(strict_types=1);

use classes\core\StackQuestion;
use classes\core\version\StackVersion;
use classes\platform\StackConfig;
use classes\platform\StackPlatform;
use classes\platform\ilias\StackPlatformIlias;

/**
 *  This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
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
            //Initialize Stack platform settings
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

            //initializes the question without external data
            $this->getStackQuestion()->generate();
        } catch (Exception $e) {
            //TODO ERROR MESSAGE
        }
    }


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
        parent::loadFromDb($question_id);
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


    //INTERFACES FORCED METHODS

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