<table class="default sortable-table" data-sortlist="[[0,0]]" width="100%">
    <colgroup>
        <col>
        <col width="100">
        <col width="100">
        <col width="400">
        <col width="150">
    </colgroup>
    <thead>
        <tr>
            <th data-sort="text"><?= dgettext('converisplugin', 'Name') ?></th>
            <th data-sort="numeric"><?= dgettext('converisplugin', 'Beginn') ?></th>
            <th data-sort="numeric"><?= dgettext('converisplugin', 'Ende') ?></th>
            <th><?= dgettext('converisplugin', 'Personen') ?></th>
            <th data-sort="text"><?= dgettext('converisplugin', 'Status') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $project) : ?>
            <tr>
                <td><?= htmlReady($project->name) ?> (<?= $project->converis_id ?>)</td>
                <td data-sort-value="<?= strtotime(htmlReady($project->start_date)) ?>">
                    <?= htmlReady(strtotime($project->start_date) > 0 ? $project->start_date : '-') ?>
                </td>
                <td data-sort-value="<?= strtotime(htmlReady($project->end_date)) ?>">
                    <?= htmlReady(strtotime($project->end_date) > 0 ? $project->end_date : '-') ?>
                </td>
                <td>
                    <?php if ($project->related_persons != null && count($project->related_persons) > 0) : ?>
                        <?php if (count($project->related_persons) == 1) : $rel = $project->related_persons->first() ?>
                            <?= htmlReady(implode(' ', [$rel->person->academic_title, $rel->person->first_name, $rel->person->last_name])) ?>
                            <?php if ($rel->role != 0) : ?>
                                (<?= htmlReady($rel->role_object->name_1) ?>)
                            <?php endif ?>
                        <?php else : ?>
                            <ul>
                                <?php foreach ($project->related_persons as $rel) : ?>
                                    <li>
                                        <?= htmlReady(implode(' ', [$rel->person->academic_title, $rel->person->first_name, $rel->person->last_name])) ?>
                                        <?php if ($rel->role != 0) : ?>
                                            (<?= htmlReady($rel->role_object->name_1) ?>)
                                        <?php endif ?>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                    <?php else : ?>
                    -
                    <?php endif ?>
                </td>
                <td><?= htmlReady($project->project_status->name_1) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
