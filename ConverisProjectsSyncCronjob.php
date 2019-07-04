<?php
/**
 * ConverisProjectsSyncCronjob.class.php
 *
 * Cronjob for syncing research projects from Converis DB.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Converis
 */

/**
 * Cron job for fetching database contents from Converis.
 */
class ConverisProjectsSyncCronjob extends CronJob {

    private $converis;

    public static function getName() {
        return dgettext('converisplugin', 'Synchronisiere Forschungsprojekte aus Converis');
    }

    public static function getDescription() {
        return dgettext('garudaplugin', 'Holt alle Forschungsprojekte inkl. Daten aus Converis');
    }

    /**
     * Empty local database and fetch all content from Converis tables.
     */
    public function execute($last_result, $parameters = [])
    {
        $host = Config::get()->CONVERIS_HOSTNAME;
        $database = Config::get()->CONVERIS_DATABASE;
        $username = Config::get()->CONVERIS_DB_USER;
        $password = Config::get()->CONVERIS_DB_PASSWORD;

        /**
         * Execute only if at least a valid hostname is given.
         */
        if ($host !== 'converis.host.name') {

            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

            $this->converis = new PDO(sprintf(
                'pgsql:dbname=%s;host=%s;user=%s;password=%s',
                $database, $host, $username, $password
            ));

            try {
                // Import project status names.
                $this->importConverisData(
                    "SELECT DISTINCT
                            c.id AS status_id,
                            c.value_1 AS name_1,
                            c.value_2 AS name_2
                        FROM iot_project p
                            JOIN iothasstatusprocess h ON (h.status_sequence = p.status_process)
                            JOIN choicegroupvalue c ON (c.id = h.status_process)
                        WHERE h.infoobjecttype = 36
                        UNION
                        SELECT DISTINCT
                            c.id AS status_id,
                            c.value_1 AS name_1,
                            c.value_2 AS name_2
                        FROM iot_project_general p
                            JOIN iothasstatusprocess h ON (h.status_sequence = p.status_process)
                            JOIN choicegroupvalue c ON (c.id = h.status_process)
                        WHERE h.infoobjecttype = 172
                        ORDER BY name_1",
                    'ConverisProjectStatus',
                    'status_id',
                    false
                );

                // Get applications.
                $this->importConverisData(
                    "SELECT DISTINCT
                        a.id AS application_id,
                        a.call_number,
                        a.start_date,
                        a.end_date,
                        a.duration_in_months,
                        a.deadline,
                        a.confirmation_of_receipt_date,
                        a.participation_role AS participation_role_id,
                        a.total_project_expenses,
                        c1.value AS total_project_expenses_cur,
                        a.expenses_university_of_passau AS expenses_upa,
                        c2.value AS expenses_upa_cur,
                        a.funding_amount,
                        c3.value AS funding_amount_cur,
                        a.funding_quota,
                        a.project_flat_charge,
                        a.own_contribution,
                        c4.value AS own_contribution_cur,
                        a.research_pool,
                        c5.value AS research_pool_cur,
                        a.funding_project_leader,
                        c6.value  AS funding_project_leader_cur,
                        a.funding_third_party,
                        c7.value AS funding_third_party_cur,
                        a.commentary_financial_data,
                        CASE
                            WHEN c8.value = 'Ja' THEN 1
                            ELSE 0
                        END AS university_is_applicant,
                        a.c_created_on AS mkdate,
                        a.c_updated_on AS chdate
                    FROM iot_application a
                        LEFT JOIN choicegroupvalue c1 ON (c1.id = a.total_project_expenses_cu)
                        LEFT JOIN choicegroupvalue c2 ON (c2.id = a.expenses_university_cur)
                        LEFT JOIN choicegroupvalue c3 ON (c3.id = a.funding_amount_cur)
                        LEFT JOIN choicegroupvalue c4 ON (c4.id = a.own_contribution_cur)
                        LEFT JOIN choicegroupvalue c5 ON (c5.id = a.research_pool_cur)
                        LEFT JOIN choicegroupvalue c6 ON (c6.id = a.funding_project_leader_cu)
                        LEFT JOIN choicegroupvalue c7 ON (c7.id = a.funding_third_party_cur)
                        LEFT JOIN choicegroupvalue c8 ON (c8.id = a.university_is_applicant)
                    WHERE c_created_on > :tstamp OR c_updated_on > :tstamp
                    ORDER BY application_id",
                    'ConverisApplication',
                    'application_id'
                );

                // Get project data.
                $this->importConverisData(
                    "SELECT DISTINCT
                        p.id AS project_id,
                        p.name,
                        'free' AS type,
                        p.long_name__1 AS long_name_1,
                        p.long_name__2 AS long_name_2,
                        p.public_title__1 AS public_title_1,
                        p.public_title__2 AS public_title_2,
                        p.c_short_description AS short_description,
                        p.description__1 AS description_1,
                        p.description__2 AS description_2,
                        p.public_description__1 AS public_description_1,
                        p.public_description__2 AS public_description_2,
                        p.abstract__1 AS abstract_1,
                        p.abstract__2 AS abstract_2,
                        p.keywords__1 AS keywords_1,
                        p.keywords__2 AS keywords_2,
                        p.url,
                        p.start_date,
                        p.end_date,
                        s.status_process AS status_id,
                        p.ispublic AS is_public,
                        a.iot_application AS application_id,
                        p.c_created_on AS mkdate,
                        p.c_updated_on AS chdate
                    FROM iot_project_general p
                        JOIN iothasstatusprocess s ON (s.status_sequence = p.status_process)
                        LEFT JOIN rel_application_has_project a ON (a.iot_project = p.id)
                    WHERE p.name IS NOT NULL
                        AND s.status_process <> 5
                        AND s.infoobjecttype = 172
                        AND (p.c_created_on > :tstamp OR p.c_updated_on > :tstamp)
                    UNION
                    SELECT
                        p.id AS project_id,
                        p.name,
                        'third_party' AS type,
                        p.long_name__1 AS long_name_1,
                        p.long_name__2 AS long_name_2,
                        p.public_title__1 AS public_title_1,
                        p.public_title__2 AS public_title_2,
                        p.c_short_description AS short_description,
                        p.description__1 AS description_1,
                        p.description__2 AS description_2,
                        p.public_description__1 AS public_description_1,
                        p.public_description__2 AS public_description_2,
                        p.abstract__1 AS abstract_1,
                        p.abstract__2 AS abstract_2,
                        p.keywords__1 AS keywords_1,
                        p.keywords__2 AS keywords_2,
                        p.url,
                        p.start_date,
                        p.end_date,
                        s.status_process AS status_id,
                        p.ispublic AS is_public,
                        a.iot_application AS application_id,
                        p.c_created_on AS mkdate,
                        p.c_updated_on AS chdate
                    FROM iot_project p
                        JOIN iothasstatusprocess s ON (s.status_sequence = p.status_process)
                        LEFT JOIN rel_application_has_project a ON (a.iot_project = p.id)
                    WHERE p.name IS NOT NULL
                        AND s.status_process <> 5
                        AND s.infoobjecttype = 36
                        AND (p.c_created_on > :tstamp OR p.c_updated_on > :tstamp)
                    ORDER BY project_id",
                    'ConverisProject',
                    'project_id'
                );

                // Get additional data for third party projects.
                $this->importConverisData(
                    "SELECT DISTINCT
                        p.id AS project_id,
                        project_number,
                        project_type AS type_id,
                        CASE
                            WHEN p.doctoral_program THEN 1
                            ELSE 0
                        END AS doctoral_program,
                        duration_in_months,
                        extension_until,
                        stepped_into_running_project,
                        date_exit_project,
                        total_project_expenses,
                        c1.value_1 AS total_project_expenses_cur,
                        expenses_university,
                        c2.value_1 AS expenses_university_cur,
                        funding_quota,
                        project_flat_charge,
                        funding_central_resources,
                        c3.value_1 AS funding_central_resources_cur,
                        funding_chair,
                        c4.value_1 AS funding_chair_cur,
                        funding_third_party,
                        c5.value_1 AS funding_third_party_cur,
                        contract_sum_netto,
                        c6.value_1 AS contract_sum_netto_cur,
                        contract_sum_brutto,
                        c7.value_1 AS contract_sum_brutto_cur,
                        contract_tax_rate,
                        contract_tax,
                        c8.value_1 AS contract_tax_cur,
                        own_contribution,
                        c9.value_1 AS own_contribution_cur,
                        funding_amount,
                        c10.value_1 AS funding_amount_cur,
                        date_of_grant_agreement,
                        commentary_funding,
                        p.c_created_on AS mkdate,
                        p.c_updated_on AS chdate
                    FROM iot_project p
                        JOIN iothasstatusprocess s ON (s.status_sequence = p.status_process)
                        LEFT JOIN choicegroupvalue c1 ON (c1.id = p.total_project_expenses_cu)
                        LEFT JOIN choicegroupvalue c2 ON (c2.id = p.expenses_university_cur)
                        LEFT JOIN choicegroupvalue c3 ON (c3.id = p.funding_central_cur)
                        LEFT JOIN choicegroupvalue c4 ON (c4.id = p.funding_chair_cur)
                        LEFT JOIN choicegroupvalue c5 ON (c5.id = p.funding_third_party_cur)
                        LEFT JOIN choicegroupvalue c6 ON (c6.id = p.contract_sum_netto_cur)
                        LEFT JOIN choicegroupvalue c7 ON (c7.id = p.contract_sum_brutto_cur)
                        LEFT JOIN choicegroupvalue c8 ON (c8.id = p.contract_tax_cur)
                        LEFT JOIN choicegroupvalue c9 ON (c9.id = p.own_contribution_cur)
                        LEFT JOIN choicegroupvalue c10 ON (c10.id = p.funding_amount_cur)
                    WHERE s.infoobjecttype = 36
                        AND s.status_process <> 5
                        AND p.c_created_on > :tstamp OR p.c_updated_on > :tstamp
                    ORDER BY project_id",
                    'ConverisProjectThirdPartyData',
                    'project_id'
                );

                // Get organisations.
                $this->importConverisData(
                    "SELECT DISTINCT
                        o.id AS organisation_id,
                        o.name__1 AS name_1,
                        o.name__2 AS name_2,
                        o.c_short_description AS short_description,
                        o.description,
                        o.address,
                        o.street,
                        o.postal_code,
                        o.city,
                        o.state,
                        o.country,
                        o.phone,
                        o.fax,
                        o.url,
                        CASE
                            WHEN c.value = 'Internal' THEN 0
                            WHEN c.value = 'External' THEN 1
                        END AS external,
                        o.c_created_on AS mkdate,
                        o.c_updated_on AS chdate
                    FROM iot_organisation o
                        LEFT JOIN choicegroupvalue c ON (c.id = o.external_or_internal)
                    WHERE o.c_created_on > :tstamp OR o.c_updated_on > :tstamp
                        AND o.status_process < 4
                    ORDER BY organisation_id",
                    'ConverisOrganisation',
                    'organisation_id'
                );

                // Get persons.
                $this->importConverisData(
                    "SELECT DISTINCT
                        p.id AS person_id,
                        CASE
                            WHEN p.ldap_person_id IS NOT NULL THEN p.ldap_person_id
                            ELSE u.login
                        END AS username,
                        p.first_name,
                        p.last_name,
                        c2.value_1 AS academic_title,
                        CASE
                            WHEN c1.value = 'Internal' THEN 0
                            WHEN c1.value = 'External' THEN 1
                        END AS external,
                        p.c_created_on AS mkdate,
                        p.c_updated_on AS chdate
                    FROM iot_person p
                        LEFT JOIN choicegroupvalue c1 ON (c1.id = p.external)
                        LEFT JOIN choicegroupvalue c2 ON (c2.id = p.academic_title)
                        LEFT JOIN convuserhaspremrole r ON (r.infoobject = p.id)
                        LEFT JOIN converisuser u ON (u.id = r.converisuser)
                    WHERE p.c_created_on > :tstamp OR p.c_updated_on > :tstamp
                    ORDER BY person_id",
                    'ConverisPerson',
                    'person_id'
                );

                // Get cards.
                $this->importConverisData(
                    "SELECT DISTINCT
                        c.id AS card_id,
                        p.id AS person_id,
                        co.iot_organisation AS organisation_id,
                        CASE
                            WHEN c1.value = 'Internal' THEN 0
                            WHEN c1.value = 'External' THEN 1
                            ELSE 0
                        END AS external,
                        c.address,
                        c.email,
                        c.fax,
                        c3.value AS function,
                        c.mobile,
                        c.phone,
                        c.url,
                        c.organisation AS organisation_text,
                        c.payroll_lookup,
                        c.c_created_on AS mkdate,
                        c.c_updated_on AS chdate
                    FROM iot_card c
                        JOIN rel_pers_has_card pc ON (pc.iot_card = c.id)
                        JOIN iot_person p ON (p.id = pc.iot_person)
                        LEFT JOIN rel_card_has_orga co ON (co.iot_card = c.id)
                        LEFT JOIN choicegroupvalue c1 ON (c1.id = c.external)
                        LEFT JOIN choicegroupvalue c2 ON (c2.id = p.academic_title)
                        LEFT JOIN choicegroupvalue c3 ON (c3.id = c.function)
                    WHERE c.c_created_on > :tstamp OR c.c_updated_on > :tstamp
                        AND c.status_process < 5
                    ORDER BY card_id",
                    'ConverisCard',
                    'card_id'
                );

                // Get areas.
                $this->importConverisData(
                    "SELECT DISTINCT
                        a.id AS area_id,
                        a.name AS name_1,
                        a.name_en AS name_2,
                        a.c_short_description AS short_description,
                        c.value AS area_type,
                        a.c_created_on AS mkdate,
                        a.c_updated_on AS chdate
                    FROM iot_area a
                        LEFT JOIN choicegroupvalue c ON (c.id = a.area_type)
                    WHERE a.c_created_on > :tstamp OR a.c_updated_on > :tstamp
                        AND a.status_process < 3
                    ORDER BY area_id",
                    'ConverisArea',
                    'area_id'
                );

                // Get sources of funds.
                $this->importConverisData(
                    "SELECT DISTINCT
                        id AS source_id,
                        name,
                        short_name,
                        description,
                        website,
                        c_created_on AS mkdate,
                        c_updated_on AS chdate
                    FROM iot_source_of_funds
                    WHERE c_created_on > :tstamp OR c_updated_on > :tstamp
                        AND status_process < 3
                    ORDER BY source_id",
                    'ConverisSourceOfFunds',
                    'source_id'
                );

                // Get project types.
                $this->importConverisData(
                    "SELECT DISTINCT
                        c.id AS type_id,
                        c.value_1 AS name_1,
                        c.value_2 AS name_2
                    FROM choicegroupvalue c
                        JOIN iot_project p ON (p.project_type = c.id)
                    ORDER BY c.id",
                    'ConverisProjectType',
                    'type_id',
                    false
                );

                // Get roles.
                $this->importConverisData(
                    "SELECT DISTINCT
                        c.id AS role_id,
                        c.value_1 AS name_1,
                        c.value_2 AS name_2
                    FROM choicegroupvalue c
                        JOIN rel_card_has_project_int pi ON (pi.role = c.id)
                    UNION
                    SELECT DISTINCT
                        c.id AS role_id,
                        c.value_1 AS name_1,
                        c.value_2 AS name_2
                    FROM choicegroupvalue c
                        JOIN rel_card_has_proj_frin pi ON (pi.role = c.id)
                    UNION
                    SELECT DISTINCT
                        c.id AS role_id,
                        c.value_1 AS name_1,
                        c.value_2 AS name_2
                    FROM choicegroupvalue c
                        JOIN rel_orga_has_proj_ext oe ON (oe.role = c.id)
                    UNION
                    SELECT DISTINCT
                        c.id AS role_id,
                        c.value_1 AS name_1,
                        c.value_2 AS name_2
                    FROM choicegroupvalue c
                        JOIN rel_organisation_has_project_internal oe ON (oe.role = c.id)
                    UNION
                    SELECT DISTINCT
                        c.id AS role_id,
                        c.value_1 AS name_1,
                        c.value_2 AS name_2
                    FROM choicegroupvalue c
                        JOIN rel_orga_has_proj_ext oe ON (oe.role = c.id)
                    UNION
                    SELECT DISTINCT
                        c.id AS role_id,
                        c.value_1 AS name_1,
                        c.value_2 AS name_2
                    FROM choicegroupvalue c
                        JOIN rel_orga_has_proj_frex oe ON (oe.role = c.id)
                    UNION
                    SELECT DISTINCT
                        c.id AS role_id,
                        c.value_1 AS name_1,
                        c.value_2 AS name_2
                    FROM choicegroupvalue c
                        JOIN rel_orga_has_proj_frin oe ON (oe.role = c.id)
                    UNION
                    SELECT DISTINCT
                        c.id AS role_id,
                        c.value_1 AS name_1,
                        c.value_2 AS name_2
                    FROM choicegroupvalue c
                        JOIN iot_application a ON (a.participation_role = c.id)
                    ORDER BY role_id",
                    'ConverisRole',
                    'role_id',
                    false
                );

                // Connect projects and cards
                $this->importConverisData(
                    "SELECT DISTINCT
                        cp.iot_project AS project_id,
                        cp.iot_card AS card_id,
                        'internal' AS type,
                        cp.role AS role_id,
                        CASE
                            WHEN cp.junior_scientist THEN 1
                            ELSE 0
                        END AS junior_scientist,
                        cp.contributed_share,
                        cp.percentage_of_funding,
                        cp.start_date,
                        cp.end_date,
                        cp.iot_project_seq AS order_project,
                        cp.iot_card_seq AS order_card,
                        cp.c_created_on AS mkdate,
                        cp.c_updated_on AS chdate
                    FROM rel_card_has_project_int cp
                        JOIN iot_project p ON (p.id = cp.iot_project)
                        JOIN iothasstatusprocess s ON (s.status_sequence = p.status_process)
                    WHERE (cp.c_created_on > :tstamp OR cp.c_updated_on > :tstamp)
                        AND s.infoobjecttype = 36
                        AND s.status_process <> 5
                    UNION
                    SELECT DISTINCT
                        cp.iot_project AS project_id,
                        cp.iot_card AS person_id,
                        'external' AS type,
                        NULL::int AS role_id,
                        0 AS junior_scientist,
                        NULL AS contributed_share,
                        NULL AS percentage_of_funding,
                        NULL::timestamp AS start_date,
                        NULL::timestamp AS end_date,
                        cp.iot_project_seq AS order_project,
                        cp.iot_card_seq AS order_card,
                        cp.c_created_on AS mkdate,
                        cp.c_updated_on AS chdate
                    FROM rel_card_has_project_ext cp
                        JOIN iot_project p ON (p.id = cp.iot_project)
                        JOIN iothasstatusprocess s ON (s.status_sequence = p.status_process)
                    WHERE (cp.c_created_on > :tstamp OR cp.c_updated_on > :tstamp)
                        AND s.infoobjecttype = 36
                        AND s.status_process <> 5
                    UNION
                    SELECT DISTINCT
                        cp.iot_project_general AS project_id,
                        cp.iot_card AS card_id,
                        'internal' AS type,
                        cp.role AS role_id,
                        0 AS junior_scientist,
                        NULL AS contributed_share,
                        NULL AS percentage_of_funding,
                        NULL::timestamp AS start_date,
                        NULL::timestamp AS end_date,
                        cp.iot_project_general_seq AS order_project,
                        cp.iot_card_seq AS order_card,
                        cp.c_created_on AS mkdate,
                        cp.c_updated_on AS chdate
                    FROM rel_card_has_proj_frin cp
                        JOIN iot_project_general p ON (p.id = cp.iot_project_general)
                        JOIN iothasstatusprocess s ON (s.status_sequence = p.status_process)
                    WHERE (cp.c_created_on > :tstamp OR cp.c_updated_on > :tstamp)
                        AND s.infoobjecttype = 172
                        AND s.status_process <> 5
                    UNION
                    SELECT DISTINCT
                        cp.iot_project_general AS project_id,
                        cp.iot_card AS card_id,
                        'external' AS type,
                        NULL::int AS role_id,
                        0 AS junior_scientist,
                        NULL AS contributed_share,
                        NULL AS percentage_of_funding,
                        NULL::timestamp AS start_date,
                        NULL::timestamp AS end_date,
                        cp.iot_project_general_seq AS order_project,
                        cp.iot_card_seq AS order_card,
                        cp.c_created_on AS mkdate,
                        cp.c_updated_on AS chdate
                    FROM rel_card_has_proj_frex cp
                        JOIN iot_project_general p ON (p.id = cp.iot_project_general)
                        JOIN iothasstatusprocess s ON (s.status_sequence = p.status_process)
                    WHERE (cp.c_created_on > :tstamp OR cp.c_updated_on > :tstamp)
                        AND s.infoobjecttype = 172
                        AND p.status_process <> 5
                    ORDER BY project_id, card_id",
                    'ConverisProjectCardRelation',
                    ['project_id', 'card_id']
                );

                // Connect projects and organisations
                $this->importConverisData(
                    "SELECT DISTINCT
                        iot_project AS project_id,
                        iot_organisation AS organisation_id,
                        'internal' AS type,
                        role AS role_id,
                        start_date,
                        end_date,
                        iot_project_seq AS order_project,
                        iot_organisation_seq AS order_organisation,
                        c_created_on AS mkdate,
                        c_updated_on AS chdate
                    FROM rel_organisation_has_project_internal
                        WHERE c_created_on > :tstamp OR c_updated_on > :tstamp
                    UNION
                    SELECT DISTINCT
                        iot_project AS project_id,
                        iot_organisation AS organisation_id,
                        'external' AS type,
                        role AS role_id,
                        start_date,
                        end_date,
                        iot_project_seq AS order_project,
                        iot_organisation_seq AS order_organisation,
                        c_created_on AS mkdate,
                        c_updated_on AS chdate
                    FROM rel_orga_has_proj_ext
                        WHERE c_created_on > :tstamp OR c_updated_on > :tstamp
                    UNION
                    SELECT DISTINCT
                        iot_project_general AS project_id,
                        iot_organisation AS organisation_id,
                        'internal' AS type,
                        role AS role_id,
                        NULL::timestamp AS start_date,
                        NULL::timestamp AS end_date,
                        iot_project_general_seq AS order_project,
                        iot_organisation_seq AS order_organisation,
                        c_created_on AS mkdate,
                        c_updated_on AS chdate
                    FROM rel_orga_has_proj_frin
                        WHERE c_created_on > :tstamp OR c_updated_on > :tstamp
                    UNION
                    SELECT DISTINCT
                        iot_project_general AS project_id,
                        iot_organisation AS organisation_id,
                        'external' AS type,
                        role AS role_id,
                        NULL::timestamp AS start_date,
                        NULL::timestamp AS end_date,
                        iot_project_general_seq AS order_project,
                        iot_organisation_seq AS order_organisation,
                        c_created_on AS mkdate,
                        c_updated_on AS chdate
                    FROM rel_orga_has_proj_frex
                        WHERE c_created_on > :tstamp OR c_updated_on > :tstamp
                    ORDER BY project_id, organisation_id",
                    'ConverisProjectOrganisationRelation',
                    ['project_id', 'organisation_id']
                );

                // Connect projects and sources of funds.
                $this->importConverisData(
                    "SELECT DISTINCT
                        ps.iot_project AS project_id,
                        ps.iot_source_of_funds AS source_id,
                        ps.amount,
                        iot_project_seq AS order_project,
                        iot_source_of_funds_seq AS order_source_of_funds,
                        ps.c_created_on AS mkdate,
                        ps.c_updated_on AS chdate
                    FROM rel_proj_has_soof ps
                        JOIN iot_source_of_funds s ON (s.id = ps.iot_source_of_funds)
                        JOIN iot_project p ON (p.id = ps.iot_project)
                    WHERE ps.c_created_on > :tstamp OR ps.c_updated_on > :tstamp
                        AND s.status_process < 3
                    ORDER BY project_id, source_id",
                    'ConverisProjectSourceOfFundsRelation',
                    ['project_id', 'source_id']
                );

                // Connect cards and areas
                $this->importRawConverisData(
                    "SELECT DISTINCT
                        iot_project AS project_id,
                        iot_area AS area_id,
                        iot_project_seq AS order_project,
                        iot_area_seq AS order_area,
                        c_created_on AS mkdate,
                        c_updated_on AS chdate
                    FROM rel_area_has_project
                        WHERE c_created_on > :tstamp OR c_updated_on > :tstamp
                    UNION
                    SELECT DISTINCT
                        iot_project_general AS project_id,
                        iot_area AS area_id,
                        iot_project_general_seq AS order_project,
                        iot_area_seq AS order_area,
                        c_created_on AS mkdate,
                        c_updated_on AS chdate
                    FROM rel_area_has_proj_free
                        WHERE c_created_on > :tstamp OR c_updated_on > :tstamp
                    ORDER BY project_id, area_id",
                    'converis_project_area'
                );

            } catch (Exception $e) {
                die($e->getMessage());
            }

        } else {

            echo "Converis database connection is not configured, doing nothing.\n";

        }
    }

    /**
     * Imports data from converis by building corresponding Stud.IP SORM models.
     *
     * @param string $converisQuery the SQL query to execute against Converis DB
     * @param string $studipModelName the Stud.IP SORM model class to use for object building
     * @param string $checkKey db column used for checking whether an entry already exists in Stud.IP DB
     * @param bool $checkTimestamp check mkdate/chdate in order to import only newer entries?
     */
    private function importConverisData($converisQuery, $studipModelName, $checkKey = 'id', $checkTimestamp = true)
    {
        //echo sprintf("Processing %s...\n\n", $studipModelName);
        $stmt = $this->converis->prepare($converisQuery);
        $parameters = [];

        /*
         * Fetch maximal mkdate or chdate from table,
         * thus defining last successful import
         */
        if ($checkTimestamp) {
            $tstamp = $studipModelName::getMaxTimestamp();
            $parameters[':tstamp'] = $tstamp;

            //$converisQuery = str_replace(':tstamp', "'" . $tstamp . "'", $converisQuery);
        }

        //echo sprintf("Query:\n%s\n", $converisQuery);

        $stmt->execute($parameters);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //echo sprintf("Found %u entries in Converis.\n", count($entries));

        foreach ($entries as $one) {

            if (is_array($checkKey)) {
                $sql = "";
                $params = [];
                foreach ($checkKey as $col) {
                    if ($sql != "") {
                        $sql .= " AND ";
                    }
                    $sql .= "`" . $col . "` = ?";
                    $params[] = $one[$col];
                }
            } else {
                $sql = "`" . $checkKey . "` = ?";
                $params = [$one[$checkKey]];
            }

            //echo sprintf("%u CountBySQL:<pre>%s</pre>\n", $one[$checkKey], print_r($studipModelName::countBySQL($sql, $params), 1));

            if ($studipModelName::countBySQL($sql, $params) == 0) {
                $object = new $studipModelName();
            } else {
                if (is_array($checkKey)) {
                    $key = [];
                    foreach ($checkKey as $col) {
                        $key[] = $one[$col];
                    }
                    $object = $studipModelName::find($key);
                } else {
                    $object = $studipModelName::find($one[$checkKey]);
                }
            }

            foreach ($one as $col => $value) {
                $object->$col = html_entity_decode(strip_tags($value));
            }

            $object->store();
        }
    }

    /**
     * Imports data from converis by writing directly to Stud.IP database tables.
     *
     * @param string $converisQuery the SQL query to execute against Converis DB
     * @param string $studipTable database table to insert entries to.
     * @param bool $checkTimestamp check mkdate/chdate in order to import only newer entries?
     */
    private function importRawConverisData($converisQuery, $studipTable, $checkTimestamp = true)
    {
        //echo sprintf("Raw processing %s...\n", $studipTable);
        $stmt = $this->converis->prepare($converisQuery);
        $parameters = [];

        /*
         * Fetch maximal mkdate or chdate from table,
         * thus defining last successful import
         */
        if ($checkTimestamp) {
            $tstamp = DBManager::get()->fetchColumn(
                "SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01') FROM `" . $studipTable . "`");
            $parameters[':tstamp'] = $tstamp;

            $converisQuery = str_replace(':tstamp', "'" . $tstamp . "'", $converisQuery);
        }

        //echo sprintf("Converis query:\n%s\n", $converisQuery);

        $stmt->execute($parameters);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //echo sprintf("Found %u entries in Converis.\n\n", count($entries));

        if (count($entries) > 0) {
            $columns = array_keys($entries[0]);

            $columnList = $paramList = $updatelist = [];
            foreach ($columns as $name) {
                $columnList[] = '`' . $name . '`';
                $paramList[] = ':' . $name;
                $updateList[] = '`' . $name . '` = :' . $name;
            }

            $insert = "INSERT INTO `". $studipTable . "` (:columns) VALUES (:values) ON DUPLICATE KEY UPDATE :updates";

            $insert = str_replace(':columns', implode(', ', $columnList), $insert);
            $insert = str_replace(':values', implode(', ', $paramList), $insert);
            $insert = str_replace(':updates', implode(', ', $updateList), $insert);

            //echo sprintf("Insert query:\n%s\n", $insert);

            $stmt = DBManager::get()->prepare($insert);

            foreach ($entries as $one) {

                $params = [];

                $studipQuery = $insert;

                foreach ($one as $column => $value) {
                    $params[$column] = html_entity_decode(strip_tags($value));
                    //$studipQuery = str_replace(':' . $column, "'" . $value . "'", $studipQuery);
                }

                //echo $studipQuery . "\n";

                $stmt->execute($params);
            }
        }

    }

}
