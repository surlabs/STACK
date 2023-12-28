<?php
declare(strict_types=1);

namespace classes\core\evaluation;

use classes\core\maxima\StackSession;
use classes\core\StackQuestion;
use classes\platform\StackPlatform;

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
class StackPotentialResponseTree
{
    private int $id;
    private string $name;
    private bool $simplify;
    private int $feedback_style;
    private float $value;
    private string $feedback_variables;
    private array $nodes;
    private string $first_node;
    private ?StackQuestion $question;
    private array $trace;

    public function __construct(array $prt_data, StackQuestion $question = null)
    {
        if (!isset($prt_data['id'])) {
            $this->id = -1;
        } else {
            $this->id = (int)$prt_data['id'];
        }

        $this->name = (string)$prt_data['name'];
        $this->simplify = (bool)$prt_data['simplify'];
        $this->feedback_style = (int)$prt_data['feedback_style'];

        $this->value = (float)$prt_data['value'];

        $this->feedback_variables = (string)$prt_data['feedback_variables'];

        $this->nodes = $prt_data['nodes'];

        $this->first_node = (string)$prt_data['first_node'];

        $this->question = $question;

        $this->trace = array();
    }

    /**
     * Returns the value of the potential response tree
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * Retuns the name of the potential response tree
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the feedback style of the potential response tree
     * @return int.
     */
    public function getFeedbackStyle(): int
    {
        return $this->feedback_style;
    }

    /**
     * @return mixed The keyval-bit for some version changes.
     */
    public function getFeedbackVariablesKeyvals(): string
    {
        return $this->feedback_variables ?? '';
    }

    /**
     * A "formative" PRT is a PRT which does not contribute marks to the question.
     * This affected whether a response is "complete", and how marks are shown for feedback.
     * @return boolean
     */
    public function isFormative(): bool
    {
        return $this->feedback_style === 0;
    }

    /**
     * Returns the answer tests used by this PRT for version changes.
     * @return array
     */
    public function getAnswerTests(): array
    {
        $tests = array();

        foreach ($this->nodes as $node) {
            $tests[$node["answertest"]] = true;
        }

        return $tests;
    }

    /**
     * Representation of the PRT for Maxima offline use.
     */
    public function getMaximaRepresentation()
    {
        //TODO: Implement getMaximaRepresentation() method.
        // First we need to implement class MP_Node and child classes
    }

    /**
     * Return all the "sans" strings used in the nodes with test requiring a raw input.
     */
    public function getRawSansUsed()
    {
        //TODO: Implement getRawSansUsed() method.
        // First we need to implement static method stack_ans_test_controller::required_raw()
    }

    /**
     * Return all the non-trivial strings used in the node arguments
     * @return array
     */
    public function getRawArgumentsUsed(): array
    {
        $ans = array();

        foreach ($this->nodes as $key => $node) {
            $name = $this->getName() . '-' . ($key + 1);

            if (trim($node["sans"]) != '') {
                $ans[$name . '-sans'] = $node["sans"];
            }
            if (trim($node["tans"]) != '') {
                $ans[$name . '-tans'] = $node["tans"];
            }
        }

        return $ans;
    }

    /**
     * This lists all possible answer notes, used for question testing.
     * @return array string Of all the answer notes this tree might produce.
     */
    public function getAllAnswerNotes(): array
    {
        $node_notes = array();

        foreach ($this->nodes as $node) {
            $node_notes = array_merge($node_notes, [$node["trueanswernote"], $node["falseanswernote"]]);
        }

        $notes = array('NULL' => 'NULL');

        foreach ($node_notes as $note) {
            $notes[$note] = $note;
        }

        return $notes;
    }

    /**
     * That is to say, list the nodes in the order they are last visited
     * to allow simple guard clauses nice feature of acyclic graphs drops the orphans too.
     * @return array
     */
    private function getReversePostOrderNodes(): array
    {
        $order = [];
        $visited = [];

        if ($this->first_node === '') {
            $this->first_node = array_keys($this->nodes)[0];
        }

        $this->poRecurse($this->nodes[$this->first_node], $order, $visited);
        return array_reverse($order);
    }



    /**
     * This is a recursive function to find the postorder of the nodes.
     * @param object $node
     * @param array $postorder
     * @param array $visited
     * @return void
     */
    private function poRecurse(object $node, array &$postorder, array &$visited): void
    {
        $true_node = $this->getNode($node["truenextnode"]);
        $false_node = $this->getNode($node["falsenextnode"]);
        $visited[$node["nodename"]] = $node;

        if ($true_node != null && !array_key_exists($true_node["nodename"], $visited)) {
            $this->poRecurse($true_node, $postorder, $visited);
        }

        if ($false_node != null && !array_key_exists($false_node["nodename"], $visited)) {
            $this->poRecurse($false_node, $postorder, $visited);
        }

        $postorder[] = $node;
    }

    /**
     * Simple getter that handles the cases where the key is bad or null.
     * @param $name
     * @return object|null
     */
    private function getNode($name): ?object
    {
        if (isset($this->nodes[$name])) {
            return $this->nodes[$name];
        }

        return null;
    }

    /**
     * Summary of the nodes, for use in various logics that track answernotes and scores.
     */
    public function getNodesSummary()
    {
        //TODO: Implement getNodesSummary() method.
        // First we need to implement method compileNodeAnswertests();
    }

    /**
     * Return the options for the show validation select menu
     * @return array.
     */
    public function getFeedbackStyleOptions(): array
    {
        return array(
            '0' => StackPlatform::getTranslation('feedbackstyle0'),
            '1' => StackPlatform::getTranslation('feedbackstyle1'),
            '2' => StackPlatform::getTranslation('feedbackstyle2'),
            '3' => StackPlatform::getTranslation('feedbackstyle3'),
        );
    }

    /**
     * This is only for testing, you need to do more to check the actual text.
     *
     * @return string Raw feedback text as a single blob for checking.
     */
    public function getFeedbackTest(): string
    {
        $text = '';

        foreach ($this->nodes as $node) {
            if ($node["truefeedback"] !== null) {
                $text .= $node["truefeedback"];
            }
            if ($node["falsefeedback"] !== null) {
                $text .= $node["falsefeedback"];
            }
        }

        return $text;
    }

    public function compile()
    {
        //TODO: Implement compile() method.
        // First we need to implement stack_cas_keyval class
    }

    public static function compileNodeAnswerTest()
    {
        //TODO: Implement compileNodeAnswerTest() method.
        // First we need to implement stack_ans_test_controller class
    }

    public static function compileNode()
    {
        //TODO: Implement compileNode() method.
        // First we need to implement stack_ans_test_controller class
    }

    public function getPrtGraph()
    {
        //TODO: Implement getPrtGraph() method.
        // First we need to implement stack_abstract_graph class & getNodesSummary() method
    }

    /**
     * Returns the trace of the PRT.
     * @return array
     */
    public function getTrace(): array
    {
        return $this->trace;
    }
}