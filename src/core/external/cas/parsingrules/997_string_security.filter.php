<?php
// This file is part of Stack - https://stack.maths.ed.ac.uk
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

namespace src\core\external\cas\castext2\parsingrules;

use src\core\external\cas\stack_cas_security;
use src\core\external\maximaparser\MP_Node;
use src\core\external\maximaparser\MP_String;
use src\platform\StackPlatform;

/**
 * AST filter that rewrites any strings present in the input by disabling
 * certain chars that would allow script injection.
 *
 * Will not warn about changes, will just break stuff to keep it safe.
 *
 * Not to be used with author sourced content.
 */
class stack_ast_filter_997_string_security implements stack_cas_astfilter {

    public function filter(MP_Node $ast, array &$errors, array &$answernotes, stack_cas_security $identifierrules): MP_Node {

        $process = function($node) {
            if ($node instanceof MP_String) {
                $node->value = StackPlatform::getTranslation($node->value, null);
            }
            return true;
        };
        $ast->callbackRecurse($process);

        return $ast;
    }
}