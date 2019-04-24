<?php
/**
 * ConverisProjectThirdPartyData.php
 * model class for data about third party research projects from Converis
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    ConverisProjects
 * @property string project_id database column
 * @property string id alias column for project_id
 * @property string converis_id database column
 * @property string project_number database column
 * @property string project_type database column
 * @property string doctoral_program database column
 * @property string extension_until database column
 * @property string stepped_into_running_project database column
 * @property string date_exit_project database column
 * @property string total_project_expenses database column
 * @property string total_project_expenses_cur database column
 * @property string expenses_university database column
 * @property string expenses_university_cur database column
 * @property string funding_quota database column
 * @property string project_flat_charge database column
 * @property string funding_central_resources database column
 * @property string funding_central_resources_cur database column
 * @property string funding_chair database column
 * @property string funding_chair_cur database column
 * @property string funding_third_party database column
 * @property string funding_third_party_cur database column
 * @property string contract_sum_netto database column
 * @property string contract_sum_netto_cur database column
 * @property string contract_sum_brutto database column
 * @property string contract_sum_brutto_cur database column
 * @property string contract_tax_rate database column
 * @property string contract_tax database column
 * @property string contract_tax_cur database column
 * @property string own_contribution database column
 * @property string own_contribution_cur database column
 * @property string funding_amount database column
 * @property string funding_amount_cur database column
 * @property string date_of_grant_agreement database column
 * @property string commentary_funding database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property ConverisProject project belongs_to ConverisProject
 * @property ConverisProjectType type has_one ConverisProjectType
 */

class ConverisProjectThirdPartyData extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_projects_third_party_data';
        $config['belongs_to']['project'] = [
            'class_name' => 'ConverisProject',
            'foreign_key' => 'project_id'
        ];
        $config['has_one']['type'] = [
            'class_name' => 'ConverisProjectType',
            'foreign_key' => 'project_type',
            'assoc_foreign_key' => 'type_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}
