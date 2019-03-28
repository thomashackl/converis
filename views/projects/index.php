<form class="default" action="<?= $controller->url_for('projects/list') ?>" method="post">
    <header>
        <h1>
            <?= dgettext('converisplugin', 'Einrichtung auswählen, deren Forschungsprojekte geladen werden sollen') ?>
        </h1>
    </header>
    <section>
        <select name="institute" class="nested-select">
            <?php foreach ($institutes as $i) : ?>
                <option value="<?= $i['Institut_id'] ?>"><?= ($i['is_fak'] == 1 ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;') .
                    htmlReady($i['Name']) ?></option>
            <?php endforeach ?>
        </select>
    </section>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('converisplugin', 'Projekte laden'), 'load') ?>
    </footer>
</form>