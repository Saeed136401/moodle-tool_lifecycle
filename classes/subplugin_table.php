<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Table listing active processes
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../lib.php');

class subplugin_table extends \table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_sql("id, name, type, enabled, sortindex", '{tool_cleanupcourses_plugin}', "TRUE");
        $this->define_baseurl($PAGE->url);
        $this->pageable(false);
        $this->init();
    }

    public function init() {
        $this->define_columns(['name', 'type', 'enabled', 'sortindex']);
        $this->define_headers([
            get_string('subplugin_name', 'tool_cleanupcourses'),
            get_string('subplugin_type', 'tool_cleanupcourses'),
            get_string('subplugin_enabled', 'tool_cleanupcourses'),
            get_string('subplugin_sortindex', 'tool_cleanupcourses')
            ]);
        $this->sortable(false, 'sortindex');
        $this->setup();
    }

    /**
     * Render name column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_name($row) {

        $name = $row->name;
        $type = $row->type;

        return get_string('pluginname', $type . '_' . $name);
    }

    /**
     * Render type column.
     * @param $row
     * @return string type of the subplugin
     */
    public function col_type($row) {

        $type = $row->type;

        return get_string($type, 'tool_cleanupcourses');
    }

    /**
     * Render enabled column.
     * @param $row
     * @return string action button for enabling/disabling of the subplugin
     */
    public function col_enabled($row) {

        if ($row->enabled == 1) {
            $alt = 'disable';
            $icon = 't/hide';
            $action = ACTION_DISABLE_SUBPLUGIN;
        } else {
            $alt = 'enable';
            $icon = 't/show';
            $action = ACTION_ENABLE_SUBPLUGIN;
        }

        return  $this->format_icon_link($action, $row->id, $icon, get_string($alt));
    }

    /**
     * Render sortindex column.
     * @param $row
     * @return string action buttons for changing sortorder of the subplugin
     */
    public function col_sortindex($row) {
        global $OUTPUT;
        $output = '';
        if ($row->sortindex !== null) {
            if ($row->sortindex > 1) {
                $alt = 'up';
                $icon = 't/up';
                $action = ACTION_UP_SUBPLUGIN;
                $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
            } else {
                $output .= $OUTPUT->spacer();
            }
            $manager = new subplugin_manager();
            if ($row->sortindex < $manager->count_enabled_trigger()) {
                $alt = 'down';
                $icon = 't/down';
                $action = ACTION_DOWN_SUBPLUGIN;
                $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
            } else {
                $output .= $OUTPUT->spacer();
            }
        }

        return  $output;
    }

    /**
     * Util function for writing an action icon link
     *
     * @param string $action URL parameter to include in the link
     * @param string $subpluginid URL parameter to include in the link
     * @param string $icon The key to the icon to use (e.g. 't/up')
     * @param string $alt The string description of the link used as the title and alt text
     * @return string The icon/link
     */
    private function format_icon_link($action, $subpluginid, $icon, $alt) {
        global $PAGE, $OUTPUT;

        return $OUTPUT->action_icon(new \moodle_url($PAGE->url,
                array('action' => $action, 'subplugin' => $subpluginid, 'sesskey' => sesskey())),
                new \pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                null , array('title' => $alt)) . ' ';
    }

}