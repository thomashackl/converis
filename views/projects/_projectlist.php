<table class="default sortable-table" data-sortlist="[[0,0]]" width="100%">
    <colgroup>
        <col width="300">
        <col>
        <?php if ($is_fak == 1) : ?>
            <col width="300">
        <?php endif ?>
        <col width="75">
        <col width="75">
        <col width="150">
    </colgroup>
    <thead>
        <tr>
            <th data-sort="text"><?= dgettext('converisplugin', 'Kurzbezeichnung') ?></th>
            <th data-sort="text"><?= dgettext('converisplugin', 'Langbezeichnung') ?></th>
            <?php if ($is_fak == 1) : ?>
                <th data-sort="text"><?= dgettext('converisplugin', 'Einrichtung') ?></th>
            <?php endif ?>
            <th data-sort="numeric"><?= dgettext('converisplugin', 'Beginn') ?></th>
            <th data-sort="numeric"><?= dgettext('converisplugin', 'Ende') ?></th>
            <th data-sort="text"><?= dgettext('converisplugin', 'Status') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $project) : ?>
            <tr>
                <td><?= htmlReady($project->kurzbezeichnung) ?></td>
                <td><?= htmlReady($project->langbezeichnung) ?></td>
                <?php if ($is_fak == 1) : ?>
                    <?php
                        $institutes = explode('|', preg_replace(
                            '/(.*) \(.+\)/',
                                    '$1',
                                    htmlReady($project->interne_organisationen)
                        ));
                    ?>
                    <td>
                        <?php if (count($institutes) == 1) : ?>
                            <?= $institutes[0] ?>
                        <?php else : ?>
                            <ul>
                                <?php foreach ($institutes as $i) : ?>
                                <li><?= trim($i) ?></li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                    </td>
                <?php endif ?>
                <td data-sort-value="<?= strtotime(htmlReady($project->projektbeginn)) ?>">
                    <?= htmlReady($project->projektbeginn) ?>
                </td>
                <td data-sort-value="<?= strtotime(htmlReady($project->projektende)) ?>">
                    <?= htmlReady($project->projektende) ?>
                </td>
                <td><?= htmlReady($project->projektstatus) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
