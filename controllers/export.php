<?php
/**
 * export.php
 *
 * Export research projects to several formats.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    ConverisProjects
 */

class ExportController extends AuthenticatedController
{

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;

        if (!$GLOBALS['perm']->have_perm('root') && !$this->plugin->checkPermission()) {
            throw new AccessDeniedException();
        }

        $this->flash = Trails_Flash::instance();
        $this->set_layout(null);
    }

    public function settings_action($type, $target)
    {
        if (!Request::isXhr()) {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        PageLayout::setTitle(dgettext('converisplugin', 'Forschungsbericht erstellen'));

        $this->target = $target;
        $this->type = $type;

        $this->templates = Config::get()->CONVERIS_REPORT_TEMPLATES[$type];

        if ($type === 'institute') {
            $currentMonth = date('m');
            if ($currentMonth < 10) {
                $this->startDate = '01.10.' . (date('Y') - 1);
                $this->endDate = '30.09.' . date('Y');
            } else {
                $this->startDate = '01.10.' . date('Y');
                $this->endDate = '30.09.' . (date('Y') + 1);
            }
        } else if ($type === 'user'){
            $this->startDate = '01.04.' . (date('Y') - 3);
            $this->endDate = '31.03.' . date('Y');
        }
    }

    /**
     * Create an export by using the specified template.
     */
    public function create_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $templates = Config::get()->CONVERIS_REPORT_TEMPLATES;
        $entry = $templates[Request::option('type')][Request::option('template')];

        $this->relocate($entry['controller'], $entry['action'],
            Request::get('start_date'), Request::get('end_date'),
            Request::get('target'));
    }

}