<?php
declare(strict_types=1);

namespace classes\core\version;
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
class StackVersion
{
    /**
     * @var string question version by default last
     */
    private string $version = 'last';

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version)
    {
        $this->version = $version;
    }


    /**
     * @var int|null question_id
     */
    private ?int $id = null;
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id)
    {
        $this->id = $id;
    }

    /**
     * StackVersion constructor.
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->setVersion('last');
        $this->setId($id);
    }

    /**
     * @return bool
     */
    public function checkVersion(): bool
    {
        //TODO: Implement version check
        return true;
    }

}