<form class="default" action="<?= $controller->link_for('settings/save_templates') ?>" method="post">
    <?php foreach ($templates as $type => $entries) : ?>
    <fieldset>
        <legend>
            <?= htmlReady($type) ?>
        </legend>
        <?php foreach ($entries as $index => $data) : ?>
        <article class="studip toggle <?= ContentBoxHelper::classes($index) ?>" id="<?= $type ?>">
            <header>
                <h1>
                    <a href="<?= ContentBoxHelper::href($index, array('contentbox_type' => 'news')) ?>">
                        <?= Icon::create('news', 'clickable')->asImg(); ?>
                        <?= htmlReady($data['name']); ?>
                    </a>
                </h1>
            </header>
            <section>
                <label for="name">
                    <?= dgettext('converisplugin', 'Bezeichnung') ?>
                </label>
                <input type="text" name="templates[<?= $type ?>][<?= $index ?>][name]"
                       id="name" maxlength="100" value="<?= htmlReady($data['name']) ?>">
            </section>
            <section class="col-3">
                <label for="controller">
                    <?= dgettext('converisplugin', 'Aufzurufender Controller') ?>
                </label>
                <input type="text" name="templates[<?= $type ?>][<?= $index ?>][controller]"
                       id="controller" maxlength="25" value="<?= htmlReady($data['controller']) ?>">
            </section>
            <section class="col-3">
                <label for="action">
                    <?= dgettext('converisplugin', 'Aufzurufende Action') ?>
                </label>
                <input type="text" name="templates[<?= $type ?>][<?= $index ?>][action]"
                       id="action" maxlength="25" value="<?= htmlReady($data['action']) ?>">
            </section>
        </article>
        <?php endforeach ?>
    </fieldset>
    <?php endforeach ?>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('converisplugin', 'Einstellungen speichern'), 'submit') ?>
    </footer>
</form>