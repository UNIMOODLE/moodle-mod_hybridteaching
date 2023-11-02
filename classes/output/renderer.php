<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace mod_hybridteaching\output;

use plugin_renderer_base;

class renderer extends plugin_renderer_base {

    public function zone_errors($message) {
        return $this->render_from_template('mod_hybridteaching/view_page_zone_errors', $message);
    }
    public function zone_access($resultaccess) {
        return $this->render_from_template('mod_hybridteaching/view_page_zone_access', $resultaccess);
    }
    public function zone_records() {
        return $this->render_from_template('mod_hybridteaching/view_page_zone_records', null);
    }
}
