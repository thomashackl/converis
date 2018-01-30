<form class="default" action="<?= $controller->url_for('projects/get_projects') ?>" method="post">
    <header>
        <h1>Einrichtung auswählen, deren Forschungsprojekte geladen werden sollen:</h1>
    </header>
    <select name="institute">
        <?php foreach ($institutes as $i) : ?>
            <option value="<?= $i['Institut_id'] ?>"><?= htmlReady($i['Name']) ?></option>
        <?php endforeach ?>
    </select>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept('Projekte laden', 'load') ?>
        <script type="text/javascript">
            //<!--
            $('select[name="institute"]').select2();
            //-->
        </script>
    </footer>
</form>