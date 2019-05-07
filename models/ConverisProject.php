<?php
/**
 * ConverisProject.php - model class for research projects from Converis
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    ConverisProjects
 *
 * @property string project_id database column
 * @property string id alias column for project_id
 * @property string type database column
 * @property string name database column
 * @property string long_name_1 database column
 * @property string long_name_2 database column
 * @property string public_title_1 database column
 * @property string public_title_2 database column
 * @property string short_description database column
 * @property string description_1 database column
 * @property string description_2 database column
 * @property string public_description_1 database column
 * @property string public_description_2 database column
 * @property string abstract_1 database column
 * @property string abstract_2 database column
 * @property string keywords_1 database column
 * @property string keywords_2 database column
 * @property string url database column
 * @property string start_date database column
 * @property string end_date database column
 * @property string status_id database column
 * @property string is_public database column
 * @property string application_id database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMapCollection related_cards has_many ConverisProjectCardRelation
 * @property SimpleORMapCollection related_sources_of_funds has_many ConverisProjectSourceOfFundsRelation
 * @property SimpleORMapCollection organisations has_many ConverisOrganisation
 * @property ConverisProjectStatus status has_one ConverisProjectStatus
 * @property ConverisProjectThirdPartyData third_party_data has_one ConverisProjectThirdPartyData
 * @property ConverisApplication application has_one ConverisApplication
 * @property SimpleORMapCollection areas has_and_belongs_to_many ConverisArea
 */

class ConverisProject extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_projects';
        $config['has_one']['status'] = [
            'class_name' => 'ConverisProjectStatus',
            'foreign_key' => 'status_id',
            'assoc_foreign_key' => 'status_id'
        ];
        $config['has_one']['third_party_data'] = [
            'class_name' => 'ConverisProjectThirdPartyData',
            'foreign_key' => 'project_id'
        ];
        $config['has_one']['application'] = [
            'class_name' => 'ConverisApplication',
            'foreign_key' => 'application_id',
            'assoc_foreign_key' => 'application_id'
        ];
        $config['has_many']['related_cards'] = [
            'class_name' => 'ConverisProjectCardRelation',
            'foreign_key' => 'project_id'
        ];
        $config['has_many']['related_organisations'] = [
            'class_name' => 'ConverisProjectOrganisationRelation',
            'foreign_key' => 'project_id'
        ];
        $config['has_and_belongs_to_many']['areas'] = [
            'class_name' => 'ConverisArea',
            'thru_table' => 'converis_project_area',
            'thru_key' => 'project_id',
            'thru_assoc_key' => 'area_id',
            'order_by' => 'ORDER BY `short_description`',
        ];
        $config['has_many']['related_sources_of_funds'] = [
            'class_name' => 'ConverisProjectSourceOfFundsRelation',
            'foreign_key' => 'project_id'
        ];
        parent::configure($config);
    }

    /**
     * Gets all projects associated to a given organisation via cards.
     *
     * @param string $name organisation name to check
     * @return array
     */
    public static function findByOrganisationName($name)
    {
        $projects = DBManager::get()->fetchAll("SELECT DISTINCT p.*
            FROM `converis_projects` p
                JOIN `converis_project_card` pc ON (pc.`project_id` = p.`project_id`)
                JOIN `converis_cards` c ON (c.`card_id` = pc.`card_id`)
                JOIN `converis_organisations` o ON (o.`organisation_id` = c.`organisation_id`)
                JOIN `converis_project_status` s ON (s.`status_id` = p.`status_id`)
            WHERE o.`name_1` = :name
            ORDER BY s.`name_1`, p.`name`",
            ['name' => $name],
            __CLASS__ . '::buildExisting'
        );

        return $projects;
    }

    /**
     * Gets all projects associated to a given user via cards.
     *
     * @param string $username username to check
     * @return array
     */
    public static function findByUsername($username)
    {
        $projects = DBManager::get()->fetchAll("SELECT DISTINCT p.*
            FROM `converis_projects` p
                JOIN `converis_project_card` pc ON (pc.`project_id` = p.`project_id`)
                JOIN `converis_cards` c ON (c.`card_id` = pc.`card_id`)
                JOIN `converis_persons` pers ON (pers.`person_id` = c.`person_id`)
                JOIN `converis_project_status` s ON (s.`status_id` = p.`status_id`)
            WHERE pers.`username` = :username
            ORDER BY s.`name_1`, p.`name`",
            ['username' => $username],
            __CLASS__ . '::buildExisting'
        );

        return $projects;
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

    /**
     * Checks if the current project is relevant for the given report timeframe.
     *
     * @param DateTime $start start time
     * @param DateTime $end end time
     * @return bool
     */
    public function runsInTimeframe($start, $end)
    {
        $runs = false;

        $pStart = new DateTime($this->start_date);
        $pEnd = new DateTime($this->end_date);

        switch ($this->type) {
            case 'third_party':
                switch ($this->status->name_1) {
                    case 'Abgelehnt':
                        $declined = new DateTime($this->third_party_data->date_of_grant_agreement);
                        $runs = $declined >= $start && $declined <= $end;
                        break;
                    case 'Bewilligt':
                    case 'Beendet':
                        switch ($this->third_party_data->type->name_1) {
                            case 'Beantragt':
                                $deadline = new DateTime($this->application->deadline);
                                $runs = $deadline >= $start && $deadline <= $end;
                                break;
                            case 'EU':
                            case 'International':
                            case 'National':
                                $granted = new DateTime($this->third_party_data->date_of_grant_agreement);
                                $runs = ($pEnd >= $start && $pEnd <= $end) ||
                                    ($pEnd->getTimestamp() <= 0 && $granted >= $start && $granted <= $end);
                                break;
                            case 'Auftragsforschung':
                            case 'Kooperation':
                            case 'Lizenz':
                                $runs = ($pEnd >= $start && $pEnd <= $end) || $pEnd->getTimestamp() <= 0;
                                break;
                        }
                        break;
                }
                break;
            case 'free':
                $runs = ($pStart >= $start && $pStart <= $end) ||
                    ($pEnd >= $start && $pEnd <= $end) ||
                    ($pStart <= $start && $pEnd >= $pEnd);
                break;
        }

        return $runs;
    }

}
