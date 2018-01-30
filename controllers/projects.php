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
        PageLayout::setTitle('Projekte');
        Navigation::activateItem('/tools/converisprojects/projects');

        $this->institutes = Institute::getInstitutes();
    }

    /**
     * Loads research projects for a given institute from Converis.
     */
    public function get_projects_action()
    {
        try {
            $db = new PDO('pgsql:dbname=converis-up;host=forschung.uni-passau.de;user=studip;password=V4#vf-zBfb4AbQ');

            $this->institute = Institute::find(Request::option('institute'));

            foreach (['pr_proj_pers', 'pr_proj_orgint', 'pr_proj_orgext', 'pr_proj_soof', 'pr_proj_appl',
                         'pr_proj_third_party', 'pr_card_orga', 'pr_card_orga_pers', 'pr_all'] as $table) {
                $db->exec("DROP TABLE IF EXISTS " . $table);
            }

            /*
             * Fill temporary tables.
             */

            // selecting internal people with roles and aggregating them for each project
            // left joins because not all fields are filled
            $db->exec("select temp2.id, string_agg(concat(card.last_name, ', ' , card.first_name, ' (', temp2.role, ')'), chr(10)) as person into pr_proj_pers
                from
	                (select temp.id, temp.iot_card, cgv.value_1 as role
	                from
		                (select proj.id, rel_cpi.iot_card, rel_cpi.role
                    from iot_project as proj left join rel_card_has_project_int as rel_cpi on proj.id = rel_cpi.iot_project) as temp
	                    left join choicegroupvalue as cgv on temp.role = cgv.id) as temp2
                left join iot_card as card on temp2.iot_card = card.id
                group by temp2.id");

            // selecting internal organisations with roles and aggregating them for each project
            // left joins because not all fields are filled
            $db->exec("select temp2.id, string_agg(concat(orga.name__1, ' (', temp2.role, ')'), chr(10)) as organisation_internal into pr_proj_orgint
                from
	                (select temp.id, temp.iot_organisation, cgv.value_1 as role
	                from
		                (select proj.id, rel_opi.iot_organisation, rel_opi.role
		                from iot_project as proj
		                left join rel_organisation_has_project_internal as rel_opi on proj.id = rel_opi.iot_project) as temp
                    left join choicegroupvalue as cgv on temp.role = cgv.id) as temp2
                left join iot_organisation as orga on temp2.iot_organisation = orga.id
                group by temp2.id");

            // selecting external organisations with roles and aggregating them for each project
            // left joins because not all fields are filled
            $db->exec("select temp2.id, string_agg(concat(orga.name__1, ' (', temp2.role, ')'), chr(10)) as organisation_external into pr_proj_orgext
                from
	                (select temp.id, temp.iot_organisation, cgv.value_1 as role
	                from
		                (select proj.id, rel_ope.iot_organisation, rel_ope.role
		                from iot_project as proj left join rel_orga_has_proj_ext as rel_ope on proj.id = rel_ope.iot_project) as temp
	                left join choicegroupvalue as cgv on temp.role = cgv.id) as temp2
                left join iot_organisation as orga on temp2.iot_organisation = orga.id
                group by temp2.id");

            // selecting source of funds and aggregating them for each project
            // left joins because not all fields are filled
            $db->exec("select temp.id, string_agg(soof.name, chr(10)) as source_of_funds into pr_proj_soof
                from
	                (select proj.id, rel_ps.iot_source_of_funds
	                from iot_project as proj left join rel_proj_has_soof as rel_ps on proj.id = rel_ps.iot_project) as temp
                left join iot_source_of_funds as soof on temp.iot_source_of_funds = soof.id
                group by temp.id");

            // selecting applications with funding and aggregating them for each project
            // left joins because not all fields are filled
            $db->exec("select temp.id, string_agg(concat(appl.name, ' (', appl.funding_amount, ')'), chr(10)) as application into pr_proj_appl
                from
	                (select proj.id, rel_ap.iot_application
	                from iot_project as proj left join rel_application_has_project as rel_ap on proj.id = rel_ap.iot_project) as temp
                left join iot_application as appl on temp.iot_application = appl.id
                group by temp.id");

            // selecting all details for each project and combining them with aggregated data
            // left join because not all fields are filled
            $db->exec("select temp.*,
                    pr_proj_pers.person as personen, 
                    pr_proj_orgint.organisation_internal as interne_organisationen, 
                    pr_proj_orgext.organisation_external as externe_organisationen, 
                    pr_proj_soof.source_of_funds as mittelgeber, 
                    pr_proj_appl.application as antrag
                into pr_proj_third_party
                from (select proj.id, 
  		                  'Drittmittel' as projektart, 
                          cgv1.value_1 as projektstatus,
                          proj.public_title__1 as ueberschrift,
                          proj.name as kurzbezeichnung, 
                          proj.long_name__1 as langbezeichnung,
                          proj.public_description__1 as allgemeine_beschreibung,
                          cgv2.value_1 as projekttyp, 
                          to_char(proj.start_date, 'DD.MM.YYYY') as projektbeginn,
                          to_char(proj.end_date, 'DD.MM.YYYY') as projektende,
                          proj.total_project_expenses as gesamtkosten,
                          proj.total_project_expenses_cu as gesamtkosten_währung,
                          proj.expenses_university as kosten_uni_passau,
                          proj.expenses_university_cur as kosten_uni_passau_währung,
                          proj.funding_quota as förderquote,
                          proj.project_flat_charge as projektpauschale,
                          proj.funding_central_resources as eigenanteil_forschungspool,
                          proj.funding_central_cur as eigenanteil_forschungspool_währung,
                          proj.funding_chair as eigenanteil_mittel_projektleiter,
                          proj.funding_chair_cur as eigenanteil_mittel_pl_währung,
                          proj.funding_third_party as kofinanzierung_aus_mitteln_dritter,
                          proj.funding_third_party_cur as kofinanzierung_aus_mitteln_dritter_währung,
                          proj.contract_sum_netto as summe_netto,
                          proj.contract_sum_netto_cur as summe_netto_währung,
                          proj.contract_sum_brutto as summe_brutto,
                          proj.contract_sum_brutto_cur as summe_brutto_währung,
                          proj.contract_tax_rate as steuersatz_in_prozent,
                          proj.contract_tax as umsatzsteuer_in_prozent,
                          proj.own_contribution as eigenanteil_uni_passau_gesamt,
                          proj.own_contribution_cur as eigenanteil_uni_passau_gesamt_währung,
                          proj.funding_amount as fördersumme,
                          proj.funding_amount_cur as fördersumme_währung,
                          to_char(proj.date_of_grant_agreement, 'DD.MM.YYYY') as datum_des_bescheids
              	      from iot_project as proj left join choicegroupvalue as cgv2 on proj.project_type = cgv2.id, choicegroupvalue as cgv1
	                  where proj.status_process = cgv1.sequence
	                      and cgv1.choicegroup = 1) as temp, pr_proj_pers, pr_proj_orgint, pr_proj_orgext, pr_proj_soof, pr_proj_appl
                where temp.id = pr_proj_pers.id
                    and temp.id = pr_proj_orgint.id
                    and temp.id = pr_proj_orgext.id
                    and temp.id = pr_proj_soof.id
                    and temp.id = pr_proj_appl.id
                order by temp.id");

            // selecting all cards with organisations
            // left joins because not all fields are filled
            $db->exec("select temp2.id as card_id, temp2.name__1 as organisation, cgv.value_1 as orga_ext_int into pr_card_orga
                from
	                (select temp.id, orga.name__1, orga.external_or_internal
	                from
		                (select card.id, rel_co.iot_organisation
		                from iot_card as card
		                    left join rel_card_has_orga as rel_co on card.id = rel_co.iot_card) as temp
                    left join iot_organisation as orga on temp.iot_organisation = orga.id) as temp2
                left join choicegroupvalue as cgv on temp2.external_or_internal = cgv.id");

            // selecting all cards with information on people
            // left joins because not all fields are filled
            $db->exec("select temp2.last_name as nachname, temp2.first_name as vorname, cgv.value_1 as akademischer_titel, temp2.card_id, temp2.organisation, temp2.orga_ext_int into pr_card_orga_pers
                from
	                (select pers.last_name, pers.first_name, pers.academic_title, temp.*
                	from
		                (select rel_pc.iot_person, prco.*
		                from pr_card_orga as prco
		                    left join rel_pers_has_card as rel_pc on prco.card_id = rel_pc.iot_card) as temp
                    left join iot_person as pers on temp.iot_person = pers.id) as temp2
                left join choicegroupvalue as cgv on temp2.academic_title = cgv.id");

            // combining people with projects
            // left joins because not all fields are filled
            $db->exec("select prcop.nachname, prcop.vorname, prcop.akademischer_titel, prcop.organisation, prcop.orga_ext_int, temp2.* into pr_all
                from
	                (select cgv.value_1 as rolle_wissenschaftler, temp.*
	                from
		                (select rel_cpi.iot_card, rel_cpi.role, prptp.*
		                    from pr_proj_third_party as prptp
		                        left join rel_card_has_project_int as rel_cpi on prptp.id = rel_cpi.iot_project) as temp
                        left join choicegroupvalue as cgv on temp.role = cgv.id) as temp2
                    left join pr_card_orga_pers as prcop on temp2.iot_card = prcop.card_id
                order by prcop.nachname, prcop.vorname");

            // displaying end result
            $stmt = $db->prepare("select * from pr_all
                where organisation = ?");
            $stmt->execute([$this->institute->name]);
            $this->data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process data -> create Excel file.
            if (count($this->data) > 0) {

                require_once(__DIR__ . '/../vendor/PhpSpreadsheet/vendor/autoload.php');

                $spreadsheet = new Spreadsheet();
                $spreadsheet->getProperties()
                    ->setCreator($GLOBALS['user']->getFullname())
                    ->setLastModifiedBy($GLOBALS['user']->getFullname())
                    ->setTitle('Projekte ' . $this->institute->name)
                    ->setSubject('Projekte ' . $this->institute->name);
                $sheet = $spreadsheet->getActiveSheet();

                // Write header lines.
                $sheet->fromArray(array_keys($this->data[0]), NULL, 'A1');

                // Write data to cells.
                $sheet->fromArray($this->data, NULL, 'A2');

                $writer = new Xlsx($spreadsheet);
                $filename = $GLOBALS['TMP_PATH'] . '/projects-' . mktime() . '.xlsx';

                $writer->save($filename);

                $this->relocate(
                    FileManager::getDownloadURLForTemporaryFile(
                        $filename, 'Projekte ' . $this->institute->name . '.xlsx'));

            // No data found, redirect to main page and show corresponding message.
            } else {
                PageLayout::postInfo(sprintf(
                    'Es wurden keine Forschungsprojekte an der gewählten Einrichtung "%s" gefunden.',
                    $this->institute->name));
                $this->relocate('projects');
            }

        } catch (Exception $e) {
            PageLayout::postError($e->getMessage());
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
