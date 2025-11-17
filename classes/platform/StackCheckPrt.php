<?php
declare(strict_types=1);

namespace classes\platform;

use assStackQuestionDB;
use stack_utils;

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

class StackCheckPrt {

    /**
     * Get errors from the prts of the questions
     * @param array|null $questions
     * @return array
     */
    public static function getErrors(?array $questions = null) :array {
        $placeholders = [];
        $errors = [];

        if (isset($questions) && !empty($questions)) {
            foreach ($questions as $question) {
                $placeholders[$question] = assStackQuestionDB::getPrtsAndPlaceholders((string) $question);
            }
        } else {
            $placeholders = assStackQuestionDB::getPrtsAndPlaceholders();
        }

        foreach ($placeholders as $question_id => $data) {
            if (isset($data["prts"]) && !empty($data["prts"])) {
                foreach ($data["prts"] as $prt) {
                    if (isset($data["question_text"]) && isset($data["specific_feedback"])) {
                        if (!str_contains($data["question_text"], "[[feedback:" . $prt . "]]") && !str_contains($data["specific_feedback"], "[[feedback:" . $prt . "]]")) {
                            $errors[$question_id]["title"] = $data["title"];
                            $errors[$question_id]["missing_placeholders"][] = $prt;
                        } else if (!stack_utils::is_valid_name($prt) && is_numeric($prt)) {
                            $errors[$question_id]["title"] = $data["title"];
                            $errors[$question_id]["badname"][] = $prt;
                        }
                    }
                }
            }
        }

        $comma_errors = assStackQuestionDB::getPrtNodesWithComma();

        foreach ($comma_errors as $question_id => $data) {
            if (!empty($data["prts"])) {
                foreach ($data["prts"] as $prt => $node) {
                    foreach ($node as $node_name => $node_data) {
                        foreach ($node_data as $key => $value) {
                            if (str_contains($value, ",")) {
                                $errors[$question_id]["title"] = $data["title"];
                                $errors[$question_id]["comma_errors"][] = [
                                    "prt" => $prt,
                                    "node" => $node_name,
                                    "key" => $key,
                                    "value" => $value,
                                    "fixed_value" => (float) preg_replace('/\.{2,}/', '.', str_replace(",", ".", $value))
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Fix the missing placeholders for a given question
     * @param string $question_id
     * @return array
     */
    public static function fixMissingPlaceholders(string $question_id) :array {
        $data = assStackQuestionDB::getPrtsAndPlaceholders($question_id);

        if (isset($data) && !empty($data)) {
            $specific_feedback = $data["specific_feedback"];

            if (isset($data["prts"]) && !empty($data["prts"])) {
                foreach ($data["prts"] as $prt) {
                    if (!str_contains($data["question_text"], "[[feedback:" . $prt . "]]") && !str_contains($data["specific_feedback"], "[[feedback:" . $prt . "]]")) {
                        if ($specific_feedback ==  "") {
                            $specific_feedback = "<p>[[feedback:". $prt . "]]</p>";
                        } else {
                            $specific_feedback .= "\n<p>[[feedback:". $prt . "]]</p>";
                        }
                    }
                }
            }

            assStackQuestionDB::updateSpecificFeedback($question_id, $specific_feedback);

            return array(
                "title" => $data["title"],
                "specific_feedback" => $specific_feedback
            );
        }

        return array(
            "title" => "",
            "specific_feedback" => ""
        );
    }

    /**
     * Fix the bad prt names for a given question
     * @param string $question_id
     * @return array
     */
    public static function fixBadNames(string $question_id) :array
    {
        $data = assStackQuestionDB::getPrtsAndPlaceholders($question_id);
        $changed = "";

        if (isset($data) && !empty($data)) {
            $question_text = $data["question_text"];
            $specific_feedback = $data["specific_feedback"];

            if (isset($data["prts"]) && !empty($data["prts"])) {
                foreach ($data["prts"] as $prt) {
                    if (!stack_utils::is_valid_name($prt) && is_numeric($prt)) {
                        $new_prt = "prt" . $prt;
                        $question_text = str_replace("[[feedback:" . $prt . "]]", "[[feedback:" . $new_prt . "]]", $question_text);
                        $specific_feedback = str_replace("[[feedback:" . $prt . "]]", "[[feedback:" . $new_prt . "]]", $specific_feedback);

                        assStackQuestionDB::updateQuestionText($question_id, $question_text);
                        assStackQuestionDB::updateSpecificFeedback($question_id, $specific_feedback);

                        assStackQuestionDB::updatePrtName($question_id, $prt, $new_prt);

                        $changed = $prt . " -> " . $new_prt . "\n";
                    }
                }
            }
        }

        return array(
            "title" => $data["title"],
            "changed" => $changed,
        );
    }

    public static function fixCommaErrors(string $question_id)
    {
        $data = assStackQuestionDB::getPrtNodesWithComma($question_id);

        $changed = "";

        if (!empty($data)) {
            if (!empty($data["prts"])) {
                foreach ($data["prts"] as $prt => $node) {
                    foreach ($node as $node_name => $node_data) {
                        foreach ($node_data as $key => $value) {
                            if (str_contains($value, ",")) {
                                $fixed_value = (float) preg_replace('/\.{2,}/', '.', str_replace(",", ".", $value));

                                assStackQuestionDB::updatePrtNodeValue($question_id, (string) $prt, (string) $node_name, (string) $key, $fixed_value);

                                $changed .= $prt . " -> " . $node_name . " -> " . $key . ": " . $value . " -> " . $fixed_value . "<br>";
                            }
                        }
                    }
                }
            }
        }

        return array(
            "title" => $data["title"],
            "changed" => $changed,
        );
    }

    public static function fixCommaErrorsBulky()
    {
        $data = assStackQuestionDB::getPrtNodesWithComma();

        $changed = "";
        $fixed_count = 0;

        if (!empty($data)) {
            foreach ($data as $question_id => $question_data) {
                if (!empty($question_data["prts"])) {
                    foreach ($question_data["prts"] as $prt => $node) {
                        foreach ($node as $node_name => $node_data) {
                            foreach ($node_data as $key => $value) {
                                if (str_contains($value, ",")) {
                                    $fixed_value = (float) preg_replace('/\.{2,}/', '.', str_replace(",", ".", $value));

                                    assStackQuestionDB::updatePrtNodeValue((string) $question_id, (string) $prt, (string) $node_name, (string) $key, $fixed_value);

                                    $fixed_count++;

                                    $changed .= "Question ID: " . $question_id . " - " . $prt . " -> " . $node_name . " -> " . $key . ": " . $value . " -> " . $fixed_value . "<br>";
                                }
                            }
                        }
                    }
                }
            }
        }

        return array(
            "changed" => $changed,
            "fixed_count" => $fixed_count
        );
    }
}