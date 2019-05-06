<form class="default" action="<?= $controller->link_for('settings/save_templates') ?>" method="post">
    <?php foreach ($templates as $index => $data) : ?>
    <fieldset>
        <legend>
            <?= $data['name'] ?>
        </legend>
            <section>
                <label for="name">
                    <?= dgettext('converisplugin', 'Bezeichnung') ?>
                </label>
                <input type="text" name="templates[<?= $index ?>][name]"
                       id="name" maxlength="100" value="<?= htmlReady($data['name']) ?>">
            </section>
            <section class="col-3">
                <label for="controller">
                    <?= dgettext('converisplugin', 'Aufzurufender Controller') ?>
                </label>
                <input type="text" name="templates[<?= $index ?>][controller]"
                       id="controller" maxlength="25" value="<?= htmlReady($data['controller']) ?>">
            </section>
            <section class="col-3">
                <label for="action">
                    <?= dgettext('converisplugin', 'Aufzurufende Action') ?>
                </label>
                <input type="text" name="templates[<?= $index ?>][action]"
                       id="action" maxlength="25" value="<?= htmlReady($data['action']) ?>">
            </section>
    </fieldset>
    <?php endforeach ?>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('converisplugin', 'Einstellungen speichern'), 'submit') ?>
    </footer>
</form>