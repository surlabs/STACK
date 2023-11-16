<?php
declare(strict_types=1);

namespace src\core\maxima;
use src\core\security\StackLog;
use src\core\version\StackVersion;

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
class StackSession
{

    const STACK_SESSION_STATUS_ERROR = -1;
    const STACK_SESSION_STATUS_UNINITIALIZED = 0;
    const STACK_SESSION_STATUS_INITIALIZED = 1;
    const STACK_SESSION_STATUS_PREPARED_FOR_MAXIMA = 2;

    /**
     * @var string separator used between successive CAS commands inside the block.
     */
    const MAXIMA_COMMANDS_SEPARATOR = ",\n  ";

    /**
     * @var ?int The current status of the StackSession
     */
    private ?int $status = null;

    /**
     * @var StackVersion The version information of the current STACK Question.
     */
    private StackVersion $version;

    /*
     * @var MaximaEvaluatable[] dasdas.
     */
    private array $statements;

    /**
     * @var ?StackLog The logging of the current STACK Question.
     */
    private ?StackLog $log = null;

    /**
     * StackSession constructor.
     * @param StackVersion $version
     */
    public function __construct(StackVersion $version)
    {
        $this->version = $version;
        $this->log = new StackLog();
        $this->status = self::STACK_SESSION_STATUS_UNINITIALIZED;
    }


    //GETTERS

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @return StackVersion
     */
    public function getSessionVersion(): StackVersion
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    /**
     * @return StackLog|null
     */
    public function getLog(): ?StackLog
    {
        return $this->log;
    }

}