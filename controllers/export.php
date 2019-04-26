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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

class ExportController extends AuthenticatedController
{

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        if (!$GLOBALS['perm']->have_perm('root') &&
                !in_array($GLOBALS['user']->username, ['hackl10', 'kuchle03', 'zukows02'])) {
            throw new AccessDeniedException();
        }

        $this->plugin = $this->dispatcher->plugin;
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

        $this->templates = Config::get()->CONVERIS_REPORT_TEMPLATES[$type];

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
            Request::get('target'));

        $this->render_text($response->body);
    }

    /**
     * Generates a PDF export in the format preferred by FIM faculty.
     *
     * @param string $start start of export time frame
     * @param string $end end of export time frame
     * @param string $studipInstituteId ID of chosen Stud.IP institute
     */
    public function pdf_fim_action($start, $end, $studipInstituteId)
    {
        $this->start = $start;
        $this->end = $end;

        $startDate = strtotime($this->start);
        $endDate = strtotime($this->end);

        $this->institute = Institute::find($studipInstituteId);

        $projects = ConverisProject::findByOrganisationName($this->institute->name);

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

    /**
     * Generates a Excel format export of the performance overview.
     *
     * @param string $start start of export time frame
     * @param string $end end of export time frame
     * @param string $username username of the chosen Stud.IP user
     */
    public function xls_leistungsbezuege_action($start, $end, $username)
    {
        $this->start = $start;
        $this->end = $end;

        $startDate = strtotime($this->start);
        $endDate = strtotime($this->end);

        $person = ConverisPerson::findOneByUsername($username);

        $fullname = implode(' ', [$person->academic_title, $person->first_name, $person->last_name]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($GLOBALS['user']->getFullname())
            ->setLastModifiedBy($GLOBALS['user']->getFullname())
            ->setTitle('Leistungsbezüge ' . $fullname)
            ->setSubject('Leistungsbezüge ' . $fullname);
        $spreadsheet->removeSheetByIndex(0);

        foreach ($person->cards as $card) {

            // Create sheet for third party projects.
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Drittmittelprojekte');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('D9D9D900');
            $sheet->setCellValue('A1', $fullname . '(' . $card->organisation->name_1 . ')');

            $data = [];
            $projects = $card->related_projects;

            if ($projects !== null && count($projects) > 0) {
                foreach ($projects as $p) {
                    $data[] = 0;
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'leistungsbezuege-' . strtolower($person->last_name) . '.xlsx';

        $this->set_content_type('vnd.openxmlformats-officedocument. spreadsheetml.sheet');
        $this->response->add_header('Content-Disposition', 'attachment;' . encode_header_parameter('filename', $filename));
        $this->response->add_header('Cache-Control', 'cache, must-revalidate');
        $this->response->add_header('Pragma', 'public');

        $writer->save('php://output');

        $this->render_nothing();
    }

}