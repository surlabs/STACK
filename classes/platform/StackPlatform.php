<?php
declare(strict_types=1);

namespace classes\platform;

use classes\platform\ilias\StackPlatformIlias;

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
abstract class StackPlatform
{
    public static StackPlatform $platform;

    public static function setPlatform(string $x): void
    {
        switch ($x) {
            case 'ilias':
                self::$platform = new StackPlatformIlias();
                break;
            default:
                // TODO: Error
                break;
        }
    }

    /**
     * Gets the platform translation of a string
     * @param string $str
     * @param mixed $params
     * @return string|null
     */
    public static function getTranslation(string $str, mixed $params = null): ?string
    {
        $txt = self::$platform->getTranslationInternal($str);

        if (isset($params)) {
            if (is_string($params)) $params = array($params);

            $txt = vsprintf($txt, $params);
        }

        return $txt;
    }

    /**
     * Gets platform default settings for STACK question options
     * @return array|null
     */
    public static function getPlatformDefaultQuestionOptions(): ?array
    {
        return self::$platform->getPlatformDefaultQuestionOptionsInternal();
    }


    /**
     * @param string $tag
     * @param string $contents
     * @param array $attributes
     * @return string
     */
    public static function createTag(string $tag, string $contents, array $attributes = []): string
    {
        return self::$platform->createTagInternal($tag, $contents, $attributes);
    }

    /**
     * Set the platform configuration value for a given key to a given value
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setConfig(string $key, mixed $value): void
    {
        self::$platform->setConfigInternal($key, $value);
    }

    /**
     * Gets the platform configuration value for a given key
     * @param string $key
     * @return mixed
     */
    public static function getConfig(string $key): mixed
    {
        return self::$platform->getConfigInternal($key);
    }

    /**
     * Gets all platform configuration values
     * @return array
     */
    public static function getAllConfig() :array {
        return self::$platform->getAllConfigInternal();
    }
}