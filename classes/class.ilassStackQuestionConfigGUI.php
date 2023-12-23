<?php
declare(strict_types=1);

use classes\core\security\StackException;
use classes\platform\StackConfig;
use classes\platform\StackPlatform;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory;


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
    protected ilCtrlInterface $control;
    protected GlobalHttpState $http;
    protected Factory $factory;
    protected $request;
    protected $renderer;

    /**
     * @throws StackException|ilCtrlException
     */
    public function performCommand(string $cmd): void
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->control = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->factory = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->renderer = $DIC->ui()->renderer();

        //Initialize the plugin platform
        StackPlatform::initialize('ilias');

        //Set tabs
        try {

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

            //Get form sections to render depending on the command
            $sections = [];

            switch ($cmd) {
                case "configure":
                case "saveMain":
                    $sections = $this->configure($data);
                    $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "configure");
                    break;
                case "maxima":
                case "saveConnection":
                    $sections = $this->maxima($data);
                    $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "maxima");
                    break;
                case "defaults":
                case "saveDefaults":
                    $this->defaults($data);
                    $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "defaults");
                    break;
                case "quality":
                case "saveQuality":
                    $this->quality($data);
                    $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "quality");
                    break;
                default:
                    throw new StackException("Unknown configuration command: " . $cmd);
            }
        } catch (Exception $e) {
            throw new StackException($e->getMessage());
        }

        //Step 0: Declare dependencies

        //Create the form
        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections
        );

        //Check if the form has been submitted
        if ($this->request->getMethod() == "POST") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
        } else {
            $result = "No result yet.";
        }

        //Step 7: Render the form and the result of the data processing
        $this->tpl->setContent(
            "<pre>" . print_r($result, true) . "</pre><br/>" .
            $this->renderer->render($form));


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
     * @throws ilCtrlException
     * @throws StackException
     */
    private function saveMain(): void
    {
        //TODO SAVE MAIN CONFIGURATION
        exit;
        //Saul's magic
        //perform command to show the configuration overview
        $this->performCommand("configure");
    }

    /**
     * Shows the UI for the Maxima Connection settings
     */
    private function maxima(array $data): array
    {
        $this->tabs->activateTab("maxima");
        return PluginConfigurationMaximaUI::show($data, $this->getPluginObject());
    }

    /**
     * Shows the UI Form of the defaults values for the plugin
     */
    private function defaults(array $data): void
    {
        $this->tabs->activateTab("defaults");
        $this->tpl->setContent(PluginConfigurationDefaultsUI::show($data, $this->getPluginObject()));
    }

    /**
     * Shows the UI for the quality assurance settings
     */
    private function quality(array $data): void
    {
        $this->tabs->activateTab("quality");
        $this->tpl->setContent(PluginConfigurationQualityUI::show($data, $this->getPluginObject()));
    }

    /**
     * Saves the configuration
     */
    private function save(): void
    {
        $this->tabs->activateTab("configure");

    }
}