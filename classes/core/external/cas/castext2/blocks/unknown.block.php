<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.
//
namespace classes\core\external\cas\castext2\blocks;

use classes\core\external\cas\castext2\stack_cas_castext2_block;
use classes\core\external\maximaparser\MP_Node;
use classes\platform\StackPlatform;

/**
 * A block to turn unknown block references to something readable.
 *
 * This is a special kind of block to degrade gracefully when a user has a question
 * containing a newer block of an known type.
 * As requested in #959.
 */
class stack_cas_castext2_unknown extends stack_cas_castext2_block {

    public function compile($format, $options): ? MP_Node {
        // Unknown blocks do not get anywhere ever.
        return null;
    }

    public function is_flat() : bool {
        return true;
    }

    public function validate_extract_attributes(): array {
        return [];
    }

    /*
     * Unknown blocks are always invalid.
     */
    public function validate(&$errors = [], $options = []): bool {
        $errors[] = new $options['errclass'](StackPlatform::getTranslation("unknown_block", ['type' => $this->params[' type']]),
                $options['context'] . '/' . $this->position['start'] . '-' . $this->position['end']);
        return false;
    }
}