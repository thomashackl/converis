<form class="default" action="<?= $controller->link_for('export/create') ?>" method="post">
    <fieldset>
        <legend>
            <?= dgettext('converisplugin', 'Berichtszeitraum') ?>
        </legend>
        <section class="col-3">
            <label for="start-date">
                <?= dgettext('converisplugin', 'Beginn') ?>
            </label>
            <input type="text" name="start_date" id="start-date" value="<?= $startDate ?>" data-date-picker>
        </section>
        <section class="col-3">
            <label for="end-date">
                <?= dgettext('converisplugin', 'Ende') ?>
            </label>
            <input type="text" name="end_date" id="end-date" value="<?= $endDate ?>" data-date-picker>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= dgettext('converisplugin', 'Exportvorlage') ?>
        </legend>
        <section>
            <label for="template">
                <?= dgettext('converisplugin', 'Vorlage wählen') ?>
            </label>
            <select name="template" id="template">
                <?php foreach ($templates as $index => $data) : ?>
                <option value="<?= htmlReady($index) ?>">
                    <?= htmlReady($data['name']) ?>
                </option>
                <?php endforeach ?>
            </select>
        </section>
    </fieldset>
    <input type="hidden" name="organisation_id" value="<?= $organisationId ?>">
    <input type="hidden" name="institute_id" value="<?= $instituteId ?>">
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('converisplugin', 'Bericht erzeugen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->link_for('project/list', $organisationId)) ?>
    </footer>
</form>