<?php if ($relations['count']['third_party'] > 0) : ?>
    <h2>Drittmittelprojekte</h2>
    <?php foreach ($sections['third_party'] as $index => $section) : ?>
        <?php if (count($relations[$index]) > 0 && $dataExists) : ?>
            <pagebreak/>
        <?php endif ?>
        <?php if (count($relations[$index]) > 0) : $dataExists = true; ?>
        <table>
            <colgroup>
                <col width="16%">
                <col width="20%">
                <col width="8%">
                <col width="16%">
                <col width="16%">
                <col width="16%">
                <col width="8%">
            </colgroup>
            <thead>
                <tr class="section">
                    <td colspan="7">
                        <?= htmlReady($section['title']) ?>
                    </td>
                </tr>
                <tr class="columns">
                    <td>
                        <?= implode("</td>\n<td>", $section['columns']) ?>
                    </td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($relations[$index] as $r) : ?>
                <tr>
                    <td>
                        <?= nl2br(implode("</td>\n<td>", $controller->makeTexts($r))) ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php endif ?>
    <?php endforeach ?>
    <section class="declined">
        <span class="strong">Anträge abgelehnt:</span>
        <?= count($relations['third_party_declined']) ?>
    </section>
    <section class="sorting">
        Alphabetisch sortiert nach Mittelgeber/Förderprogramm bzw.
        Vertragspartner und anschließend nach Kurzbezeichnung.
    </section>
<?php endif ?>
<?php if ($relations['count']['free'] > 0) : ?>
    <?php if (count($relations[$index]) > 0 && $dataExists) : ?>
        <pagebreak/>
    <?php endif ?>
    <h2>Freie Projekte</h2>
    <?php foreach ($sections['free'] as $index => $section) : ?>
        <?php if (count($relations[$index]) > 0) : $dataExists = true; ?>
            <table>
                <colgroup>
                    <col width="22%">
                    <col width="6%">
                    <col width="36%">
                    <col width="25%">
                    <col width="11%">
                </colgroup>
                <thead>
                    <tr class="section">
                        <td colspan="7">
                            <?= htmlReady($section['title']) ?>
                        </td>
                    </tr>
                    <tr class="columns">
                        <td>
                            <?= nl2br(implode("</td>\n<td>", $section['columns'])) ?>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($relations[$index] as $r) : ?>
                        <tr>
                            <td>
                                <?= implode("</td>\n<td>", $controller->makeTexts($r)) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    <?php endforeach ?>
    <section class="sorting">
        Alphabetisch sortiert nach Kurzbezeichnung.
    </section>
<?php endif;