<?php
declare(strict_types=1);

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
class MoodleXmlImport
{
    /**
     * Plugin instance for language management
     * @var ilassStackQuestionPlugin
     */
    private ilassStackQuestionPlugin $plugin;

    /**
     * The current question
     * @var assStackQuestion
     */
    private assStackQuestion $question;

    /**
     * Question_id for the first question to import
     * (When only one question, use this as Question_Id)
     * @var int If first question this var is higher than 0.
     */
    private int $first_question;


    /**
     * @var string    allowed html tags, e.g. "<em><strong>..."
     */
    private string $rte_tags = "";

    /**
     * media objects created for an imported question
     * This list will be cleared for every new question
     * @var array    id => object
     */
    private array $media_objects = array();

    /**
     * Set all the parameters for this question, including the creation of
     * the first assStackQuestion object.
     * @param ilassStackQuestionPlugin $plugin
     * @param int $first_question_id the question_id for the first question to import.
     * @param assStackQuestion $question
     */
    function __construct(ilPlugin $plugin, int $first_question_id, assQuestion $question)
    {
        //Set Plugin and first question id.
        $this->setPlugin($plugin);
        $this->setFirstQuestion($first_question_id);
        $this->setQuestion($question);
    }

    /* MAIN METHODS BEGIN */

    /**
     * ### MAIN METHOD OF THIS CLASS ###
     * This method is called from assStackQuestion to import the questions from an MoodleXML file.
     * @param $xml_file
     * @return bool
     */
    public function import($xml_file): bool
    {
        //Step 1: Get data from XML.
        //LIBXML_NOCDATA Merge CDATA as Textnodes
        $xml = simplexml_load_file($xml_file, null, LIBXML_NOCDATA);

        //Step 2: Initialize question in ILIAS
        $number_of_questions_created = 0;

        foreach ($xml->question as $question) {

            if($this->loadFromMoodleXML($question)) {
                $number_of_questions_created++;
            }
            var_dump($xml);
            exit;
            $type = (string)$question->attributes()['type'];

        }

        if($number_of_questions_created > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Initializes $this->getQuestion with the values from the XML object.
     * @param SimpleXMLElement $question
     * @return bool
     */
    public function loadFromMoodleXML(SimpleXMLElement $question): bool
    {
        return true;
    }

    /**
     * @param ilassStackQuestionPlugin $plugin
     */
    public function setPlugin(ilassStackQuestionPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return ilassStackQuestionPlugin
     */
    public function getPlugin(): ilassStackQuestionPlugin
    {
        return $this->plugin;
    }

    /**
     * @param assStackQuestion $question
     */
    public function setQuestion(assStackQuestion $question)
    {
        $this->question = $question;
    }

    /**
     * @return assStackQuestion
     */
    public function getQuestion(): assStackQuestion
    {
        return $this->question;
    }

    /**
     * @param int $first_question
     */
    public function setFirstQuestion(int $first_question)
    {
        $this->first_question = $first_question;
    }

    /**
     * @return int
     */
    public function getFirstQuestion(): int
    {
        return $this->first_question;
    }

    /**
     * @param $tags
     */
    public function setRTETags($tags)
    {
        $this->rte_tags = $tags;
    }

    /**
     * @return string    allowed html tags, e.g. "<em><strong>..."
     */
    public function getRTETags(): string
    {
        return $this->rte_tags;
    }

    /* GETTERS AND SETTERS END */

}
