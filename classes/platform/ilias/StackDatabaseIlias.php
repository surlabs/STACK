<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use classes\platform\StackDatabase;
use ilDBInterface;

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
class StackDatabaseIlias extends StackDatabase {
    private ilDBInterface $db;

    public function __construct() {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Inserts a new row in the database
     *
     * Usage: StackDatabase::insert('table_name', ['column1' => 'value1', 'column2' => 'value2']);
     *
     * @param string $table
     * @param array $data
     * @return void
     */
    public function insertInternal(string $table, array $data): void {
        $this->db->insert($table, $data);
    }

    /**
     * Inserts a new row in the database, if the row already exists, updates it
     *
     * Usage: StackDatabase::insertOnDuplicatedKey('table_name', ['column1' => 'value1', 'column2' => 'value2']);
     *
     * @param string $table
     * @param array $data
     * @return void
     */
    public function insertOnDuplicatedKeyInternal(string $table, array $data): void {
        $this->db->query("INSERT INTO " . $table . " (" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", array_values($data)) . ") ON DUPLICATE KEY UPDATE " . implode(", ", array_map(function ($key, $value) {
            return $key . " = " . $value;
        }, array_keys($data), array_values($data))));
    }

    /**
     * Updates a row/s in the database
     *
     * Usage: StackDatabase::update('table_name', ['column1' => 'value1', 'column2' => 'value2'], ['id' => 1]);
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @return void
     */
    public function updateInternal(string $table, array $data, array $where): void {
        $this->db->update($table, $data, $where);
    }

    /**
     * Deletes a row/s in the database
     *
     * Usage: StackDatabase::delete('table_name', ['id' => 1]);
     *
     * @param string $table
     * @param array $where
     * @return void
     */
    public function deleteInternal(string $table, array $where): void {
        $this->db->query("DELETE FROM " . $table . " WHERE " . implode(" AND ", array_map(function ($key, $value) {
            return $key . " = " . $value;
        }, array_keys($where), array_values($where))));
    }

    /**
     * Selects a row/s in the database
     *
     * Usage: StackDatabase::select('table_name', ['id' => 1]);
     *
     * @param string $table
     * @param array|null $where
     * @return array
     */
    public function selectInternal(string $table, ?array $where = null): array {
        $query = "SELECT * FROM " . $table;

        if (isset($where)) {
            $query .= " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                return $key . " = " . $value;
            }, array_keys($where), array_values($where)));
        }

        $result = $this->db->query($query);

        $rows = [];

        while ($row = $this->db->fetchAssoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}