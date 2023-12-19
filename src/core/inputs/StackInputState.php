<?php
declare(strict_types=1);

namespace src\core\inputs;

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
class StackInputState
{
    protected string $status;

    protected array $contents;
    protected string $contentsModified;
    protected string $contentsDisplayed;
    protected string $errors;
    protected string $note;
    protected string $lvars;
    protected bool $simp;

    /**
     * Constructor
     *
     * @param string $status
     * @param array $contents
     * @param string $contentsModified
     * @param string $contentsDisplayed
     * @param string $errors
     * @param string $note
     * @param string $lvars
     * @param bool $simp
     */
    public function __construct(string $status, array $contents, string $contentsModified, string $contentsDisplayed, string $errors, string $note, string $lvars, bool $simp = false) {
        $this->status              = $status;
        $this->contents            = $contents;
        $this->contentsModified    = $contentsModified;
        $this->contentsDisplayed   = $contentsDisplayed;
        $this->errors              = $errors;
        $this->note                = $note;
        $this->lvars               = $lvars;
        $this->simp                = $simp;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    public function setContents(array $contents): void
    {
        $this->contents = $contents;
    }

    public function getContentsModified(): string
    {
        return $this->contentsModified;
    }

    public function setContentsModified(string $contentsModified): void
    {
        $this->contentsModified = $contentsModified;
    }

    public function getContentsDisplayed(): string
    {
        return $this->contentsDisplayed;
    }

    public function setContentsDisplayed(string $contentsDisplayed): void
    {
        $this->contentsDisplayed = $contentsDisplayed;
    }

    public function getErrors(): string
    {
        return $this->errors;
    }

    public function setErrors(string $errors): void
    {
        $this->errors = $errors;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): void
    {
        $this->note = $note;
    }

    public function getLvars(): string
    {
        return $this->lvars;
    }

    public function setLvars(string $lvars): void
    {
        $this->lvars = $lvars;
    }

    public function isSimp(): bool
    {
        return $this->simp;
    }

    public function setSimp(bool $simp): void
    {
        $this->simp = $simp;
    }
}