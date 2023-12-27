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

        /*
        $upload_handler = new \ilObjFileUploadHandlerGUI();
        $file = self::$factory->input()->field()->file(
            $upload_handler,
            $plugin_object->txt('ui_author_import_from_moodle_xml_title'),
            $plugin_object->txt('ui_author_import_from_moodle_xml_description')
        )->withAcceptedMimeTypes(['text/xml'])->withMaxFiles(1);


        $form = self::$factory->input()->container()->form()->standard(
            $form_action,
            ['moodle_xml' => $file]);
        */

        $form_action = self::$control->getFormAction($gui_object, "importQuestionFromMoodleXML");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($form_action);
        $form->setTitle($plugin_object->txt('ui_author_import_moodle_xml_title'));

        //Upload XML file
        $item = new ilFileInputGUI($plugin_object->txt('ui_author_import_moodle_xml_file'), 'questions_xml');
        $item->setSuffixes(array('xml'));
        $form->addItem($item);

        $hiddenFirstId = new ilHiddenInputGUI('first_question_id');
        $hiddenFirstId->setValue($_GET['q_id']);
        $form->addItem($hiddenFirstId);

        $form->addCommandButton("importQuestionFromMoodleXmlDoImport", $plugin_object->txt("ui_author_import_moodle_xml_import_button"));
        $form->addCommandButton("editQuestion", $plugin_object->txt("ui_author_import_moodle_xml_cancel_import_button"));

        return self::$renderer->render(self::$factory->legacy($form->getHTML()));
    }


}