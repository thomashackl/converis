<h2><?= sprintf(
            dngettext('converisplugin',
                'Ein Forschungsprojekt für %2$s',
                '%1$u Forschungsprojekte für %2$s',
                count($projects)),
            count($projects), $name) ?></h2>
<table class="default sortable-table" data-sortlist="[[4,0],[0,0]]" width="100%">
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
                    <?= htmlReady(strtotime($project->start_date) > 0 ?
                        date('d.m.Y', strtotime($project->start_date)) :
                        '-') ?>
                </td>
                <td data-sort-value="<?= strtotime(htmlReady($project->end_date)) ?>">
                    <?= htmlReady(strtotime($project->end_date) > 0 ?
                        date('d.m.Y', strtotime($project->end_date)) :
                        '-') ?>
                </td>
                <td>
                    <?php if ($project->related_cards != null && count($project->related_cards) > 0) : ?>
                        <?php if (count($project->related_cards) == 1) : $rel = $project->related_cards->first() ?>
                            <?= htmlReady(implode(' ', [
                                $rel->card->person->academic_title,
                                $rel->card->person->first_name,
                                $rel->card->person->last_name
                            ])) ?>
                            <?php if ($rel->role != 0) : ?>
                                (<?= htmlReady($rel->role_object->name_1) ?>)
                            <?php endif ?>
                        <?php else : ?>
                            <ul>
                                <?php foreach ($project->related_cards as $rel) : ?>
                                    <li>
                                        <?= htmlReady(implode(' ', [
                                            $rel->card->person->academic_title,
                                            $rel->card->person->first_name,
                                            $rel->card->person->last_name
                                        ])) ?>
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
