<?php

/**
 * performancerecord.php
 *
 * Performance record ("Leistungsbezüge" exports for research projects).
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

require_once(__DIR__ . '/../vendor/autoload.php');

use Mpdf\Mpdf;

class FIM extends AuthenticatedController
class FIM extends AuthenticatedController
{

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;

        if (!$GLOBALS['perm']->have_perm('root') || ConverisAdmin::findOneByUser_id($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->flash = Trails_Flash::instance();
        $this->set_layout(null);
    }

    /**
     * Generates a PDF export in the format preferred by FIM faculty.
     *
     * @param string $start start of export time frame
     * @param string $end end of export time frame
     * @param string $studipInstituteId ID of chosen Stud.IP institute
     */
    public function pdf_action($start, $end, $studipInstituteId)
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

            if ($p->runsInTimeframe(new DateTime($start), new DateTime($end))) {
                $this->projects[] = $p;
            }

        }

        $mpdf = new Mpdf(['orientation' => 'L']);
        $mpdf->setFooter('Daten vom ' . date('d.m.Y H:i') . ', Seite {PAGENO}/{nb}');
        $mpdf->WriteHTML($this->render_template_as_string('export_templates/fim_pdf'));
        $mpdf->Output('Drittmittelprojekte-' . $this->institute->name . '-' . $this->start . '-' . $this->end . '.pdf', 'D');
    }

}