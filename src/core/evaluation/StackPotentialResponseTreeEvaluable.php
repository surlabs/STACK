<?php
declare(strict_types=1);

namespace src\core\evaluation;
use src\core\filters\StackParser;

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
class StackPotentialResponseTreeEvaluable
{
    private string $signature;
    private ?array $feedback;
    private ?string $renderedfeedback;
    private ?array $path;
    private ?array $notes;
    private ?int $score;
    private ?int $penalty;
    private ?string $evaluated;
    private array $errors = array();
    private int $weight = 1;
    //TODO: Define the type of this variable, old type: castext2_static_replacer
    private $statics;
    private array $trace = array();

    //TODO: Define the type of $statics in constructor, old type: castext2_static_replacer
    public function __construct(string $signature, int $weight, $statics, array $trace) {
        $this->signature = $signature;
        $this->weight = $weight;
        $this->errors = [];
        $this->statics = $statics;
        $this->trace = $trace;
    }

    /**
     * Set evaluated value
     * @param string $value
     * @return void
     */
    public function setEvaluated(string $value): void {
        $this->evaluated = $value;
    }

    /**
     * Get if is valid checking if there are errors
     * @return bool
     */
    public function isValid(): bool {
        return count($this->getErrors()) === 0;
    }

    /**
     * Get the signature
     * @return string
     */
    public function getSignature(): string {
        return $this->signature;
    }

    /**
     * Set the errors
     * @param array $errors
     * @return void
     */
    public function setErrors(array $errors) :void {
        $this->errors = $errors;
    }

    /**
     * Get the source context
     * @return string
     */
    public function getSourceContext(): string {
        // Assume the signature has the PRT-name and use it.
        return explode('(', substr($this->signature, 4))[0];
    }

    /**
     * Check if is evaluated
     * @return bool
     */
    public function isEvaluated(): bool {
        return $this->evaluated !== null;
    }

    /**
     * Set rendered feedback
     * @param string $feedback
     * @return void
     */
    public function setRenderedFeedback(string $feedback) :void {
        $this->renderedfeedback = $feedback;
    }

    private function unpack() {
        //TODO: Implement unpack() method
        // First we need to implement the class castext2_parser_utils
    }

    /**
     * Get the score
     * @return int|null
     */
    public function getScore() :?int {
        if ($this->score === null) {
            $this->unpack();
        }
        // The score is null when we have errors. No matter what.
        if (!$this->isValid()) {
            return null;
        }
        return $this->score;
    }

    /**
     * Get the penalty
     * @return int|null
     */
    public function getPenalty() :?int {
        if ($this->penalty === null) {
            $this->unpack();
        }
        // The penalty is null when we have errors. No matter what.
        if (!$this->isValid()) {
            return null;
        }
        // The penalty is 0 if the score is 1. No matter what.
        if ($this->score == 1) {
            return 0;
        }

        return $this->penalty;
    }

    /**
     * Get the fraction
     * @return float
     */
    public function getFraction() : float {
        return $this->weight * ($this->getScore() ?? 0);
    }

    /**
     * Get the fractional penalty
     * @return float
     */
    public function getFractionalPenalty() : float {
        return $this->weight * ($this->getPenalty() ?? 0);
    }

    /**
     * Get the path
     * @return array
     */
    public function getPath() : array {
        if ($this->path === null) {
            $this->unpack();
        }
        return $this->path;
    }

    public function getFeedback() {
        //TODO: Implement getFeedback() method
        // First we need to implement the class castext2_parser_utils
    }

    /**
     * Get answer notes
     * @return array
     */
    public function getAnswerNotes(): array {
        $path = $this->getPath();

        $notes = array();

        // Note at this point those values are still Maxima string so unwrap them.
        for ($i = 0; $i < count($path); $i++) {
            if ($path[$i][2] !== '""') {
                $notes[] = trim($path[$i][2]);
            }
            // We need to check the array_key_exists because in the case of a guard clause it will not.
            // Do we actually want to ignore the missing note here or indicate the note is missing with a note?
            if (array_key_exists($i, $this->notes) && $this->notes[$i] !== '""') {
                $notes[] = trim($this->notes[$i]);
            }
        }

        // Note at this point those values are still Maxima string so unwrap them.
        for ($i = 0; $i < count($notes); $i++) {
            $notes[$i] = trim(StackParser::maximaStringToPhpString($notes[$i]));
        }

        return $notes;
    }

    /**
     * Get errors
     * @param string|null $format
     * @return array
     */
    public function getErrors(?string $format = 'strings') :array {
        // Apparently one wants to separate feedback-var errors?
        $err = [];

        foreach ($this->errors as $er) {
            if (!str_contains($er->getContext(), '/fv')) {
                if ($format === 'strings') {
                    $err[] = $er->getLegacyError();
                } else {
                    $err[] = $er;
                }
            }
        }

        return $err;
    }

    /**
     * Get fv errors
     * @param string|null $format
     * @return array
     */
    public function getFvErrors(?string $format = 'strings') :array {
        $err = [];

        foreach ($this->errors as $er) {
            if (str_contains($er->getContext(), '/fv')) {
                if ($format === 'strings') {
                    $err[] = $er->getLegacyError();
                } else {
                    $err[] = $er;
                }
            }
        }

        return $err;
    }

    /**
     * Get trace
     * @return array
     */
    public function getTrace(): array {
        return $this->trace;
    }
}