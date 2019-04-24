<form class="default" action="<?= $controller->url_for('projects/get') ?>" method="post">
    <section>
        <h1>
            <?= dgettext('converisplugin', 'Welche Forschungsprojekte sollen angezeigt werden?') ?>
        </h1>
        <label>
            <input type="radio" name="type" value="institute" checked>
            <?= dgettext('converisplugin', 'für eine Einrichtung') ?>
        </label>
        <label>
            <input type="radio" name="type" value="user">
            <?= dgettext('converisplugin', 'für eine Person') ?>
        </label>
    </section>
    <section class="typeselect" id="type-institute">
        <label for="select-institute">
            <?= dgettext('converisplugin', 'Einrichtung wählen') ?>
        </label>
        <select name="institute_id" id="select-institute" class="nested-select">
            <?php foreach ($institutes as $i) : ?>
                <option value="<?= $i['Institut_id'] ?>"><?= ($i['is_fak'] == 1 ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;') .
                    htmlReady($i['Name']) ?></option>
            <?php endforeach ?>
        </select>
    </section>
    <section id="type-user" class="typeselect hidden-js">
        <label for="select-user">
            <?= dgettext('converisplugin', 'Person wählen') ?>
        </label>
        <?= $usersearch->render() ?>
    </section>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('converisplugin', 'Projekte laden'), 'load') ?>
    </footer>
</form>