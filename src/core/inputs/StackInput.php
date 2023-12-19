<?php
declare(strict_types=1);

namespace src\core\inputs;
use src\core\options\StackOptions;
use src\core\security\StackException;
use src\core\security\StackQuestionSecurity;
use src\core\security\StackQuestionTeacherAnswer;
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
abstract class StackInput
{
    const STACK_INPUT_BLANK = '';
    const STACK_INPUT_VALID = 'valid';
    const STACK_INPUT_INVALID = 'invalid';
    const STACK_INPUT_SCORE = 'score';

    const STACK_INPUT_GRAMMAR_FIX_INSERT_STARS = 1;
    const STACK_INPUT_GRAMMAR_FIX_SPACES = 2;
    const STACK_INPUT_GRAMMAR_FIX_SINGLE_CHAR = 4;

    private ?string $name;

    private array $contextSession = array();

    private ?string $teacherAnswer;

    private array $extraOptions = array(
        'hideanswer' => false,
        'allowempty' => false
    );

    private StackOptions $options;

    private array $parameters = array();

    private array $errors = array();

    private array $rawContents = array();

    private bool $runtime = true;

    /**
     * StackInput constructor
     * @throws StackException
     */
    public function __construct(string $name, ?string $teacherAnswer, StackOptions $options, ?array $parameters = null, bool $runtime = true) {
        if (trim($name) === '') {
            throw new StackException('StackInput: $name must be non-empty.');
        }

        $this->name = $name;
        $this->teacherAnswer = $teacherAnswer;
        $this->runtime = $runtime;

        $this->options = $options;

        if ($parameters != null && !is_array($parameters)) {
            throw new StackException('StackInput: __construct: 3rd argument, $parameters, ' . 'must be null or an array of parameters.');
        }

        if ($parameters)  {
            foreach ($parameters as $name => $value) {
                $this->setParameter($name, $value);
            }
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
     * @param mixed $value
     * @throws StackException
     */
    private function setParameter(string $key, mixed $value): void
    {
        if (!$this->isParameterUsed($key)) {
            throw new StackException('StackInput: setting parameter ' . $key . ' which does not exist for inputs of type ' . get_class($this));
        }

        if ($key == 'showValidation' && !$value && $this->isParameterUsed('mustVerify')) {
            $this->setParameter('mustVerify', false);
        }

        $this->parameters[$key] = $value;

        if ($key == 'insertStars') {
            $this->parameters['grammarAutofixes'] = $this->convertLegacyInsertStars($value);
        }

        if ($key == 'options') {
            if ($value != "") {
                $tmp_options = explode(',', $value);

                foreach ($tmp_options as $option) {
                    $option = strtolower(trim($option));

                    list($option, $arg) = $this->parseOption($option);

                    if (array_key_exists($option, $this->extraOptions)) {
                        if ($arg === '') {
                            $this->extraOptions[$option] = true;
                        } else {
                            $this->extraOptions[$option] = $arg;
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
            0 => 0,
            // Insert stars for implied multiplication only.
            1 => self::STACK_INPUT_GRAMMAR_FIX_INSERT_STARS,
            // Insert stars assuming single-character variable names.
            2 => self::STACK_INPUT_GRAMMAR_FIX_INSERT_STARS | self::STACK_INPUT_GRAMMAR_FIX_SINGLE_CHAR,
            // Insert stars for spaces only.
            3 => self::STACK_INPUT_GRAMMAR_FIX_SPACES,
            // Insert stars for implied multiplication and for spaces.
            4 => self::STACK_INPUT_GRAMMAR_FIX_INSERT_STARS | self::STACK_INPUT_GRAMMAR_FIX_SPACES,
            // Insert stars assuming single-character variables, implied and for spaces.
            5 => self::STACK_INPUT_GRAMMAR_FIX_INSERT_STARS | self::STACK_INPUT_GRAMMAR_FIX_SINGLE_CHAR | self::STACK_INPUT_GRAMMAR_FIX_SPACES
        ];

        return $map[$value];
    }

    /**
     * Get value of parameter
     * @param string $string
     * @param mixed $default
     * @return string|null
     */
    private function getParameter(string $string, mixed $default): mixed
    {
        return $this->parameters[$string] ?? $default;
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
        foreach ($this->extraOptions as $option => $arg) {
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
                    if (!is_bool($arg)) {
                        $this->errors[] = StackPlatform::getTranslation('numericalinputoptboolerr', array($option, $arg));
                    }
                    break;
                case 'mindp':
                case 'maxdp':
                case 'minsf':
                case 'maxsf':
                case 'checkvars':
                    if (!($arg || filter_var($arg, FILTER_VALIDATE_INT) || filter_var($arg, FILTER_VALIDATE_FLOAT))) {
                        $this->errors[] = StackPlatform::getTranslation('numericalinputoptinterr', array($option, $arg));
                    }
                    break;
                case 'mul':
                    // Mul was deprecated in version 4.2.
                    $this->errors[] = StackPlatform::getTranslation('stackversionmulerror', null);


                    if (!is_bool($arg)) {
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

        if (array_key_exists('mindp', $this->extraOptions) && array_key_exists('maxdp', $this->extraOptions)) {
            if ((float) $this->extraOptions['mindp'] > (float) $this->extraOptions['maxdp']) {
                $this->errors[] = StackPlatform::getTranslation('numericalinputminmaxerr', null);
            }
        }

        if (array_key_exists('minsf', $this->extraOptions) && array_key_exists('maxsf', $this->extraOptions)) {
            if ((float) $this->extraOptions['minsf'] > (float) $this->extraOptions['maxsf']) {
                $this->errors[] = StackPlatform::getTranslation('numericalinputminmaxerr', null);
            }
        }

        if ((array_key_exists('mindp', $this->extraOptions) || array_key_exists('maxdp', $this->extraOptions)) && (array_key_exists('minsf', $this->extraOptions) || array_key_exists('maxsf', $this->extraOptions))) {
            $this->errors[] = StackPlatform::getTranslation('numericalinputminsfmaxdperr', null);
        }
    }

    /**
     * Set context session
     * @param array|null $contextSession
     * @return void
     */
    public function setContextSession(?array $contextSession) {
        $this->$contextSession = $contextSession;
    }

    /**
     * Check if a value for a parameter is valid
     * @param string $key
     * @param string $value
     * @return bool
     * @throws StackException
     */
    public function isValidParameter(string $key, mixed $value): bool
    {
        if (!$this->isParameterUsed($key)) {
            throw new StackException('StackInput: setting parameter ' . $key . ' which does not exist for inputs of type ' . get_class($this));
        }

        switch($key) {
            case 'strictSyntax':
            case 'forbidFloats':
            case 'lowestTerms':
            case 'sameType':
            case 'mustVerify':
                return is_bool($value);
            case 'showValidation':
                $number = filter_var($value, FILTER_VALIDATE_FLOAT);

                if ($number === false) {
                    $number = filter_var($value, FILTER_VALIDATE_INT);
                }

                return is_numeric($number) && $number >= 0 && $number <= 3;
            case 'insertStars':
                $number = filter_var($value, FILTER_VALIDATE_FLOAT);

                if ($number === false) {
                    $number = filter_var($value, FILTER_VALIDATE_INT);
                }

                return is_numeric($number);
        }

        return true;
    }

    /**
     * Get the value of one extra options
     * @param string $option
     * @param mixed $default the default to return if this parameter is not set.
     * @return mixed
     */
    public function getExtraOption(string $option, mixed $default = false): mixed
    {
        if (array_key_exists($option, $this->extraOptions)) {
            return $this->extraOptions[$option];
        } else {
            return $default;
        }
    }

    /**
     * Return the value of all extra options.
     * @return array
     */
    public function getExtraOptions(): array
    {
        return $this->extraOptions;
    }

    /**
     * Get the teacher answer if it is set
     * @return string|null
     */
    public function getTeacherAnswer(): ?string
    {
        return $this->teacherAnswer;
    }

    /**
     * Get the teacher answer displayed in the general feedback
     * @param string $value
     * @param string $display
     * @return string
     */
    public function getTeacherAnswerDisplay(string $value, string $display) :string {
        if (!$this->getExtraOption('hideanswer')) {
            return '';
        }

        if (trim($value) == 'EMPTYANSWER') {
            return StackPlatform::getTranslation('teacheranswerempty', null);
        }

        return StackPlatform::getTranslation('teacheranswershow_disp', array('\( '.$display.' \)'));
    }

    /**
     * Check if the response is blank
     * @param array $response
     * @return bool
     */
    protected function isBlankResponse(array $response): bool {
        foreach ($response as $value) {
            if (trim($value) != '' || $value == 'EMPTYANSWER') {
                return false;
            }
        }

        return true;
    }

    public function validateStudentResponse()
    {
        //TODO: Implement validateStudentResponse() method.
        // First we have to implement class stack_secure_loader & static method stack_utils::php_string_to_maxima_string
    }

    /**
     * Allow different input types to change the CAS method used
     * @return string
     */
    protected function getValidationMethod() :string {
        return $this->getParameter('sameType', false) ? 'checktype' : 'typeless';
    }

    /**
     * Sort out which filters to apply, based on options to the input
     * Should be mostly independent of input type
     * @param StackQuestionSecurity $basesecurity
     * @return array
     */
    protected function validateContentsFilters(StackQuestionSecurity $basesecurity) :array {
        $secrules = clone $basesecurity;

        $secrules->setAllowedWords(explode(',', $this->getParameter('allowWords', '')));
        $secrules->setForbiddenWords(explode(',', $this->getParameter('forbidWords', '')));

        $grammarautofixes = $this->getParameter('grammarAutofixes', 0);

        $filterstoapply = array();

        if ($this->getParameter('forbidFloats', false)) {
            $filterstoapply[] = '101_no_floats';
        }

        if (get_class($this) === 'StackScientificUnitsInput' || get_class($this) === 'StackNumericalInput') {
            $filterstoapply[] = '210_x_used_as_multiplication';
        }

        // The common insert stars rules, that will be forced
        // and if you do not allow insertion of stars then it is invalid.
        $filterstoapply[] = '402_split_prefix_from_common_function_name';
        $filterstoapply[] = '403_split_at_number_letter_boundary';
        $filterstoapply[] = '406_split_implied_variable_names';

        $filterstoapply[] = '502_replace_pm';

        // Replace evaluation groups with tuples.
        $filterstoapply[] = '504_insert_tuples_for_groups';
        // Then ban the rest.
        $filterstoapply[] = '505_no_evaluation_groups';

        // Remove scripts and other related things from string-values.
        $filterstoapply[] = '997_string_security';

        // If stars = 0 then strict, ignore the other strict syntax.
        if ($grammarautofixes === 0) {
            $filterstoapply[] = '999_strict';
        }

        // Insert stars = 1.
        if ($grammarautofixes & self::STACK_INPUT_GRAMMAR_FIX_INSERT_STARS) {
            // The rules are applied anyway, we just check the use of them.
            // If code-tidy issue just negate the test and cut this one out.
            $donothing = true;
        } else if ($grammarautofixes !== 0) {
            $filterstoapply[] = '991_no_fixing_stars';
        }

        // Fix spaces = 2.
        if ($grammarautofixes & self::STACK_INPUT_GRAMMAR_FIX_SPACES) {
            // The rules are applied anyway, we just check the use of them.
            // If code-tidy issue just negate the test and cut this one out.
            $donothing = true;
        } else if ($grammarautofixes !== 0) {
            $filterstoapply[] = '990_no_fixing_spaces';
        }

        // Assume single letter variable names = 4.
        if ($grammarautofixes & self::STACK_INPUT_GRAMMAR_FIX_SINGLE_CHAR) {
            $filterstoapply[] = '410_single_char_vars';
        }

        // Consolidate M_1 to M1 and so on.
        if ($this->getExtraOption('consolidatesubscripts', false)) {
            $filterstoapply[] = '420_consolidate_subscripts';
        }

        return array($secrules, $filterstoapply);
    }

    protected function validateContents() {
        //TODO: Implement validateContents() method.
        // First we have to implement static method stack_ast_container::make_from_student_source
    }

    protected function extraOptionVariables() {
        //TODO: Implement extraOptionVariables() method.
        // First we have to implement static method stack_ast_container::make_from_teacher_source & class stack_secure_loader
    }

    protected function validationDisplay() {
        //TODO: Implement validationDisplay() method.
        // First we have to implement static methods castext2_parser_utils::postprocess_mp_parsed & class MP_Node and child classes
    }

    /**
     * @param StackInputState $state
     * @param string $fieldname
     * @param bool $readonly
     * @param array $tavalue
     */
    abstract public function render(StackInputState $state, string $fieldname, bool $readonly, array $tavalue);

    /**
     * This function returns the HTML of errors for the input
     * @return string
     */
    protected function renderErrors(): string
    {
        $errors = $this->getErrors();

        if ($errors) {
            $errors = implode(' ', $errors);
        }

        return StackPlatform::createTag("div", $errors, array('class' => 'error'));
    }

    public function renderValidation()
    {
        //TODO: Implement renderValidation() method.
        // First we have to implement static methods stack_ast_container::make_from_teacher_source, stack_maths::process_lang_string
    }

    /**
     * Get translation for list of variables
     * @param string $vars
     * @return string
     */
    protected function tagListOfVariables(string $vars) :string {
        return StackPlatform::getTranslation('studentValidation_listofvariables', array($vars));
    }

    /**
     * Transforms the student's response input into an array
     * @param array $response
     * @return array
     */
    protected function responseToContents(array $response) :array {
        $contents = array();

        if (array_key_exists($this->name, $response)) {
            $val = $response[$this->name];

            if (trim($val) == '' && $this->getExtraOption('allowempty')) {
                $val = 'EMPTYANSWER';
            }

            $contents = array($val);
        }

        return $contents;
    }

    /**
     * Transforms the lines array into a single string representing the student's answer
     * @param array $caslines
     * @return string
     * @throws StackException
     */
    protected function casLinesToAnswer(array $caslines) :string {
        if (array_key_exists(0, $caslines)) {
            return $caslines[0];
        }
        throw new StackException('casLinesToAnswer could not create the answer.');
    }

    /**
     * @param array $contents
     * @return string
     */
    public function contentsToMaxima(array $contents) :string {
        if (array_key_exists(0, $contents)) {
            return $contents[0];
        } else {
            return '';
        }
    }

    public function getCorrectResponse()
    {
        //TODO: Implement getCorrectResponse() method.
        // First we have to implement static method stack_ast_container::make_from_teacher_source
    }

    /**
     * Transforms a Maxima expression into an array of raw inputs which are part of a response.
     * Most inputs are very simple, but textarea and matrix need more here.
     * This is used to take a Maxima expression, e.g. a Teacher's answer or a test case, and directly transform it into expected inputs.
     *
     * @param array $in
     * @return array
     */
    public function maximaToResponseArray(array $in) :array {
        $response[$this->name] = $in;

        if ($this->getParameter('mustVerify', true)) {
            $response[$this->name . '_val'] = $in;
        }
        return $response;
    }

    public function replaceValidationTags() {
        //TODO: Implement replaceValidationTags() method.
        // First we have to implement renderValidation()
    }

    /**
     * The AJAX instant validation method mostly returns a Maxima expression.
     * Mostly, we need an array, labelled with the input name.
     *
     * The text areas and equiv input types are not Maxima expressions yet, as they have newline characters in.
     *
     * The matrix type is different.  The javascript creates a single Maxima expression, and we need to split this up into an array of individual elements.
     * @param string $in
     * @return array
     */
    public function ajaxToResponseArray(string $in) :array {
        return array($this->name => $in);
    }

    /**
     * Return the list of errors
     * @return array
     */
    public function getErrors(): array {
        $errors = array();

        foreach ($this->errors as $err) {
            $errors[trim($err)] = true;
        }

        return array_keys($errors);
    }

    /**
     * Provide a summary of the student's response for the Moodle reporting.
     * Notes do something different here.
     * @param string $name
     * @param StackInputState $state
     * @return string
     */
    public function summariseResponse(string $name, StackInputState $state): string {
        return $name . ': ' . $this->contentsToMaxima($state->getContents()) . ' [' . $state->getStatus() . ']';
    }
}