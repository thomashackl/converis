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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            new Navigation(dgettext('converisplugin', 'Projektliste'),
                PluginEngine::getURL($this, array('institute' => Request::option('institute')), 'projects/list')));
        Navigation::activateItem('/tools/converisprojects/list');

        $this->institute = Institute::find(Request::option('institute'));

        $this->flash['institute'] = $this->institute->id;

        PageLayout::setTitle(sprintf(dgettext('converisplugin', 'Liste der Forschungsprojekte für %s'), $this->institute->name));

        $converisOrganisation = ConverisOrganisation::findOneByName_1($this->institute->name);

        $projectRelations = SimpleCollection::createFromArray(
            ConverisProjectOrganisationRelation::findByOrganisation_id($converisOrganisation->converis_id)
        );
        $this->projects = ConverisProject::findManyByConveris_id($projectRelations->pluck('project_id'));

        $actions = new ActionsWidget();
        $actions->addLink(dgettext('converisplugin', 'Excel-Export'),
            $this->url_for('projects/export_excel', $this->institute->id),
            Icon::create('file-excel'));
        $this->sidebar->addWidget($actions);
    }

    public function export_excel_action($institute_id)
    {
        $institute = Institute::find($institute_id);

        $free = ConverisProject::findByInstitute_id($institute->id);

        $third_party = ConverisProjectAggThirdParty::findByInstitute_id($institute->id);

        $projects = array_merge($free, $third_party);
        usort($projects, function($a, $b) {
            return strnatcasecmp($a['kurzbezeichnung'], $b['kurzbezeichnung']);
        });

        // Process data -> create Excel file.
        if (count($projects) > 0) {

            require_once(__DIR__ . '/../vendor/autoload.php');

            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator($GLOBALS['user']->getFullname())
                ->setLastModifiedBy($GLOBALS['user']->getFullname())
                ->setTitle('Projekte ' . $institute->name)
                ->setSubject('Projekte ' . $institute->name);
            $sheet = $spreadsheet->getActiveSheet();

            // Write header lines.
            $sheet->fromArray(
                array_filter(
                    array_keys(
                        $projects[0]->getTableMetadata()['fields']
                    ),
                    function ($one) {
                        return !in_array($one, ['id', 'mkdate', 'chdate']);
                    }
                ),
                '', 'A1');

            // Write data to cells.
            $sheet->fromArray(array_map(function ($one) {
                return array_filter(
                    $one->toArray(),
                    function ($col) {
                        return !in_array($col, ['id', 'mkdate', 'chdate']);
                    },
                    ARRAY_FILTER_USE_KEY
                );
            }, $projects), '', 'A2');

            $writer = new Xlsx($spreadsheet);
            $filename = $GLOBALS['TMP_PATH'] . '/projects-' . mktime() . '.xlsx';

            $writer->save($filename);

            $this->relocate(
                FileManager::getDownloadURLForTemporaryFile(
                    $filename, 'Projekte ' . $institute->name . '.xlsx'));

        // No data found, redirect to main page and show corresponding message.
        } else {
            PageLayout::postInfo(sprintf(
                'Es wurden keine Forschungsprojekte an der gewählten Einrichtung "%s" gefunden.',
                $institute->name));
            $this->relocate('projects');
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
