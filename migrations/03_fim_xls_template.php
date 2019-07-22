<?php

class FIMXLSTemplate extends Migration {

    public function description()
    {
        return 'FIM export template in XLSX format';
    }

    public function up()
    {

        $templates = Config::get()->CONVERIS_REPORT_TEMPLATES;

        $templates['institute'] = [
            'pdf_fim' => [
                'name' => 'Forschungsbericht FIM (PDF)',
                'controller' => 'fim',
                'action' => 'pdf'
            ],
            'xls_fim' => [
                'name' => 'Forschungsbericht FIM (Excel)',
                'controller' => 'fim',
                'action' => 'xls'
            ]
        ];

        Config::get()->store('CONVERIS_REPORT_TEMPLATES', $templates);
        Config::get()->CONVERIS_REPORT_TEMPLATES = $templates;
    }

    public function down()
    {
        $templates = Config::get()->CONVERIS_REPORT_TEMPLATES;

        $templates['institute'] = [
            'pdf_fim' => [
                'name' => 'Forschungsbericht FIM',
                'controller' => 'fim',
                'action' => 'pdf'
            ]
        ];

        Config::get()->store('CONVERIS_REPORT_TEMPLATES', json_encode($templates));
        Config::get()->CONVERIS_REPORT_TEMPLATES = $templates;
    }

}