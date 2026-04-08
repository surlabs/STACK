<?php
/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 *
 * Analytics query layer for STACK question data.
 * Reads from denormalized tables populated at submission time; never recalculates from raw JSON.
 */

class assStackQuestionAnalyticsDB
{
    // -------------------------------------------------------------------------
    // Attempt-level queries
    // -------------------------------------------------------------------------

    /**
     * Overall performance summary for a question.
     * Returns: attempt_count, unique_users, avg_fraction, avg_total_points, error_rate
     */
    public static function getAttemptSummary(int $question_id): array
    {
        global $DIC;
        $db = $DIC->database();

        $res = $db->queryF(
            "SELECT
                COUNT(*)                                      AS attempt_count,
                COUNT(DISTINCT user_id)                       AS unique_users,
                AVG(CASE WHEN max_points > 0 THEN total_points / max_points ELSE 0 END) AS avg_fraction,
                AVG(total_points)                             AS avg_total_points,
                SUM(has_error) / COUNT(*)                     AS error_rate
             FROM xqcas_anl_attempts
             WHERE question_id = %s",
            ['integer'],
            [$question_id]
        );

        $row = $db->fetchAssoc($res);
        return $row ?: [];
    }

    /**
     * Attempt rows for a question, optionally filtered by a list of active_ids.
     * Useful to feed a dashboard table or export.
     *
     * @param int[] $active_ids  Empty means all attempts.
     */
    public static function getAttempts(int $question_id, array $active_ids = []): array
    {
        global $DIC;
        $db = $DIC->database();

        $where = 'WHERE question_id = ' . $db->quote($question_id, 'integer');
        if (!empty($active_ids)) {
            $quoted = array_map(fn($id) => $db->quote((int) $id, 'integer'), $active_ids);
            $where .= ' AND active_id IN (' . implode(',', $quoted) . ')';
        }

        $res = $db->query(
            "SELECT question_id, active_id, pass, user_id, seed,
                    total_points, max_points, prt_count, has_error, stamp
             FROM xqcas_anl_attempts $where
             ORDER BY stamp DESC"
        );

        $rows = [];
        while ($row = $db->fetchAssoc($res)) {
            $rows[] = $row;
        }
        return $rows;
    }

    // -------------------------------------------------------------------------
    // PRT-level queries
    // -------------------------------------------------------------------------

    /**
     * Performance distribution for a single PRT across all attempts of a question.
     * Buckets: [0, 0.25), [0.25, 0.5), [0.5, 0.75), [0.75, 1), [1]
     */
    public static function getPrtFractionDistribution(int $question_id, string $prt_name): array
    {
        global $DIC;
        $db = $DIC->database();

        $res = $db->queryF(
            "SELECT fraction FROM xqcas_anl_prt
             WHERE question_id = %s AND prt_name = %s",
            ['integer', 'text'],
            [$question_id, $prt_name]
        );

        $buckets = ['[0,0.25)' => 0, '[0.25,0.5)' => 0, '[0.5,0.75)' => 0, '[0.75,1)' => 0, '1' => 0];
        while ($row = $db->fetchAssoc($res)) {
            $f = (float) $row['fraction'];
            if ($f >= 1.0) {
                $buckets['1']++;
            } elseif ($f >= 0.75) {
                $buckets['[0.75,1)']++;
            } elseif ($f >= 0.5) {
                $buckets['[0.5,0.75)']++;
            } elseif ($f >= 0.25) {
                $buckets['[0.25,0.5)']++;
            } else {
                $buckets['[0,0.25)']++;
            }
        }
        return $buckets;
    }

    /**
     * Frequency of each answer_note for a PRT, sorted descending.
     * Each note in the semicolon-separated string is counted individually.
     *
     * Returns: [['note' => '...', 'count' => N], ...]
     */
    public static function getAnswerNoteFrequency(int $question_id, string $prt_name): array
    {
        global $DIC;
        $db = $DIC->database();

        $res = $db->queryF(
            "SELECT answer_notes FROM xqcas_anl_prt
             WHERE question_id = %s AND prt_name = %s AND answer_notes IS NOT NULL AND answer_notes <> ''",
            ['integer', 'text'],
            [$question_id, $prt_name]
        );

        $freq = [];
        while ($row = $db->fetchAssoc($res)) {
            foreach (explode(';', $row['answer_notes']) as $note) {
                $note = trim($note);
                if ($note === '') {
                    continue;
                }
                $freq[$note] = ($freq[$note] ?? 0) + 1;
            }
        }

        arsort($freq);
        $result = [];
        foreach ($freq as $note => $count) {
            $result[] = ['note' => $note, 'count' => $count];
        }
        return $result;
    }

    /**
     * Aggregate stats for every PRT of a question in a single query.
     * Returns: [prt_name => [attempt_count, avg_fraction, error_count, ...], ...]
     */
    public static function getPrtSummaryByQuestion(int $question_id): array
    {
        global $DIC;
        $db = $DIC->database();

        $res = $db->queryF(
            "SELECT
                prt_name,
                COUNT(*)        AS attempt_count,
                AVG(fraction)   AS avg_fraction,
                MAX(max_points) AS max_points,
                SUM(has_error)  AS error_count
             FROM xqcas_anl_prt
             WHERE question_id = %s
             GROUP BY prt_name",
            ['integer'],
            [$question_id]
        );

        $rows = [];
        while ($row = $db->fetchAssoc($res)) {
            $rows[$row['prt_name']] = $row;
        }
        return $rows;
    }

    // -------------------------------------------------------------------------
    // Input-level queries
    // -------------------------------------------------------------------------

    /**
     * Most frequent responses for an input, sorted descending.
     *
     * @param int $limit  Maximum distinct responses to return.
     * Returns: [['response_value' => '...', 'count' => N, 'valid_count' => N], ...]
     */
    public static function getInputResponseFrequency(int $question_id, string $input_name, int $limit = 50): array
    {
        global $DIC;
        $db = $DIC->database();

        $res = $db->queryF(
            "SELECT response_value, COUNT(*) AS cnt, SUM(is_valid) AS valid_count
             FROM xqcas_anl_inputs
             WHERE question_id = %s AND input_name = %s
             GROUP BY response_value
             ORDER BY cnt DESC",
            ['integer', 'text'],
            [$question_id, $input_name]
        );

        $rows = [];
        $i = 0;
        while ($row = $db->fetchAssoc($res)) {
            $rows[] = [
                'response_value' => $row['response_value'],
                'count'          => (int) $row['cnt'],
                'valid_count'    => (int) $row['valid_count'],
            ];
            if (++$i >= $limit) {
                break;
            }
        }
        return $rows;
    }

    /**
     * Validity rate per input for a question.
     * Returns: [input_name => ['attempt_count' => N, 'valid_rate' => 0.0-1.0], ...]
     */
    public static function getInputValidityByQuestion(int $question_id): array
    {
        global $DIC;
        $db = $DIC->database();

        $res = $db->queryF(
            "SELECT input_name, COUNT(*) AS attempt_count, AVG(is_valid) AS valid_rate
             FROM xqcas_anl_inputs
             WHERE question_id = %s
             GROUP BY input_name",
            ['integer'],
            [$question_id]
        );

        $rows = [];
        while ($row = $db->fetchAssoc($res)) {
            $rows[$row['input_name']] = [
                'attempt_count' => (int) $row['attempt_count'],
                'valid_rate'    => (float) $row['valid_rate'],
            ];
        }
        return $rows;
    }

    // -------------------------------------------------------------------------
    // Hint queries (reads xqcas_hint_tracking populated by the hint-tracking feature)
    // -------------------------------------------------------------------------

    /**
     * How many unique attempts opened each hint for a question.
     * Returns: [['hint_index' => N, 'hint_title' => '...', 'open_count' => N, 'unique_attempts' => N], ...]
     */
    public static function getHintUsage(int $question_id): array
    {
        global $DIC;
        $db = $DIC->database();

        $res = $db->queryF(
            "SELECT hint_index, hint_title,
                    SUM(CASE WHEN event_type = 'open' THEN 1 ELSE 0 END) AS open_count,
                    COUNT(DISTINCT CONCAT(active_id, '_', pass))         AS unique_attempts
             FROM xqcas_hint_tracking
             WHERE question_id = %s
             GROUP BY hint_index, hint_title
             ORDER BY hint_index ASC",
            ['integer'],
            [$question_id]
        );

        $rows = [];
        while ($row = $db->fetchAssoc($res)) {
            $rows[] = [
                'hint_index'     => (int) $row['hint_index'],
                'hint_title'     => $row['hint_title'],
                'open_count'     => (int) $row['open_count'],
                'unique_attempts'=> (int) $row['unique_attempts'],
            ];
        }
        return $rows;
    }

    /**
     * Correlation: did students who opened at least one hint score lower?
     * Returns two rows keyed 'with_hint' and 'without_hint', each with avg_fraction and attempt_count.
     */
    public static function getHintScoreCorrelation(int $question_id): array
    {
        global $DIC;
        $db = $DIC->database();

        // Attempts that used at least one hint
        $res_hint = $db->queryF(
            "SELECT AVG(CASE WHEN a.max_points > 0 THEN a.total_points / a.max_points ELSE 0 END) AS avg_fraction,
                    COUNT(*) AS attempt_count
             FROM xqcas_anl_attempts a
             WHERE a.question_id = %s
               AND EXISTS (
                   SELECT 1 FROM xqcas_hint_tracking h
                   WHERE h.question_id = a.question_id
                     AND h.active_id   = a.active_id
                     AND h.pass        = a.pass
                     AND h.event_type  = 'open'
               )",
            ['integer'],
            [$question_id]
        );

        $res_no_hint = $db->queryF(
            "SELECT AVG(CASE WHEN a.max_points > 0 THEN a.total_points / a.max_points ELSE 0 END) AS avg_fraction,
                    COUNT(*) AS attempt_count
             FROM xqcas_anl_attempts a
             WHERE a.question_id = %s
               AND NOT EXISTS (
                   SELECT 1 FROM xqcas_hint_tracking h
                   WHERE h.question_id = a.question_id
                     AND h.active_id   = a.active_id
                     AND h.pass        = a.pass
                     AND h.event_type  = 'open'
               )",
            ['integer'],
            [$question_id]
        );

        return [
            'with_hint'    => $db->fetchAssoc($res_hint)    ?: ['avg_fraction' => null, 'attempt_count' => 0],
            'without_hint' => $db->fetchAssoc($res_no_hint) ?: ['avg_fraction' => null, 'attempt_count' => 0],
        ];
    }

    // -------------------------------------------------------------------------
    // Cross-question queries (for multi-question dashboards / test-level views)
    // -------------------------------------------------------------------------

    /**
     * Summary stats for all questions in a list, in a single query.
     *
     * @param int[] $question_ids
     * Returns: [question_id => [attempt_count, avg_fraction, error_rate], ...]
     */
    public static function getAttemptSummaryBatch(array $question_ids): array
    {
        global $DIC;
        $db = $DIC->database();

        if (empty($question_ids)) {
            return [];
        }

        $quoted = array_map(fn($id) => $db->quote((int) $id, 'integer'), $question_ids);
        $res = $db->query(
            "SELECT question_id,
                    COUNT(*)        AS attempt_count,
                    AVG(CASE WHEN max_points > 0 THEN total_points / max_points ELSE 0 END) AS avg_fraction,
                    SUM(has_error) / COUNT(*) AS error_rate
             FROM xqcas_anl_attempts
             WHERE question_id IN (" . implode(',', $quoted) . ")
             GROUP BY question_id"
        );

        $rows = [];
        while ($row = $db->fetchAssoc($res)) {
            $rows[(int) $row['question_id']] = $row;
        }
        return $rows;
    }
}
