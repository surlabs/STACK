<?php
declare(strict_types=1);

namespace classes\core\evaluation;
use classes\core\maxima\StackSession;
use classes\core\StackQuestion;
use classes\platform\StackPlatform;
use stdClass;

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
    private int $feedbackStyle;
    private float $value;
    private StackSession $feedbackVariables;
    private object $nodes;
    private string $firstNode;
    private ?StackQuestion $question;
    private array $trace;

    public function __construct(stdClass $prtdata, float $value, StackQuestion $question = null)
    {
        if (property_exists($prtdata, 'id')) {
            $this->id = $prtdata->id;
        }

        $this->name = $prtdata->name;
        $this->simplify = (bool)$prtdata->autosimplify;
        $this->feedbackStyle = (int)$prtdata->feedbackstyle;

        $this->value = $value;

        $this->feedbackVariables = $prtdata->feedbackvariables;

        $this->nodes = $prtdata->nodes;
        foreach ($this->nodes as $node) {
            if (!property_exists($node, 'id')) {
                $node->id = null;
            }
        }

        $this->firstNode = (string)$prtdata->firstnodename;

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
        return $this->feedbackStyle;
    }

    /**
     * @return mixed The keyval-bit for some version changes.
     */
    public function getFeedbackVariablesKeyvals(): mixed
    {
        return $this->feedbackVariables ?? '';
    }

    /**
     * A "formative" PRT is a PRT which does not contribute marks to the question.
     * This affected whether a response is "complete", and how marks are shown for feedback.
     * @return boolean
     */
    public function isFormative(): bool
    {
        return $this->feedbackStyle === 0;
    }

    /**
     * Returns the answer tests used by this PRT for version changes.
     * @return array
     */
    public function getAnswerTests(): array
    {
        $tests = array();

        foreach ($this->nodes as $node) {
            $tests[$node->answertest] = true;
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
    public function getRawSansUsed() {
        //TODO: Implement getRawSansUsed() method.
        // First we need to implement static method stack_ans_test_controller::required_raw()
    }

    /**
     * Return all the non-trivial strings used in the node arguments
     * @return array
     */
    public function getRawArgumentsUsed() :array {
        $ans = array();

        foreach ($this->nodes as $key => $node) {
            $name = $this->getName() . '-' . ($key + 1);

            if (trim($node->sans) != '') {
                $ans[$name . '-sans'] = $node->sans;
            }
            if (trim($node->tans) != '') {
                $ans[$name . '-tans'] = $node->tans;
            }
        }

        return $ans;
    }

    /**
     * This lists all possible answer notes, used for question testing.
     * @return array string Of all the answer notes this tree might produce.
     */
    public function getAllAnswerNotes() :array {
        $nodenotes = array();

        foreach ($this->nodes as $node) {
            $nodenotes = array_merge($nodenotes, [$node->trueanswernote, $node->falseanswernote]);
        }

        $notes = array('NULL' => 'NULL');

        foreach ($nodenotes as $note) {
            $notes[$note] = $note;
        }

        return $notes;
    }

    /**
     * That is to say, list the nodes in the order they are last visited to allow simple guard clauses nice feature of acyclic graphs drops the orphans too.
     * @return array
     */
    private function getReversePostOrderNodes(): array {
        $order   = [];
        $visited = [];

        if ($this->firstNode === '') {
            $this->firstNode = array_keys($this->nodes)[0];
        }

        $this->poRecurse($this->nodes[$this->firstNode], $order, $visited);
        return array_reverse($order);
    }

    /**
     * This is a recursive function to find the postorder of the nodes.
     * @param object $node
     * @param array $postorder
     * @param array $visited
     * @return void
     */
    private function poRecurse(object $node, array &$postorder, array &$visited): void {
        $truenode                 = $this->getNode($node->truenextnode);
        $falsenode                = $this->getNode($node->falsenextnode);
        $visited[$node->nodename] = $node;

        if ($truenode != null && !array_key_exists($truenode->nodename, $visited)) {
            $this->poRecurse($truenode, $postorder, $visited);
        }

        if ($falsenode != null && !array_key_exists($falsenode->nodename, $visited)) {
            $this->poRecurse($falsenode, $postorder, $visited);
        }

        $postorder[] = $node;
    }

    /**
     * Simple getter that handles the cases where the key is bad or null.
     * @param $name
     * @return object|null
     */
    private function getNode($name) :?object {
        if (isset($this->nodes[$name])) {
            return $this->nodes[$name];
        }

        return null;
    }

    /**
     * Summary of the nodes, for use in various logics that track answernotes and scores.
     */
    public function getNodesSummary() {
        //TODO: Implement getNodesSummary() method.
        // First we need to implement method compileNodeAnswertests();
    }

    /**
     * Return the options for the show validation select menu
     * @return array.
     */
    public function getFeedbackStyleOptions() :array {
        return array(
            '0' => StackPlatform::getTranslation('feedbackstyle0', null),
            '1' => StackPlatform::getTranslation('feedbackstyle1', null),
            '2' => StackPlatform::getTranslation('feedbackstyle2', null),
            '3' => StackPlatform::getTranslation('feedbackstyle3', null),
        );
    }

    /**
     * This is only for testing, you need to do more to check the actual text.
     *
     * @return string Raw feedback text as a single blob for checking.
     */
    public function getFeedbackTest() :string {
        $text = '';

        foreach ($this->nodes as $node) {
            if ($node->truefeedback !== null) {
                $text .= $node->truefeedback;
            }
            if ($node->falsefeedback !== null) {
                $text .= $node->falsefeedback;
            }
        }

        return $text;
    }

    public function compile() {
        //TODO: Implement compile() method.
        // First we need to implement stack_cas_keyval class
    }

    public static function compileNodeAnswerTest() {
        //TODO: Implement compileNodeAnswerTest() method.
        // First we need to implement stack_ans_test_controller class
    }

    public static function compileNode() {
        //TODO: Implement compileNode() method.
        // First we need to implement stack_ans_test_controller class
    }

    public function getPrtGraph() {
        //TODO: Implement getPrtGraph() method.
        // First we need to implement stack_abstract_graph class & getNodesSummary() method
    }

    /**
     * Returns the trace of the PRT.
     * @return array
     */
    public function getTrace() :array {
        return $this->trace;
    }
}