<?php
declare(strict_types=1);

namespace src\core\options;

use src\core\maxima\StackSession;
use src\core\security\StackException;

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
class StackOptions
{

    const STACK_OPTIONS_STATUS_ERROR = -1;
    const STACK_OPTIONS_STATUS_UNINITIALIZED = 0;
    const STACK_OPTIONS_STATUS_INITIALIZED = 1;
    const STACK_OPTIONS_STATUS_PREPARED_FOR_MAXIMA = 2;

    /**
     * @var int The current status of the StackOptions object
     */
    private int $status = self::STACK_OPTIONS_STATUS_UNINITIALIZED;

    /**
     * @var array The array of options
     */
    private array $data = [];

    /**
     * @var array The array of options in Maxima format
     */
    private array $maxima_options = [];

    //STACK Options

    private ?string $display_mode = null;

    private ?string $multiplication_sign = null;

    private ?string $complex_number = null;

    private ?string $inverse_trigonometric = null;

    private ?string $logic_symbols = null;

    private ?bool $surd_for_square_root = null;

    private ?bool $question_level_simplify = null;

    private ?bool $assume_positive = null;

    private ?bool $assume_real = null;

    private ?string $matrix_parentheses = null;

    /**
     * StackOptions constructor.
     * Creates a new StackOptions object with the given options.
     * If blank array, the default options are used.
     * @param array $array_options
     * @throws StackException
     */
    public function __construct(array $array_options)
    {
        $this->data = StackOptionsDefault::getDefaultOptions();

        //Overwrite default with given options
        foreach ($array_options as $option_key => $option_value) {
            if (!array_key_exists($option_key, $this->data)) {
                //TODO: Log error, invalid option name
                $this->status = self::STACK_OPTIONS_STATUS_ERROR;
                throw new StackException('stack_options construct: $key ' . $option_key . ' is not a valid option name.');
            } else {
                if (isset($this->data[$option_key]['value'])) {
                    $this->data[$option_key]['value'] = $option_value;
                } else {
                    //TODO: Log error, invalid option value
                    return null;
                }
            }
        }

        try {
            //Set attributes of the object
            //Castings are done here
            $this->display_mode = (string)$this->data['display']['value'];
            $this->multiplication_sign = (string)$this->data['multiplicationsign']['value'];
            $this->complex_number = (string)$this->data['complexno']['value'];
            $this->inverse_trigonometric = (string)$this->data['inversetrig']['value'];
            $this->logic_symbols = (string)$this->data['logicsymbol']['value'];
            $this->surd_for_square_root = (bool)$this->data['sqrtsign']['value'];
            $this->question_level_simplify = (bool)$this->data['simplify']['value'];
            $this->assume_positive = (bool)$this->data['assumepos']['value'];
            $this->assume_real = (bool)$this->data['assumereal']['value'];
            $this->matrix_parentheses = (string)$this->data['matrixparens']['value'];

            $this->status = self::STACK_OPTIONS_STATUS_INITIALIZED;
        } catch (StackException $e) {
            //TODO: Log error, invalid option value
            $this->status = self::STACK_OPTIONS_STATUS_ERROR;
        }
    }

    /**
     * Sets maxima_options array with the options in Maxima format.
     * @return array|null returns true if the object has been initialized, false otherwise
     */
    public function prepareForMaxima(): ?bool
    {
        if ($this->status !== self::STACK_OPTIONS_STATUS_INITIALIZED) {
            //TODO: Log error, object already initialized or with error
            return false;
        }

        try {
            $names = [];
            $commands = [];
            foreach ($this->data as $option_key => $option_value) {
                if (!is_null($option_value['castype'])) {
                    $value = $this->formatOptionsValueBasedOnType($option_value);
                    $this->processCasType($option_value, $value, $names, $commands);
                }
            }
        } catch (StackException $e) {
            //TODO: Log error,  invalid option value
        }

        $this->maxima_options = [
            'names' => implode(', ', $names),
            'commands' => implode(StackSession::MAXIMA_COMMANDS_SEPARATOR, $commands)
        ];

        $this->status = self::STACK_OPTIONS_STATUS_PREPARED_FOR_MAXIMA;
        return true;
    }

    /**
     * @param $option
     * @return mixed|string
     */
    private function formatOptionsValueBasedOnType($option): mixed
    {
        return ('boolean' === $option['type']) ? ($option['value'] ? 'true' : 'false') : $option['value'];
    }

    /**
     * @param $option
     * @param $value
     * @param $names
     * @param $commands
     * @return void
     */
    private function processCasType($option, $value, &$names, &$commands): void
    {
        switch ($option['castype']) {
            case 'ex':
                $names[] = $option['caskey'];
                $commands[] = $option['caskey'] . ':' . $value;
                break;
            case 'exs':
                $names[] = $option['caskey'];
                $commands[] = $option['caskey'] . ':"' . $value . '"';
                break;
            case 'fun':
                $commands[] = $option['caskey'] . '("' . $value . '")';
                break;
        }
    }

    /**
     * Returns current status of the object.
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getMaximaOptions(): array
    {
        return $this->maxima_options;
    }

    /**
     * @return string|null
     */
    public function getDisplayMode(): ?string
    {
        return $this->display_mode;
    }

    /**
     * @return string|null
     */
    public function getMultiplicationSign(): ?string
    {
        return $this->multiplication_sign;
    }

    /**
     * @return string|null
     */
    public function getComplexNumber(): ?string
    {
        return $this->complex_number;
    }

    /**
     * @return string|null
     */
    public function getInverseTrigonometric(): ?string
    {
        return $this->inverse_trigonometric;
    }

    /**
     * @return string|null
     */
    public function getLogicSymbols(): ?string
    {
        return $this->logic_symbols;
    }

    /**
     * @return bool|null
     */
    public function getSurdForSquareRoot(): ?bool
    {
        return $this->surd_for_square_root;
    }

    /**
     * @return bool|null
     */
    public function getQuestionLevelSimplify(): ?bool
    {
        return $this->question_level_simplify;
    }

    /**
     * @return bool|null
     */
    public function getAssumePositive(): ?bool
    {
        return $this->assume_positive;
    }

    /**
     * @return bool|null
     */
    public function getAssumeReal(): ?bool
    {
        return $this->assume_real;
    }

    /**
     * @return string|null
     */
    public function getMatrixParentheses(): ?string
    {
        return $this->matrix_parentheses;
    }

}