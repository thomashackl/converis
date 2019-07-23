<?php
/**
 * sorting.php
 *
 * Custom sorting for project status values.
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

class SortingController extends AuthenticatedController
{

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        if ($GLOBALS['perm']->have_perm('root')) {
            $this->plugin = $this->dispatcher->plugin;
            $this->flash = Trails_Flash::instance();

            if (Request::isXhr()) {
                $this->set_layout(null);
            } else {
                $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
            }

            $this->sidebar = Sidebar::get();
            $this->sidebar->setImage('sidebar/admin-sidebar.png');

            if (Studip\ENV == 'development') {
                $style = $this->plugin->getPluginURL().'/assets/stylesheets/converisplugin.css';
                $js = $this->plugin->getPluginURL().'/assets/javascripts/sort-status.js';
            } else {
                $css = $this->plugin->getPluginURL().'/assets/stylesheets/converisplugin.min.css';
                $js = $this->plugin->getPluginURL().'/assets/javascripts/sort-status.min.js';
            }
            PageLayout::addStylesheet($css);
            PageLayout::addScript($js);

        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * List available project status values and allow sorting them.
     */
    public function index_action()
    {
        Navigation::activateItem('/tools/converisprojects/sorting');
        PageLayout::setTitle(dgettext('converisplugin', 'Projektstatussortierung'));

        $this->status = ConverisProjectStatus::findBySQL("1 ORDER BY `position`, `name_1`");
    }

    /**
     * Updates sorting order of status values.
     */
    public function store_action()
    {
        $result = [];

        $status = ConverisProjectStatus::find(Request::int('status'));
        $oldPos = Request::int('oldpos');
        $newPos = Request::int('newpos');

        if ($status != null) {

            $status->position = Request::int('newpos');
            if ($status->store() !== false) {

                if ($oldPos > $newPos) {

                    $other = ConverisProjectStatus::findBySQL(
                        "`position` >= :newPos AND `position` < :oldPos AND `status_id` != :id",
                        [
                            'newPos' => $newPos,
                            'oldPos' => $oldPos,
                            'id' => Request::int('status')
                        ]
                    );

                    foreach ($other as $one) {
                        $one->position++;
                        $one->store();
                    }

                    $result = DBManager::get()->fetchAll(
                        "SELECT `status_id`, `position` FROM `converis_project_status` ORDER BY `position`, `name_1`");

                } else if ($oldPos < $newPos) {

                    $other = ConverisProjectStatus::findBySQL(
                        "`position` > :oldPos AND `position` <= :newPos AND `status_id` != :id",
                        [
                            'newPos' => $newPos,
                            'oldPos' => $oldPos,
                            'id' => Request::int('status')
                        ]
                    );

                    foreach ($other as $one) {
                        $one->position--;
                        $one->store();
                    }

                    $result = DBManager::get()->fetchAll(
                        "SELECT `status_id`, `position` FROM `converis_project_status` ORDER BY `position`, `name_1`");

                }

            } else {
                $result = null;
            }

        } else {
            $result = null;
        }

        $this->render_json($result);
    }

}