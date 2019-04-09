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
 * @category    Garuda
 */

require_once(__DIR__ . '/../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

class ExportController extends AuthenticatedController
{

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        if ($GLOBALS['perm']->have_perm('root') || in_array($GLOBALS['user']->username, ['hackl10', 'kuchle03', 'zukows02'])) {
            $this->plugin = $this->dispatcher->plugin;
            $this->flash = Trails_Flash::instance();
            $this->set_layout(null);
        } else {
            throw new AccessDeniedException();
        }
    }

    public function settings_action($studipInstituteId, $converisOrganisationId)
    {
        if (!Request::isXhr()) {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        PageLayout::setTitle(dgettext('converisplugin', 'Forschungsbericht erstellen'));

        $this->organisationId = $converisOrganisationId;
        $this->instituteId = $studipInstituteId;

        $this->templates = Config::get()->CONVERIS_REPORT_TEMPLATES;

        $currentMonth = date('m');
        if ($currentMonth < 10) {
            $this->startDate = '01.10.' . (date('Y') - 1);
            $this->endDate = '30.09.' . date('Y');
        } else {
            $this->startDate = '01.10.' . date('Y');
            $this->endDate = '30.09.' . (date('Y') + 1);
        }
    }

    /**
     * Create an export by using the specified template.
     */
    public function create_action()
    {
        $response = $this->relay('export/' . Request::option('template'),
            Request::get('start_date'), Request::get('end_date'),
            Request::option('institute_id'), Request::int('organisation_id'));
        $this->body = $response->body;
        $this->render_text($this->body);
    }

    /**
     * Generates a PDF export in the format preferred by FIM faculty.
     *
     * @param string $start start of export time frame
     * @param string $end end of export time frame
     * @param string $studipInstituteId ID of chosen Stud.IP institute
     * @param int $converisOrganisationId ID of chosen Converis organisation
     */
    public function pdf_fim_action($start, $end, $studipInstituteId, $converisOrganisationId)
    {

        PageLayout::postInfo('Stud.IP institute: ' . $studipInstituteId . ', Converis organisation: ' . $converisOrganisationId);

        $this->start = $start;
        $this->end = $end;

        $startDate = strtotime($this->start);
        $endDate = strtotime($this->end);

        $this->institute = Institute::find($studipInstituteId);

        $projectRelations = SimpleCollection::createFromArray(
            ConverisProjectOrganisationRelation::findByOrganisation_id($converisOrganisationId)
        );
        $projects = ConverisProject::findBySQL(
            "`converis_id` IN (:ids) AND `type` = 'third_party' ORDER BY `long_name_1`, `long_name_2`",
            ['ids' => $projectRelations->pluck('project_id')]
        );

        /*
         * Filter found projects by specified time span. This cannot be done
         * directly in SQL as it depends on project status and type.
         */
        $this->projects = [];
        foreach ($projects as $p) {

            $pStart = strtotime($p->start_date);
            $pEnd = strtotime($p->end_date);

            $deadline = $p->application != null ? strtotime($p->application->deadline) : 0;
            $granted = $p->third_party_data != null ? strtotime($p->third_party_data->date_of_grant_agreement) : 0;

            switch ($p->project_status->name_1) {
                case 'Bei Mittelgeber eingereicht':
                    if ($deadline >= $startDate && $deadline <= $endDate) {
                        $this->projects[] = $p;
                    }
                    break;
                case 'Bewilligt':
                case 'Beendet':
                    if ($p->third_party_data->type != null) {
                        switch ($p->third_party_data->type->name_1) {
                            case 'Auftragsforschung':
                            case 'Kooperation':
                            case 'Lizenz':
                                if ($pEnd >= $startDate || $pEnd <= 0) {
                                    $this->projects[] = $p;
                                }
                                break;
                            case 'EU':
                            case 'International':
                            case 'National':
                                if ($pEnd >= $startDate || ($pEnd <= 0 && $granted >= $startDate && $granted <= $endDate)) {
                                    $this->projects[] = $p;
                                }
                                break;
                        }
                    }
            }
        }

        $mpdf = new Mpdf(['orientation' => 'L']);
        $mpdf->setFooter('Daten vom ' . date('d.m.Y H:i') . ', Seite {PAGENO}/{nb}');
        $mpdf->WriteHTML($this->render_template_as_string('export/pdf_fim'));
        $mpdf->Output('Drittmittelprojekte-' . $this->institute->name . '-' . $this->start . '-' . $this->end . '.pdf', 'D');
    }

    public function excel_action($institute_id)
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

}