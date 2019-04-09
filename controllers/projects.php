<?php
/**
 * projects.php
 *
 * Show projects belonging to an institute.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Garuda
 */

class ProjectsController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        if ($GLOBALS['perm']->have_perm('root') || in_array($GLOBALS['user']->username, ['hackl10', 'kuchle03', 'zukows02'])) {
            $this->plugin = $this->dispatcher->plugin;
            $this->flash = Trails_Flash::instance();

            if (Request::isXhr()) {
                $this->set_layout(null);
            } else {
                $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
            }

            // Navigation handling.
            Navigation::activateItem('/tools/converisprojects');

            $this->sidebar = Sidebar::get();
            $this->sidebar->setImage('sidebar/doctoral_cap-sidebar.png');

            if (Studip\ENV == 'development') {
                $style = $this->plugin->getPluginURL().'/assets/stylesheets/converisplugin.css';
            } else {
                $style = $this->plugin->getPluginURL().'/assets/stylesheets/converisplugin.min.css';
            }
            PageLayout::addStylesheet($style);

        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Shows current configuration settings for message sending,
     * like cronjob schedule or cleanup interval.
     */
    public function index_action()
    {
        PageLayout::setTitle($this->plugin->getDisplayName() . ' - ' .
            dgettext('converisplugin', 'Einrichtung wählen'));
        Navigation::activateItem('/tools/converisprojects/projects');

        $this->institutes = Institute::getInstitutes();
    }

    /**
     * Loads research projects for a given institute from Converis.
     */
    public function list_action()
    {
        $navigation = Navigation::getItem('/tools/converisprojects');
        $navigation->addSubNavigation('list',
            new Navigation(dgettext('converisplugin', 'Forschungsprojekte'),
                PluginEngine::getURL($this, array('institute' => Request::option('institute')), 'projects/list')));
        Navigation::activateItem('/tools/converisprojects/list');

        $this->institute = Institute::find(Request::option('institute'));

        $this->flash['institute'] = $this->institute->id;

        PageLayout::setTitle(
            sprintf(dgettext('converisplugin', 'Liste der Forschungsprojekte für %s'),
                $this->institute->name));

        $converisOrganisation = ConverisOrganisation::findOneByName_1($this->institute->name);

        $projectRelations = SimpleCollection::createFromArray(
            ConverisProjectOrganisationRelation::findByOrganisation_id($converisOrganisation->converis_id)
        );
        $this->projects = ConverisProject::findManyByConveris_id($projectRelations->pluck('project_id'));

        if (count($this->projects) > 0) {
            $actions = new ActionsWidget();
            $actions->addLink(dgettext('converisplugin', 'PDF-Export'),
                $this->url_for('export/settings', $this->institute->id, $converisOrganisation->converis_id),
                Icon::create('file-pdf'))->asDialog('size=auto');
            $this->sidebar->addWidget($actions);
        }
    }

    // customized #url_for for plugins
    public function url_for($to = '') {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map("urlencode", $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->plugin, $params, join("/", $args));
    }
}
