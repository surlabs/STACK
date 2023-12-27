<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Button\Bulky;
use ILIAS\UI\Renderer;
use classes\core\security\StackException;

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
class AuthorMainUI
{

    private static Factory $factory;
    private static Renderer $renderer;
    private static ilCtrlInterface $control;

    /**
     * Shows the selection ui for new questions
     * @throws ilCtrlException
     */
    public static function show(ilPlugin $plugin_object): array
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$renderer = $DIC->ui()->renderer();
        self::$control = $DIC->ctrl();

        return [
            self::getImportFromMoodleXML($plugin_object),
            self::getEditQuestionForm($plugin_object)
        ];
    }

    /**
     * @throws ilCtrlException
     */
    private static function getImportFromMoodleXML(ilPlugin $plugin_object): Bulky
    {
        return self::$factory->button()->bulky(
            self::$factory->symbol()->icon()->standard(
                'import',
                $plugin_object->txt('ui_author_import_from_moodle_xml_button_label'),
                'medium'
            ),
            $plugin_object->txt('ui_author_import_from_moodle_xml_button_label'),
            self::$control->getLinkTargetByClass("assStackQuestionGUI", "importQuestionFromMoodleXmlRenderUI")
        );
    }

    /**
     * @throws ilCtrlException
     */
    private static function getEditQuestionForm(ilPlugin $plugin_object): Bulky
    {
        return self::$factory->button()->bulky(
            self::$factory->symbol()->icon()->standard(
                'nota',
                $plugin_object->txt('ui_author_create_blank_question_button_label'),
                'medium'
            ),
            $plugin_object->txt('ui_author_create_blank_question_button_label'),
            self::$control->getLinkTargetByClass("assStackQuestionGUI", "editQuestionForm")
        );
    }

}