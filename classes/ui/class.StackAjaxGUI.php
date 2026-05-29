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

use classes\ui\author\StackQuestionAuthoringUI;
use Customizing\global\plugins\Services\UIComponent\UserInterfaceHook\SurContextHUB\classes\api\SurContextCore;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Implementation\Component\Input\ArrayInputData;
use JetBrains\PhpStorm\NoReturn;

/**
 * @ilCtrl_isCalledBy StackAjaxGUI: ilUIPluginRouterGUI
 */
class StackAjaxGUI
{
    private WrapperFactory $wrapper;
    private RefineryFactory $refinery;
    private ilassStackQuestionPlugin $plugin;

    public function __construct()
    {
        global $DIC;

        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->plugin = ilassStackQuestionPlugin::getInstance();
    }

    /**
     * @throws ilCtrlException
     */
    public static function getEndpoint(): string
    {
        global $DIC;

        return $DIC->ctrl()->getLinkTargetByClass(['ilUIPluginRouterGUI', StackAjaxGUI::class]);
    }

    /**
     * @throws Exception
     */
    public function executeCommand(): void
    {
        if ($this->wrapper->query()->has("action")) {
            $endpoint = $this->wrapper->query()->retrieve("action", $this->refinery->kindlyTo()->string());

            $this->{$endpoint}();
        } elseif ($this->wrapper->post()->has("action")) {
            $endpoint = $this->wrapper->post()->retrieve("action", $this->refinery->kindlyTo()->string());

            $this->{$endpoint}();
        } else {
            throw new Exception("No action specified");
        }
    }

    #[NoReturn] public static function sendResponse($data, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        header('Content-type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * @throws ilCtrlException
     * @throws stack_exception
     * @throws ilTaxonomyException
     */
    #[NoReturn] public function generateWithAI(): void
    {
        if (!ilassStackQuestionPlugin::isSurContextHubActive()) {
            self::sendResponse([
                "error" => "Sur Context Hub plugin is not active."
            ], 400);
        }

        global $DIC;

        $formData = json_decode($this->wrapper->post()->retrieve("formData", $this->refinery->kindlyTo()->string()), true);
        $currentFieldName = $this->wrapper->post()->retrieve("currentFieldName", $this->refinery->kindlyTo()->string());
        $question_id = (int) $this->wrapper->post()->retrieve("questionId", $this->refinery->kindlyTo()->string());
        $context = $this->wrapper->post()->retrieve("context", $this->refinery->kindlyTo()->string());

        // Initialize a fake question and authoring UI to build the form
        $question = new assStackQuestion();
        $question->loadFromDb($question_id);

        $authoring_gui = new StackQuestionAuthoringUI($this->plugin, $question, new assStackQuestionGUI());

        $form = $authoring_gui->buildForm(false);

        // Fill the current form data
        $input_group = $form->getInputGroup()->withInput(new ArrayInputData($formData));

        // Get the current field name
        $result = $this->findInputByName($input_group->getInputs(), $currentFieldName);

        // Get the content of the form with real names and current data
        $content = $input_group->getContent();

        // Final current form data
        $final_form_data = json_encode($content->value());

        // Prompt
        $prompt = $this->buildGenerateWithAIPrompt(
            isset($result) ? $result['label'] ?? null : null,
            isset($result) ? $result['key'] ?? null: null,
            isset($result) ? $result['path'] ?? null: null,
            $final_form_data,
            $DIC->user()->getLanguage(),
            $context
        );

        $body = [
            "prompt" => $prompt,
        ];

        $response = SurContextCore::callToEndpoint("POST", "llm/", $body);

        self::sendResponse([
            "generated_text" => $response["response"] ?? "",
        ]);
    }

    /**
     * @param array $inputs
     * @param string $fieldName
     * @param string $path
     * @return array|null  ['key' => ..., 'label' => ..., 'path' => ...] or null if not found
     */
    private function findInputByName(array $inputs, string $fieldName, string $path = ""): ?array
    {
        foreach ($inputs as $index => $input) {

            $currentPath = $path === '' ? (string)$index : $path . '/' . $index;

            if ($input->getName() === $fieldName) {
                $label = $input->getLabel();

                if (str_contains($currentPath, 'positive')) {
                    $label .= ' (positive)';
                } elseif (str_contains($currentPath, 'negative')) {
                    $label .= ' (negative)';
                }

                return [
                    'key'   => $index,
                    'label' => $label,
                    'path'  => $currentPath
                ];
            }

            if (method_exists($input, 'getInputs')) {
                $children = $input->getInputs();

                if (is_array($children) && !empty($children)) {
                    $result = $this->findInputByName($children, $fieldName, $currentPath);
                    if ($result !== null) {
                        return $result;
                    }
                }
            } else if (method_exists($input, 'getTabs')) {
                $tabs = $input->getTabs();

                foreach ($tabs as $tabIndex => $tab) {
                    $tabPath = $currentPath . '/tabs/' . $tabIndex;

                    $result = $this->findInputByName($tab, $fieldName, $tabPath);
                    if ($result !== null) {
                        return $result;
                    }
                }
            } else if (method_exists($input, 'getColumns')) {
                $columns = $input->getColumns();

                foreach ($columns as $colIndex => $column) {
                    $columnPath = $currentPath . '/columns/' . $colIndex;

                    $result = $this->findInputByName($column, $fieldName, $columnPath);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
        }

        return null;
    }


    private function buildGenerateWithAIPrompt(?string $field_label, ?string $field_name, ?string $field_path, bool|string $form_data, string $lang_key, string $context = ""): string
    {
        return 'You are an expert assistant for STACK (System for Teaching and Assessment using a Computer Algebra Kernel) questions.

        STACK is a computer-aided assessment system for mathematics where:
        - Questions include algebraic expressions using CAS (Computer Algebra System) syntax
        - Variables are written as {@variable@} in question text
        - Students enter mathematical answers that are evaluated algebraically
        - PRTs (Potential Response Trees) evaluate answers through nodes with true/false branches
        - Each PRT node tests a condition and provides feedback based on the result
        
        # Task
        
        Generate content for the field specified below using the form data as context.
        
        # Output Format
        
        Return ONLY the text content for the target field:
        - NO explanations or meta-commentary
        - NO JSON structure or quotes
        - NO markdown formatting
        - Use html tags like <b>, <i>, <span>, <br> and others for styling and line breaks if needed
        - Write entirely in the specified language
        - If the field has existing content, enhance it intelligently
        - Take into account the teacher_request but do not stray too far from the specified rules
        
        # Field-Specific Guidelines
        
        **Question Text** (question_text):
        - State the problem clearly and concisely (More than 2 lines)
        - Use {@variable@} syntax for CAS variables from question_variables
        - Specify what format the answer should be in
        - Use LaTeX notation with \[ \] for display math or \( \) for inline math
        
        **Specific Feedback** (specific_feedback):
        - Common used to display the feedback of the prts like [[feedback:prt1]]
        - Sometyimes provide general hints or explanations about the solution (3-5 sentences)
        
        **Specific feedback (positive) / Specific feedback (negative)**:
        - Explain the solution method and key mathematical concepts (3-5 sentences)
        - Show the complete solution process using LaTeX
        - Reference variables from the question using {@variable@}
        - Use [[feedback:prt_name]] to display results from specific PRTs
        - Be pedagogical - explain WHY, not just show the answer
        - Positive is used to display the feedback when the prt node is true or passed
        - Negative is used to display the feedback when the prt node is false or failed
                
        **Feedback for Correct/Partially Correct/Incorrect Answers** (prtcorrect, prtpartiallycorrect, prtincorrect):
        - Short, encouraging messages (1 sentence)
        - Often include icons or styling with <span> tags
        - Examples provided are typically sufficient
        - Only modify if a specific pedagogical tone is needed

        # Critical Rules
        
        1. NEVER invent variables that dont exist in question_variables or prt_feedback_variables
        2. NEVER reference inputs or PRT nodes that dont exist in the form data
        3. Stay consistent with the mathematical difficulty level of the question
        4. For CAS code fields, ALWAYS end statements with semicolons
        5. Use {@variable@} syntax when referencing variables in text/feedback
        6. Empty feedback is acceptable if default messages are sufficient
        7. If multiple inputs (ans1, ans2, ...) exist in the form data, the question text MUST define a clear mathematical relationship for EACH input.
           - Never leave an input without a corresponding equation, definition, or condition.
           - If a second input exists, introduce a second equation or define it as a function/value derived from the first variable.
        8. When multiple inputs are present, introduce each input immediately before it appears.
           - Place the definition or equation for a variable directly above its corresponding [[input:...]].
        9. For multi-input questions, structure the text like:
           - Explanation for ans1
           - [[input:ans1]] [[validation:ans1]]
           - Explanation for ans2
           - [[input:ans2]] [[validation:ans2]]
       10. NEVER use [[feedback:prt1]] inside a positive/negative feedback section.
           - These sections should focus on explaining the solution method, cannot reference itself.

        # Examples from Real STACK Questions
        
        Question text:
        1. "Find \[ \int {@p@} d{@v@}\] [[input:ans1]] [[validation:ans1]]"
        2. "Solve for {@x@} in the equation \[ {@a@}{@x@}^2 + {@b@}{@x@} + {@c@} = 0 \] [[input:ans1]] [[validation:ans1]]"
        3. "Solve the linear equation \(4+4x=12\) for \(x\). Give your answer as a single number (an integer or a simplified fraction). 

        \(x=\) [[input:ans1]] [[validation:ans1]]
        
        Then use your value of \(x\) to calculate \(y=2x\), and enter that value as a single number.
        
        \(y=\) [[input:ans2]] [[validation:ans2]]"
        4. "Differentiate the function \[ f({@x@}) = {@a@}{@x@}^3 + {@b@}{@x@}^2 + {@c@}{@x@} + {@d@} \] with respect to {@x@}. [[input:ans1]] [[validation:ans1]]"
        
        Specific feedback:
        1. "We can either do this question by inspection (i.e. spot the answer) or in a more formal manner by using the substitution \[ u = ({@v@}-{@a@}).\] Then, since \(\frac{d}{d{@v@}}u=1\) we have \[ \int {@p@} d{@v@} = \int u^{@n@} du = \frac{u^{@n+1@}}{@n+1@}+c = {@ta@}+c.\]"
        2. "[[feedback:prt1]]"
        
        Specific feedback (positive):
        1. "Excellent! You correctly factored the quadratic expression."
        2. "Well done! You applied the substitution method perfectly to arrive at the integral solution."
        
        Specific feedback (negative):
        1. "Your answer does not satisfy \({@p(x0)=subst(x=x0,f0)@}\)."
        2. "It seems you made an error in applying the quadratic formula. Remember to calculate the discriminant first."
        3. "In your substitution, ensure that you also change the differential \(d{@v@}\) accordingly."
        
        
        # Input Data
        
        {
          "inputLabel": "' . $field_label . '",
          "inputKey": "' . $field_name . '",
          "inputPath": "' . $field_path . '",
          "formData": ' . $form_data . ',
          "language": "' . $lang_key . '"
          "teacher_request": "' . $context . '"
        }
        
        Generate the content now:';
    }
}