<ul id="onglets_majeurs" class="clearfix onglets_courts">
    <?php foreach($onglets->getItemsAppellation() as $key => $appellation): ?>
        <li <?php if ($onglets->getCurrentKeyAppellation() == $key): ?>class="ui-tabs-selected"<?php endif; ?>>
            <a href="<?php echo url_for($onglets->getUrl($key)->getRawValue()) ?>"><?php echo str_replace('AOC', '<span>AOC</span> <br />',$configuration->get($appellation->getHash())->libelle) ?></a>
            <?php include_partial('ongletsLieux', array('declaration' => $declaration,
                                                        'configuration' => $configuration,
                                                        'appellation_key' => $key,
                                                        'onglets' => $onglets)); ?>
        </li>
    <?php endforeach; ?>
</ul>