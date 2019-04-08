<form class="default" action="<?= $controller->link_for('export/pdf_fim') ?>" method="post">
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
    <input type="hidden" name="organisation_id" value="<?= $organisationId ?>">
    <input type="hidden" name="institute_id" value="<?= $instituteId ?>">
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('converisplugin', 'Bericht erzeugen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->link_for('project/list')) ?>
    </footer>
</form>