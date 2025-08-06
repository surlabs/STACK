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

use assStackQuestionUtils;
use Expand;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Input\Field\Renderer as RendererILIAS;
use ILIAS\UI\Implementation\Render\Template;
use ilRTE;
use ilTaxonomyTree;
use ilTemplate;
use ilTemplateException;
use ilTinyMCE;

/**
 * Class Renderer
 */
class Renderer extends RendererILIAS
{
    private \ILIAS\UI\Renderer $default_renderer;

    protected function getComponentInterfaceName(): array
    {
        return [
            TextareaRTE::class,
            ExpandableSection::class,
        ];
    }

    /**
     * @throws ilTemplateException
     */
    public function render(Component $component, ?\ILIAS\UI\Renderer $default_renderer = null): string
    {
        global $DIC;

        $DIC->ui()->mainTemplate()->addJavaScript('public/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/Component/Input/Field/customField.js');
        $DIC->ui()->mainTemplate()->addCss('public/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/Component/Input/Field/customField.css');

        if (isset($default_renderer)) {
            $this->default_renderer = $default_renderer;
        } else if (!isset($this->default_renderer)) {
            $this->default_renderer = $DIC->ui()->renderer();
        }

        return match (true) {
            $component instanceof TextareaRTE => $this->renderTextareaRTE($component),
            $component instanceof ExpandableSection => $this->renderExpandableSection($component),
            $component instanceof TabSection => $this->renderTabSection($component),
            $component instanceof ColumnSection => $this->renderColumnSection($component),
            $component instanceof Legacy => $component->getHtml(),
            $component instanceof ButtonSection => $this->renderButtonSection($component),
            $component instanceof TaxonomySelect => $this->renderTaxonomySelect($component),
            default => $this->default_renderer->render($component),
        };
    }

    /**
     * @throws ilTemplateException
     */
    protected function wrapInFormContext(
        FormInput $component,
        string $label,
        string $input_html,
        ?string $id_for_label = null,
        ?string $dependant_group_html = null
    ): string {
        $tpl = new ilTemplate("Input/tpl.context_form.html", true, true, 'components/ILIAS/UI/src');

        $tpl->setVariable("LABEL", $label);
        $tpl->setVariable("INPUT", $input_html);
        $tpl->setVariable("UI_COMPONENT_NAME", $this->getComponentCanonicalNameAttribute($component));
        $tpl->setVariable("INPUT_NAME", $component->getName());

        if ($component->getOnLoadCode() !== null) {
            $binding_id = $this->bindJavaScript($component) ?? $this->createId();
            $tpl->setVariable("BINDING_ID", $binding_id);
        }

        if ($id_for_label) {
            $tpl->setCurrentBlock('for');
            $tpl->setVariable("ID", $id_for_label);
            $tpl->parseCurrentBlock();
        } else {
            $tpl->touchBlock('tabindex');
        }

        $byline = $component->getByline();
        if ($byline) {
            $tpl->setVariable("BYLINE", $byline);
        }

        $required = $component->isRequired();
        if ($required) {
            $tpl->setCurrentBlock('required');
            $tpl->setVariable("REQUIRED_ARIA", $this->txt('required_field'));
            $tpl->parseCurrentBlock();
        }

        if ($component->isDisabled()) {
            $tpl->touchBlock("disabled");
        }

        $error = $component->getError();
        if ($error) {
            $error_id = $this->createId();
            $tpl->setVariable("ERROR_LABEL", $this->txt("ui_error"));
            $tpl->setVariable("ERROR_ID", $error_id);
            $tpl->setVariable("ERROR", $error);
            if ($id_for_label) {
                $tpl->setVariable("ERROR_FOR_ID", $id_for_label);
            }
        }

        if ($dependant_group_html) {
            $tpl->setVariable("DEPENDANT_GROUP", $dependant_group_html);
        }
        return $tpl->get();
    }

    protected function maybeDisable(FormInput $component, ilTemplate|Template $tpl): void
    {
        if ($component->isDisabled()) {
            $tpl->setVariable("DISABLED", 'disabled="disabled"');
        }
    }

    protected function applyName(FormInput $component, ilTemplate|Template $tpl): ?string
    {
        $name = $component->getName();
        $tpl->setVariable("NAME", $name);
        return $name;
    }

    protected function applyValue(FormInput $component, ilTemplate|Template $tpl, callable $escape = null): void
    {
        $value = $component->getValue();
        if (!is_null($escape)) {
            $value = $escape($value);
        }
        if (isset($value) && $value != '') {
            $tpl->setVariable("VALUE", assStackQuestionUtils::_solveKeyBracketsBug($value));
        }
    }

    private function getTemplateCustom(string $name): ilTemplate
    {
        return new ilTemplate("Component/Input/Field/$name", true, true, 'public/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion');
    }

    /**
     * @throws ilTemplateException
     */
    private function renderTextareaRTE(TextareaRTE $component): string
    {
        /** @var $component TextareaRTE */
        $component = $component->withAdditionalOnLoadCode(
            static function ($id): string {
                return "
                    il.UI.Input.textarea.init('$id');
                ";
            }
        );

        $tpl = $this->getPreparedTextareaRTETemplate($component);

        return $this->wrapInFormContext($component, $component->getLabel(), $tpl->get());
    }

    protected function getPreparedTextareaRTETemplate(TextareaRTE $component): ilTemplate
    {
        $tpl = $this->getTemplateCustom("tpl.textareaRte.html");

        if (0 < $component->getMaxLimit()) {
            $tpl->setVariable('REMAINDER_TEXT', $this->txt('ui_chars_remaining'));
            $tpl->setVariable('REMAINDER', $component->getMaxLimit() - strlen($component->getValue() ?? ''));
            $tpl->setVariable('MAX_LIMIT', $component->getMaxLimit());
        }

        if (null !== $component->getMinLimit()) {
            $tpl->setVariable('MIN_LIMIT', $component->getMinLimit());
        }

        $this->applyName($component, $tpl);
        $this->applyValue($component, $tpl, $this->htmlEntities());
        $this->maybeDisable($component, $tpl);

        $rte_string = ilRTE::_getRTEClassname();
        /** @var ilTinyMCE $rte */
        $rte = new $rte_string();

        $rte->addPlugin("emoticons");
        $rte->addPlugin("latex");
        $rte->addButton("latex");
        $rte->addButton("pastelatex");

        $rteSupport = $component->getRTESupport();

        if (!empty($rteSupport)) {
            $rte->addRTESupport($rteSupport["obj_id"], $rteSupport["obj_type"], $rteSupport["module"], false, $rteSupport['cfg_template'], $rteSupport['hide_switch']);

            $tpl->setVariable('RTE_EDITOR', "yesRTEditor");
        } else {
            $tpl->setVariable('RTE_EDITOR', "noRTEditor");
        }

        return $tpl;
    }

    /**
     * @throws ilTemplateException
     */
    private function renderExpandableSection(ExpandableSection $component): string
    {
        $section_tpl = $this->getTemplateCustom("tpl.expandableSection.html");

        $inputs_html = "";

        foreach ($component->getInputs() as $input) {
            $inputs_html .= $this->render($input);
        }

        $section_tpl->setVariable("INPUTS", $inputs_html);
        $section_tpl->setVariable("LABEL", $component->getLabel());

        if ($component->getByline() !== null) {
            $section_tpl->setCurrentBlock("byline");
            $section_tpl->setVariable("BYLINE", $component->getByline());
            $section_tpl->parseCurrentBlock();
        }

        $expand = new Expand($component->isExpandedByDefault());

        $section_tpl->setVariable("VIEW_CONTROL", $this->render($expand));

        return $section_tpl->get();
    }

    /**
     * @throws ilTemplateException
     */
    private function renderTabSection(TabSection $component): string
    {
        $section_tpl = $this->getTemplateCustom("tpl.tabSection.html");

        $section_tpl->setVariable("LABEL", $component->getLabel());

        if ($component->getByline() !== null) {
            $section_tpl->setCurrentBlock("byline");
            $section_tpl->setVariable("BYLINE", $component->getByline());
            $section_tpl->parseCurrentBlock();
        }

        $tabs_buttons = "";
        $tabs_panels = "";

        $isFirst = " active";

        $uid = uniqid();

        foreach ($component->getTabs() as $tab_name => $tab) {
            $tabs_buttons .= "<div class='tab-button$isFirst' data-tab='$tab_name' data-section-id='$uid'>{$tab_name}</div>";

            $inputs_html = "";

            foreach ($tab as $input) {
                $inputs_html .= $this->render($input);
            }

            $tabs_panels .= "<div class='tab-panel$isFirst' data-tab-panel='$tab_name' data-section-id='$uid'>$inputs_html</div>";

            $isFirst = "";
        }

        $section_tpl->setVariable("TAB_BUTTONS", $tabs_buttons);
        $section_tpl->setVariable("TAB_PANELS", $tabs_panels);

        return $section_tpl->get();
    }

    /**
     * @throws ilTemplateException
     */
    private function renderColumnSection(ColumnSection $component): string
    {
        $section_tpl = $this->getTemplateCustom("tpl.columnSection.html");

        $section_tpl->setVariable("LABEL", $component->getLabel());

        if ($component->getByline() !== null) {
            $section_tpl->setCurrentBlock("byline");
            $section_tpl->setVariable("BYLINE", $component->getByline());
            $section_tpl->parseCurrentBlock();
        }

        $columns_html = "";

        foreach ($component->getColumns() as $column_name => $column) {
            $inputs_html = "";

            foreach ($column as $input) {
                $inputs_html .= $this->render($input);
            }

            $columns_html .= "<div class='column'";

            $columns_html .= $component->renderColumnStyle($column_name);

            $columns_html .= ">$inputs_html</div>";
        }

        $section_tpl->setVariable("COLUMNS", $columns_html);

        return $section_tpl->get();
    }

    /**
     * @throws ilTemplateException
     */
    private function renderButtonSection(ButtonSection $component): string
    {
        $section_tpl = $this->getTemplateCustom("tpl.buttonSection.html");

        $buttons_html = "";

        foreach ($component->getButtons() as $button) {
            $buttons_html .= $this->render($button);
        }

        $section_tpl->setVariable("INPUTS", $buttons_html);

        return $this->wrapInFormContext($component, $component->getLabel(), $section_tpl->get());
    }

    /**
     * @throws ilTemplateException
     */
    private function renderTaxonomySelect(TaxonomySelect $component): string
    {
        global $DIC;

        $tax_tpl = $this->getTemplateCustom("tpl.taxonomySelect.html");
        $tax_id = "taxonomy_select_" . $component->getTaxonomy()->getId();

        $tax_tpl->setVariable("ID_TAX", $tax_id);
        $tax_tpl->setVariable("TXT_SELECT", $this->txt("select"));
        $tax_tpl->setVariable("TXT_RESET", $this->txt("reset"));

        $this->applyName($component, $tax_tpl);
        $tax_tpl->setVariable("VALUE", json_encode($component->getValue() ?? []));

        $DIC->language()->loadLanguageModule("tax");

        $nodes = [];

        $tree = $component->getTaxonomy()->getTree();

        foreach ($tree->getChilds($tree->readRootId()) as $node) {
            $nodes[] = [
                "id" => (int) $node["child"],
                "title" => $node["title"]
            ];
        }

        $modal = $this->getUIFactory()->modal()->lightbox($this->getUIFactory()->modal()->lightboxTextPage($this->buildTaxonomyNodes($nodes, $tax_id), $this->txt("tax_nodes")));
        $modal_rendered = $this->render($modal);

        $tax_tpl->setVariable("MODAL", $modal_rendered);
        $tax_tpl->setVariable("MODAL_SIGNAL", $modal->getShowSignal());

        return $this->wrapInFormContext($component, $component->getLabel(), $tax_tpl->get());
    }

    private function buildTaxonomyNodes(array $nodes, string $taxonomy_id): string
    {
        global $DIC;

        $checkboxs = "";

        foreach ($nodes as $node) {
            $checkboxs .= $DIC->ui()->renderer()->render(
                $this->getUIFactory()->input()->field()->checkbox($node["title"])->withAdditionalOnLoadCode(function ($id) use ($node, $taxonomy_id) {
                    return "$('#$id').attr('node-id', {$node['id']}).attr('node-title', '{$node['title']}').addClass('tax-node').attr('taxonomy-id', '$taxonomy_id');";
                })
            );
        }

        return $checkboxs;
    }
}