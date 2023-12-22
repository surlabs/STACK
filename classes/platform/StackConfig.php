<?php
declare(strict_types=1);

namespace classes\platform;

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

class StackConfig {
    private static array $config = [];

    private static array $updatedPaths = [];

    /**
     * Load the platform configuration
     * @return void
     */
    public static function load() :void {
        $config = StackDatabase::select('xqcas_configuration');

        foreach ($config as $row) {
            $json_decoded = json_decode($row['value'], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $row['value'] = $json_decoded;
            }

            if (isset($row['category'])) {
                self::$config[$row['category']][$row['parameter_name']] = $row['value'];
            } else {
                self::$config[$row['parameter_name']] = $row['value'];
            }
        }
    }

    /**
     * Set the platform configuration value for a given key to a given value
     * @param string $key
     * @param mixed $value
     * @param string|null $category
     * @return void
     */
    public static function set(string $key, mixed $value, ?string $category = null): void {
        if (isset($category)) {
            if (!isset(self::$config[$category])) {
                self::$config[$category] = [];
            }

            self::$config[$category][$key] = $value;
            self::$updatedPaths[] = $category . '/' . $key;
        } else {
            self::$config[$key] = $value;
            self::$updatedPaths[] = $key;
        }
    }

    /**
     * Gets the platform configuration value for a given key
     * @param string $key
     * @param string|null $category
     * @return mixed
     */
    public static function get(string $key, ?string $category = null): mixed {
        if (isset($category)) {
            return self::$config[$category][$key];
        } else {
            return self::$config[$key];
        }
    }

    /**
     * Gets all the platform configuration values
     * @param string|null $category
     * @return array
     */
    public static function getAll(?string $category = null) :array {
        if (isset($category)) {
            return self::$config[$category];
        } else {
            return self::$config;
        }
    }

    /**
     * Save the platform configuration if the parameter is updated
     * @return void
     */
    public static function save() :void {
        foreach (self::$updatedPaths as $fullPath) {
            $path = explode('/', $fullPath);

            $data = array();

            $where = array();

            if (count($path) === 1) {
                $where['parameter_name'] = $path[0];
                $data['value'] = self::$config[$path[0]];
            } elseif (count($path) === 2) {
                $where['parameter_name'] = $path[1];
                $data['value'] = self::$config[$path[0]][$path[1]];
                $data['category'] = $path[0];
            }

            if (is_array($data['value'])) {
                $data['value'] = json_encode($data['value']);
            }

            StackDatabase::update(
                'xqcas_configuration',
                $data,
                $where
            );

            // This eliminates one by one, in case in any case the execution of save() fails,
            // those that have not been saved will be saved in the next execution
            self::$updatedPaths[$fullPath] = null;
        }
    }
}