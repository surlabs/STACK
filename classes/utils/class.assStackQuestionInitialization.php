<?php
/**
 *  This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
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

require_once __DIR__ . '/ilias_moodle_functions.php';
require_once __DIR__ . '/class.StackIframeHolder.php';
if (!class_exists('api\\util\\StackIframeHolder', false)) {
    class_alias(StackIframeHolder::class, 'api\\util\\StackIframeHolder');
}
require_once __DIR__ . '/../stack/maximaparser/MP_classes.php';
require_once __DIR__ . '/../stack/maximaparser/lexer.base.class.php';
require_once __DIR__ . '/../stack/maximaparser/decimal.comma.lexer.class.php';
require_once __DIR__ . '/../stack/maximaparser/parser.options.class.php';
require_once __DIR__ . '/../stack/maximaparser/parser.common.classes.php';
require_once __DIR__ . '/../stack/maximaparser/autogen/parser-root.php';
require_once __DIR__ . '/../stack/maximaparser/autogen/parser-equivline.php';

$stackParsingRulesDir = __DIR__ . '/../stack/cas/parsingrules';
require_once $stackParsingRulesDir . '/filter.interface.php';
foreach (glob($stackParsingRulesDir . '/*.filter.php') as $stackParsingRuleFile) {
    require_once $stackParsingRuleFile;
}
require_once $stackParsingRulesDir . '/504_insert_tuples_for_groups.php';
