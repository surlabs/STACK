<?php
// This file is part of Stateful
//
// Stateful is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stateful is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stateful.  If not, see <http://www.gnu.org/licenses/>.
namespace src\core\external\cas\castext2\blocks;

use src\core\external\cas\castext2\stack_cas_castext2_block;
use src\core\external\maximaparser\MP_Node;
use src\core\external\maximaparser\MP_String;

class stack_cas_castext2_special_raw extends stack_cas_castext2_block {

    public $content;

    public function __construct($params, $children=array(), $mathmode=false, $value='') {
        parent::__construct($params, $children, $mathmode);
        $this->content = $value;
    }

    public function compile($format, $options): ?MP_Node {
        return new MP_String($this->content);
    }

    public function is_flat(): bool {
        return true;
    }

    public function validate_extract_attributes(): array {
        return array();
    }
}