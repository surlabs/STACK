<?php
declare(strict_types=1);

namespace classes\platform\ilias;

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
     * @return bool
     */
    public static function doHealthcheck(): bool
    {
        //TODO: Implement healthcheck
        /*
         * En versiones anteriores implementado en:
         * classes/model/configuration/class.assStackQuestionHealthcheck.php
         */
        return true;
    }

}