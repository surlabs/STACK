<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;
use classes\platform\StackPlatform;

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
    private array $config = [];
    private Factory $factory;
    private Renderer $renderer;
    private ilLanguage $language;

    public function __construct()
    {
        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();
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
        // TODO: Check this to use $this->factory and $this->renderer instead of pure HTML

        $html = "<" . $tag;

        foreach ($attributes as $key => $value) {
            $html .= " " . $key . "=\"" . $value . "\"";
        }

        $html .= ">" . $contents . "</" . $tag . ">";

        return $html;
    }

    /**
     * Set the platform configuration value for a given key to a given value
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setConfigInternal(string $key, mixed $value): void
    {
        $this->config[$key] = $value;

        //TODO: Save config to database
    }

    /**
     * Gets the platform configuration value for a given key
     * @param string $key
     * @return mixed
     */
    public function getConfigInternal(string $key): mixed
    {
        return $this->config[$key];
    }

    /**
     * Gets all the platform configuration values
     * @return array
     */
    public function getAllConfigInternal() :array {
        return $this->config;
    }
}