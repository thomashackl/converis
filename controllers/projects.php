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
        if ($GLOBALS['perm']->have_perm('root') || ConverisAdmin::findByUsername($GLOBALS['user']->username)) {
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

            Navigation::activateItem('/tools/converisprojects/projects');

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

        $this->institutes = Institute::getInstitutes();
    }

    /**
     * Loads research projects for a given institute from Converis.
     */
    public function list_action($institute_id = '')
    {
        $this->institute = Institute::find($institute_id != '' ? $institute_id : Request::option('institute'));

        PageLayout::setTitle(
            sprintf(dgettext('converisplugin', 'Liste der Forschungsprojekte für %s'),
                $this->institute->name));

        $converisOrganisation = ConverisOrganisation::findOneByName_1($this->institute->name);

        $projectRelations = SimpleCollection::createFromArray(
            ConverisProjectOrganisationRelation::findByOrganisation_id($converisOrganisation->converis_id)
        );
        $this->projects = ConverisProject::findManyByConveris_id($projectRelations->pluck('project_id'));

        $views = new ViewsWidget();
        $views->addLink(
            dgettext('converisplugin', 'Einrichtung wählen'),
            $this->url_for('projects')
        );
        $views->addLink(
            dgettext('converisplugin', 'Projektliste'),
            $this->url_for('projects/list', Request::option('institute'))
        )->setActive(true);
        $this->sidebar->addWidget($views);

        if (count($this->projects) > 0) {
            $actions = new ActionsWidget();
            $actions->addLink(dgettext('converisplugin', 'PDF-Export'),
                $this->url_for('export/settings', $this->institute->id, $converisOrganisation->converis_id),
                Icon::create('file-pdf'))->asDialog('size=auto');
            $this->sidebar->addWidget($actions);
        }
    }

}
