<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Input\Field\Section;
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
 */
class PluginConfigurationMainUI
{

    private static Factory $factory;
    private static Renderer $renderer;
    private static ilCtrlInterface $control;

    /**
     * Shows the plugin configuration overview
     */
    public static function show(array $data, ilPlugin $plugin_object): string
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$renderer = $DIC->ui()->renderer();
        self::$control = $DIC->ctrl();

        try {

            //Form action
            $form_action = self::$control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "save");

            //try to show a composed form
            $content = self::$factory->input()->container()->form()->standard($form_action, [
                self::getValidationDefaultsSection($data, $plugin_object),
            ]);

        } catch (Exception $e) {
            $content = self::$factory->messageBox()->failure($e->getMessage());
        }

        return self::$renderer->render($content);
    }

    /**
     * Gets the defaults validation section
     * @throws StackException
     */
    private static function getValidationDefaultsSection(array $data, ilPlugin $plugin_object): Section
    {

        if (isset($data['instant_validation']) && $data['instant_validation'] == '1') {
            $validation_value = $data['instant_validation'];
        } elseif (isset($data['instant_validation']) && $data['instant_validation'] == '0') {
            $validation_value = $data['instant_validation'];
        } else {
            throw new StackException("Error: instant_validation value not found");
        }

        $validation_options = self::$factory->input()->field()->radio(
            "",
            ""
        )
            ->withOption('0',
                $plugin_object->txt("ui_admin_configuration_defaults_user_validation_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_user_validation_description"))
            ->withOption('1',
                $plugin_object->txt("ui_admin_configuration_defaults_instant_validation_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_instant_validation_description")
            )
            ->withValue($validation_value);

        return self::$factory->input()->field()->section(
            [
                $validation_options
            ],
            $plugin_object->txt("ui_admin_configuration_defaults_validation_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_validation_description")
        );
    }

}