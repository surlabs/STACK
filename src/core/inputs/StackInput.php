<?php
declare(strict_types=1);

namespace src\core\inputs;
use src\core\options\StackOptions;
use src\core\security\StackException;
use src\platform\StackPlatform;

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
class StackInput
{
    const STACK_INPUT_BLANK = '';
    const STACK_INPUT_VALID = 'valid';
    const STACK_INPUT_INVALID = 'invalid';
    const STACK_INPUT_SCORE = 'score';
    const STACK_INPUT_GRAMMAR_FIX_INSERT_STARS = 1;
    const STACK_INPUT_GRAMMAR_FIX_SPACES = 2;
    const STACK_INPUT_GRAMMAR_FIX_SINGLE_CHAR = 4;

    private ?string $name;

    private array $context_session = array();

    private ?string $teacher_answer;

    private static array $all_parameter_names = array(
        'mustVerify',
        'showValidation',
        'boxWidth',
        'boxHeight',
        'strictSyntax',
        'insertStars',
        'syntaxHint',
        'syntaxAttribute',
        'forbidWords',
        'allowWords',
        'forbidFloats',
        'lowestTerms',
        'sameType'
    );

    private array $extra_options = array(
        'hideanswer' => false,
        'allowempty' => false
    );

    private StackOptions $options;

    private array $parameters = array();

    private array $errors = array();

    private array $raw_contents = array();

    private bool $runtime = true;

    /**
     * StackInput constructor
     * @throws StackException
     */
    public function __construct($name, $teacher_answer, $options = null, $parameters = null, $runtime = true) {
        if (trim($name) === '') {
            throw new StackException('StackInput: $name must be non-empty.');
        }

        $this->name = $name;
        $this->teacher_answer = $teacher_answer;
        $this->runtime = $runtime;

        if ($options == null || !is_a($options, 'src\core\options\StackOptions')) {
            throw new StackException('StackInput: $options must be stack_options.');
        }

        $this->options = $options;

        if ($parameters == null || !is_array($parameters)) {
            throw new StackException('StackInput: __construct: 3rd argument, $parameters, ' . 'must be null or an array of parameters.');
        }

        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
    }

    /**
     * Check if parameter is used
     * @param string $key
     * @return bool
     */
    private function isParameterUsed(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Set value of parameter
     * @param string $key
     * @param string $value
     * @throws StackException
     */
    private function setParameter(string $key, String $value): void
    {
        if (!$this->isParameterUsed($key)) {
            throw new StackException('StackInput: setting parameter ' . $key . ' which does not exist for inputs of type ' . get_class($this));
        }

        if ($key == 'showValidation' && !$value && $this->isParameterUsed('mustVerify')) {
            $this->setParameter('mustVerify', "0");
        }

        $this->parameters[$key] = $value;

        if ($key == 'insertStars') {
            $this->parameters['grammarAutofixes'] = (string) $this->convertLegacyInsertStars($value);
        }

        if ($key == 'options') {
            if ($value != "") {
                $tmp_options = explode(',', $value);

                foreach ($tmp_options as $option) {
                    $option = strtolower(trim($option));

                    list($option, $arg) = $this->parseOption($option);

                    if (array_key_exists($option, $this->extra_options)) {
                        if ($arg === '') {
                            $this->extra_options[$option] = "true";
                        } else {
                            $this->extra_options[$option] = $arg;
                        }
                    } else {
                        $this->errors[] = StackPlatform::getTranslation('inputoptionunknown', array($option));
                    }
                }
            }

            $this->validateExtraOptions();
        }
    }

    /**
     * Convert legacy insert stars
     * @param string $value
     * @return int
     */
    private function convertLegacyInsertStars(string $value) : int
    {
        $map = [
            // Don't insert stars.
            "0" => 0,
            // Insert stars for implied multiplication only.
            "1" => StackInput::STACK_INPUT_GRAMMAR_FIX_INSERT_STARS,
            // Insert stars assuming single-character variable names.
            "2" => StackInput::STACK_INPUT_GRAMMAR_FIX_INSERT_STARS | StackInput::STACK_INPUT_GRAMMAR_FIX_SINGLE_CHAR,
            // Insert stars for spaces only.
            "3" => StackInput::STACK_INPUT_GRAMMAR_FIX_SPACES,
            // Insert stars for implied multiplication and for spaces.
            "4" => StackInput::STACK_INPUT_GRAMMAR_FIX_INSERT_STARS | StackInput::STACK_INPUT_GRAMMAR_FIX_SPACES,
            // Insert stars assuming single-character variables, implied and for spaces.
            "5" => StackInput::STACK_INPUT_GRAMMAR_FIX_INSERT_STARS | StackInput::STACK_INPUT_GRAMMAR_FIX_SINGLE_CHAR | StackInput::STACK_INPUT_GRAMMAR_FIX_SPACES
        ];

        return $map[$value];
    }

    /**
     * Get value of parameter
     * @param string $string
     * @return string|null
     */
    private function getParameter(string $string): ?string
    {
        return $this->parameters[$string];
    }

    /**
     * Parse option separated by : to get option and argument
     * @param string $option
     * @return array|null
     */
    private function parseOption(string $option): ?array
    {
        $arg = '';

        if (!(strpos($option, ':') === false)) {
            $ops = explode(':', $option);
            $option = $ops[0];
            $arg = trim($ops[1]);
        }

        return(array($option, $arg));
    }

    /**
     * Check if all extra options are valid
     * @return void
     */
    private function validateExtraOptions(): void
    {
        foreach ($this->extra_options as $option => $arg) {
            switch ($option) {
                case 'manualgraded':
                case 'novars':
                case 'simp':
                case 'floatnum':
                case 'intnum':
                case 'rationalnum':
                case 'hideanswer':
                case 'allowempty':
                case 'rationalized':
                case 'negpow':
                case 'consolidatesubscripts':
                case 'hidedomain':
                case 'comments':
                case 'firstline':
                case 'assume_pos':
                case 'assume_real':
                case 'calculus':
                case 'nounits':
                case 'hideequiv':
                    if (!($arg == "true" || $arg == "false")) {
                        $this->errors[] = StackPlatform::getTranslation('numericalinputoptboolerr', array($option, $arg));
                    }
                    break;
                case 'mindp':
                case 'maxdp':
                case 'minsf':
                case 'maxsf':
                case 'checkvars':
                    if (!($arg == "false" || filter_var($arg, FILTER_VALIDATE_INT) || filter_var($arg, FILTER_VALIDATE_FLOAT))) {
                        $this->errors[] = StackPlatform::getTranslation('numericalinputoptinterr', array($option, $arg));
                    }
                    break;
                case 'mul':
                    // Mul was deprecated in version 4.2.
                    $this->errors[] = StackPlatform::getTranslation('stackversionmulerror', null);


                    if (!($arg == "true" || $arg == "false")) {
                        $this->errors[] = StackPlatform::getTranslation('numericalinputoptboolerr', array($option, $arg));
                    }
                    break;

                case 'align':
                    if ($arg !== 'left' && $arg !== 'right') {
                        $this->errors[] = StackPlatform::getTranslation('inputopterr', array($option, $arg));
                    }
                    break;
                case 'validator':
                    if (!preg_match('/^([a-zA-Z]+|[a-zA-Z]+[0-9a-zA-Z_]*[0-9a-zA-Z]+)$/', $arg)) {
                        $this->errors[] = StackPlatform::getTranslation('inputvalidatorerr', array($option, $arg));
                    }
                    break;
                default:
                    $this->errors[] = StackPlatform::getTranslation('inputoptionunknown', array($option));
            }
        }

        if (array_key_exists('mindp', $this->extra_options) && array_key_exists('maxdp', $this->extra_options)) {
            if ((float) $this->extra_options['mindp'] > (float) $this->extra_options['maxdp']) {
                $this->errors[] = StackPlatform::getTranslation('numericalinputminmaxerr', null);
            }
        }

        if (array_key_exists('minsf', $this->extra_options) && array_key_exists('maxsf', $this->extra_options)) {
            if ((float) $this->extra_options['minsf'] > (float) $this->extra_options['maxsf']) {
                $this->errors[] = StackPlatform::getTranslation('numericalinputminmaxerr', null);
            }
        }

        if ((array_key_exists('mindp', $this->extra_options) || array_key_exists('maxdp', $this->extra_options)) && (array_key_exists('minsf', $this->extra_options) || array_key_exists('maxsf', $this->extra_options))) {
            $this->errors[] = StackPlatform::getTranslation('numericalinputminsfmaxdperr', null);
        }
    }
}