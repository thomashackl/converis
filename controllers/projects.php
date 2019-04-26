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
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        SimpleORMap::expireTableScheme();

        if ($GLOBALS['perm']->have_perm('root') || ConverisAdmin::findByUsername($GLOBALS['user']->username)) {

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
                $js = $this->plugin->getPluginURL().'/assets/javascripts/converisplugin.js';
            } else {
                $css = $this->plugin->getPluginURL().'/assets/stylesheets/converisplugin.min.css';
                $js = $this->plugin->getPluginURL().'/assets/javascripts/converisplugin.min.js';
            }
            PageLayout::addStylesheet($css);
            PageLayout::addScript($js);

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
            dgettext('converisplugin', 'Kontext auswählen'));

        $this->institutes = Institute::getInstitutes();

        $this->usersearch = QuickSearch::get('user', new StandardSearch('username'))
            ->withButton();
    }

    /**
     * Loads research projects for a given institute or user from Converis.
     */
    public function get_action()
    {
        if (Request::option('type') === 'institute') {
            $context = Request::option('institute_id');
            $name = Institute::find($context)->name;
            $projects = SimpleCollection::createFromArray(
                ConverisProject::findByOrganisationName(Institute::find(Request::option('institute_id'))->name)
            )->pluck('project_id');
        } else {
            $context = Request::username('user');
            $name = User::findByUsername($context)->getFullname();
            $projects = SimpleCollection::createFromArray(
                ConverisProject::findByUsername(Request::username('user'))
            )->pluck('project_id');
        }

        if (count($projects) > 0) {
            $this->flash['type'] = Request::option('type');
            $this->flash['context'] = $context;
            $this->flash['projects'] = $projects;
            $this->relocate('projects/list');
        } else {
            PageLayout::postInfo(sprintf(
                dgettext('converisplugin', 'Es wurden keine Projekte für %s gefunden'),
                $name
            ));
            $this->flash->discard();
            $this->relocate('projects');
        }
    }

    public function list_action()
    {
        $this->type = $this->flash['type'];
        $this->context = $this->flash['context'];
        $this->name = $this->type === 'institute' ?
            Institute::find($this->flash['context'])->name :
            User::findByUsername($this->flash['context'])->getFullname();
        $this->projects = ConverisProject::findMany($this->flash['projects']);

        $this->flash->keep();

        PageLayout::setTitle(
            sprintf(dgettext('converisplugin', 'Liste der Forschungsprojekte für %s'), $this->name));

        $views = new ViewsWidget();
        $views->setTitle(dgettext('converisplugin', 'Projekte'));
        $views->addLink(
            dgettext('converisplugin', 'Auswahl'),
            $this->link_for('projects')
        );
        $views->addLink(
            dgettext('converisplugin', 'Projektliste'),
            $this->link_for('projects/list')
        )->setActive(true);
        $this->sidebar->addWidget($views);

        $actions = new ActionsWidget();
        $actions->addLink(dgettext('converisplugin', 'Forschungsbericht erstellen'),
            $this->link_for('export/settings', $this->type, $this->context),
            Icon::create('literature2'))->asDialog('size=auto');
        $this->sidebar->addWidget($actions);

    }

}
