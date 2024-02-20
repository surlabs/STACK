<?php
/**
 *  This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
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

/**
 * This class provides all the global variables needed within the stack folder
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 * @ingroup    ModulesTestQuestionPool
 *
 */

/**
 * Simulating moodles global configuration
 */

use classes\platform\StackConfig;

$CFG = new stdClass;
// the base url of the installation (without script)
$CFG->wwwroot = ilUtil::_getHttpPath();
// the server path of the installation
$CFG->dirroot = realpath(dirname(__FILE__) . '/../..');
// the data directory of the plugin
$CFG->dataroot = ILIAS_WEB_DIR . "/" . CLIENT_ID . '/xqcas';
$GLOBALS['CFG'] =& $CFG;

define('PARAM_RAW', 'raw');
define('MOODLE_INTERNAL', '1');

//include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/locallib.php';


if (!function_exists('getLanguage')) {
    function getLanguage()
    {
        global $DIC;

        $lng = $DIC->language();

        return $lng->getUserLanguage();
    }

}

if (!function_exists('getString')) {
    function getString($identifier, $string, $a = null)
    {
        $string = $string[$identifier];
        if ($a !== NULL) {
            if (is_object($a) or is_array($a)) {
                $a = (array)$a;
                $search = array();
                $replace = array();
                foreach ($a as $key => $value) {
                    if (is_int($key)) {
                        // we do not support numeric keys - sorry!
                        continue;
                    }
                    $search[] = '{$a->' . $key . '}';
                    $replace[] = (string)$value;
                }
                if ($search) {
                    $string = str_replace($search, $replace, $string);
                }
            } else {
                $string = str_replace('{$a}', (string)$a, $string);
            }
        }

        return $string;
    }
}

/**
 * Translates a string taken as output from Maxima.
 *
 * This function takes a variable number of arguments, the first of which is assumed to be the identifier
 * of the string to be translated.
 */
if (!function_exists('stack_trans')) {
    function stack_trans()
    {
        $nargs = func_num_args();

        if ($nargs > 0) {
            $arg_list = func_get_args();
            $identifier = func_get_arg(0);
            $a = array();
            if ($nargs > 1) {
                for ($i = 1; $i < $nargs; $i++) {
                    $index = $i - 1;
                    $a["m{$index}"] = func_get_arg($i);
                }
            }
            $return = stack_string($identifier, $a);
            echo $return;
        }
    }
}
/**
 * EXCEPTIONS
 */

if (!function_exists('get_config')) {
    function get_config($section = 'qtype_stack')
    {
        //require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/model/configuration/class.assStackQuestionConfig.php');

        $configs = new stdClass();

        $saved_config = StackConfig::getAll();
        /*
         * CONNECTION CONFIGURATION
         */
        //Platform type
        $configs->platform = $saved_config['platform_type'];
        //Maxima version
        $configs->maximaversion = $saved_config['maxima_version'];
        //Connection timeout
        $configs->castimeout = $saved_config['cas_connection_timeout'];
        //Caching
        $configs->casresultscache = $saved_config['cas_result_caching'];
        //Maxima command - If blank: maxima
        if ($saved_config['platform_type'] == 'server') {
            $configs->maximacommandserver = $saved_config['maxima_pool_url'];
        } elseif (!$saved_config['maxima_command'] or $saved_config['platform_type'] == 'unix') {
            $configs->maximacommand = "maxima";
        } else {
            $configs->maximacommand = $saved_config['maxima_command'];
        }
        //Plot command - If blank: gnuplot
        if (!$saved_config['plot_command']) {
            $configs->plotcommand = "gnuplot";
        } else {
            $configs->plotcommand = $saved_config['plot_command'];
        }
        //CAS debug
        $configs->casdebugging = $saved_config['cas_debugging'] == 1;

        /*
         * DISPLAY CONFIGURATION
         */
        //Instant validation
        $configs->ajaxvalidation = $saved_config['instant_validation'];
        //Maths filter
        $configs->mathsdisplay = $saved_config['maths_filter'];
        //Replace dollars
        $configs->replacedollars = $saved_config['replace_dollars'];

        /*
         * DEFAULT OPTIONS CONFIGURATION
         */
        //simp variable in Maxima
        $configs->questionsimplify = $saved_config['options_question_simplify'];
        //assume_pos variable in maxima
        $configs->assumepositive = $saved_config['options_assume_positive'];
        //PRT Correct message
        $configs->prtcorrect = $saved_config['options_prt_correct'];
        //PRT Partially Correct message
        $configs->prtpartiallycorrect = $saved_config['options_prt_partially_correct'];
        //PRT Incorrect message
        $configs->prtincorrect = $saved_config['options_prt_incorrect'];
        //Multiplication sign
        $configs->multiplicationsign = $saved_config['options_multiplication_sign'];
        //Sqrt sign
        $configs->sqrtsign = $saved_config['options_sqrt_sign'];
        //Complex numbers
        $configs->complexno = $saved_config['options_complex_numbers'];
        //Inverse trigonometric
        $configs->inversetrig = $saved_config['options_inverse_trigonometric'];
        $configs->matrixparens = "[";

        //assume_real variable in maxima
        $configs->assumereal = $saved_config['options_assume_real'];
        //assume_real variable in maxima
        $configs->logicsymbol = $saved_config['options_logic_symbol'];

        /*
         * DEFAULT INPUTS CONFIGURATION
         */
        //Default input type
        $configs->inputtype = $saved_config['input_type'];
        //Default box size
        $configs->inputboxsize = $saved_config['input_box_size'];
        //Use strict syntax
        $configs->inputstrictsyntax = $saved_config['input_strict_syntax'];
        //Insert stars when multiplication
        $configs->inputinsertstars = $saved_config['input_insert_stars'];
        //Forbidden words
        $configs->inputforbidwords = $saved_config['input_forbidden_words'];
        //Forbid floats
        $configs->inputforbidfloat = $saved_config['input_forbid_float'];
        //Require lowest terms
        $configs->inputrequirelowestterms = $saved_config['input_require_lowest_terms'];
        //Check answer type
        $configs->inputcheckanswertype = $saved_config['input_check_answer_type'];
        //Student must verify
        $configs->inputmustverify = $saved_config['input_must_verify'];
        //Show validation button
        $configs->inputshowvalidation = $saved_config['input_show_validation'];

        $configs->maximalocalfolder = ilUtil::getWebspaceDir('filesystem') . '/xqcas/stack';
        $configs->stackmaximaversion = "2023121100";
        $configs->version = "2023121100";

        return $configs;
    }
}

/**
 * Simple html output class
 *
 * @copyright 2009 Tim Hunt, 2010 Petr Skoda
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 * @package core
 * @category output
 */
if (!class_exists('html_writer')) {

    class html_writer
    {

        /**
         * Outputs a tag with attributes and contents
         *
         * @param string $tagname The name of tag ('a', 'img', 'span' etc.)
         * @param string $contents What goes between the opening and closing tags
         * @param array $attributes The tag attributes (array('src' => $url, 'class' => 'class1') etc.)
         * @return string HTML fragment
         */
        public static function tag($tagname, $contents, array $attributes = null)
        {
            return self::start_tag($tagname, $attributes) . $contents . self::end_tag($tagname);
        }

        /**
         * Outputs an opening tag with attributes
         *
         * @param string $tagname The name of tag ('a', 'img', 'span' etc.)
         * @param array $attributes The tag attributes (array('src' => $url, 'class' => 'class1') etc.)
         * @return string HTML fragment
         */
        public static function start_tag($tagname, array $attributes = null)
        {
            return '<' . $tagname . self::attributes($attributes) . '>';
        }

        /**
         * Outputs a closing tag
         *
         * @param string $tagname The name of tag ('a', 'img', 'span' etc.)
         * @return string HTML fragment
         */
        public static function end_tag($tagname)
        {
            return '</' . $tagname . '>';
        }

        /**
         * Outputs an empty tag with attributes
         *
         * @param string $tagname The name of tag ('input', 'img', 'br' etc.)
         * @param array $attributes The tag attributes (array('src' => $url, 'class' => 'class1') etc.)
         * @return string HTML fragment
         */
        public static function empty_tag($tagname, array $attributes = null)
        {
            return '<' . $tagname . self::attributes($attributes) . ' />';
        }

        /**
         * Outputs a tag, but only if the contents are not empty
         *
         * @param string $tagname The name of tag ('a', 'img', 'span' etc.)
         * @param string $contents What goes between the opening and closing tags
         * @param array $attributes The tag attributes (array('src' => $url, 'class' => 'class1') etc.)
         * @return string HTML fragment
         */
        public static function nonempty_tag($tagname, $contents, array $attributes = null)
        {
            if ($contents === '' || is_null($contents)) {
                return '';
            }

            return self::tag($tagname, $contents, $attributes);
        }

        /**
         * Outputs a HTML attribute and value
         *
         * @param string $name The name of the attribute ('src', 'href', 'class' etc.)
         * @param string $value The value of the attribute. The value will be escaped with {@link s()}
         * @return string HTML fragment
         */
        public static function attribute($name, $value)
        {
            if ($value instanceof moodle_url) {
                return ' ' . $name . '="' . $value->out() . '"';
            }

            // special case, we do not want these in output
            if ($value === null) {
                return '';
            }

            // no sloppy trimming here!
            return ' ' . $name . '="' . s($value) . '"';
        }


        /**
         * Outputs a list of HTML attributes and values
         *
         * @param array $attributes The tag attributes (array('src' => $url, 'class' => 'class1') etc.)
         *       The values will be escaped with {@link s()}
         * @return string HTML fragment
         */
        public static function attributes(array $attributes = null)
        {
            $attributes = (array)$attributes;
            $output = '';
            foreach ($attributes as $name => $value) {
                $output .= self::attribute($name, $value);
            }

            return $output;
        }

        /**
         * Generates random html element id.
         *
         * @staticvar int $counter
         * @staticvar type $uniq
         * @param string $base A string fragment that will be included in the random ID.
         * @return string A unique ID
         */
        public static function random_id($base = 'random')
        {
            static $counter = 0;
            static $uniq;

            if (!isset($uniq)) {
                $uniq = uniqid();
            }

            $counter++;

            return $base . $uniq . $counter;
        }

        /**
         * Generates a simple html link
         *
         * @param string|moodle_url $url The URL
         * @param string $text The text
         * @param array $attributes HTML attributes
         * @return string HTML fragment
         */
        public static function link($url, $text, array $attributes = null)
        {
            $attributes = (array)$attributes;
            $attributes['href'] = $url;

            return self::tag('a', $text, $attributes);
        }

        /**
         * Generates a simple checkbox with optional label
         *
         * @param string $name The name of the checkbox
         * @param string $value The value of the checkbox
         * @param bool $checked Whether the checkbox is checked
         * @param string $label The label for the checkbox
         * @param array $attributes Any attributes to apply to the checkbox
         * @return string html fragment
         */
        public static function checkbox($name, $value, $checked = true, $label = '', array $attributes = null)
        {
            $attributes = (array)$attributes;
            $output = '';

            if ($label !== '' and !is_null($label)) {
                if (empty($attributes['id'])) {
                    $attributes['id'] = self::random_id('checkbox_');
                }
            }
            $attributes['type'] = 'checkbox';
            $attributes['value'] = $value;
            $attributes['name'] = $name;
            $attributes['checked'] = $checked ? 'checked' : null;

            $output .= self::empty_tag('input', $attributes);

            if ($label !== '' and !is_null($label)) {
                $output .= self::tag('label', $label, array('for' => $attributes['id']));
            }

            return $output;
        }

        /**
         * Generates a simple select yes/no form field
         *
         * @param string $name name of select element
         * @param bool $selected
         * @param array $attributes - html select element attributes
         * @return string HTML fragment
         */
        public static function select_yes_no($name, $selected = true, array $attributes = null)
        {
            $options = array('1' => get_string('yes'), '0' => get_string('no'));

            return self::select($options, $name, $selected, null, $attributes);
        }

        /**
         * Generates a simple select form field
         *
         * @param array $options associative array value=>label ex.:
         *                array(1=>'One, 2=>Two)
         *              it is also possible to specify optgroup as complex label array ex.:
         *                array(array('Odd'=>array(1=>'One', 3=>'Three)), array('Even'=>array(2=>'Two')))
         *                array(1=>'One', '--1uniquekey'=>array('More'=>array(2=>'Two', 3=>'Three')))
         * @param string $name name of select element
         * @param string|array $selected value or array of values depending on multiple attribute
         * @param array|bool $nothing add nothing selected option, or false of not added
         * @param array $attributes html select element attributes
         * @return string HTML fragment
         */
        public static function select(array $options, $name, $selected = '', $nothing = array('' => 'choosedots'), array $attributes = null)
        {
            $attributes = (array)$attributes;
            if (is_array($nothing)) {
                foreach ($nothing as $k => $v) {
                    if ($v === 'choose' or $v === 'choosedots') {
                        $nothing[$k] = get_string('choosedots');
                    }
                }
                $options = $nothing + $options; // keep keys, do not override
            } else {
                if (is_string($nothing) and $nothing !== '') {
                    // BC
                    $options = array('' => $nothing) + $options;
                }
            }

            // we may accept more values if multiple attribute specified
            $selected = (array)$selected;
            foreach ($selected as $k => $v) {
                $selected[$k] = (string)$v;
            }

            if (!isset($attributes['id'])) {
                $id = 'menu' . $name;
                // name may contaion [], which would make an invalid id. e.g. numeric question type editing form, assignment quickgrading
                $id = str_replace('[', '', $id);
                $id = str_replace(']', '', $id);
                $attributes['id'] = $id;
            }

            if (!isset($attributes['class'])) {
                $class = 'menu' . $name;
                // name may contaion [], which would make an invalid class. e.g. numeric question type editing form, assignment quickgrading
                $class = str_replace('[', '', $class);
                $class = str_replace(']', '', $class);
                $attributes['class'] = $class;
            }
            $attributes['class'] = 'select ' . $attributes['class']; // Add 'select' selector always

            $attributes['name'] = $name;

            if (!empty($attributes['disabled'])) {
                $attributes['disabled'] = 'disabled';
            } else {
                unset($attributes['disabled']);
            }

            $output = '';
            foreach ($options as $value => $label) {
                if (is_array($label)) {
                    // ignore key, it just has to be unique
                    $output .= self::select_optgroup(key($label), current($label), $selected);
                } else {
                    $output .= self::select_option($label, $value, $selected);
                }
            }

            return self::tag('select', $output, $attributes);
        }

        /**
         * Returns HTML to display a select box option.
         *
         * @param string $label The label to display as the option.
         * @param string|int $value The value the option represents
         * @param array $selected An array of selected options
         * @return string HTML fragment
         */
        private static function select_option($label, $value, array $selected)
        {
            $attributes = array();
            $value = (string)$value;
            if (in_array($value, $selected, true)) {
                $attributes['selected'] = 'selected';
            }
            $attributes['value'] = $value;

            return self::tag('option', $label, $attributes);
        }

        /**
         * Returns HTML to display a select box option group.
         *
         * @param string $groupname The label to use for the group
         * @param array $options The options in the group
         * @param array $selected An array of selected values.
         * @return string HTML fragment.
         */
        private static function select_optgroup($groupname, $options, array $selected)
        {
            if (empty($options)) {
                return '';
            }
            $attributes = array('label' => $groupname);
            $output = '';
            foreach ($options as $value => $label) {
                $output .= self::select_option($label, $value, $selected);
            }

            return self::tag('optgroup', $output, $attributes);
        }

        /**
         * This is a shortcut for making an hour selector menu.
         *
         * @param string $type The type of selector (years, months, days, hours, minutes)
         * @param string $name fieldname
         * @param int $currenttime A default timestamp in GMT
         * @param int $step minute spacing
         * @param array $attributes - html select element attributes
         * @return HTML fragment
         */
        public static function select_time($type, $name, $currenttime = 0, $step = 5, array $attributes = null)
        {
            if (!$currenttime) {
                $currenttime = time();
            }
            $currentdate = usergetdate($currenttime);
            $userdatetype = $type;
            $timeunits = array();

            switch ($type) {
                case 'years':
                    for ($i = 1970; $i <= 2020; $i++) {
                        $timeunits[$i] = $i;
                    }
                    $userdatetype = 'year';
                    break;
                case 'months':
                    for ($i = 1; $i <= 12; $i++) {
                        $timeunits[$i] = userdate(gmmktime(12, 0, 0, $i, 15, 2000), "%B");
                    }
                    $userdatetype = 'month';
                    $currentdate['month'] = (int)$currentdate['mon'];
                    break;
                case 'days':
                    for ($i = 1; $i <= 31; $i++) {
                        $timeunits[$i] = $i;
                    }
                    $userdatetype = 'mday';
                    break;
                case 'hours':
                    for ($i = 0; $i <= 23; $i++) {
                        $timeunits[$i] = sprintf("%02d", $i);
                    }
                    break;
                case 'minutes':
                    if ($step != 1) {
                        $currentdate['minutes'] = ceil($currentdate['minutes'] / $step) * $step;
                    }

                    for ($i = 0; $i <= 59; $i += $step) {
                        $timeunits[$i] = sprintf("%02d", $i);
                    }
                    break;
                default:
                    throw new Exception("Time type $type is not supported by html_writer::select_time().");
            }

            if (empty($attributes['id'])) {
                $attributes['id'] = self::random_id('ts_');
            }
            $timerselector = self::select($timeunits, $name, $currentdate[$userdatetype], null, array('id' => $attributes['id']));
            $label = self::tag('label', get_string(substr($type, 0, -1), 'form'), array('for' => $attributes['id'], 'class' => 'accesshide'));

            return $label . $timerselector;
        }

        /**
         * Shortcut for quick making of lists
         *
         * Note: 'list' is a reserved keyword ;-)
         *
         * @param array $items
         * @param array $attributes
         * @param string $tag ul or ol
         * @return string
         */
        public static function alist(array $items, array $attributes = null, $tag = 'ul')
        {
            $output = '';

            foreach ($items as $item) {
                $output .= html_writer::start_tag('li') . "\n";
                $output .= $item . "\n";
                $output .= html_writer::end_tag('li') . "\n";
            }

            return html_writer::tag($tag, $output, $attributes);
        }

        /**
         * Returns hidden input fields created from url parameters.
         *
         * @param moodle_url $url
         * @param array $exclude list of excluded parameters
         * @return string HTML fragment
         */
        public static function input_hidden_params(moodle_url $url, array $exclude = null)
        {
            $exclude = (array)$exclude;
            $params = $url->params();
            foreach ($exclude as $key) {
                unset($params[$key]);
            }

            $output = '';
            foreach ($params as $key => $value) {
                $attributes = array('type' => 'hidden', 'name' => $key, 'value' => $value);
                $output .= self::empty_tag('input', $attributes) . "\n";
            }

            return $output;
        }

        /**
         * Generate a script tag containing the the specified code.
         *
         * @param string $jscode the JavaScript code
         * @param moodle_url|string $url optional url of the external script, $code ignored if specified
         * @return string HTML, the code wrapped in <script> tags.
         */
        public static function script($jscode, $url = null)
        {
            if ($jscode) {
                $attributes = array('type' => 'text/javascript');

                return self::tag('script', "\n//<![CDATA[\n$jscode\n//]]>\n", $attributes) . "\n";
            } else {
                if ($url) {
                    $attributes = array('type' => 'text/javascript', 'src' => $url);

                    return self::tag('script', '', $attributes) . "\n";
                } else {
                    return '';
                }
            }
        }

        /**
         * Renders HTML table
         *
         * This method may modify the passed instance by adding some default properties if they are not set yet.
         * If this is not what you want, you should make a full clone of your data before passing them to this
         * method. In most cases this is not an issue at all so we do not clone by default for performance
         * and memory consumption reasons.
         *
         * @param html_table $table data to be rendered
         * @return string HTML code
         */
        public static function table(html_table $table)
        {
            // prepare table data and populate missing properties with reasonable defaults
            if (!empty($table->align)) {
                foreach ($table->align as $key => $aa) {
                    if ($aa) {
                        $table->align[$key] = 'text-align:' . fix_align_rtl($aa) . ';'; // Fix for RTL languages
                    } else {
                        $table->align[$key] = null;
                    }
                }
            }
            if (!empty($table->size)) {
                foreach ($table->size as $key => $ss) {
                    if ($ss) {
                        $table->size[$key] = 'width:' . $ss . ';';
                    } else {
                        $table->size[$key] = null;
                    }
                }
            }
            if (!empty($table->wrap)) {
                foreach ($table->wrap as $key => $ww) {
                    if ($ww) {
                        $table->wrap[$key] = 'white-space:nowrap;';
                    } else {
                        $table->wrap[$key] = '';
                    }
                }
            }
            if (!empty($table->head)) {
                foreach ($table->head as $key => $val) {
                    if (!isset($table->align[$key])) {
                        $table->align[$key] = null;
                    }
                    if (!isset($table->size[$key])) {
                        $table->size[$key] = null;
                    }
                    if (!isset($table->wrap[$key])) {
                        $table->wrap[$key] = null;
                    }
                }
            }
            if (empty($table->attributes['class'])) {
                $table->attributes['class'] = 'generaltable';
            }
            if (!empty($table->tablealign)) {
                $table->attributes['class'] .= ' boxalign' . $table->tablealign;
            }

            // explicitly assigned properties override those defined via $table->attributes
            $table->attributes['class'] = trim($table->attributes['class']);
            $attributes = array_merge($table->attributes, array('id' => $table->id, 'width' => $table->width, 'summary' => $table->summary, 'cellpadding' => $table->cellpadding, 'cellspacing' => $table->cellspacing,));
            $output = html_writer::start_tag('table', $attributes) . "\n";

            $countcols = 0;

            if (!empty($table->head)) {
                $countcols = count($table->head);

                $output .= html_writer::start_tag('thead', array()) . "\n";
                $output .= html_writer::start_tag('tr', array()) . "\n";
                $keys = array_keys($table->head);
                $lastkey = end($keys);

                foreach ($table->head as $key => $heading) {
                    // Convert plain string headings into html_table_cell objects
                    if (!($heading instanceof html_table_cell)) {
                        $headingtext = $heading;
                        $heading = new html_table_cell();
                        $heading->text = $headingtext;
                        $heading->header = true;
                    }

                    if ($heading->header !== false) {
                        $heading->header = true;
                    }

                    if ($heading->header && empty($heading->scope)) {
                        $heading->scope = 'col';
                    }

                    $heading->attributes['class'] .= ' header c' . $key;
                    if (isset($table->headspan[$key]) && $table->headspan[$key] > 1) {
                        $heading->colspan = $table->headspan[$key];
                        $countcols += $table->headspan[$key] - 1;
                    }

                    if ($key == $lastkey) {
                        $heading->attributes['class'] .= ' lastcol';
                    }
                    if (isset($table->colclasses[$key])) {
                        $heading->attributes['class'] .= ' ' . $table->colclasses[$key];
                    }
                    $heading->attributes['class'] = trim($heading->attributes['class']);
                    $attributes = array_merge($heading->attributes, array('style' => $table->align[$key] . $table->size[$key] . $heading->style, 'scope' => $heading->scope, 'colspan' => $heading->colspan,));

                    $tagtype = 'td';
                    if ($heading->header === true) {
                        $tagtype = 'th';
                    }
                    $output .= html_writer::tag($tagtype, $heading->text, $attributes) . "\n";
                }
                $output .= html_writer::end_tag('tr') . "\n";
                $output .= html_writer::end_tag('thead') . "\n";

                if (empty($table->data)) {
                    // For valid XHTML strict every table must contain either a valid tr
                    // or a valid tbody... both of which must contain a valid td
                    $output .= html_writer::start_tag('tbody', array('class' => 'empty'));
                    $output .= html_writer::tag('tr', html_writer::tag('td', '', array('colspan' => count($table->head))));
                    $output .= html_writer::end_tag('tbody');
                }
            }

            if (!empty($table->data)) {
                $oddeven = 1;
                $keys = array_keys($table->data);
                $lastrowkey = end($keys);
                $output .= html_writer::start_tag('tbody', array());

                foreach ($table->data as $key => $row) {
                    if (($row === 'hr') && ($countcols)) {
                        $output .= html_writer::tag('td', html_writer::tag('div', '', array('class' => 'tabledivider')), array('colspan' => $countcols));
                    } else {
                        // Convert array rows to html_table_rows and cell strings to html_table_cell objects
                        if (!($row instanceof html_table_row)) {
                            $newrow = new html_table_row();

                            foreach ($row as $cell) {
                                if (!($cell instanceof html_table_cell)) {
                                    $cell = new html_table_cell($cell);
                                }
                                $newrow->cells[] = $cell;
                            }
                            $row = $newrow;
                        }

                        $oddeven = $oddeven ? 0 : 1;
                        if (isset($table->rowclasses[$key])) {
                            $row->attributes['class'] .= ' ' . $table->rowclasses[$key];
                        }

                        $row->attributes['class'] .= ' r' . $oddeven;
                        if ($key == $lastrowkey) {
                            $row->attributes['class'] .= ' lastrow';
                        }

                        $output .= html_writer::start_tag('tr', array('class' => trim($row->attributes['class']), 'style' => $row->style, 'id' => $row->id)) . "\n";
                        $keys2 = array_keys($row->cells);
                        $lastkey = end($keys2);

                        $gotlastkey = false; //flag for sanity checking
                        foreach ($row->cells as $key => $cell) {
                            if ($gotlastkey) {
                                //This should never happen. Why do we have a cell after the last cell?
                                mtrace("A cell with key ($key) was found after the last key ($lastkey)");
                            }

                            if (!($cell instanceof html_table_cell)) {
                                $mycell = new html_table_cell();
                                $mycell->text = $cell;
                                $cell = $mycell;
                            }

                            if (($cell->header === true) && empty($cell->scope)) {
                                $cell->scope = 'row';
                            }

                            if (isset($table->colclasses[$key])) {
                                $cell->attributes['class'] .= ' ' . $table->colclasses[$key];
                            }

                            $cell->attributes['class'] .= ' cell c' . $key;
                            if ($key == $lastkey) {
                                $cell->attributes['class'] .= ' lastcol';
                                $gotlastkey = true;
                            }
                            $tdstyle = '';
                            $tdstyle .= isset($table->align[$key]) ? $table->align[$key] : '';
                            $tdstyle .= isset($table->size[$key]) ? $table->size[$key] : '';
                            $tdstyle .= isset($table->wrap[$key]) ? $table->wrap[$key] : '';
                            $cell->attributes['class'] = trim($cell->attributes['class']);
                            $tdattributes = array_merge($cell->attributes, array('style' => $tdstyle . $cell->style, 'colspan' => $cell->colspan, 'rowspan' => $cell->rowspan, 'id' => $cell->id, 'abbr' => $cell->abbr, 'scope' => $cell->scope,));
                            $tagtype = 'td';
                            if ($cell->header === true) {
                                $tagtype = 'th';
                            }
                            $output .= html_writer::tag($tagtype, $cell->text, $tdattributes) . "\n";
                        }
                    }
                    $output .= html_writer::end_tag('tr') . "\n";
                }
                $output .= html_writer::end_tag('tbody') . "\n";
            }
            $output .= html_writer::end_tag('table') . "\n";

            return $output;
        }

        /**
         * Renders form element label
         *
         * By default, the label is suffixed with a label separator defined in the
         * current language pack (colon by default in the English lang pack).
         * Adding the colon can be explicitly disabled if needed. Label separators
         * are put outside the label tag itself so they are not read by
         * screenreaders (accessibility).
         *
         * Parameter $for explicitly associates the label with a form control. When
         * set, the value of this attribute must be the same as the value of
         * the id attribute of the form control in the same document. When null,
         * the label being defined is associated with the control inside the label
         * element.
         *
         * @param string $text content of the label tag
         * @param string|null $for id of the element this label is associated with, null for no association
         * @param bool $colonize add label separator (colon) to the label text, if it is not there yet
         * @param array $attributes to be inserted in the tab, for example array('accesskey' => 'a')
         * @return string HTML of the label element
         */
        public static function label($text, $for, $colonize = true, array $attributes = array())
        {
            if (!is_null($for)) {
                $attributes = array_merge($attributes, array('for' => $for));
            }
            $text = trim($text);
            $label = self::tag('label', $text, $attributes);

            // TODO MDL-12192 $colonize disabled for now yet
            // if (!empty($text) and $colonize) {
            //     // the $text may end with the colon already, though it is bad string definition style
            //     $colon = get_string('labelsep', 'langconfig');
            //     if (!empty($colon)) {
            //         $trimmed = trim($colon);
            //         if ((substr($text, -strlen($trimmed)) == $trimmed) or (substr($text, -1) == ':')) {
            //             //debugging('The label text should not end with colon or other label separator,
            //             //           please fix the string definition.', DEBUG_DEVELOPER);
            //         } else {
            //             $label .= $colon;
            //         }
            //     }
            // }

            return $label;
        }


    }
}

/**
 * Add quotes to HTML characters.
 *
 * Returns $var with HTML characters (like "<", ">", etc.) properly quoted.
 * This function is very similar to {@link p()}
 *
 * @param string $var the string potentially containing HTML characters
 * @return string
 */
if (!function_exists('s')) {
    function s($var)
    {

        if ($var === false) {
            return '0';
        }

        // When we move to PHP 5.4 as a minimum version, change ENT_QUOTES on the
        // next line to ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, and remove the
        // 'UTF-8' argument. Both bring a speed-increase.
        return preg_replace('/&amp;#(\d+|x[0-9a-f]+);/i', '&#$1;', htmlspecialchars($var, ENT_QUOTES, 'UTF-8'));
    }
}