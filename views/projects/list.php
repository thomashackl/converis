<?php if (count($projects) > 0) : ?>
    <h2><?= dgettext('converisplugin', 'Forschungsprojekte') ?></h2>
    <?= $this->render_partial('projects/_projectlist',
        ['projects' => $projects, 'is_fak' => $institute['is_fak']]) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('converisplugin', 'Es wurden keine Forschungsprojekte gefunden.')) ?>
<?php endif ?>
