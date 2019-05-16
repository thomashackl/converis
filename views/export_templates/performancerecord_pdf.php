<html>
    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 7pt;
            }
            h1 {
                background-color: #d9d9d9;
                font-size: 8pt;
                font-weight: bold;
                width: 100%;
            }
            h2 {
                background-color: #d9d9d9;
                font-size: 7.5pt;
                font-weight: bold;
                width: 100%;
            }
            table {
                border-collapse: collapse;
                border-spacing: 0;
            }
            tr.section td {
                background-color: #fce9da;
                font-size: 7.5pt;
                font-weight: bold;
            }
            tr.columns td {
                background-color: #d9d9d9;
                font-weight: bold;
            }
            td {
                border: 1px solid #000000;
                padding: 2px;
            }
            section.declined {
                background-color: #fce9da;
                margin-top: 6pt;
                width: 100%;
            }
            section.declined span.strong {
                font-weight: bold;
            }
            section.sorting {
                font-style: italic;
                margin-top: 6pt;
            }
        </style>
    </head>
    <body>
        <?php foreach ($person->cards as $card) : ?>
            <h1>
                <?= htmlReady($person->getFullname()) ?> (<?= htmlReady($card->organisation->name_1) ?>)
            </h1>
            <?= $this->render_partial('export_templates/_performancerecord_projects',
                ['relations' => $controller->getProjects($card), 'sections' => $sections]) ?>
        <?php endforeach ?>
    </body>
</html>