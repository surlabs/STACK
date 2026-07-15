<?php
declare(strict_types=1);

use classes\platform\ilias\StackBulktestingIlias;
use classes\platform\StackCheckPrt;
use classes\platform\StackConfig;
use classes\platform\StackPlatform;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;


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
 * @ilCtrl_isCalledBy ilassStackQuestionConfigGUI: ilObjComponentSettingsGUI
 * @ilCtrl_Calls ilassStackQuestionConfigGUI: ilFormPropertyDispatchGUI
 *
 */
class ilassStackQuestionConfigGUI extends ilPluginConfigGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilCtrl $control;
    protected GlobalHttpState $http;
    protected Factory $factory;
    protected ilCtrl $ctrl;
    protected $request;
    protected Renderer $renderer;
    private ilLanguage $language;

    /**
     * @throws stack_exception|ilCtrlException
     */
    public function performCommand($cmd): void
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->control = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->factory = $DIC->ui()->factory();
        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->renderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();

        //Initialize the plugin platform
        StackPlatform::initialize('ilias');
        //Initialize some STACK required parameters
        require_once __DIR__ . '/utils/class.assStackQuestionInitialization.php';
        //Set tabs
        //try {

        $this->tabs->addTab(
            "configure",
            $this->getPluginObject()->txt("ui_admin_configuration_overview_title"),
            $this->control->getLinkTarget($this, "configure")
        );

        $this->tabs->addTab(
            "maxima",
            $this->getPluginObject()->txt("ui_admin_configuration_maxima_title"),
            $this->control->getLinkTarget($this, "maxima")
        );

        $this->tabs->addTab(
            "defaults",
            $this->getPluginObject()->txt("ui_admin_configuration_defaults_title"),
            $this->control->getLinkTarget($this, "defaults")
        );

        $this->tabs->addTab(
            "quality",
            $this->getPluginObject()->txt("ui_admin_configuration_quality_title"),
            $this->control->getLinkTarget($this, "quality")
        );

        //Add plugin title and description
        $this->tpl->setTitle($this->getPluginObject()->txt('ui_admin_configuration_title'));
        $this->tpl->setDescription($this->getPluginObject()->txt('ui_admin_configuration_description'));

        //Get stored settings from the platform database
        $data = StackConfig::getAll();

        switch ($cmd) {
            case "configure":
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "configure");
                $rendered = $this->renderForm($form_action, $cmd);
                break;
            case "maxima":
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "maxima");
                $rendered = $this->renderForm($form_action, $cmd);
                break;
            case "defaults":
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "defaults");
                $rendered = $this->renderForm($form_action, $cmd);
                break;
            case "quality":
                $this->quality($data);
                return;
            case "healthcheck":
                $sections[] = $this->healthcheck();
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "healthcheck");
                $rendered = $this->renderPanel($data, $form_action, $sections);
                break;
            case 'clearCache':
                StackConfig::clearCache();
                $this->quality($data);
                return;
            case "bulktesting":
                //TODO connect with the bulktesting class
                $data = StackBulktestingIlias::doBulktesting();
                $sections = $this->bulktesting($data);
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "bulktesting");
                $rendered = $this->renderPanel($data, $form_action, $sections);
                break;
            case "checkPrts":
                $rendered = "";
                $comma_count = 0;

                foreach (StackCheckPrt::getErrors() as $question_id => $error) {
                    $no_prts = true;

                    if (!empty($error["missing_placeholders"])) {
                        $no_prts = false;

                        $pane = '<div style="display: flex; width: 100%; justify-content: space-between;">';
                        $pane .= sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_placeholders_missing_placeholders'), $question_id, implode(', ', $error["missing_placeholders"]));
                        $this->ctrl->setParameterByClass("ilassStackQuestionConfigGUI", "question_id", $question_id);
                        $pane .= $this->renderer->render($this->factory->button()->standard($DIC->language()->txt("qpl_qst_xqcas_fix"), $this->ctrl->getLinkTargetByClass("ilassStackQuestionConfigGUI", "fixPrtPlaceholders")));
                        $pane .= '</div>';
                        $pane .= '<br><strong>Title: </strong>' . $error["title"];

                        $rendered .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->confirmation($pane));
                    }

                    if (!empty($error["badname"])) {
                        $no_prts = false;

                        $pane = '<div style="display: flex; width: 100%; justify-content: space-between;">';
                        $pane .= sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_bad_name'), $question_id, implode(', ', $error["badname"]));
                        $this->ctrl->setParameterByClass("ilassStackQuestionConfigGUI", "question_id", $question_id);
                        $pane .= $this->renderer->render($DIC->ui()->factory()->button()->standard($DIC->language()->txt("qpl_qst_xqcas_fix"), $this->ctrl->getLinkTargetByClass("ilassStackQuestionConfigGUI", "fixPrtName")));
                        $pane .= '</div>';
                        $pane .= '<br><strong>Title: </strong>' . $error["title"];

                        $rendered .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->info($pane));
                    }

                    if (!empty($error["comma_errors"])) {
                        $no_prts = false;

                        $pane = '<div style="display: flex; width: 100%; justify-content: space-between;">';
                        $pane .= sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_comma_errors'), $question_id);
                        $this->ctrl->setParameterByClass("ilassStackQuestionConfigGUI", "question_id", $question_id);
                        $pane .= $this->renderer->render($DIC->ui()->factory()->button()->standard($DIC->language()->txt("qpl_qst_xqcas_fix"), $this->ctrl->getLinkTargetByClass("ilassStackQuestionConfigGUI", "fixPrtCommaErrors")));
                        $pane .= '</div>';

                        foreach ($error["comma_errors"] as $comma_error) {
                            $pane .= sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_comma_error'), $comma_error["prt"], $comma_error["node"], $comma_error["key"], $comma_error["value"], $comma_error["fixed_value"]);
                            $pane .= '<br>';
                        }

                        $pane .= '<br><strong>Title: </strong>' . $error["title"];

                        $comma_count++;

                        $rendered .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->confirmation($pane));
                    }

                    if ($no_prts) {
                        $pane = sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_placeholders_no_prts'), $question_id);
                        $pane .= '<br><br><strong>Title: </strong>' . $error["title"];

                        $rendered .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure($pane));
                    }
                }

                if ($rendered == "") {
                    $rendered = $this->renderer->render($this->factory->messageBox()->success($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prts_all_ok')));
                }

                if ($comma_count > 1) {
                    $bulky_comma = $this->renderer->render($DIC->ui()->factory()->button()->standard($DIC->language()->txt("qpl_qst_xqcas_fix_all_comma_errors"), $this->ctrl->getLinkTargetByClass("ilassStackQuestionConfigGUI", "fixCommaErrorsBulky")));

                    $rendered = $bulky_comma . "<br><br>" . $rendered;
                }

                break;
            case "fixPrtPlaceholders":
                $result = StackCheckPrt::fixMissingPlaceholders($this->request->getQueryParams()["question_id"]);


                $rendered = "<h2>" . $DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_placeholders_fixed') . "</h2>";
                $rendered .= "<br><strong>Title: </strong><br>" . $result["title"];
                $rendered .= "<br><br><strong>" . $DIC->language()->txt('qpl_qst_xqcas_options_specific_feedback') . "</strong>:<br>" . $result["specific_feedback"];

                $rendered = $this->renderer->render($this->factory->messageBox()->success($rendered));
                break;
            case "fixPrtName":
                $result = StackCheckPrt::fixBadNames($this->request->getQueryParams()["question_id"]);

                $rendered = "<h2>" . $DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_names_fixed') . "</h2>";
                $rendered .= "<br><strong>Title: </strong><br>" . $result["title"];
                $rendered .= "<br><br>" . $result["changed"];

                $rendered = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($rendered));

                break;
            case "fixPrtCommaErrors":
                $result = StackCheckPrt::fixCommaErrors($this->request->getQueryParams()["question_id"]);

                $rendered = "<h2>" . $DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_comma_errors_fixed') . "</h2>";
                $rendered .= "<br><strong>Title: </strong><br>" . $result["title"];
                $rendered .= "<br><br>" . $result["changed"];

                $rendered = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($rendered));

                break;
            case "fixCommaErrorsBulky":
                $result = StackCheckPrt::fixCommaErrorsBulky();

                $rendered = "<h2>" . $DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_comma_errors_fixed') . "</h2>";
                $rendered .= "<br><strong>" . $DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_comma_errors_fixed_count') . "</strong> " . $result["fixed_count"];
                $rendered .= "<br><br>" . $result["changed"];
                $rendered = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($rendered));

                break;
            default:
                throw new stack_exception("Unknown configuration command: " . $cmd);
        }
        //} catch (Exception $e) {
        //    throw new stack_exception("Error at perform command: " . $e->getMessage());
        //}

        //sets the rendered content as the main content of the template
        $this->tpl->setContent($rendered);

    }

    /**
     * Renders the form with the given data and sections
     * @param string $form_action
     * @param string $section_type
     * @return string
     * @throws ilCtrlException
     * @throws stack_exception
     */
    private function renderForm(string $form_action, string $section_type): string
    {
        //Create the form
        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $this->buildSection($section_type)
        );

        $saving_info = "";

        //Check if the form has been submitted
        if ($this->request->getMethod() == "POST") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
            if($result){
                $saving_info = $this->save();
                $form = $this->factory->input()->container()->form()->standard(
                    $form_action,
                    $this->buildSection($section_type)
                );
            }
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
        $page = $this->factory->modal()->lightboxTextPage("LOREN IPSUM", $this->language->txt("qpl_qst_xqcas_message_question_text"));
        $modal = $this->factory->modal()->lightbox($page);

        $button = $this->factory->button()->standard($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_show_question_text_action_text"), '')
            ->withOnClick($modal->getShowSignal());

        //Return the UI component
        /*return $this->renderer->render($this->factory->panel()->sub(
            "LOREN IPSUM",
            $this->factory->legacy(
                "LOREN IPSUM" .
                $this->renderer->render($this->factory->divider()->horizontal()) .
                $this->renderer->render($sections)
            )
        ));*/
        return $this->renderer->render($sections);
    }

    /**
     * Shows the configuration overview of the plugin
     */
    private function configure(array $data): array
    {
        $this->tabs->activateTab("configure");
        return PluginConfigurationMainUI::show($data, $this->getPluginObject());
    }

    /**
     * Shows the UI for the Maxima Connection settings
     * @throws stack_exception|ilCtrlException
     */
    private function maxima(array $data): array
    {
        $this->tabs->activateTab("maxima");
        return PluginConfigurationMaximaUI::show($data, $this->getPluginObject());
    }

    /**
     * Shows the UI Form of the defaults values for the plugin
     */
    private function defaults(array $data): array
    {
        $this->tabs->activateTab("defaults");
        return PluginConfigurationDefaultsUI::show($data, $this->getPluginObject());
    }

    /**
     * Shows the UI for the quality assurance settings
     */
    private function quality(array $data): void
    {
        $this->tabs->activateTab("quality");
        $this->tpl->setContent(PluginConfigurationQualityUI::show($data, $this->getPluginObject()));
    }

    private function healthcheck(): array
    {
        $this->tabs->activateTab("quality");
        return PluginConfigurationHealthcheckUI::show($this->getPluginObject());
    }

    private function bulktesting(array $data): array
    {
        $this->tabs->activateTab("quality");
        return PluginConfigurationBulktestingUI::show($data, $this->getPluginObject());
    }

    /**
     * Saves the configuration
     */
    private function save(): string
    {
        $result = StackConfig::save();

        if ($result === true) {
            return $this->renderer->render($this->factory->messageBox()->success($this->plugin_object->txt("ui_admin_configuration_saved")));
        } else {
            return $this->renderer->render($this->factory->messageBox()->failure($this->plugin_object->txt("ui_admin_configuration_not_saved")));
        }
    }

    /**
     * @throws stack_exception
     * @throws ilCtrlException
     */
    private function buildSection(string $section_type): array
    {
        $data = StackConfig::getAll();

        return match ($section_type) {
            "configure" => $this->configure($data),
            "maxima" => $this->maxima($data),
            "defaults" => $this->defaults($data),
            default => throw new stack_exception("Unknown section type: " . $section_type),
        };
    }
}