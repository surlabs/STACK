<?php
declare(strict_types=1);

namespace src\platform\ilias;

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilCtrlInterface;
use ilLanguage;
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
    private Factory $factory;
    private Renderer $renderer;
    private ilLanguage $language;
    private ilCtrlInterface $control;

    public function __construct()
    {
        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();
        $this->control = $DIC->ctrl();
    }

    /**
     * Gets the platform translation of a string
     * @param string $str
     * @return string|null
     */
    public function getTranslationInternal(string $str): ?string
    {
        return $this->language->txt($str);
    }

    /**
     * Gets platform default settings for STACK question options
     * @return array|null
     */
    public function getPlatformDefaultQuestionOptionsInternal(): ?array
    {
        return [];
    }

    /**
     * Creates an HTML object from the contents
     * @param string $tag
     * @param string $contents
     * @param array $attributes
     * @return string
     */
    public function createTagInternal(string $tag, string $contents, array $attributes = []): string {
        $html = "<" . $tag;

        foreach ($attributes as $key => $value) {
            $html .= " " . $key . "=\"" . $value . "\"";
        }

        $html .= ">" . $contents . "</" . $tag . ">";

        return $html;
    }
}