<?php
declare(strict_types=1);

namespace src\core\options;
use src\platform\StackPlatform;

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 * This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 * originally created by Chris Sangwin.
 *
 * The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "STACK Question" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/STACK
 *
 * If you need support, please contact the maintainer of this software at:
 * stack@surlabs.es
 *
 *********************************************************************/
class StackOptionsDefault
{

    /**
     * StackDefaultOptions constructor.
     * Creates a new StackDefaultOptions object with the given options from
     * the array of platform options or the default options if
     * the given array is empty.
     */
    public static function getDefaultOptions(): ?array
    {

        /**
         * Array of default options
         * Types can be: boolean, string, html, list.
         * STACK Default options
         */
        $default_options_data = [
            'display' => [
                'type' => 'list',
                'value' => 'LaTeX',
                'strict' => true,
                'values' => ['LaTeX', 'String'],
                'caskey' => 'OPT_OUTPUT',
                'castype' => 'string',
            ],
            'multiplicationsign' => [
                'type' => 'list',
                'value' => 'dot',
                'strict' => true,
                'values' => ['dot', 'cross', 'onum', 'none'],
                'caskey' => 'make_multsgn',
                'castype' => 'fun',
            ],
            'complexno' => [
                'type' => 'list',
                'value' => 'i',
                'strict' => true,
                'values' => ['i', 'j', 'symi', 'symj'],
                'caskey' => 'make_complexJ',
                'castype' => 'fun',
            ],
            'inversetrig' => [
                'type' => 'list',
                'value' => 'cos-1',
                'strict' => true,
                'values' => ['cos-1', 'acos', 'arccos', 'arccos-arcosh'],
                'caskey' => 'make_arccos',
                'castype' => 'fun',
            ],
            'logicsymbol' => [
                'type' => 'list',
                'value' => 'lang',
                'strict' => true,
                'values' => ['lang', 'symbol'],
                'caskey' => 'make_logic',
                'castype' => 'fun',
            ],
            'sqrtsign' => [
                'type' => 'boolean',
                'value' => true,
                'strict' => true,
                'values' => [],
                'caskey' => 'sqrtdispflag',
                'castype' => 'ex',
            ],
            'simplify' => [
                'type' => 'boolean',
                'value' => true,
                'strict' => true,
                'values' => [],
                'caskey' => 'simp',
                'castype' => 'ex',
            ],
            'assumepos' => [
                'type' => 'boolean',
                'value' => false,
                'strict' => true,
                'values' => [],
                'caskey' => 'assume_pos',
                'castype' => 'ex',
            ],
            'assumereal' => [
                'type' => 'boolean',
                'value' => false,
                'strict' => true,
                'values' => [],
                'caskey' => 'assume_real',
                'castype' => 'ex',
            ],
            'matrixparens' => [
                'type' => 'list',
                'value' => '[',
                'strict' => true,
                'values' => ['[', '(', '', '{', '|'],
                'caskey' => 'lmxchar',
                'castype' => 'exs',
            ],
        ];

        $array_platform_default_options = StackPlatform::getPlatformDefaultOptions();

        //Overwrite default with given options
        foreach ($array_platform_default_options as $option_key => $option_value) {
            if (isset($default_options_data[$option_key]['value'])) {
                $default_options_data[$option_key]['value'] = $option_value;
            } else {
                //TODO: Log error, invalid option value
                return null;
            }
        }

        return $default_options_data;
    }

}