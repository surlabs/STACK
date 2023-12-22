<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use classes\platform\StackDatabase;
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
    private ilLanguage $language;

    public function __construct()
    {
        global $DIC;

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
     * @param string|null $category
     * @return void
     */
    public function setConfigInternal(string $key, mixed $value, ?string $category = null): void
    {
        if (isset($category)) {
            $this->config[$category][$key] = $value;
        } else {
            $this->config[$key] = $value;
        }

        StackDatabase::insertOnDuplicatedKey(
            'xqcas_configuration',
            array(
                'parameter_name' => $key,
                'value' => $value,
                'category' => $category
            )
        );
    }

    /**
     * Gets the platform configuration value for a given key
     * @param string $key
     * @param string|null $category
     * @return mixed
     */
    public function getConfigInternal(string $key, ?string $category = null): mixed {
        if (isset($category)) {
            return $this->config[$category][$key];
        } else {
            return $this->config[$key];
        }
    }

    /**
     * Gets all the platform configuration values
     * @param string|null $category
     * @return array
     */
    public function getAllConfigInternal(?string $category = null) :array {
        if (isset($category)) {
            return $this->config[$category];
        } else {
            return $this->config;
        }
    }
}