<?php
declare(strict_types=1);

namespace classes\core;

use classes\core\version\StackVersion;
use classes\core\security\StackQuestionSecurity;
use classes\core\security\StackException;

use classes\core\options\StackVariables;
use classes\core\maxima\StackSession;

use classes\core\text\StackText;
use classes\core\options\StackOptions;
use classes\core\inputs\StackInput;
use classes\core\evaluation\StackPotentialResponseTree;

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
    const STACK_QUESTION_STATUS_ERROR = -1;
    const STACK_QUESTION_STATUS_UNINITIALIZED = 0;
    const STACK_QUESTION_STATUS_INTERNALLY_INITIALIZED = 1;
    const STACK_QUESTION_STATUS_INTERNALLY_VALIDATED = 2;
    const STACK_QUESTION_STATUS_EXTERNALLY_INITIALIZED = 3;
    const STACK_QUESTION_STATUS_EXTERNALLY_VALIDATED = 4;
    const STACK_QUESTION_STATUS_EVALUATED = 5;
    const STACK_QUESTION_STATUS_STATIC = 6;

    /**
     * @var ?int The status  of the current STACK Question.
     */
    private ?int $status = null;

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
     * @var ?StackText The not inline feedback of the current STACK Question.
     */
    private ?StackText $specific_feedback = null;

    /**
     * @var StackText|null Default feedback Text for fully correct PRTs
     */
    private ?StackText $default_feedback_for_fully_correct_prt = null;

    /**
     * @var ?StackText The default feedback Text for partially correct PRTs.
     */
    private ?StackText $default_feedback_for_partially_correct_prt = null;

    /**
     * @var ?StackText The default feedback Text for fully incorrect PRTs.
     */
    private ?StackText $default_feedback_for_fully_incorrect_prt = null;

    /**
     * @var ?StackText The general feedback (how to solve) Text of the current STACK Question.
     */
    private ?StackText $general_feedback_text = null;

    /**
     * @var ?StackText The hint Text of the current STACK Question.
     */
    private ?StackText $hint = null;

    /**
     * @var ?StackOptions The options of the current STACK Question.
     */
    private ?StackOptions $options = null;

    /**
     * @var ?StackInput[] The inputs of the current STACK Question.
     */
    private ?array $inputs = null;

    /**
     * @var ?StackPotentialResponseTree[] The potential response trees of the current STACK Question.
     */
    private ?array $potential_response_trees = null;

    /**
     * @var ?StackSession The session of variables of the current STACK Question.
     */
    private ?StackSession $session = null;

    /**
     * @var ?StackQuestionSecurity
     * the question level common security
     * settings, i.e. forbidden keys and wether units are in play.
     */
    private ?StackQuestionSecurity $security = null;

    /**
     * @var array The internal data of the question as array
     */
    private array $internal_data = [];

    /**
     * @var array The external data of the question as array
     */
    private array $external_data = [];

    /**
     * StackQuestion constructor.
     * @param StackVersion $version
     */
    public function __construct(StackVersion $version)
    {
        if ($version->checkVersion()) {
            //Build the object with the version of the question
            $this->security = new StackQuestionSecurity($version);
            $this->session = new StackSession($version);

            $this->version = $version;
            $this->status = self::STACK_QUESTION_STATUS_UNINITIALIZED;
        } else {
            //TODO: Log error, invalid version
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
        }
    }

    /**
     * Initializes the question for different purposes
     * Mask the initialisation of the question, which is done internally
     * @param bool $with_external_data_from_user
     * @param bool $with_external_data_from_teacher
     * @return bool
     */
    public function generate(bool $with_external_data_from_user = false, bool $with_external_data_from_teacher = false): bool
    {
        //Ensure that the object is in the correct status
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_UNINITIALIZED) {
            //Get the JSON data of the question from the DB
            $array_internal = $this->getSecurity()->getQuestionInternalFromDB($this->getVersion());
            //Check JSON format and security for internal data of the question
            if (!StackQuestionSecurity::checkInternal($array_internal)) {
                //TODO: Log error, internal data is not secure
                $this->status = self::STACK_QUESTION_STATUS_ERROR;
                return false;
            } else {
                //Initialise the object with the internal data of the question from the JSON
                if (!$this->internalInitialization($array_internal)) {
                    //TODO: Log error, internal data could not be initialized
                    $this->status = self::STACK_QUESTION_STATUS_ERROR;
                    return false;
                }
            }
            //Check JSON format and security for external data of the question if required
            if ($with_external_data_from_user || $with_external_data_from_teacher) {
                //force internal validation to ensure that the object is in the correct status
                $this->validateInternal();
                if ($with_external_data_from_teacher) {
                    //Correct solution from teacher
                    $json_external = $this->getSecurity()->getQuestionExternalJSONFromTeacher($this);
                } elseif ($with_external_data_from_user) {
                    //Student answer
                    $json_external = $this->getSecurity()->getQuestionExternalJSONFromStudent($this);
                }
                //Check JSON format and security for external data of the question
                if (!StackQuestionSecurity::checkExternal($json_external)) {
                    //TODO: Log error, external data is not secure
                    $this->status = self::STACK_QUESTION_STATUS_ERROR;
                    return false;
                } else {
                    //Initialise the object with the external data of the question from the JSON
                    if (!$this->externalInitialization($json_external)) {
                        //TODO: Log error, external data could not be initialized
                        $this->status = self::STACK_QUESTION_STATUS_ERROR;
                        return false;
                    }
                    //Only here must be the status set to externally initialized
                    $this->status = self::STACK_QUESTION_STATUS_EXTERNALLY_INITIALIZED;
                }
            }

            return true;
        } else {
            //TODO: Log error, not in the correct status
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
            return false;
        }
    }

    /**
     * Initialises indeed the object with the internal data of the question from the DB
     * @param array $array_internal
     * @return bool
     */
    private function internalInitialization(array $array_internal): bool
    {
        //Ensure that the object is in the correct status
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_UNINITIALIZED) {
            try {
                $this->text = new StackText($array_internal['text']);
                $this->description = new StackText($array_internal['description']);
                $this->specific_feedback = new StackText($array_internal['specific_feedback']);

                $this->options = new StackOptions($array_internal['options']);
                $this->variables = new StackVariables($array_internal['variables']);

                $this->default_feedback_for_fully_correct_prt = new StackText($array_internal['default_feedback_for_fully_correct_prt']);
                $this->default_feedback_for_partially_correct_prt = new StackText($array_internal['default_feedback_for_partially_correct_prt']);
                $this->default_feedback_for_fully_incorrect_prt = new StackText($array_internal['default_feedback_for_fully_incorrect_prt']);

                $this->general_feedback_text = new StackText($array_internal['general_feedback_text']);
                $this->hint = new StackText($array_internal['hint']);

                foreach ($array_internal['inputs'] as $input_identifier => $input_data) {
                    //TODO FACTORIA DE INPUTS
                    //$this->inputs[$input_identifier] = new StackInput($input_data);
                }

                foreach ($array_internal['potential_response_trees'] as $prt_identifier => $potential_response_tree_data) {
                    $this->potential_response_trees[$prt_identifier] = new StackPotentialResponseTree($potential_response_tree_data);
                }

                //If everything was right, set as the internal data of the question
                $this->internal_data = $array_internal;

                //Only here must be the status  set to internally initialized
                $this->status = self::STACK_QUESTION_STATUS_INTERNALLY_INITIALIZED;

                return true;
            } catch (StackException $e) {
                //TODO: Log error, invalid internal data
                $this->status = self::STACK_QUESTION_STATUS_ERROR;
                return false;
            }
        } else {
            //TODO: Log error, not in the correct status
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
            return false;
        }
    }

    /**
     * Validates the question internally before being externally validated / evaluated
     * Mask the internal validation of the question
     * @return bool
     */
    protected function validateInternal(): bool
    {
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_INTERNALLY_INITIALIZED) {
            //Validate the question
            if (!$this->internalValidation()) {
                //TODO: Log error, not possible to validate the question
                $this->status = self::STACK_QUESTION_STATUS_ERROR;
                return false;
            }

            //Only here must be the status  set to internally validated
            $this->status = self::STACK_QUESTION_STATUS_INTERNALLY_VALIDATED;

            return true;
        } else {
            //TODO: Log error, not in the correct status
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
            return false;
        }
    }

    /**
     * Validates indeed the author changes to the question
     * @return bool
     */
    private function internalValidation(): bool
    {
        //Ensure that the object is in the correct status
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_INTERNALLY_INITIALIZED) {
            //TODO: Indeed, validate the question
            $this->getOptions()->prepareForMaxima();

            return true;
        } else {
            //TODO: Log error, not possible to validate the question
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
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
        //Ensure that the object is in the correct status
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_INTERNALLY_VALIDATED) {
            //TODO: Initialise the object with the external data of the question
            $this->status = self::STACK_QUESTION_STATUS_EXTERNALLY_INITIALIZED;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validates the student answer to the question
     * Mask the external validation of the question
     * @param bool $refresh_validation forces the validation of the question if true
     * @return bool
     */
    protected function validateExternal(bool $refresh_validation = false): bool
    {
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_EXTERNALLY_INITIALIZED ||
            ($refresh_validation && $this->status >= self::STACK_QUESTION_STATUS_INTERNALLY_VALIDATED)) {

            //Validate the question
            if (!$this->externalValidation($refresh_validation)) {
                //TODO: Log error, not possible to validate the question
                $this->status = self::STACK_QUESTION_STATUS_ERROR;
                return false;
            }

            //Only here must be the status  set to externally validated
            $this->status = self::STACK_QUESTION_STATUS_EXTERNALLY_VALIDATED;

            return true;
        } else {
            //TODO: Log error, not in the correct status
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
            return false;
        }
    }

    /**
     * Validates indeed the student answer to the question
     * @param bool $refresh_validation
     * @return bool
     */
    private function externalValidation(bool $refresh_validation = false): bool
    {
        //Ensure that the object is in the correct status
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_EXTERNALLY_INITIALIZED || $refresh_validation) {
            //TODO: Indeed, validate the user answer
            return true;
        } else {
            //TODO: Log error, not possible to validate the user answer
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
            return false;
        }
    }

    /**
     * Evaluates the unit tests of the question
     * @return bool
     */
    protected function evaluateInternal(): bool
    {
        //Ensure that the object is in the correct status
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_INTERNALLY_VALIDATED) {

            if (!$this->internalEvaluation()) {
                //TODO: Log error, not possible to evaluate the question
                $this->status = self::STACK_QUESTION_STATUS_ERROR;
                return false;
            }

            //Only here must be the status  set to evaluated
            $this->status = self::STACK_QUESTION_STATUS_EVALUATED;

            return true;
        } else {
            //TODO: Log error, not in the correct status
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
            return false;
        }
    }

    /**
     * Evaluates indeed the unit tests of the question
     * @return bool
     */
    private function internalEvaluation(): bool
    {
        //Ensure that the object is in the correct status
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_INTERNALLY_VALIDATED) {
            //TODO: Indeed, evaluate the unit tests of the question
            return true;
        } else {
            //TODO: Log error, not possible to validate the user answer
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
            return false;
        }
    }

    /**
     * Evaluates the student answer to the question
     * @return bool
     */
    protected function evaluateExternal(): bool
    {
        //Ensure that the object is in the correct status
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_EXTERNALLY_VALIDATED) {

            if (!$this->externalEvaluation()) {
                //TODO: Log error, not possible to evaluate the question
                $this->status = self::STACK_QUESTION_STATUS_ERROR;
                return false;
            }

            //Only here must be the status set to evaluated
            $this->status = self::STACK_QUESTION_STATUS_EVALUATED;

            return true;
        } else {
            //TODO: Log error, not in the correct status
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
            return false;
        }
    }

    /**
     * Evaluates indeed the student answer to the question
     * @return bool
     */
    private function externalEvaluation(): bool
    {
        //Ensure that the object is in the correct status
        if ($this->getStatus() === self::STACK_QUESTION_STATUS_EXTERNALLY_VALIDATED) {
            //TODO: Indeed, evaluate the student answer to the question
            return true;
        } else {
            //TODO: Log error, not in the correct status
            $this->status = self::STACK_QUESTION_STATUS_ERROR;
            return false;
        }
    }

    // Getters

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return StackVersion
     */
    public function getVersion(): StackVersion
    {
        return $this->version;
    }

    /**
     * @return StackText|null
     */
    public function getText(): ?StackText
    {
        return $this->text;
    }

    /**
     * @return StackText|null
     */
    public function getDescription(): ?StackText
    {
        return $this->description;
    }

    /**
     * @return StackVariables|null
     */
    public function getVariables(): ?StackVariables
    {
        return $this->variables;
    }


    /**
     * @return StackText|null
     */
    public function getSpecificFeedback(): ?StackText
    {
        return $this->specific_feedback;
    }

    /**
     * @return StackText|null
     */
    public function getDefaultFeedbackForFullyCorrectPrt(): ?StackText
    {
        return $this->default_feedback_for_fully_correct_prt;
    }

    /**
     * @return StackText|null
     */
    public function getDefaultFeedbackForPartiallyCorrectPrt(): ?StackText
    {
        return $this->default_feedback_for_partially_correct_prt;
    }

    /**
     * @return StackText|null
     */
    public function getDefaultFeedbackForFullyIncorrectPrt(): ?StackText
    {
        return $this->default_feedback_for_fully_incorrect_prt;
    }

    /**
     * @return StackText|null
     */
    public function getGeneralFeedbackText(): ?StackText
    {
        return $this->general_feedback_text;
    }

    /**
     * @return StackText|null
     */
    public function getHint(): ?StackText
    {
        return $this->hint;
    }

    /**
     * @return array|null
     */
    public function getInputs(): ?array
    {
        return $this->inputs;
    }

    /**
     * @return array|null
     */
    public function getPotentialResponseTrees(): ?array
    {
        return $this->potential_response_trees;
    }

    /**
     * @return StackOptions|null
     */
    public function getOptions(): ?StackOptions
    {
        return $this->options;
    }

    /**
     * @return StackSession|null
     */
    public function getSession(): ?StackSession
    {
        return $this->session;
    }

    /**
     * @return StackQuestionSecurity|null
     */
    public function getSecurity(): ?StackQuestionSecurity
    {
        return $this->security;
    }

    /**
     * @return array
     */
    public function getInternalData(): array
    {
        return $this->internal_data;
    }

    /**
     * @return array
     */
    public function getExternalData(): array
    {
        return $this->external_data;
    }

}