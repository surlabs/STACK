<?php

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 *  This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 *  originally created by Chris Sangwin.
 *
 *  The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 *  For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 *  To report bugs or participate in discussions, visit the Mantis system and filter by
 *  the category "STACK Question" at https://mantis.ilias.de.
 *
 *  More information and source code are available at:
 *  https://github.com/surlabs/STACK
 *
 *  If you need support, please contact the maintainer of this software at:
 *  stack@surlabs.es
 *
 */

declare(strict_types=1);

namespace public\Customizing\global\plugins\Modules\TestQuestionPool\Questions\assStackQuestion\classes\ui\Component\Input\Field;

use Closure;
use ILIAS\Data\Factory;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Implementation\Component\Input\Field\FormInput;
use ILIAS\UI\Implementation\Component\Input\Input;
use ilObject;
use ilObjTaxonomy;
use ilTaxNodeAssignment;
use ilTaxonomyException;

/**
 * Class TaxonomySelect
 */
class TaxonomySelect extends FormInput {
    private ilObjTaxonomy $taxonomy;
    public function __construct(ilObjTaxonomy $taxonomy)
    {
        global $DIC;

        $this->taxonomy = $taxonomy;

        parent::__construct(new Factory(), $DIC->refinery(), sprintf($DIC->language()->txt('qpl_qst_edit_form_taxonomy'), $taxonomy->getTitle()));
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        return $this->requirement_constraint;
    }

    public function getUpdateOnLoadCode(): Closure
    {
        return fn($id) => "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
    }

    public function withValue($value): Input
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");
        $clone = clone $this;

        foreach ($value as $key => $item) {
            if (is_numeric($item)) {
                $tree = $this->getTaxonomy()->getTree();

                foreach ($tree->getChilds($tree->readRootId()) as $node) {
                    if ((int) $node["child"] == $item) {
                        $value[$key] = [
                            "id" => (int) $node["child"],
                            "title" => $node["title"]
                        ];
                    }
                }
            }
        }

        $clone->value = $value;
        return $clone;
    }

    protected function isClientSideValueOk($value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if (is_array($item)) {
                    if (!isset($item['id']) || !is_numeric($item['id'])) {
                        return false;
                    }
                } else if (!is_numeric($item)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function getTaxonomy(): ilObjTaxonomy
    {
        return $this->taxonomy;
    }

    /**
     * @throws ilTaxonomyException
     */
    public static function saveTaxonomySelect(int $objId, int $id, int $taxonomy_id, $nodes): void
    {
        $nodes = $nodes ?? [];

        $tax_node_ass = new ilTaxNodeAssignment(ilObject::_lookupType($objId), $objId, 'quest', $taxonomy_id);

        $current_ass = $tax_node_ass->getAssignmentsOfItem($id);
        $exising = array();

        $new_node_ids = array_map(fn($n) => (int) $n["id"], $nodes);

        foreach ($current_ass as $ca) {
            if (!in_array((int) $ca["node_id"], $new_node_ids)) {
                $tax_node_ass->deleteAssignment((int) $ca["node_id"], $id);
            } else {
                $exising[] = (int) $ca["node_id"];
            }
        }

        foreach ($nodes as $node) {
            if (!in_array($node["id"], $exising)) {
                $tax_node_ass->addAssignment($node["id"], $id);
            }
        }
    }
}