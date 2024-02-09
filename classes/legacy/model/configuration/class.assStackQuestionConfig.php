<?php
declare(strict_types=1);
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
 * STACK Question plugin config class
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 *
 */
class assStackQuestionConfig
{

    //TODO SAUL: eliminar esta clase y sustituir con StackConfig

	/** @var assStackQuestionServer */
	protected static $server;

	/** @var array */
	protected $settings;


	public function __construct($plugin_object = "")
	{
		$this->plugin_object = $plugin_object;
	}

	/*
	 * GET SETTINGS FROM DATABASE
	 */


	/**
	 * Get a configuration setting
	 * @param $name
	 * @return mixed
	 */
	public function get($name)
	{
		if (!isset($this->settings))
		{
			$this->settings = self::_getStoredSettings('all');
		}

		return $this->settings[$name];
	}

	/**
	 * This class can be called from anywhere to get configuration
	 * @param $selector // a string for select  the type of settings needed
	 * @return array // of selected settings
	 */
	public static function _getStoredSettings($selector)
	{
		global $DIC;
		$db = $DIC->database();
		$settings = array();
		if ($selector == 'all')
		{
			$query = 'SELECT * FROM xqcas_configuration';
		} else
		{
			$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "' . $selector . '"';
		}
		$result = $db->query($query);
		while ($row = $db->fetchAssoc($result))
		{
			$settings[$row['parameter_name']] = $row['value'];
		}

		return $settings;
	}


	/**
	 * Read the server configuration from a configuration array
	 * This avoids a second reading
	 * @param $config
	 */
	public static function _readServers($config)
	{
		//require_once(__DIR__ . '/class.assStackQuestionServer.php');
		assStackQuestionServer::readServersFromConfig($config);
	}


	/**
	 * Get the maxima server address for the current request
	 * The chosen server is cached for the request
	 *
	 * @return string
	 */
	public static function _getServerAddress()
	{
		//require_once(__DIR__ . '/class.assStackQuestionServer.php');

		if (isset(self::$server))
		{
			return self::$server->getAddress();
		}

		if (!empty($_REQUEST['server_id']))
		{
			self::$server = assStackQuestionServer::getServerById($_REQUEST['server_id']);

			return self::$server->getAddress();

		}

        $action = $_GET['cmdClass'] ? $_GET['cmdClass'] : "";

		switch (strtolower($action))
		{
			case 'iltestplayerfixedquestionsetgui':
			case 'iltestplayerrandomquestionsetgui':
			case 'iltestplayerdynamicquestionsetgui':
				$purpose = assStackQuestionServer::PURPOSE_RUN;
				break;

			default:
				switch (basename($_SERVER['SCRIPT_FILENAME']))
				{
					case 'validation.php':
					case 'instant_validiation.php':
						$purpose = assStackQuestionServer::PURPOSE_RUN;
						break;
					default:
						$purpose = assStackQuestionServer::PURPOSE_EDIT;
				}
		}


		self::$server = assStackQuestionServer::getServerForPurpose($purpose);

		return self::$server->getAddress();
	}


	/*
	 * SAVE SETTINGS TO DATABASE
	*/

	/**
	 * Saves new connection to maxima settings to the DB
	 */
	public function saveConnectionSettings()
	{
		//Old settings

		$saved_connection_data = self::_getStoredSettings('connection');
		//New settings
		$new_connection_data = $this->getAdminInput();

		/*
		 * IF AUTOMATIC DETECTION OF PLATFORM
		 * USE THIS
		 *

		$uname = strtolower(php_uname());
		if (strpos($uname, "darwin") !== false) {
			$new_connection_data['platform_type'] = 'unix';
		} else if (strpos($uname, "win") !== false) {
			$new_connection_data['platform_type'] = 'win';
		} else if (strpos($uname, "linux") !== false) {
			$new_connection_data['platform_type'] = 'unix';
		} else {
			$new_connection_data['platform_type'] = 'unix';
		}
		*/

		//Checkboxes workaround
		if (!array_key_exists('cas_debugging', $new_connection_data))
		{
			$new_connection_data['cas_debugging'] = 0;
		}

		//Save to DB
		foreach ($saved_connection_data as $paremeter_name => $saved_value)
		{
			if (array_key_exists($paremeter_name, $new_connection_data) AND $saved_connection_data[$paremeter_name] != $new_connection_data[$paremeter_name])
			{
				$this->saveToDB($paremeter_name, $new_connection_data[$paremeter_name], 'connection');
			}
		}

		//Force re-creation of maxima local file with new content from stack4.
		require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionInitialization.php');
		//require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/cas/installhelper.class.php');
        global $CFG;

		if (!file_exists($CFG->dataroot . '/stack/maximalocal.mac'))
		{
			stack_cas_configuration::create_maximalocal();
		} else
		{
			unlink($CFG->dataroot . '/stack/maximalocal.mac');
			stack_cas_configuration::create_maximalocal();
		}

		return TRUE;
	}

	/**
	 * Saves new Maths display settings to the DB
	 */
	public function saveDisplaySettings()
	{
		//Old settings
		$saved_display_data = self::_getStoredSettings('display');
		//New settings
		$new_display_data = $this->getAdminInput();

		//Checkboxes workaround
		if (!array_key_exists('instant_validation', $new_display_data))
		{
			$new_display_data['instant_validation'] = 0;
		}
		if (!array_key_exists('replace_dollars', $new_display_data))
		{
			$new_display_data['replace_dollars'] = 0;
		}
        if (!array_key_exists('allow_jsx_graph', $new_display_data))
        {
            $new_display_data['allow_jsx_graph'] = 0;
        }else{
            $new_display_data['allow_jsx_graph'] = 1;
        }

		//Save to DB
		foreach ($saved_display_data as $paremeter_name => $saved_value)
		{
			if (array_key_exists($paremeter_name, $new_display_data) AND $saved_display_data[$paremeter_name] != $new_display_data[$paremeter_name])
			{
				$this->saveToDB($paremeter_name, $new_display_data[$paremeter_name], 'display');
			}
		}

		return TRUE;
	}

	/**
	 * Saves new default options settings to the DB
	 */
	public function saveDefaultOptionsSettings()
	{
		//Old settings
		$saved_options_data = self::_getStoredSettings('options');
		//New settings
		$new_options_data = $this->getAdminInput();

		//Checkboxes workaround
		if (!array_key_exists('options_question_simplify', $new_options_data))
		{
			$new_options_data['options_question_simplify'] = 0;
		}
		if (!array_key_exists('options_assume_positive', $new_options_data))
		{
			$new_options_data['options_assume_positive'] = 0;
		}
		if (!array_key_exists('options_sqrt_sign', $new_options_data))
		{
			$new_options_data['options_sqrt_sign'] = 0;
		}

		//Save to DB
		foreach ($saved_options_data as $paremeter_name => $saved_value)
		{
			if (array_key_exists($paremeter_name, $new_options_data) AND $saved_options_data[$paremeter_name] != $new_options_data[$paremeter_name])
			{
				$this->saveToDB($paremeter_name, $new_options_data[$paremeter_name], 'options');
			}
		}

		return TRUE;
	}

	/**
	 * Saves new default inputs settings to the DB
	 */
	public function saveDefaultInputsSettings()
	{
		//Old settings
		$saved_inputs_data = self::_getStoredSettings('inputs');
		//New settings
		$new_inputs_data = $this->getAdminInput();

		//Checkboxes workaround
		if (!array_key_exists('input_strict_syntax', $new_inputs_data))
		{
			$new_inputs_data['input_strict_syntax'] = 0;
		}
		if (!array_key_exists('input_insert_stars', $new_inputs_data))
		{
			$new_inputs_data['input_insert_stars'] = 0;
		}
		if (!array_key_exists('input_forbid_float', $new_inputs_data))
		{
			$new_inputs_data['input_forbid_float'] = 0;
		}
		if (!array_key_exists('input_require_lowest_terms', $new_inputs_data))
		{
			$new_inputs_data['input_require_lowest_terms'] = 0;
		}
		if (!array_key_exists('input_check_answer_type', $new_inputs_data))
		{
			$new_inputs_data['input_check_answer_type'] = 0;
		}
		if (!array_key_exists('input_must_verify', $new_inputs_data))
		{
			$new_inputs_data['input_must_verify'] = 0;
		}
		if (!array_key_exists('input_show_validation', $new_inputs_data))
		{
			$new_inputs_data['input_show_validation'] = 0;
		}

		//Save to DB
		foreach ($saved_inputs_data as $paremeter_name => $saved_value)
		{
			if (array_key_exists($paremeter_name, $new_inputs_data) AND $saved_inputs_data[$paremeter_name] != $new_inputs_data[$paremeter_name])
			{
				$this->saveToDB($paremeter_name, $new_inputs_data[$paremeter_name], 'inputs');
			}
		}

		return TRUE;
	}

	public function saveFeedbackStyleSettings()
	{
		//Old settings
		$saved_feedback_data = self::_getStoredSettings('feedback');
		//New settings
		$new_feedback_data = $this->getAdminInput();

		//Check if Content Style has changed, if changed set pther fields to 0, otherwise, save it with new values
		if ($saved_feedback_data["feedback_stylesheet_id"] != $new_feedback_data["feedback_stylesheet_id"])
		{
			//Save to DB
			foreach ($saved_feedback_data as $parameter_name => $saved_value)
			{
				if ($parameter_name != "feedback_stylesheet_id")
				{
					$this->saveToDB($parameter_name, "0", 'feedback');
				} else
				{
					$this->saveToDB($parameter_name, $new_feedback_data[$parameter_name], 'feedback');
				}
			}
		} else
		{
			//Save to DB modified dat
			foreach ($saved_feedback_data as $paremeter_name => $saved_value)
			{
				if (array_key_exists($paremeter_name, $new_feedback_data) AND $saved_feedback_data[$paremeter_name] != $new_feedback_data[$paremeter_name])
				{
					$this->saveToDB($paremeter_name, $new_feedback_data[$paremeter_name], 'feedback');
				}
			}
		}

		return TRUE;
	}

	/**
	 * Saves new default inputs settings to the DB
	 */
	public function saveDefaultPRTsSettings()
	{
		//Old settings
		$saved_prts_data = self::_getStoredSettings('prts');
		//New settings
		$new_prts_data = $this->getAdminInput();

		//Checkboxes workaround
		if (!array_key_exists('prt_simplify', $new_prts_data))
		{
			$new_prts_data['prt_simplify'] = 1;
		}
		if (!array_key_exists('prt_node_answer_test', $new_prts_data))
		{
			$new_prts_data['prt_node_answer_test'] = 'AlgEquiv';
		}
		if (!array_key_exists('prt_node_options', $new_prts_data))
		{
			$new_prts_data['prt_node_options'] = '';
		}
		if (!array_key_exists('prt_node_quiet', $new_prts_data))
		{
			$new_prts_data['prt_node_quiet'] = '1';
		}
		if (!array_key_exists('prt_pos_mod', $new_prts_data))
		{
			$new_prts_data['prt_pos_mod'] = '+';
		}
		if (!array_key_exists('prt_pos_score', $new_prts_data))
		{
			$new_prts_data['prt_pos_score'] = '1';
		}
		if (!array_key_exists('prt_pos_penalty', $new_prts_data))
		{
			$new_prts_data['prt_pos_penalty'] = '0';
		}
		if (!array_key_exists('prt_pos_answernote', $new_prts_data))
		{
			$new_prts_data['prt_pos_answernote'] = 'prt1-1-T';
		}
		if (!array_key_exists('prt_neg_mod', $new_prts_data))
		{
			$new_prts_data['prt_neg_mod'] = '+';
		}
		if (!array_key_exists('prt_neg_score', $new_prts_data))
		{
			$new_prts_data['prt_neg_score'] = '0';
		}
		if (!array_key_exists('prt_neg_penalty', $new_prts_data))
		{
			$new_prts_data['prt_neg_penalty'] = '0';
		}
		if (!array_key_exists('prt_neg_answernote', $new_prts_data))
		{
			$new_prts_data['prt_neg_answernote'] = 'prt1-1-F';
		}

		//Save to DB
		foreach ($saved_prts_data as $paremeter_name => $saved_value)
		{
			if (array_key_exists($paremeter_name, $new_prts_data) AND $saved_prts_data[$paremeter_name] != $new_prts_data[$paremeter_name])
			{
				$this->saveToDB($paremeter_name, $new_prts_data[$paremeter_name], 'prts');
			}
		}

		return TRUE;
	}

	/**
	 * Save a configuration setting to the database
	 * (needs to be public for assStackQuestionServer::saveServers)
	 *
	 * @param $parameter_name //Is the of the parameter to modify (this is the Primary Key in DB)
	 * @param $value //Is the value of the parameter
	 * @param $group_name //Is the selector for different categories of data
	 */
	public function saveToDB($parameter_name, $value, $group_name)
	{
		global $DIC;
		$db = $DIC->database();
		$db->replace('xqcas_configuration', array('parameter_name' => array('text', $parameter_name)), array('value' => array('clob', $value), 'group_name' => array('text', $group_name),));
	}

	/*
	 * GET DATA FROM POST
	 */

	/**
	 * @return array|mixed|string //The data sent by post
	 */
	public function getAdminInput()
	{
		//https://mantis.ilias.de/view.php?id=25290
		$data = ilUtil::stripSlashesRecursive($_POST, FALSE);
		//Clean array
		unset($data['cmd']);

		return $data;
	}

	/*
	 * SET DEFAULT CONFIGURATION
	 */

	/**
	 * Sets connection configuration to default values.
	 */
	public function setDefaultSettingsForConnection()
	{
		//Default values for connection
        $connection_default_values = array('platform_type' => 'server', 'maxima_version' => '5.44.0', 'cas_connection_timeout' => '250', 'cas_result_caching' => 'db', 'maxima_command' => '', 'plot_command' => '', 'cas_debugging' => '1', 'cas_maxima_libraries' => 'stats, distrib, descriptive, simplex');		foreach ($connection_default_values as $paremeter_name => $value)
		{
			$this->saveToDB($paremeter_name, $value, 'connection');
		}

		//Force re-creation of maxima local file with new content from stack4.
		require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionInitialization.php');
		//require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/cas/installhelper.class.php');
        global $CFG;

		if (!file_exists($CFG->dataroot . '/stack/maximalocal.mac'))
		{
			stack_cas_configuration::create_maximalocal();
		} else
		{
			unlink($CFG->dataroot . '/stack/maximalocal.mac');
			stack_cas_configuration::create_maximalocal();
		}

		return TRUE;
	}

	/**
	 * Sets display configuration to default values.
	 */
	public function setDefaultSettingsForDisplay()
	{
		//Default values for display
		$display_default_values = array('instant_validation' => '0', 'maths_filter' => 'mathjax', 'replace_dollars' => '1');
		foreach ($display_default_values as $paremeter_name => $value)
		{
			$this->saveToDB($paremeter_name, $value, 'display');
		}

		return TRUE;
	}

	/**
	 * Sets default options configuration to default values.
	 */
	public function setDefaultSettingsForOptions()
	{
		//Default values for options
		$options_default_values = array('options_question_simplify' => '1', 'options_assume_positive' => '0', 'options_prt_correct' => $this->plugin_object->txt('default_prt_correct_message'), 'options_prt_partially_correct' => $this->plugin_object->txt('default_prt_partially_correct_message'), 'options_prt_incorrect' => $this->plugin_object->txt('default_prt_incorrect_message'), 'options_multiplication_sign' => 'dot', 'options_sqrt_sign' => '1', 'options_complex_numbers' => 'i', 'options_inverse_trigonometric' => 'cos-1', 'options_matrix_parents' => '[');
		foreach ($options_default_values as $paremeter_name => $value)
		{
			$this->saveToDB($paremeter_name, $value, 'options');
		}

		return TRUE;
	}

	/**
	 * Sets default inputs configuration to default values.
	 */
	public function setDefaultSettingsForInputs()
	{
		//Default values for inputs
		$inputs_default_values = array('input_type' => 'algebraic', 'input_box_size' => '15', 'input_strict_syntax' => '1', 'input_insert_stars' => '0', 'input_forbidden_words' => '', 'input_forbid_float' => '1', 'input_require_lowest_terms' => '0', 'input_check_answer_type' => '0', 'input_must_verify' => '1', 'input_show_validation' => '1', 'input_syntax_hint' => '', 'input_allow_words' => '', 'input_extra_options' => '');
		//Is not the first time, replace current values by default values
		foreach ($inputs_default_values as $paremeter_name => $value)
		{
			$this->saveToDB($paremeter_name, $value, 'inputs');
		}

		return TRUE;
	}

	/**
	 * Sets default prts configuration to default values.
	 */
	public function setDefaultSettingsForPRTs()
	{
		//Default values for prts
		$prts_default_values = array('prt_simplify' => '1', 'prt_node_answer_test' => 'AlgEquiv', 'prt_node_options' => '', 'prt_node_quiet' => '1', 'prt_pos_mod' => '=', 'prt_pos_score' => '1', 'prt_pos_penalty' => '0', 'prt_pos_answernote' => 'prt1-1-T', 'prt_neg_mod' => '=', 'prt_neg_score' => '0', 'prt_neg_penalty' => '0', 'prt_neg_answernote' => 'prt1-1-F');
		//Is not the first time, replace current values by default values
		foreach ($prts_default_values as $paremeter_name => $value)
		{
			$this->saveToDB($paremeter_name, $value, 'prts');
		}

		return TRUE;
	}
}