<?php if ($onglets->getItemsLieu($appellation_key)->hasManyLieu()): ?>
<ul class="sous_onglets">
    <?php foreach($onglets->getItemsLieu($appellation_key) as $key => $lieu): ?>
        <?php if ($key != 'lieu'): ?>
        <li <?php if ($onglets->getCurrentKeyAppellation() == $appellation_key && $onglets->getCurrentKeyLieu() == $key): ?>class="ui-tabs-selected"<?php endif; ?>>
            <a href="<?php echo url_for($onglets->getUrl('recolte', $appellation_key, $key)->getRawValue()) ?>"><?php echo $lieu->getConfig()->libelle ?></a>
        </li>
        <?php endif; ?>
    <?php endforeach; ?>
        <?php if (!$declaration->recolte->get($appellation_key)->hasAllLieu()): ?>
        <li class="ajouter ajouter_lieu"><a href="#">Ajouter un lieu dit</a></li>
        <?php endif; ?>
</ul>
<?php endif; ?>