<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Button\Bulky;
use ILIAS\UI\Renderer;
use ILIAS\UI\Implementation\Component\Panel\Standard;

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
class PluginConfigurationMainUI
{

    private static Factory $factory;
    private static Renderer $renderer;
    private static ilCtrlInterface $control;
    private static $request;
    private static $ui;

    /**
     * Shows the plugin configuration overview
     */
    public static function show(array $data, ilPlugin $plugin_object): string
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$renderer = $DIC->ui()->renderer();
        self::$control = $DIC->ctrl();
        self::$request = $DIC->http()->request();

        $content = '';

        try {

            //Show security reminder
            $content .= self::$renderer->render(self::getSecurityReminderButton($plugin_object));
            $content .= self::$renderer->render(self::getMaximaConnectionPanel($data, $plugin_object));

            //Show global configuration form
        } catch (Exception $e) {

        }

        return $content;
    }

    /**
     * Gets the security button showed in the configuration page
     * @throws ilCtrlException
     */
    private static function getSecurityReminderButton(ilPlugin $plugin_object): Bulky
    {
        return self::$factory->button()->bulky(
            self::$factory->symbol()->icon()->standard(
                'nota',
                $plugin_object->txt('ui_admin_configuration_security_button_label'),
                'medium'
            ),
            $plugin_object->txt('ui_admin_configuration_security_button_label'),
            self::$control->getLinkTargetByClass("ilassStackQuestionConfigGUI", 'security')
        );
    }

    private static function getMaximaConnectionPanel(array $data, ilPlugin $plugin_object): ILIAS\UI\Component\Input\Container\Form\Standard
    {
        global $DIC;

        $checkbox_input = self::$factory->input()->field()->checkbox("Checkbox", "Check or not.")
            ->withValue(true);

        $form_action = self::$control->getLinkTargetByClass("ilassStackQuestionConfigGUI", 'security');
        //Step 2: define form and form actions
        $form = self::$factory->input()->container()->form()->standard($form_action, [ $checkbox_input]);

        //Step 3: implement some form data processing. Note, the value of the checkbox will
        // be 'checked' if checked a null if unchecked.
        if (self::$request->getMethod() == "POST") {
            $form = $form->withRequest(self::$request);
            $result = $form->getData();
        } else {
            $result = "No result yet.";
        }

        return $form;
    }

}