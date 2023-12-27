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
    private ilTabsGUI $tabs;
    private ilCtrlInterface $control;
    private \ILIAS\HTTP\Services $http;
    private \ILIAS\UI\Factory $factory;
    private \ILIAS\UI\Renderer $renderer;
    private $global_request;

    /**
     * assStackQuestionGUI constructor.
     * Works as any other ILIAS Question GUI constructor
     */
    public function __construct($id = -1)
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->control = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->global_request = $DIC->http()->request();

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

    //UI Functionalities

    /**
     * Try to avoid use of tabs
     * @return void
     */
    public function setQuestionTabs(): void
    {
        //Instead of the usual tabs for questions, stack uses a single tab
        // and a panel with the different sections on top
        $this->addBackTab($this->tabs);
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
        if($this->object->getId()==-1){
            //New question, show creation selection menu
            $sections = AuthorMainUI::show($this->object->getPlugin());
            $form_action = $this->control->getLinkTargetByClass("assStackQuestionGUI", "configure");
            $rendered = $this->renderPanel($this->object->getStackQuestion()->getInternalData(), $form_action, $sections);

        }
        $this->tpl->setContent($rendered);
        return true;
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

    /**
     * Renders the form with the given data and sections
     * @param array $data
     * @param string $form_action
     * @param array $sections
     * @return string
     */
    private function renderForm(array $data, string $form_action, array $sections): string
    {
        //Create the form
        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections
        );

        //Check if the form has been submitted
        if ($this->global_request->getMethod() == "POST") {
            $form = $form->withRequest($this->global_request);
            $result = $form->getData();
            $saving_info = "ok";
        } else {
            $saving_info = "";
        }

        return $saving_info . $this->renderer->render($form);
    }

    /**
     * Renders the panel with the given data and sections
     * @param array $data
     * @param string $form_action
     * @param array $sections
     * @return string
     */
    private function renderPanel(array $data, string $form_action, array $sections): string
    {

        //TODO REPLACE WITH ACTUAL PANEL
        $page = $this->factory->modal()->lightboxTextPage("LOREN IPSUM", $this->lng->txt("qpl_qst_xqcas_message_question_text"));
        $modal = $this->factory->modal()->lightbox($page);

        $button = $this->factory->button()->standard($this->lng->txt("qpl_qst_xqcas_ui_author_randomisation_show_question_text_action_text"), '')
            ->withOnClick($modal->getShowSignal());

        //Return the UI component
        return $this->renderer->render($this->factory->panel()->sub(
            "LOREN IPSUM",
            $this->factory->legacy(
                "LOREN IPSUM" .
                $this->renderer->render($this->factory->divider()->horizontal()) .
                $this->renderer->render([$button, $modal])
            )
        ));
    }

}