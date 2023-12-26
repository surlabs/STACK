<?php
declare(strict_types=1);

/*
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

/**
 * @ilCtrl_isCalledBy assStackQuestionGUI: ilObjQuestionPoolGUI, ilObjTestGUI, ilQuestionEditGUI, ilTestExpressPageObjectGUI
 * @ilCtrl_Calls assStackQuestionGUI: ilFormPropertyDispatchGUI
 */
class assStackQuestionGUI extends assQuestionGUI
{

    /**
     * @var assStackQuestion
     */
    public assQuestion $object;

    /**
     * assStackQuestionGUI constructor.
     */
    public function __construct($id = -1)
    {
        parent::__construct();
        $this->object = new assStackQuestion();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    /**
     * Returns the HTML for the Test View
     * @param $active_id
     * @param $pass
     * @param $is_question_postponed
     * @param $user_post_solutions
     * @param $show_specific_inline_feedback
     * @return void
     */
    public function getTestOutput(
        $active_id,
        $pass,
        $is_question_postponed,
        $user_post_solutions,
        $show_specific_inline_feedback
    )
    {
        //TODO REDO
    }

    /**
     * Returns question view with correct response filled in
     * @param integer $active_id The active user id
     * @param integer|null $pass The test pass
     * @param boolean $graphicalOutput Show visual feedback for right/wrong answers
     * @param boolean $result_output Show the reached points for parts of the question
     * @param boolean $show_question_only Show the question without the ILIAS content around
     * @param boolean $show_feedback Show the question feedback
     * @param boolean $show_correct_solution Show the correct solution instead of the user solution
     * @param boolean $show_manual_scoring Show specific information for the manual scoring output
     * @param bool $show_question_text
     * @return string
     */
    public function getSolutionOutput(
        $active_id,
        $pass = null,
        $graphicalOutput = false,
        $result_output = false,
        $show_question_only = true,
        $show_feedback = false,
        $show_correct_solution = false,
        $show_manual_scoring = false,
        $show_question_text = true
    ): string
    {
        //TODO REDO
        return '';
    }

    /**
     * Returns the HTML for the question Preview
     * @param bool $show_question_only
     * @param bool $showInlineFeedback
     * @return string HTML
     */
    public function getPreview($show_question_only = false, $showInlineFeedback = false): string
    {
        //TODO REDO
        return '';
    }

    /**
     * Returns the HTML for the specific feedback output
     * @param $userSolution
     * @return string HTML Code with the rendered specific feedback text including the general feedback
     */
    public function getSpecificFeedbackOutput($userSolution): string
    {
        //TODO REDO
        return '';
    }

    /**
     * Evaluates a posted edit form and writes the form data in the question object
     * (called frm generic commands in assQuestionGUI)
     * Converts the data from post into assStackQuestion ($this->object)
     * Called before editQuestion()
     *
     * @return integer    0: question can be saved / 1: form is not complete
     */
    public function writePostData($always = false): int
    {
        $hasErrors = !$always && $this->editQuestion(TRUE);
        if (!$hasErrors) {

            //Parent
            $this->writeQuestionGenericPostData();

            //This
            $this->writeQuestionSpecificPostData();

            //Taxonomies
            $this->saveTaxonomyAssignments();

            return 0;
        }
        return 1;
    }

    /**
     * Writes the data from $_POST into assStackQuestion
     * Called before editQuestion()
     */
    public function writeQuestionSpecificPostData(): void
    {
        //TODO REDO
    }

    /**
     * Populate taxonomy section in a form
     * (made public to be called from authoring GUI)
     *
     * @param ilPropertyFormGUI $form
     */
    public function populateTaxonomyFormSection(ilPropertyFormGUI $form): void
    {
        parent::populateTaxonomyFormSection($form);
    }


    /**
     * Creates an output of the edit form for the question
     *
     * @param bool $check_only
     *
     * @return bool
     */
    public function editQuestion(bool $check_only = false): bool
    {
        //TODO REDO
        return true;
    }

    /**
     * Sets the ILIAS tabs for this question type
     * called from ilObjTestGUI and ilObjQuestionPoolGUI
     */
    public function setQuestionTabs(): void
    {
        //TODO REDO
    }

    /**
     * Actually runs the Importing of questions
     * @return void
     */
    public function importQuestionFromMoodle()
    {
        //TODO REDO
    }

    /**
     * Actually runs the export to MoodleXML
     * @return void
     */
    public function exportQuestionToMoodle()
    {
        //TODO REDO
    }

}