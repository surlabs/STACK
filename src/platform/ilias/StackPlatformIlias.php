<?php
declare(strict_types=1);

namespace src\platform\ilias;

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
class StackPlatformIlias extends StackPlatform
{
    /**
     * Gets all required platform information to run stack
     * @return array|null
     */
    public static function getPlatformInformation(): ?array
    {
        // TODO: Implement getPlatformInformation() method.
        return [];
    }


    /**
     * Gets platform default settings for STACK question options
     * @return array|null
     */
    public static function getPlatformDefaultQuestionOptions(): ?array
    {
        //TODO: Implement
        return [];
    }

}