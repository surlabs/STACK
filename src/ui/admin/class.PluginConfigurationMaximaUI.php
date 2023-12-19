<?php
declare(strict_types=1);

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
 */
class PluginConfigurationMaximaUI
{

    private static Factory $factory;
    private static Renderer $renderer;
    private static ilCtrlInterface $control;

    private static $request;


    /**
     * Shows the plugin configuration Maxima settings form
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
            $content .= self::$renderer->render(self::getAddServerButton($plugin_object));

            //Show Maxima servers available
            $content .= self::$renderer->render(self::getServerUI($data, $plugin_object));

            //Show local configuration optional block
            $content .= self::$renderer->render(self::getLocalUI($data, $plugin_object));
        } catch (Exception $e) {

            echo $e->getMessage();
            exit;

        }

        return $content;
    }

    /**
     * Gets the form for the plugin configuration Maxima settings when using
     * the Server option to connect to Maxima
     * @param array $data
     * @param ilPlugin $plugin_object
     */
    private static function getServerUI(array $data, ilPlugin $plugin_object): ILIAS\UI\Component\Input\Container\Form\Standard
    {
    }

    /**
     * Gets the form for the plugin configuration Maxima settings when using
     * the Local option to connect to Maxima
     * @param array $data
     * @param ilPlugin $plugin_object
     * @return string
     */
    private static function getLocalUI(array $data, ilPlugin $plugin_object): ILIAS\UI\Component\Input\Container\Form\Standard
    {
    }

    /**
     * Gets the add server button showed in the maxima servers configuration
     * @throws ilCtrlException
     */
    private static function getAddServerButton(ilPlugin $plugin_object): ILIAS\UI\Implementation\Component\Button\Bulky
    {
        return self::$factory->button()->bulky(
            self::$factory->symbol()->icon()->standard(
                'nota',
                $plugin_object->txt('ui_admin_configuration_add_server_button_label'),
                'medium'
            ),
            $plugin_object->txt('ui_admin_configuration_add_server_button_label'),
            self::$control->getLinkTargetByClass("ilassStackQuestionConfigGUI", 'addServer')
        );
    }


}