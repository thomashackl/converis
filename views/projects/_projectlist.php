<table class="default sortable-table" data-sortlist="[[0,0]]" width="100%">
    <colgroup>
        <col>
        <col width="150">
        <col width="150">
        <col width="150">
    </colgroup>
    <thead>
        <tr>
            <th data-sort="text"><?= dgettext('converisplugin', 'Name') ?></th>
            <th data-sort="numeric"><?= dgettext('converisplugin', 'Beginn') ?></th>
            <th data-sort="numeric"><?= dgettext('converisplugin', 'Ende') ?></th>
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
                <td><?= htmlReady($project->project_status->name_1) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
