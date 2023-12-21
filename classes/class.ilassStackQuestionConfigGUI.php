<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use src\core\security\StackException;

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
    private Factory $factory;
    private Renderer $renderer;
    private ilLanguage $language;
    private ilGlobalTemplateInterface $tpl;
    private ilTabsGUI $tabs;
    private ilCtrlInterface $control;

    /**
     * @throws StackException
     */
    public function performCommand(string $cmd): void
    {
        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->control = $DIC->ctrl();

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

            //Get stored settings
            $data = assStackQuestionConfig::_getStoredSettings('all');

            switch ($cmd) {
                case "configure":
                    $this->configure($data);
                    break;
                case "maxima":
                    $this->maxima($data);
                    break;
                case "defaults":
                    $this->defaults($data);
                    break;
                case "quality":
                    $this->quality($data);
                    break;
                case "save":
                    $this->save();
                    break;
                default:
                    throw new StackException("Unknown configuration command: " . $cmd);
            }
        } catch (Exception $e) {
            throw new StackException($e->getMessage());
        }
    }

    /**
     * Shows the configuration overview of the plugin
     */
    private function configure(array $data): void
    {
        $this->tabs->activateTab("configure");
        $this->tpl->setContent(PluginConfigurationMainUI::show($data, $this->getPluginObject()));
    }

    /**
     * Shows the UI for the Maxima Connection settings
     */
    private function maxima(array $data): void
    {
        $this->tabs->activateTab("maxima");
        $this->tpl->setContent(PluginConfigurationMaximaUI::show($data, $this->getPluginObject()));
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
        $this->tpl->setContent(PluginConfigurationMainUI::save($this->getPluginObject()));
    }
}