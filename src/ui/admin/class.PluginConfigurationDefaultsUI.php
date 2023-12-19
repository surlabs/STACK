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
class PluginConfigurationDefaultsUI
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

        try {

            //Question level simplify
            $options_question_level_simplify = self::$factory->input()->field()->checkbox(
                $plugin_object->txt("ui_admin_configuration_defaults_question_simplify_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_question_simplify_description")
            );

            //Assume positive
            $options_assume_positive = self::$factory->input()->field()->checkbox(
                $plugin_object->txt("ui_admin_configuration_defaults_assume_positive_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_assume_positive_description")
            );

            //Assume real
            $options_assume_real = self::$factory->input()->field()->checkbox(
                $plugin_object->txt("ui_admin_configuration_defaults_assume_real_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_assume_real_description")
            );

            //Surd for sqrt
            $options_surd_for_sqrt = self::$factory->input()->field()->checkbox(
                $plugin_object->txt("ui_admin_configuration_defaults_surd_for_sqrt_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_surd_for_sqrt_description")
            );

            //Complex numbers
            $complex_numbers_options = [
                "i" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_i"),
                "j" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_j"),
                "symi" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_symi"),
                "symj" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_symj")
            ];

            $options_complex_numbers = self::$factory->input()->field()->select(
                $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_title"),
                $complex_numbers_options,
                $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_description")
            )->withValue(true);

            //Multiplication sign
            $multiplication_sign_options = [
                "dot" => $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_dot"),
                "cross" => $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_cross"),
                "none" => $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_none")
            ];

            $options_multiplication_sign = self::$factory->input()->field()->select(
                $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_title"),
                $multiplication_sign_options,
                $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_description")
            )->withValue(true);

            //Inverse trigonometric functions
            $inverse_trigonometric_options = [
                "cos-1" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_cos"),
                "acos" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_acos"),
                "arccos" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_arccos"),
                "arccos-arcosh" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_arccos_arcosh")
            ];

            $options_inverse_trigonometric = self::$factory->input()->field()->select(
                $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_title"),
                $inverse_trigonometric_options,
                $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_description")
            )->withValue(true);

            //Logic symbols
            $logic_symbols_options = [
                "lang" => $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_lang"),
                "symbol" => $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_symbolic")
            ];

            $options_logic_symbols = self::$factory->input()->field()->select(
                $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_title"),
                $logic_symbols_options,
                $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_description")
            )->withValue("symbol");

            //Shape of Matrix Parentheses
            $matrix_parentheses_options = [
                '[' => '[',
                '(' => '(',
                '' => '',
                '{' => '{',
                '|' => '|',
            ];

            $options_matrix_parentheses = self::$factory->input()->field()->select(
                $plugin_object->txt("ui_admin_configuration_defaults_matrix_parentheses_title"),
                $matrix_parentheses_options,
                $plugin_object->txt("ui_admin_configuration_defaults_matrix_parentheses_description")
            )->withValue(true);

            //Options section
            $options_section = self::$factory->input()->field()->section(
                [
                    $options_question_level_simplify,
                    $options_assume_positive,
                    $options_assume_real,
                    $options_surd_for_sqrt,
                    $options_complex_numbers,
                    $options_multiplication_sign,
                    $options_inverse_trigonometric,
                    $options_logic_symbols,
                    $options_matrix_parentheses
                ],
                $plugin_object->txt("ui_admin_configuration_defaults_options_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_options_description")
            );

            //Form action
            $form_action = self::$control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "save");

            $content = self::$factory->input()->container()->form()->standard($form_action, [
                $options_section
            ]);

        } catch (Exception $e) {
            $content = self::$factory->legacy("error");
        }

        if (self::$request->getMethod() == "POST") {
            $content = $content->withRequest(self::$request);
            $result = $content->getData()[0] ?? "";
        } else {
            $result = "No result yet.";
        }

        return self::$renderer->render($content);
    }


}