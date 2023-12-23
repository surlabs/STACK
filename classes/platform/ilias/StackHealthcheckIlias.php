<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use classes\platform\StackConfig;

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
class StackHealthcheckIlias
{

    /**
     * Performs a healthcheck on the maxima settings and platform requirements
     * @return array
     */
    public static function doHealthcheck(): array
    {
        $healthcheck_data = [];

        //Check if the platform config is loaded
        $healthcheck_data['is_stack_config_loaded'] = self::isStackConfigLoaded();

        //Check if the mbstring extension is loaded
        $healthcheck_data['is_mbstring_loaded'] = self::isMbstringLoaded();

        //Checks the current supported maxima libraries
        $healthcheck_data['is_maxima_libraries_supported'] = self::validateMaximaLibraries();

        return $healthcheck_data;
    }

    /**
     * Checks if the platform config is loaded
     * @return array
     */
    public static function isStackConfigLoaded(): array
    {
        $platform_data = StackConfig::getAll();
        if (!empty ($platform_data))
            $data['platform'] = [
                'type' => 'success',
                'data' => $platform_data,
                'message' => 'Platform data retrieved successfully.'
            ];
        else {
            $data['platform'] = [
                'type' => 'error',
                'data' => null,
                'message' => 'Platform data could not be retrieved.'
            ];
        }
        return $data;
    }

    /**
     * Checks if the mbstring extension is loaded
     * @return array
     */
    public static function isMbstringLoaded(): array
    {
        if (!extension_loaded('mbstring')) {
            $data = [
                'type' => 'error',
                'data' => null,
                'message' => 'STACK requires the PHP mbstring extension to be used. STACK questions might not work properly until this is installed.',
            ];
        } else {
            $data = [
                'type' => 'success',
                'data' => null,
                'message' => 'The PHP mbstring extension is installed.',
            ];
        }
        return $data;
    }

}