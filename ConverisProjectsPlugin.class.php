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

        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

        // Localization
        bindtextdomain('converisplugin', realpath(__DIR__.'/locale'));

        // Plugin only available for roots or converis admins.
        if (!$GLOBALS['perm']->have_perm('root') && !$this->checkPermission()) {
            $navigation = new Navigation($this->getDisplayName(),
                PluginEngine::getURL($this, [], 'projects'));

            $navigation->addSubNavigation('projects',
                new Navigation(dgettext('converisplugin', 'Forschungsprojekte'),
                    PluginEngine::getURL($this, [], 'projects')));

            if ($GLOBALS['perm']->have_perm('root')) {
                $navigation->addSubNavigation('templates',
                    new Navigation(dgettext('converisplugin', 'Berichtsvorlagen'),
                        PluginEngine::getURL($this, [], 'settings/templates')));
                $navigation->addSubNavigation('admins',
                    new Navigation(dgettext('converisplugin', 'Berechtigungen'),
                        PluginEngine::getURL($this, [], 'settings/admins')));
            }

            Navigation::addItem('/tools/converisprojects', $navigation);
        }
    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName() {
        return dgettext('converisplugin', 'Forschungsprojekte');
    }

    public function checkPermission()
    {
        return count(ConverisAdmin::findByUser_id($GLOBALS['user']->id)) > 0;
    }

    public function perform($unconsumed_path) {
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, [], null), '/'),
            'projects'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    public static function onEnable($pluginId) {
        parent::onEnable($pluginId);
        StudipAutoloader::addAutoloadPath(__DIR__);
        ConverisProjectsSyncCronjob::register()->schedulePeriodic(4, 0)->activate();
    }

    public static function onDisable($pluginId) {
        StudipAutoloader::addAutoloadPath(__DIR__);
        ConverisProjectsSyncCronjob::unregister();
        parent::onDisable($pluginId);
    }

}
