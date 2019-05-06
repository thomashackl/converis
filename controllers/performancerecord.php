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

use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PerformanceRecordController extends AuthenticatedController
{

    var $start = 0;
    var $end = 0;

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;

        if (!$GLOBALS['perm']->have_perm('root') && !ConverisAdmin::existsByUsername($GLOBALS['user']->username)) {
            throw new AccessDeniedException();
        }

        $this->flash = Trails_Flash::instance();
        $this->set_layout(null);
    }

    public function xls_action($start, $end, $username, $format = 'xls')
    {
        $this->start = new DateTime($start);
        $this->end = new DateTime($end);

        $startDate = $this->start->getTimestamp();
        $endDate = $this->end->getTimestamp();

        $sections = [
            'third_party' => [
                'third_party_submitted' => [
                    'title' => 'Anträge eingereicht - gemeinnützig begutachtete Forschung',
                    'columns' => [
                        'Mittelgeber/Förderprogramm',
                        'Kurzbezeichnung | Langbezeichnung',
                        'Laufzeit an der Universität Passau (geplant)',
                        'Finanzen (beantragt)',
                        'Verschlagwortung (DFG, Destatis sowie freie Schlagworte)',
                        'Beteiligte interne Personen und deren Rolle im Projekt',
                        'Rolle Universität Passau im Projekt'
                    ]
                ],
                'third_party_1' => [
                    'title' => 'Projekte bewilligt - gemeinnützig begutachtete Forschung',
                    'columns' => [
                        'Mittelgeber/Förderprogramm',
                        'Kurzbezeichnung | Langbezeichnung',
                        'Laufzeit an der Universität Passau',
                        'Finanzen',
                        'Verschlagwortung (DFG, Destatis sowie freie Schlagworte)',
                        'Beteiligte interne Personen und deren Rolle im Projekt',
                        'Rolle Universität Passau im Projekt'
                    ]
                ],
                'third_party_2' => [
                    'title' => 'Projekte bewilligt - wirtschaftliche Tätigkeit',
                    'columns' => [
                        'Vertragspartner',
                        'Kurzbezeichnung | Langbezeichnung',
                        'Laufzeit an der Universität Passau',
                        'Finanzen',
                        'Verschlagwortung (DFG, Destatis sowie freie Schlagworte)',
                        'Beteiligte interne Personen und deren Rolle im Projekt',
                        'Rolle Universität Passau im Projekt'
                    ]
                ]
            ],
            'free' => [
                'free' => [
                    'title' => 'Freie Projekte',
                    'columns' => [
                        'Kurzbezeichnung | Langbezeichnung',
                        'Laufzeit | Status',
                        'Verschlagwortung | Kurzbeschreibung',
                        'Beteiligte interne Personen und deren Rolle im Projekt',
                        'Rolle Universität Passau im Projekt'
                    ]
                ]
            ]
        ];

        $person = ConverisPerson::findOneByUsername($username);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($GLOBALS['user']->getFullname())
            ->setLastModifiedBy($GLOBALS['user']->getFullname())
            ->setTitle('Leistungsbezüge ' . $person->getFullname())
            ->setSubject('Leistungsbezüge ' . $person->getFullname());
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        $spreadsheet->getDefaultStyle()->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        // Some style definitions for consistent usage.
        $borderStyle = [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ];
        $greyStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'd9d9d9']
            ],
            'font' => [
                'bold' => true
            ]
        ];
        $paleStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'fce9da']
            ],
            'font' => [
                'bold' => true
            ]
        ];
        $footerStyle = [
            'font' => [
                'italic' => true
            ]
        ];

        $counter = 1;

        foreach ($person->cards as $card) {

            $relations = $this->getProjects($card);

            /*
             * Create sheet for third party projects.
             */
            $sheet = $spreadsheet->createSheet();

            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

            $sheet->getColumnDimension('A')->setWidth(43);
            $sheet->getColumnDimension('B')->setWidth(54.5);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(40);
            $sheet->getColumnDimension('E')->setWidth(43);
            $sheet->getColumnDimension('F')->setWidth(43);
            $sheet->getColumnDimension('G')->setWidth(23);
            $sheet->setTitle('Drittmittelprojekte' . (count($person->cards) > 1 ? '_' . $counter : ''));

            // Set header line.
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')
                ->applyFromArray($greyStyle);
            $sheet->getStyle('A1')
                ->getFont()
                ->setBold(true);
            $sheet->setCellValue('A1', $person->getFullname() . '(' . $card->organisation->name_1 . ')');

            $row = 3;

            foreach ($sections['third_party'] as $index => $section) {

                if (count($relations[$index]) > 0) {

                    $sheet->mergeCells('A' . $row . ':G' . $row);

                    $sheet->getStyle('A' . $row)
                        ->applyFromArray($paleStyle);

                    $sheet->setCellValue('A' . $row, $section['title']);

                    $startRow = $row;

                    $row++;

                    for ($col = 'A'; $col <= 'G'; $col++) {
                        $sheet->getStyle($col . $row)
                            ->applyFromArray($greyStyle);
                    }

                    $sheet->fromArray($section['columns'], '', 'A' . $row);

                    foreach ($relations[$index] as $r) {

                        $row++;

                        $texts = $this->makeTexts($r);

                        $sheet->fromArray($texts, '', 'A' . $row);
                    }

                    $endRow = $row;

                    $sheet->getStyle('A' . $startRow . ':G' . $endRow)
                        ->getBorders()
                        ->applyFromArray($borderStyle);

                    $row += 2;
                }
            }

            $sheet->getStyle('A' . $row)
                ->applyFromArray($paleStyle)
                ->getFont()
                ->setBold(false);
            $sheet->setCellValue('A' . $row, 'Anträge abgelehnt: ' .
                count($relations['third_party_declined']));
            $row += 2;

            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->setCellValue('A' . $row,
                'Alphabetisch sortiert nach Mittelgeber/Förderprogramm '.
                'bzw. Vertragspartner und anschließend nach Kurzbezeichnung.');
            $sheet->getStyle('A' . $row)
                ->applyFromArray($footerStyle);
            $row++;
            $sheet->setCellValue('A' . $row, 'Stand: ' . date('d.m.Y'));
            $sheet->getStyle('A' . $row)
                ->applyFromArray($footerStyle);

            /*
             * Create sheet for free projects.
             */
            $sheet = $spreadsheet->createSheet();
            $sheet->getColumnDimension('A')->setWidth(44);
            $sheet->getColumnDimension('B')->setWidth(12);
            $sheet->getColumnDimension('C')->setWidth(74);
            $sheet->getColumnDimension('D')->setWidth(53);
            $sheet->getColumnDimension('E')->setWidth(22);
            $sheet->setTitle('Freie Projekte' . (count($person->cards) > 1 ? '_' . $counter : ''));

            // Set header line.
            $sheet->mergeCells('A1:E1');
            $sheet->getStyle('A1')
                ->applyFromArray($greyStyle);
            $sheet->getStyle('A1')
                ->getFont()
                ->setBold(true);
            $sheet->setCellValue('A1',
                $person->getFullname() . '(' . $card->organisation->name_1 . ')');

            $row = 3;

            foreach ($sections['free'] as $index => $section) {

                if (count($relations[$index]) > 0) {

                    $sheet->mergeCells('A' . $row . ':E' . $row);

                    $sheet->getStyle('A' . $row)
                        ->applyFromArray($paleStyle);

                    $sheet->setCellValue('A' . $row, $section['title']);

                    $startRow = $row;

                    $row++;

                    for ($col = 'A'; $col <= 'E'; $col++) {
                        $sheet->getStyle($col . $row)
                            ->applyFromArray($greyStyle);
                    }

                    $sheet->fromArray($section['columns'], '', 'A' . $row);

                    foreach ($relations[$index] as $r) {

                        $row++;

                        $texts = $this->makeTexts($r);

                        $sheet->fromArray($texts, '', 'A' . $row);
                    }

                    $endRow = $row;

                    $sheet->getStyle('A' . $startRow . ':E' . $endRow)
                        ->getBorders()
                        ->applyFromArray($borderStyle);

                    $row += 2;
                }
            }

            $sheet->setCellValue('A' . $row,
                'Alphabetisch sortiert nach Kurzbezeichnung');
            $sheet->getStyle('A' . $row)
                ->applyFromArray($footerStyle);
            $row++;
            $sheet->setCellValue('A' . $row, 'Stand: ' . date('d.m.Y'));
            $sheet->getStyle('A' . $row)
                ->applyFromArray($footerStyle);

            $counter++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'leistungsbezuege-' . strtolower($person->last_name);

        if ($format === 'xls') {
            $writer = new Xlsx($spreadsheet);
            $filename .= '.xlsx';
            $this->set_content_type('vnd.openxmlformats-officedocument. spreadsheetml.sheet');
        } else if ($format === 'pdf') {
            $writer = new Mpdf($spreadsheet);
            $filename .= '.pdf';
            $this->set_content_type('application/pdf');
            $writer->writeAllSheets();
        }

        $this->response->add_header('Content-Disposition',
            'attachment;' . encode_header_parameter('filename', $filename));
        $this->response->add_header('Cache-Control', 'cache, must-revalidate');
        $this->response->add_header('Pragma', 'public');

        $this->response->add_header('X-Dialog-Close', 1);

        $writer->save('php://output');
        $this->render_nothing();
    }

    /**
     * Generates a PDF format export of the performance overview.
     *
     * @param string $start start of export time frame
     * @param string $end end of export time frame
     * @param string $username username of the chosen Stud.IP user
     */
    public function pdf_action($start, $end, $username)
    {
        $this->relocate('performancerecord/xls', $start, $end, $username, 'pdf');
    }

    private function getProjects($card)
    {
        $projects = [
            'count' => 0,
            'third_party_submitted' => [],
            'third_party_1' => [],
            'third_party_2' => [],
            'third_party_declined' => [],
            'free' => []
        ];

        if ($card->related_projects !== null && count($card->related_projects) > 0) {

            /*
             * Iterate over related projects and sort
             * them into the relevant section.
             */
            foreach ($card->related_projects as $rel) {
                // Just consider projects running in given time frame.
                if ($rel->project->runsInTimeframe($this->start, $this->end)) {
                    if ($rel->project->type === 'third_party') {

                        /*
                         * Section 1: EU, International or National
                         * third party projects that are not yet approved.
                         */
                        if ($rel->project->status->name_1 == 'Bei Mittelgeber eingereicht' &&
                            in_array($rel->project->third_party_data->type->name_1,
                                ['EU', 'National', 'International'])) {

                            $projects['third_party_submitted'][] = $rel;

                        /*
                         * Section 2: third party projects that are approved
                         * and of type EU, National or International
                         */
                        } else if ($rel->project->status->name_1 == 'Bewilligt' &&
                            in_array($rel->project->third_party_data->type->name_1,
                                ['EU', 'National', 'International'])) {

                            $projects['third_party_1'][] = $rel;

                        /*
                         * Section 3: third party projects that are approved
                         * and of type Auftragsforschung, Kooperation or Lizenz
                         */
                        } else if ($rel->project->status->name_1 == 'Bewilligt' &&
                            in_array($rel->project->third_party_data->type->name_1,
                                ['Auftragsforschung', 'Kooperation', 'Lizenz'])) {

                            $projects['third_party_2'][] = $rel;

                        /*
                         * Section 4: declined projects
                         */
                        } else if ($rel->project->status->name_1 === 'Abgelehnt') {

                            $projects['third_party_declined'][] = $rel;

                        }

                    /*
                     * Section 5: free projects
                     */
                    } else if ($rel->project->type === 'free') {

                        $projects['free'][] = $rel;

                    }
                }
            }

            $projects['count'] = count($projects['third_party_submitted']) + count($projects['third_party_1']) +
                count($projects['third_party_2']) + count($projects['third_party_declined']) +
                count($projects['free']);

            /*
             * Sort projects.
             */
            foreach (['third_party_submitted', 'third_party_1', 'third_party_2', 'third_party_declined'] as $section) {
                usort($projects[$section], function($a, $b) {
                    if ($a->project->related_sources_of_funds != null &&
                            count($a->project->related_sources_of_funds) > 0) {
                        $sort1 = $a->project->related_sources_of_funds->first()->source_of_funds->name;
                    } else {
                        $sort1 = '';
                    }
                    $sort1 .= $a->project->name;

                    if ($b->project->related_sources_of_funds != null &&
                            count($b->project->related_sources_of_funds) > 0) {
                        $sort2 = $b->project->related_sources_of_funds->first()->source_of_funds->name;
                    } else {
                        $sort2 = '';
                    }
                    $sort2 .= $b->project->name;

                    return strnatcasecmp($sort1, $sort2);
                });
            }

            usort($projects['free'], function($a, $b) {
                return strnatcasecmp($a->project->name, $b->project->name);
            });
        }

        return $projects;
    }

    /**
     * Generates the texts shown in the output document for a project.
     *
     * @param ConverisProjectCardRelation $relation relation data
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function makeTexts(&$relation)
    {
        $project = $relation->project;

        if ($project->type === 'third_party') {
            if ($project->status->name_1 == 'Bei Mittelgeber eingereicht') {
                $useApplication = true;
            } else {
                $useApplication = false;
            }

            if ($project->related_sources_of_funds != null && count($project->related_sources_of_funds) > 0) {
                $sofNames = implode(', ',
                    $project->related_sources_of_funds->map(function ($one) {
                        return $one->source_of_funds->name;
                    }));
            } else {
                $sofNames = 'k. A.';
            }

            $nameDesc = $project->name;
            if ($project->long_name_1 != '' || $project->long_name_2 != '') {
                $nameDesc .= ' | ' . ($project->long_name_1 != '' ?
                        $project->long_name_1 :
                        $project->long_name_2);
            }

            if ($useApplication) {
                $pStart = new DateTime($project->application->start_date);
                $pEnd = new DateTime($project->application->end_date);
            } else {

                $pStart = new DateTime($project->third_party_data->stepped_into_running_project);
                if ($pStart->getTimestamp() <= 0) {
                    $pStart = new DateTime($project->start_date);
                }
                $pEnd = new DateTime($project->third_party_data->date_exit_project);
                if ($pEnd->getTimestamp() <= 0) {
                    $pEnd = new DateTime($project->third_party_data->extension_until);
                    if ($pEnd->getTimestamp() <= 0) {
                        $pEnd = new DateTime($project->end_date);
                    }
                }

            }

            // Build duration text.
            if ($pStart->getTimestamp() <= 0 && $pEnd->getTimestamp() <= 0) {
                $duration = 'k. A.';
            } else if ($pStart->getTimestamp() > 0 && $pEnd->getTimestamp() <= 0) {
                $duration = 'ab ' . $pStart->format('d.m.Y');
            } else if ($pStart->getTimestamp() <= 0 && $pEnd->getTimestamp() > 0) {
                $duration = 'bis ' . $pEnd->format('d.m.Y');
            } else {
                $duration = $pStart->format('d.m.Y') . ' - ' .
                    $pEnd->format('d.m.Y');
            }
            if ($useApplication && $project->application->duration_in_months != null) {
                $duration .= "\n(" . $project->application->duration_in_months . ' Monate)';
            } else if ($project->third_party_data->duration_in_months != null) {
                $duration .= "\n(" . $project->third_party_data->duration_in_months . ' Monate)';
            }

            // Build cost text.
            $cost = new RichText();
            if (in_array($project->third_party_data->type->name_1, ['EU', 'International', 'National'])) {
                $cost->createText('Gesamtprojektkosten: ' .
                    implode(' ',
                        [
                            number_format($useApplication ?
                                $project->application->total_project_expenses :
                                $project->third_party_data->total_project_expenses, 2, ',', '.'),
                            ($useApplication ?
                                $project->application->total_project_expenses_cur :
                                $project->third_party_data->total_project_expenses_cur
                            )
                        ]) . "\n\n");
                $upaheader = $cost->createTextRun("Universität Passau:\n");
                $upaheader->getFont()->setUnderline(true);
                $cost->createText('Kosten Universität Passau: ' .
                    implode(' ',
                        [
                            number_format($useApplication ?
                                $project->application->expenses_upa :
                                $project->third_party_data->expenses_university, 2, ',', '.'),
                            ($useApplication ?
                                $project->application->expenses_upa_cur :
                                $project->third_party_data->expenses_university_cur
                            )
                        ]) . "\n");
                $cost->createText('Fördersumme: ' .
                    implode(' ',
                        [
                            number_format($useApplication ?
                                $project->application->funding_amount :
                                $project->third_party_data->funding_amount, 2, ',', '.'),
                            ($useApplication ?
                                $project->application->funding_amount_cur :
                                $project->third_party_data->funding_amount_cur
                            )
                        ]) . "\n");
                $cost->createText('Förderquote: ' .
                    ($useApplication ?
                        ($project->application->funding_quota ?: 'k. A.') :
                        ($project->third_party_data->funding_quota ?: 'k. A.')
                    ) . "\n");
                $cost->createText('Eigenanteil Projektteam: ' .
                    implode(' ',
                        [
                            number_format($useApplication ?
                                $project->application->funding_project_leader :
                                $project->third_party_data->funding_chair, 2, ',', '.'),
                            ($useApplication ?
                                $project->application->funding_project_leader_cur :
                                $project->third_party_data->funding_chair_cur
                            )
                        ]) . "\n");
                $cost->createText('Eigenanteil Forschungspool: ' .
                    implode(' ',
                        [
                            number_format($useApplication ?
                                $project->application->research_pool :
                                $project->third_party_data->funding_central_resources, 2, ',', '.'),
                            ($useApplication ?
                                $project->application->research_pool_cur :
                                $project->third_party_data->funding_central_resources_cur
                            )
                        ]) . "\n");
                $cost->createText('Kofinanzierung extern: ' .
                    implode(' ',
                        [
                            number_format($useApplication ?
                                $project->application->funding_third_party :
                                $project->third_party_data->funding_third_party, 2, ',', '.'),
                            ($useApplication ?
                                $project->application->funding_third_party_cur :
                                $project->third_party_data->funding_third_party_cur
                            )
                        ]) . "\n");
                $cost->createText('Hinweis: ' .
                    ($useApplication ?
                        ($project->application->commentary_financial_data ?: 'k. A.') :
                        ($project->third_party_data->commentary_funding ?: 'k. A.')
                    ));

                if (!$useApplication) {
                    $cost->createText("\n\n");
                    $costpart = $cost->createTextRun(sprintf("Anteil %s:\n", $relation->card->person->getFullname()));
                    $costpart->getFont()->setUnderline(true);
                    $cost->createText('an Fördersumme: ' . ($relation->percentage_of_funding ?: 'k. A.' . "\n"));
                    $cost->createText('an Eigenanteil: ' . ($relation->contributed_share ?: 'k. A.' . "\n"));
                }
            } else if (in_array($project->third_party_data->type->name_1, ['Auftragsforschung', 'Kooperation', 'Lizenz'])) {
                $cost->createText(' Summe (netto): ' .
                    number_format($project->third_party_data->contract_sum_netto, 2, ',', '.') .
                    $project->third_party_data->contract_sum_netto_cur
                );
                $cost->createText("\nHinweis: " . $project->third_party_data->commentary_funding ?: 'k. A.');
                if (!$useApplication) {
                    $cost->createText("\n\n");
                    $costpart = $cost->createTextRun(sprintf("Anteil %s:\n", $relation->card->person->getFullname()));
                    $costpart->getFont()->setUnderline(true);
                    $cost->createText('an Fördersumme: ' . ($relation->percentage_of_funding ?: 'k. A.' . "\n"));
                    $cost->createText('an Eigenanteil: ' . ($relation->contributed_share ?: 'k. A.' . "\n"));
                }
            }

            // Keywords
            $keywords = [];
            if ($project->areas != null && count($project->areas) > 0) {
                foreach ($project->areas as $area) {
                    $keywords[] = $area->area_type . ': ' . $area->short_description;
                }
            } else {
                $keywords = ['k. A.'];
            }

            // List internal persons with their corresponding role.
            $persons = [];
            if ($project->related_cards != null && count($project->related_cards) > 0) {
                $internal = $project->related_cards->filter(function ($c) {
                    return $c->type === 'internal';
                });
                foreach ($internal as $rel) {
                    $current = $rel->card->person->getFullname();
                    if ($rel->role_id != 0) {
                        $current .= ' (' . $rel->role->name_1 . ')';
                    }
                    $persons[] = $current;
                }

                if (count($persons) == 1) {
                    $persons = $persons[0];
                } else if (count($persons) > 1) {
                    $persons = implode("\n", array_map(function ($p) {
                        return '- ' . $p;
                    }, $persons));
                }
            }

            // UPA role in project
            if ($useApplication) {
                $role = $project->application->participation_role != 0 ?
                    $project->application->participation_role->name_1 : 'k. A.';
            } else {
                $role = $project->application !== null && $project->application->participation_role != 0 ?
                    $project->application->participation_role->name_1 : 'k. A.';
            }

            return [
                $sofNames,
                $nameDesc,
                $duration,
                $cost,
                implode(', ', $keywords),
                $persons,
                $role
            ];
        } else if ($project->type === 'free') {

            // Short and long name
            $name = $project->name . ' | ' .
                ($project->long_name_1 ?: $project->long_name_2);

            // Duration and status
            $pStart = new DateTime($project->start_date);
            $pEnd = new DateTime($project->end_date);

            $durationStatus = '';
            if ($pStart->getTimestamp() <= 0 && $pEnd->getTimestamp() <= 0) {
                $durationStatus = 'k. A.';
            } else if ($pStart->getTimestamp() > 0 && $pEnd->getTimestamp() <= 0) {
                $durationStatus = 'ab ' . $pStart->format('d.m.Y');
            } else if ($pStart->getTimestamp() <= 0 && $pEnd->getTimestamp() > 0) {
                $durationStatus = 'bis ' . $pEnd->format('d.m.Y');
            } else {
                $durationStatus = $pStart->format('d.m.Y') . ' - ' .
                    $pEnd->format('d.m.Y');
            }
            $durationStatus .= "\n" . $project->status->name_1;

            // Keywords
            $keywordsText = new RichText();
            $kwheader = $keywordsText->createTextRun("Verschlagwortung: ");
            $kwheader->getFont()->setUnderline(true);
            $keywords = [];
            if ($project->areas != null && count($project->areas) > 0) {
                foreach ($project->areas as $area) {
                    $keywords[] = $area->area_type . ': ' . $area->short_description;
                }
            } else {
                $keywords = ['k. A.'];
            }
            $keywordsText->createText(implode(', ', $keywords));

            // List internal persons with their corresponding role.
            $persons = [];
            if ($project->related_cards != null && count($project->related_cards) > 0) {
                $internal = $project->related_cards->filter(function ($c) {
                    return $c->type === 'internal';
                });
                foreach ($internal as $rel) {
                    $current = $rel->card->person->getFullname();
                    if ($rel->role_id != 0) {
                        $current .= ' (' . $rel->role->name_1 . ')';
                    }
                    $persons[] = $current;
                }

                if (count($persons) == 1) {
                    $persons = $persons[0];
                } else if (count($persons) > 1) {
                    $persons = implode("\n", array_map(function ($p) {
                        return '- ' . $p;
                    }, $persons));
                }
            }

            return [
                $name,
                $durationStatus,
                $keywordsText,
                $persons,
                ''
            ];
        }
    }

}