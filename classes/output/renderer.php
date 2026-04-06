<?php

namespace local_notification_manager\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base {
    /**
     * Render create meeting page content.
     *
     * @param main $page
     * @return string
     */
    public function render_main(main $page): string {
        $data = $page->export_for_template($this);
        return $this->render_from_template('local_notification_manager/main', $data);
    }
}
