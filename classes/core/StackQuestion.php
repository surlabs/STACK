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

class StackQuestion
{
    const STACK_QUESTION_STATE_ERROR = -1;
    const STACK_QUESTION_STATE_UNINITIALIZED = 0;
    const STACK_QUESTION_STATE_INITIALIZED = 1;
    const STACK_QUESTION_STATE_VALIDATED = 2;
    const STACK_QUESTION_STATE_EVALUATED = 3;

    /**
     * @var int The state of the current STACK Question.
     */
    private int $state;

    /**
     * @var StackVersion The version information of the current STACK Question.
     */
    private StackVersion $version;

    /**
     * @var ?StackText The text of the current STACK Question.
     */
    private ?StackText $text = null;

    /**
     * @var ?StackText The description of the current STACK Question.
     */
    private ?StackText $description = null;

    /**
     * @var ?StackVariables
     */
    private ?StackVariables $variables = null;

    /**
     * @var ?StackQuestionNote
     */
    private ?StackQuestionNote $note = null;

    /**
     * @var ?StackText The not inline feedback of the current STACK Question.
     */
    private ?StackText $specific_feedback = null;

    /**
     * @var ?StackText[] The default feedback displayed by all potential response trees in common
     */
    private ?array $default_feedback_texts = null;

    /**
     * @var ?StackText The general feedback of the current STACK Question.
     */
    private ?StackText $general_feedback_text = null;

    /**
     * @var ?StackText The hint of the current STACK Question.
     */
    private ?StackText $hint = null;

    /**
     * @var ?StackInput[] The inputs of the current STACK Question.
     */
    private ?array $inputs = null;

    /**
     * @var ?StackPotentialResponseTree[] The potential response trees of the current STACK Question.
     */
    private ?array $potential_response_trees = null;

    /**
     * @var ?StackOptions The options of the current STACK Question.
     */
    private ?StackOptions $options = null;

    /**
     * @var ?StackSession The session of variables of the current STACK Question.
     */
    private ?StackSession $session = null;

    /**
     * @var StackQuestionTeacherAnswer[] The solution of the current STACK Question.
     */
    private ?array $solution = null;

    /**
     * @var ?StackQuestionSecurity
     * the question level common security
     * settings, i.e. forbidden keys and wether units are in play.
     */
    private ?StackQuestionSecurity $security = null;

    /**
     * @var ?StackLog The logging of the current STACK Question.
     */
    private ?StackLog $log = null;

    /**
     * @var ?StackCache The cache of the current STACK Question.
     */
    private ?StackCache $cache = null;

    /**
     * @var array Set of expensive to evaluate but static things.
     */
    private array $compiled_cache = [];

    /**
     * StackQuestion constructor.
     * @param StackVersion $version
     * @throws StackException
     */
    public function __construct(StackVersion $version)
    {
        //Only here must be the state set to uninitialized
        $this->state = self::STACK_QUESTION_STATE_UNINITIALIZED;

        if ($this->checkVersion($version)) {
            $this->version = $version;
        } else {
            //TODO: Log error, version of the question is not correct
            throw new StackException('UNSET');
        }
    }

    /**
     * Checks the version of the question
     * @param StackVersion $version
     * @return bool
     */
    private function checkVersion(StackVersion $version): bool
    {
        if ($this->state === self::STACK_QUESTION_STATE_UNINITIALIZED) {
            //TODO: Check version of the question
            return true;
        } else {
            //TODO: Log error, version of the question is not correct
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /**
     * Receives the question data in JSON format and initializes the object
     * for a specific purpose.
     * Mask the initialisation of the question
     * @param string $json_internal Information stored in the DB
     * @param ?string $json_external Information of the question usage
     * @return bool
     */
    protected function initialize(string $json_internal, ?string $json_external = null): bool
    {
        //Ensure that the object is in the correct state
        if ($this->state === self::STACK_QUESTION_STATE_UNINITIALIZED) {
            //Check JSON format and security for internal data of the question
            if (!StackQuestionSecurity::checkInternal($json_internal)) {
                //TODO: Log error, internal data is not secure
                $this->state = self::STACK_QUESTION_STATE_ERROR;
                return false;
            } else {
                //Initialise the object with the internal data of the question from the JSON
                if (!$this->internalInitialization($json_internal)) {
                    //TODO: Log error, internal data could not be initialized
                    $this->state = self::STACK_QUESTION_STATE_ERROR;
                    return false;
                }
            }
            //Check JSON format and security for external data of the question if required
            if ($json_external !== null) {
                if (!StackQuestionSecurity::checkExternal($json_external)) {
                    //TODO: Log error, external data is not secure
                    $this->state = self::STACK_QUESTION_STATE_ERROR;
                    return false;
                } else {
                    //Initialise the object with the external data of the question from the JSON
                    if (!$this->externalInitialization($json_external)) {
                        //TODO: Log error, external data could not be initialized
                        $this->state = self::STACK_QUESTION_STATE_ERROR;
                        return false;
                    }
                }
            }

            //Only here must be the state set to initialized
            $this->state = self::STACK_QUESTION_STATE_INITIALIZED;

            return true;
        } else {
            //TODO: Log error, not in the correct state
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /**
     * Initialises indeed the object with the internal data of the question from the DB
     * @param string $json_internal
     * @return bool
     */
    private function internalInitialization(string $json_internal): bool
    {
        //Ensure that the object is in the correct state
        if ($this->state === self::STACK_QUESTION_STATE_UNINITIALIZED) {
            //TODO: Initialise the object with the internal data of the question from the JSON
            return true;
        } else {
            return false;
        }
    }

    /**
     * Initialises indeed the object with the external data of the question from the student answer
     * @param string $json_external
     * @return bool
     */
    private function externalInitialization(string $json_external): bool
    {
        //Ensure that the object is in the correct state
        if ($this->state === self::STACK_QUESTION_STATE_UNINITIALIZED) {
            //TODO: Initialise the object with the external data of the question
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validates the author changes to the question
     * Mask the internal validation of the question
     * @return bool
     */
    protected function validateInternal(): bool
    {
        if ($this->state === self::STACK_QUESTION_STATE_INITIALIZED) {
            //Validate the question
            if (!$this->internalValidation()) {
                //TODO: Log error, not possible to validate the question
                $this->state = self::STACK_QUESTION_STATE_ERROR;
                return false;
            }

            //Only here must be the state set to validated
            $this->state = self::STACK_QUESTION_STATE_VALIDATED;

            return true;
        } else {
            //TODO: Log error, not in the correct state
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /**
     * Validates indeed the author changes to the question
     * @return bool
     */
    private function internalValidation(): bool
    {
        //Ensure that the object is in the correct state
        if ($this->state === self::STACK_QUESTION_STATE_INITIALIZED) {
            //TODO: Indeed, validate the question
            return true;
        } else {
            //TODO: Log error, not possible to validate the question
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /**
     * Validates the student answer to the question
     * Mask the external validation of the question
     * @return bool
     */
    protected function validateExternal(): bool
    {
        if ($this->state === self::STACK_QUESTION_STATE_INITIALIZED) {

            //Validate the question
            if (!$this->externalValidation()) {
                //TODO: Log error, not possible to validate the question
                $this->state = self::STACK_QUESTION_STATE_ERROR;
                return false;
            }

            //Only here must be the state set to validated
            $this->state = self::STACK_QUESTION_STATE_VALIDATED;

            return true;
        } else {
            //TODO: Log error, not in the correct state
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /**
     * Validates indeed the student answer to the question
     * @return bool
     */
    private function externalValidation(): bool
    {
        //Ensure that the object is in the correct state
        if ($this->state === self::STACK_QUESTION_STATE_INITIALIZED) {
            //TODO: Indeed, validate the user answer
            return true;
        } else {
            //TODO: Log error, not possible to validate the user answer
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /**
     * Evaluates the unit tests of the question
     * @return bool
     */
    protected function evaluateInternal(): bool
    {
        //Ensure that the object is in the correct state
        if ($this->state === self::STACK_QUESTION_STATE_VALIDATED) {

            if (!$this->internalEvaluation()) {
                //TODO: Log error, not possible to evaluate the question
                $this->state = self::STACK_QUESTION_STATE_ERROR;
                return false;
            }

            //Only here must be the state set to evaluated
            $this->state = self::STACK_QUESTION_STATE_EVALUATED;

            return true;
        } else {
            //TODO: Log error, not in the correct state
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /**
     * Evaluates indeed the unit tests of the question
     * @return bool
     */
    private function internalEvaluation(): bool
    {
        //Ensure that the object is in the correct state
        if ($this->state === self::STACK_QUESTION_STATE_VALIDATED) {
            //TODO: Indeed, evaluate the unit tests of the question
            return true;
        } else {
            //TODO: Log error, not possible to validate the user answer
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /**
     * Evaluates the student answer to the question
     * @return bool
     */
    protected function evaluateExternal(): bool
    {
        //Ensure that the object is in the correct state
        if ($this->state === self::STACK_QUESTION_STATE_VALIDATED) {

            if (!$this->externalEvaluation()) {
                //TODO: Log error, not possible to evaluate the question
                $this->state = self::STACK_QUESTION_STATE_ERROR;
                return false;
            }

            //Only here must be the state set to evaluated
            $this->state = self::STACK_QUESTION_STATE_EVALUATED;

            return true;
        } else {
            //TODO: Log error, not in the correct state
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /**
     * Evaluates indeed the student answer to the question
     * @return bool
     */
    private function externalEvaluation(): bool
    {
        //Ensure that the object is in the correct state
        if ($this->state === self::STACK_QUESTION_STATE_VALIDATED) {
            //TODO: Indeed, evaluate the student answer of the question
            return true;
        } else {
            //TODO: Log error, not in the correct state
            $this->state = self::STACK_QUESTION_STATE_ERROR;
            return false;
        }
    }

    /*
     * Getters and setters
     */

    /**
     * There is no setter for the state because it is set only on this class
     * @return int|null
     */
    public function getState(): ?int
    {
        return $this->state;
    }

    /**
     * @return StackVersion
     */
    public function getVersion(): StackVersion
    {
        return $this->version;
    }

    /**
     * @param StackVersion $version
     */
    public function setVersion(StackVersion $version): void
    {
        $this->version = $version;
    }

    /**
     * @return StackText
     */
    public function getText(): StackText
    {
        return $this->text;
    }

    /**
     * @param StackText $text
     */
    public function setText(StackText $text): void
    {
        $this->text = $text;
    }

    /**
     * @return StackText
     */
    public function getDescription(): StackText
    {
        return $this->description;
    }

    /**
     * @param StackText $description
     */
    public function setDescription(StackText $description): void
    {
        $this->description = $description;
    }

    /**
     * @return StackVariables
     */
    public function getVariables(): StackVariables
    {
        return $this->variables;
    }

    /**
     * @param StackVariables $variables
     */
    public function setVariables(StackVariables $variables): void
    {
        $this->variables = $variables;
    }

    /**
     * @return StackQuestionNote
     */
    public function getNote(): StackQuestionNote
    {
        return $this->note;
    }

    /**
     * @param StackQuestionNote $note
     */
    public function setNote(StackQuestionNote $note): void
    {
        $this->note = $note;
    }

    /**
     * @return StackText
     */
    public function getSpecificFeedback(): StackText
    {
        return $this->specific_feedback;
    }

    /**
     * @param StackText $specific_feedback
     */
    public function setSpecificFeedback(StackText $specific_feedback): void
    {
        $this->specific_feedback = $specific_feedback;
    }

    /**
     * @return array
     */
    public function getDefaultFeedbackTexts(): array
    {
        return $this->default_feedback_texts;
    }

    /**
     * @param array $default_feedback_texts
     */
    public function setDefaultFeedbackTexts(array $default_feedback_texts): void
    {
        $this->default_feedback_texts = $default_feedback_texts;
    }

    /**
     * @return StackText
     */
    public function getGeneralFeedbackText(): StackText
    {
        return $this->general_feedback_text;
    }

    /**
     * @param StackText $general_feedback_text
     */
    public function setGeneralFeedbackText(StackText $general_feedback_text): void
    {
        $this->general_feedback_text = $general_feedback_text;
    }

    /**
     * @return StackText
     */
    public function getHint(): StackText
    {
        return $this->hint;
    }

    /**
     * @param StackText $hint
     */
    public function setHint(StackText $hint): void
    {
        $this->hint = $hint;
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
    public function getPotentialResponseTrees(): array
    {
        return $this->potential_response_trees;
    }

    /**
     * @param array $potential_response_trees
     */
    public function setPotentialResponseTrees(array $potential_response_trees): void
    {
        $this->potential_response_trees = $potential_response_trees;
    }

    /**
     * @return StackOptions
     */
    public function getOptions(): StackOptions
    {
        return $this->options;
    }

    /**
     * @param StackOptions $options
     */
    public function setOptions(StackOptions $options): void
    {
        $this->options = $options;
    }

    /**
     * @return StackSession
     */
    public function getSession(): StackSession
    {
        return $this->session;
    }

    /**
     * @param StackSession $session
     */
    public function setSession(StackSession $session): void
    {
        $this->session = $session;
    }

    /**
     * @return array
     */
    public function getSolution(): array
    {
        return $this->solution;
    }

    /**
     * @param array $solution
     */
    public function setSolution(array $solution): void
    {
        $this->solution = $solution;
    }

    /**
     * @return StackQuestionSecurity
     */
    public function getSecurity(): StackQuestionSecurity
    {
        return $this->security;
    }

    /**
     * @param StackQuestionSecurity $security
     */
    public function setSecurity(StackQuestionSecurity $security): void
    {
        $this->security = $security;
    }

    /**
     * @return StackLog
     */
    public function getLog(): StackLog
    {
        return $this->log;
    }

    /**
     * @param StackLog $log
     */
    public function setLog(StackLog $log): void
    {
        $this->log = $log;
    }

    /**
     * @return StackCache
     */
    public function getCache(): StackCache
    {
        return $this->cache;
    }

    /**
     * @param StackCache $cache
     */
    public function setCache(StackCache $cache): void
    {
        $this->cache = $cache;
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
}