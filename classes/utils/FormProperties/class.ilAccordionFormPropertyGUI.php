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

//require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/FormProperties/class.ilMultipartFormPropertyGUI.php';
//require_once './Services/Accordion/classes/class.ilAccordionGUI.php';

/**
 * Accordion property GUI class
 * This object implements the ILIAS Accordion object as a Form property.
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 *
 */
class ilAccordionFormPropertyGUI extends ilMultipartFormPropertyGUI
{

	/**
	 * @var ilTemplate
	 */
	private $template;

	/**
	 * @var float
	 */
	private $width;

	function __construct($a_title = "", $a_postvar = "", $a_container_width = "", $a_show_title = "")
	{
		$a_title = "";

		parent::__construct($a_title, $a_postvar, $a_container_width, $a_show_title);

		//Set template for accordion
		$template = new ilTemplate('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/tpl.accordion_form_property.html', TRUE, TRUE);
		$this->setTemplate($template);
	}

	/**
	 * @return HTML for this form property
	 */
	protected function render()
	{
		//Create Accordion object
		$accordion = new ilAccordionGUI();
		$accordion->setId($this->getTitle());

		//Marko's suggestion allow multiopened
		$accordion->setAllowMultiOpened(TRUE);

		//Set container width
		$this->getTemplate()->setVariable("CONTAINER_WIDTH", $this->getContainerWidth());

		//Filling parts
		foreach ($this->getParts() as $part)
		{
			//Addition of form properties
			foreach ($part->getContent() as $form_property)
			{
				$this->getTemplate()->setVariable("PART_TYPE", $part->getType());

				//Fill Title and Info
				$this->getTemplate()->setCurrentBlock('prop_container');
				$this->getTemplate()->setVariable("PART_TYPE", $part->getType());

				if ($this->getShowTitle())
				{
					if ($form_property->getRequired())
					{
						$this->getTemplate()->setVariable("PROP_TITLE", $form_property->getTitle() . "<font color=\"red\"> *</font>");
					} else
					{
						$this->getTemplate()->setVariable("PROP_TITLE", $form_property->getTitle());
					}
				}
				//Set width
				$this->getTemplate()->setVariable("TITLE_WIDTH", $this->getWidthDivision('title'));
				$this->getTemplate()->setVariable("FOOTER_WIDTH", $this->getWidthDivision('footer'));
				$this->getTemplate()->setVariable("PROP_INFO", $form_property->getInfo());

				//Fill Form property
				$form_property->insert($this->getTemplate(), $this->getWidthDivision('content'));

				//Fill info and footer
				$this->getTemplate()->setCurrentBlock('prop_container');
				$this->getTemplate()->setVariable("TITLE_WIDTH", $this->getWidthDivision('title'));
				$this->getTemplate()->setVariable("CONTENT_WIDTH", $this->getWidthDivision('content'));
				$this->getTemplate()->setVariable("FOOTER_WIDTH", $this->getWidthDivision('footer'));
				$this->getTemplate()->parseCurrentBlock();
			}
			$accordion->addItem($part->getTitle(), $this->getTemplate()->get(), TRUE);
			//Set template for accordion
			$template = new ilTemplate('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/tpl.accordion_form_property.html', TRUE, TRUE);
			$this->setTemplate($template);
		}

		return $accordion->getHTML();
	}

	/*
	 * GETTERS AND SETTERS
	 */

	/**
	 * @param \ilTemplate $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}

	/**
	 * @return \ilTemplate
	 */
	public function getTemplate()
	{
		return $this->template;
	}

}