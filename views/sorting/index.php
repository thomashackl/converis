<?php if (count($status) > 0) : ?>
    <table class="default status" data-update-position-url="<?= $controller->link_for('sorting/store') ?>">
        <caption>
            <?= dgettext('converisplugin', 'Projektstatus und deren Sortierung') ?>
        </caption>
        <colgroup>
            <col width="5%">
            <col width="45%">
            <col width="45%">
        </colgroup>
        <thead>
            <tr>
                <th><?= dgettext('converisplugin', '#') ?></th>
                <th><?= dgettext('converisplugin', 'Name (deutsch)') ?></th>
                <th><?= dgettext('converisplugin', 'Name (englisch)') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($status as $one) : ?>
                <tr class="status-sort" data-position="<?= $one->position ?>"
                    id="status-<?= $one->id ?>" data-status-id="<?= $one->id ?>">
                    <td><?= $one->position + 1 ?>.</td>
                    <td><?= htmlReady($one->name_1) ?></td>
                    <td><?= htmlReady($one->name_2) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php else : ?>
    <?= MessageBox::info('converisplugin', 'Es wurden keine Projektstatus gefunden.') ?>
<?php endif;
