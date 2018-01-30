<?php
/**
 * ConverisProjectsPlugin.class.php
 *
 * Plugin for showing research projects from Converis attached to an institute.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class ConverisProjectsPlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        // Plugin only available for roots or role.
        if ($GLOBALS['perm']->have_perm('root')) {
            $navigation = new Navigation($this->getDisplayName(),
                PluginEngine::getURL($this, array(), 'projects'));
            $navigation->addSubNavigation('projects',
                new Navigation('Forschungsprojekte',
                    PluginEngine::getURL($this, array(), 'projects')));
            Navigation::addItem('/tools/converisprojects', $navigation);
        }
    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName() {
        return 'Forschungsprojekte';
    }

    public function perform($unconsumed_path) {
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'projects'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

}
