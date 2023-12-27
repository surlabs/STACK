<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Button\Bulky;
use ILIAS\UI\Implementation\Component\Input\Field\Section;
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
class AuthorImportMoodleXmlUI
{

    private static Factory $factory;
    private static Renderer $renderer;
    private static ilCtrlInterface $control;
    private static $request;

    /**
     * Shows the selection ui for new questions
     * @throws ilCtrlException
     */
    public static function show(assStackQuestionGUI $gui_object, ilPlugin $plugin_object): string
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$renderer = $DIC->ui()->renderer();
        self::$control = $DIC->ctrl();
        self::$request = $DIC->http()->request();

        $upload_handler = new \ilObjFileUploadHandlerGUI();
        $file = self::$factory->input()->field()->file(
            $upload_handler,
            $plugin_object->txt('ui_author_import_from_moodle_xml_title'),
            $plugin_object->txt('ui_author_import_from_moodle_xml_description')
        );
        $form_action = self::$control->getFormAction($gui_object, "importQuestionFromMoodleXML");

        $form = self::$factory->input()->container()->form()->standard(
            $form_action,
            ['moodle_xml' => $file]);

        if (self::$request->getMethod() == "POST") {
            $form = $form->withRequest(self::$request);
            $result = $form->getData();
            $moodle_xml_import = new MoodleXmlImport($plugin_object, 100, $gui_object->object);
            var_dump($upload_handler->getUploadResult());exit;
            $moodle_xml_import->import($result['moodle_xml']);
            exit;
        }

        return self::$renderer->render($form);
    }

    /**
     */
    private static function getImportFromMoodleXML(ilPlugin $plugin_object): Section
    {
        $file = self::$factory->input()->field()->file(new \ilUIDemoFileUploadHandlerGUI(), "File Upload", "You can drop your files here");

        return self::$factory->input()->field()->section(
            $file,
            $plugin_object->txt('ui_author_import_from_moodle_xml_button_label'),
            $plugin_object->txt('ui_author_import_from_moodle_xml_button_description'),
        );
    }


}