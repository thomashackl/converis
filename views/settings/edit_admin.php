<form class="default" action="<?= $controller->link_for('settings/save_role') ?>" method="post">
    <section>
        <label for="type">
            <?= dgettext('converisplugin', 'Art der Berechtigung') ?>
        </label>
        <select name="type" id="type">
            <option value="local">
                <?= dgettext('converisplugin', 'Einrichtungsbezogen') ?>
            </option>
            <option value="local">
                <?= dgettext('converisplugin', 'Global') ?>
            </option>
        </select>
    </section>
    <input type="hidden" name="role_id" value="<?= $admin->id ?>">
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('converisplugin', 'Berechtigung erteilen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->link_for('settings/roles')) ?>
    </footer>
</form>