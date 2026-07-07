<?php

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

declare(strict_types=1);

namespace classes\ui;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use stack_potentialresponse_tree_lite;

/**
 * StackScoringTableData
 *
 * @authors Jesús Copado Mejías, Saúl Díaz Díaz, Abraham Morales Rodríguez <stack@surlabs.es>
 */
class StackScoringTableData implements DataRetrieval
{
    private stack_potentialresponse_tree_lite $prt_data;
    private float $max_weight;
    private float $questionPoint;

    public function __construct(stack_potentialresponse_tree_lite $prt_data, float $max_weight, float $questionPoint)
    {
        $this->prt_data = $prt_data;
        $this->max_weight = $max_weight;
        $this->questionPoint = $questionPoint;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array          $visible_column_ids,
        Range          $range,
        Order          $order,
        mixed          $additional_viewcontrol_data,
        mixed          $filter_data,
        mixed          $additional_parameters
    ): Generator
    {
        $records_to_display = $this->getRecords();

        foreach ($records_to_display as $record) {

            $row_id = (string)($record['node_id'] ?? uniqid('node_row_'));
            yield $row_builder->buildDataRow($row_id, $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int
    {
        return count($this->prt_data->get_nodes());
    }

    protected function getRecords(): array
    {
        $records = [];
        $prt_max_weight = $this->prt_data->get_value();
        $prt_max_points = ($prt_max_weight / $this->max_weight) * $this->questionPoint;

        foreach ($this->prt_data->get_nodes() as $node) {
            $records[] = [
                'node_name' => $node->nodename,
                'positive_comparison' => '<span class="positiveComparison">' . $node->truescoremode . ($node->truescore * $prt_max_points) . '</span>',
                'negative_comparison' => '<span class="negativeComparison">' . $node->falsescoremode . ($node->falsescore * $prt_max_points) . '</span>',
            ];
        }

        return $records;
    }
}
