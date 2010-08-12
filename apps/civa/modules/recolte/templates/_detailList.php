<div id="col_scroller">
    <div id="col_scroller_cont" class="clearfix">
        <?php foreach ($details as $key => $detail): ?>
        <?php if (in_array($detail_action_mode, array('add', 'update')) && $key == $detail_key): ?>
        <?php include_partial('detailItemForm', array('detail' => $detail, 'key' => $key, 'onglets' => $onglets, 'form' => $form, 'is_new' => ($detail_action_mode == 'add'))) ?>
        <?php else: ?>
        <?php
                    include_partial('detailItem', array('detail' => $detail,
                        'key' => $key,
                        'onglets' => $onglets,
                        'acheteurs_negoce' => $acheteurs_negoce,
                        'acheteurs_cave' => $acheteurs_cave,
                        'has_acheteurs_mout' => $has_acheteurs_mout,
                        'acheteurs_mouts' => $acheteurs_mout)) ?>
        <?php endif; ?>
<?php endforeach; ?>
        <a href="<?php echo url_for($onglets->getUrl('recolte_add')->getRawValue()) ?>" id="ajout_col"><img src="/images/boutons/btn_ajouter_colonne.png" alt="Ajouter une colonne" /></a>
    </div>
</div>
