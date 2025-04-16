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

namespace ui\Component\Input\Field;

use Closure;
use ILIAS\Data\Factory;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Implementation\Component\Input\Field\FormInput;
use ilObjTaxonomy;

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

    protected function isClientSideValueOk($value): bool
    {
        return true;
    }

    public function getTaxonomy(): ilObjTaxonomy
    {
        return $this->taxonomy;
    }
}