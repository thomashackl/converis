<html>
    <head>
        <meta charset="UTF-8">
        <style>
            body {
                border: 1px solid #000000;
                font-family: Arial, sans-serif;
                font-size: 9pt;
            }
            h1 {
                background-color: #dadada;
                font-size: 11pt;
                margin-bottom: 0;
                padding-bottom: 5px;
                padding-left: 5px;
                padding-top: 10px;
            }
            h2 {
                background-color: #dadada;
                font-size: 10pt;
                margin-top: 0;
                padding-bottom: 10px;
                padding-left: 5px;
                padding-top: 5px;
            }
            article {
                padding-left: 10px;
                padding-right: 10px;
            }
            section {
                margin-top: 10px;
                margin-bottom: 10px;
            }
            table {
                border: 1px solid #000000;
                border-collapse: collapse;
                border-spacing: 0;
                margin-left: 15px;
                margin-right: 15px;
            }
            th {
                background-color: #cdcdcd;
                font-weight: bold;
                text-align: left;
            }
            td, th {
                border: 1px solid #000000;
                padding: 5px;
                vertical-align: top;
            }
            .number {
                text-align: right;
            }
        </style>
    </head>
    <body>
        <h1>
            Drittmittelprojekte im Berichtszeitraum <?= $start ?> - <?= $end ?>
        </h1>
        <h2>
            <?= htmlReady($institute->name) ?>
        </h2>
        <article>
            <section>
                Bitte geben Sie in unten stehender Tabelle Drittmittelprojekte
                an, die in den Berichtszeitraum fallen.
            </section>
            <section>
                Forschungsleistung kann und soll nicht allein in monetären
                Erfolgen, d.h. der Höhe der eingeworbenen Drittmittel gemessen
                werden, denn auch die Antragstellung selbst ist eine
                anerkennenswerte Leistung. In Einzelfällen kann es sinnvoll
                sein, auch abgelehnte Projekte anzugeben, z.B., wenn ein
                Folgeprojekt geplant ist, oder wenn das Projekt zwar
                hervorragend bewertet, aber auf Grund der Vielzahl exzellenter
                Anträge doch nicht gefördert werden konnte.
            </section>
            <section>
                Um einen Überblick über die Forschungsaktivitäten und auch das
                Engagement im Bereich Drittmittelforschung zu erhalten, wird
                daher darum gebeten, Projekte und Anträge in verschiedenen
                Stadien zu erfassen (laufende Projekte, in der Vorbereitung
                und in der Begutachtung befindliche Projekte, abgelehnte
                Projekte)
            </section>
        </article>
        <table>
            <colgroup>
                <col width="14%">
                <col width="28%">
                <col width="15%">
                <col width="14%">
                <col width="15%">
                <col width="14%">
            </colgroup>
            <thead>
                <tr>
                    <th>
                        Mittelgeber sowie ggf. Förderprogr.
                    </th>
                    <th>
                        Kurztitel sowie Kurzbeschreibung (1-2 Sätze)
                    </th>
                    <th>
                        Fördersumme über die gesamte Laufzeit
                        (Gesamtprojektförder- volumen).
                        Bei Beteiligung weiterer Einrichtungen bitte
                        gesondert angeben:
                    </th>
                    <th>
                        Finanzierungsanteil des Lehrstuhls / der Professur
                        über die gesamte Laufzeit inkl. aller Overheads u.ä.
                    </th>
                    <th>
                        Bei Beteiligung weiterer Einrichtungen bitte gesondert
                        angeben: Koordinator / Sprecher für Gesamtprojekt
                    </th>
                    <th>
                        Status / Laufzeit
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p) : ?>
                <tr>
                    <td<?php $soof = $p->related_sources_of_funds ?>>
                        <?php foreach ($soof as $one) : ?>
                            <?= htmlReady($one->source_of_funds->name) ?>
                            <br>
                        <?php endforeach ?>
                    </td>
                    <td>
                        <?= htmlReady($p->long_name_1 ?: $p->long_name_2) ?>
                        <br>
                        <?= htmlReady($p->abstract_1 ?: $p->abstract_2) ?>
                    </td>
                    <td class="number">
                        <?= htmlReady($p->third_party_data->funding_amount) ?>
                        <?= htmlReady($p->third_party_data->funding_amount_cur) ?>
                    </td>
                    <td class="number">
                        <?= htmlReady($p->third_party_data->funding_chair) ?>
                        <?= htmlReady($p->third_party_data->funding_chair_cur) ?>
                    </td>
                    <td>
                        <?php
                            $orga = $p->related_organisations->filter(function($one) {
                                return $one->type == 'internal';
                            });
                        ?>
                        <?php if (count($orga) == 1) : $one = $orga->first(); ?>
                            <?= htmlReady($one->organisation->name_1) ?>
                            <?php if ($one->role != '') : ?>
                                (<?= htmlReady($one->role_object->name_1) ?>)
                            <?php endif ?>
                        <?php else : ?>
                            <ul>
                                <?php foreach ($orga as $one) :?>
                                <li>
                                    <?= htmlReady($one->organisation->name_1) ?>
                                    <?php if ($one->role != '') : ?>
                                        (<?= htmlReady($one->role_object->name_1) ?>)
                                    <?php endif ?>
                                </li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                    </td>
                    <td>
                        <?= htmlReady($p->project_status->name_1) ?>
                        <br>
                        <?php if (strtotime($p->start_date) > 0) : ?>
                            <?= htmlReady(date('d.m.Y', strtotime($p->start_date))) ?>
                        <?php endif ?>
                        <?php if (strtotime($p->end_date) > 0) : ?>
                            <?= dgettext( 'converisplugin', 'bis') ?>
                            <?= htmlReady(date('d.m.Y', strtotime($p->end_date))) ?>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </body>
</html>