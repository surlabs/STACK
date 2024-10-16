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

class StackCheckPrtPlaceholders {

    /**
     * Get the missing placeholders for a given question and errors in prt name
     * @param array|null $questions
     * @return array
     */
    public static function getErrors(?array $questions = null) :array {
        $placeholders = [];
        $missing = [];

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
                        if (strpos($data["question_text"], "[[feedback:" . $prt . "]]") === false && strpos($data["specific_feedback"], "[[feedback:" . $prt . "]]") === false) {
                            $missing[$question_id]["title"] = $data["title"];
                            $missing[$question_id]["missing"][] = $prt;
                        } else if (!stack_utils::is_valid_name($prt) && is_numeric($prt)) {
                            $missing[$question_id]["title"] = $data["title"];
                            $missing[$question_id]["badname"][] = $prt;
                        }
                    }
                }
            } else {
                $missing[$question_id] = array(
                    "title" => $data["title"],
                    "missing" => []
                );
            }
        }

        return $missing;
    }

    /**
     * Fix the missing placeholders for a given question
     * @param string $question_id
     * @return array
     */
    public static function fixMissings(string $question_id) :array {
        $data = assStackQuestionDB::getPrtsAndPlaceholders($question_id);

        if (isset($data) && !empty($data)) {
            $specific_feedback = $data["specific_feedback"];

            if (isset($data["prts"]) && !empty($data["prts"])) {
                foreach ($data["prts"] as $prt) {
                    if (strpos($data["question_text"], "[[feedback:" . $prt . "]]") === false && strpos($data["specific_feedback"], "[[feedback:" . $prt . "]]") === false) {
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
}