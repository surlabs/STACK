<?php
declare(strict_types=1);

namespace src\core\options;

use src\core\security\StackException;

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
class StackOptions
{

    const STACK_OPTIONS_STATUS_ERROR = -1;
    const STACK_OPTIONS_STATUS_UNINITIALIZED = 0;
    const STACK_OPTIONS_STATUS_INITIALIZED = 1;

    /**
     * The current status of the StackOptions object.
     * @var int
     */
    private int $status = self::STACK_OPTIONS_STATUS_UNINITIALIZED;

    /**
     * The array of options.
     * @var array
     */
    private array $data = [];

    /**
     * StackOptions constructor.
     * Creates a new StackOptions object with the given options.
     * If blank array, the default options are used.
     * @param array $array_options
     * @throws StackException
     */
    public function __construct(array $array_options)
    {
        $this->data = StackOptionsDefault::getDefaultOptions();

        //Overwrite default with given options
        foreach ($array_options as $option_key => $option_value) {
            if (!array_key_exists($option_key, $this->data)) {
                //TODO: Log error, invalid option name
                $this->status = self::STACK_OPTIONS_STATUS_ERROR;
                throw new StackException('stack_options construct: $key ' . $option_key . ' is not a valid option name.');
            } else {
                $this->data[$option_key]['value'] = $option_value;
            }
        }

        //This must be the unique place where the status is set to initialized
        $this->status = self::STACK_OPTIONS_STATUS_INITIALIZED;
    }

    /**
     * Returns current status of the object.
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

}