<ul id="liste_sepages">
    <?php foreach($onglets->getItemsCepage() as $key => $cepage): ?>
        <li <?php if ($onglets->getCurrentKeyCepage() == $key): ?>class="ui-tabs-selected"<?php endif; ?>>
            <a href="<?php echo url_for($onglets->getUrl(null, null, $key)->getRawValue()) ?>">
            <?php echo $cepage->libelle ?>
            <?php if ($declaration->get($onglets->getItemsCepage()->getHash())->exist($key) && $declaration->get($cepage->getHash())->detail->count() > 0): ?>
                <span>(<?php echo $declaration->get($cepage->getHash())->detail->count() ?>)</span>
            <?php endif; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>