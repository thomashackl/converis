<form class="default" action="<?= $controller->link_for('settings/save_admin') ?>" method="post">
    <section>
        <label for="type">
            <?= dgettext('converisplugin', 'Art der Berechtigung') ?>
        </label>
        <select name="type" id="type">
            <option value="local">
                <?= dgettext('converisplugin', 'Einrichtungsbezogen') ?>
            </option>
            <option value="global">
                <?= dgettext('converisplugin', 'Global') ?>
            </option>
        </select>
    </section>
    <section>
        <label>
            <?= dgettext('converisplugin', 'Benutzer') ?>
            <?php if ($admin->id != '') : ?>
                <?= htmlReady($admin->user->getFullname()) ?>
            <?php else : ?>
                <?= $usersearch->render() ?>
            <?php endif ?>
        </label>
    </section>
    <input type="hidden" name="admin_id" value="<?= $admin->id ?>">
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('converisplugin', 'Berechtigung erteilen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->link_for('settings/roles')) ?>
    </footer>
</form>