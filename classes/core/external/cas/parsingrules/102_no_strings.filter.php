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

namespace classes\core\external\cas\castext2\parsingrules;

use classes\core\external\cas\stack_cas_security;
use classes\core\external\maximaparser\MP_Node;
use classes\core\external\maximaparser\MP_String;
use classes\platform\StackPlatform;

/**
 * AST filter that prevents the use of any strings.
 */
class stack_ast_filter_102_no_strings implements stack_cas_astfilter {
    public function filter(MP_Node $ast, array &$errors, array &$answernotes, stack_cas_security $identifierrules): MP_Node {
        $checkfloats = function($node) use (&$answernotes, &$errors) {
            if ($node instanceof MP_String) {
                $node->position['invalid'] = true;
                if (array_search('Illegal_strings', $answernotes) === false) {
                    $answernotes[] = 'Illegal_strings';
                    $errors[] = StackPlatform::getTranslation('Illegal_strings', null);
                }
            }
            return true;
        };

        $ast->callbackRecurse($checkfloats);
        return $ast;
    }
}