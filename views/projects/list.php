<?php if (count($free) > 0) : ?>
    <h2><?= dgettext('converisplugin', 'Freie Projekte') ?></h2>
    <?= $this->render_partial('projects/_projectlist',
        ['projects' => $free, 'is_fak' => $institute['is_fak']]) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('converisplugin', 'Es wurden keine freien Projekte gefunden.')) ?>
<?php endif ?>
<?php if (count($third_party) > 0) : ?>
    <h2><?= dgettext('converisplugin', 'Drittmittelprojekte') ?></h2>
    <?= $this->render_partial('projects/_projectlist',
        ['projects' => $third_party, 'is_fak' => $institute['is_fak']]) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('converisplugin', 'Es wurden keine Drittmittelprojekte gefunden.')) ?>
<?php endif ?>
