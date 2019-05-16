<?php if (count($admins) > 0) : ?>
    <table class="default">
        <caption>
            <?= dgettext('converisplugin', 'Admins für Forschungsprojekte und -berichte') ?>
        </caption>
        <colgroup>
            <col width="250">
            <col width="100">
            <col>
            <col width="30">
        </colgroup>
        <thead>
            <th><?= dgettext('converisplugin', 'Person') ?></th>
            <th><?= dgettext('converisplugin', 'Rechte') ?></th>
            <th><?= dgettext('converisplugin', 'Einrichtungen') ?></th>
            <th><?= dgettext('converisplugin', 'Aktionen') ?></th>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin) : ?>
                <tr>
                    <td>
                        <?= htmlReady($admin->user->getFullname('full_rev_username')) ?>
                    </td>
                    <td>
                        <?= $admin->type === 'local' ?
                            dgettext('converisplugin', 'Einrichtungsbezogen') :
                            dgettext('converisplugin', 'Global') ?>
                    </td>
                    <td>
                        <?php if ($admin->institutes != null && count($admin->institutes) > 0) : ?>
                            <ul>
                                <?php foreach ($admin->institutes as $inst) : ?>
                                    <li><?= htmlReady($inst->name) ?></li>
                                <?php endforeach ?>
                            </ul>
                        <?php else : ?>
                        -
                        <?php endif ?>
                    </td>
                    <td>
                        <a href="<?= $controller->link_for('settings/delete_admin', $admin->id) ?>"
                           data-confirm="<?= sprintf(dgettext('converisplugin',
                                       'Soll die Berechtigung für %s wirklich gelöscht werden?'),
                               $admin->user->getFullname('full')) ?>">
                            <?= Icon::create('trash') ?></td>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php else : ?>
    <?= MessageBox::info(dgettext('converisprojects', 'Es wurden keine Admins zugewiesen.')) ?>
<?php endif;
