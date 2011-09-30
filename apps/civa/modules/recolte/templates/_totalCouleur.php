<?php use_helper('civa') ?>
<div id="col_recolte_totale" class="col_recolte col_total">
    <h2>Total 
    <?php if($onglets->getCurrentAppellation()->getConfig()->hasManyLieu()): ?>
        <?php echo $couleur->getConfig()->libelle ?>
    <?php else: ?>
        <?php echo $onglets->getCurrentAppellation()->getConfig()->libelle ?>
    <?php endif; ?>
        <?php echo $couleur->getKey() ?>
    </h2>



    <div class="col_cont">
        <?php if ($onglets->getCurrentAppellation()->getConfig()->hasLieuEditable()): ?>
            <p class="lieu">&nbsp;</p>
        <?php endif; ?>
        <p class="denomination">&nbsp;</p>
        <p class="mention">&nbsp;</p>
        <p class="superficie">
               <input id="appellation_total_superficie_orig" type="hidden" value="<?php echoFloat($couleur->getTotalSuperficie()); ?>" />
            <input id="appellation_total_superficie" type="text" readonly="readonly" value="<?php echoFloat($couleur->getTotalSuperficie()); ?>" />
        </p>

        <?php if (!$onglets->getCurrentCepage()->getConfig()->hasNoNegociant()): ?>
        <div class="vente_raisins">
                <?php include_partial('itemAcheteurs', array('acheteurs' => $acheteurs->negoces,
                                                              'acheteurs_value' => $couleur->getVolumeAcheteurs('negoces')));?>
        </div>
        <?php endif; ?>

        <?php if (!$onglets->getCurrentCepage()->getConfig()->hasNoCooperative()): ?>
        <div class="caves">
            <?php
                include_partial('itemAcheteurs', array('acheteurs' => $acheteurs->cooperatives,
                                                              'acheteurs_value' => $couleur->getVolumeAcheteurs('cooperatives')))
                ?>
        </div>
        <?php endif; ?>

        <?php if ($has_acheteurs_mout && !$onglets->getCurrentCepage()->getConfig()->hasNoMout()): ?>
        <div class="mouts">
            <?php
                include_partial('itemAcheteurs', array('acheteurs' => $acheteurs->mouts,
                                                              'acheteurs_value' => $couleur->getVolumeAcheteurs('mouts')))
                ?>
        </div>
        <?php endif; ?>

        <p class="vol_place">
   <input type="hidden" id="appellation_total_cave_orig" value="<?php echoFloat($couleur->getTotalCaveParticuliere()); ?>" />
   <input type="text" id="appellation_total_cave" readonly="readonly" value="<?php echoFloat( $couleur->getTotalCaveParticuliere()); ?>" />
   </p>
        <p class="vol_total_recolte">
   <input id="appellation_total_volume_orig" type="hidden" value="<?php echoFloat( $couleur->getTotalVolume()); ?>" />
   <input id="appellation_total_volume" type="text" readonly="readonly" value="<?php echoFloat( $couleur->getTotalVolume()); ?>" />
   </p>
        <ul class="vol_revendique_dplc">
    <?php if ($couleur->getConfig()->hasRendement()): ?>
    <li class="rendement <?php if ($couleur->getDplcTotal()) echo 'alerte'; ?>">Rdt : <strong><span id="appellation_current_rendement"><?php echo round($couleur->getRendementRecoltant(),0); ?></span>&nbsp;hl/ha</strong><span class="picto_rdt_aide_col_total"><a href="" class="msg_aide" rel="help_popup_DR_total_appellation" title="Message aide"></a></span></li>
    <?php endif; ?>
       <?php if ($couleur->getConfig()->hasRendement()): ?>
                <?php if ($couleur->getConfig()->hasRendementCouleur()): ?>
		    <input type="hidden" id="appellation_max_volume" value="<?php echoFloat($couleur->getVolumeMaxCouleur()); ?>"/>
		       <input type="hidden" id="appellation_rendement" value="<?php echoFloat( $couleur->getConfig()->getRendementCouleur()); ?>"/>

                    <li>
		       <input type="hidden" id="appellation_volume_revendique_orig" readonly="readonly" value="<?php echoFloat( $couleur->getVolumeRevendiqueCouleur()); ?>" />
		       <input type="text" id="appellation_volume_revendique" readonly="readonly" value="<?php echoFloat( $couleur->getVolumeRevendiqueCouleur()); ?>" />
		       </li>
                    <li><input type="hidden" id="appellation_volume_dplc_orig" readonly="readonly" class="alerte" value="<?php echoFloat( $couleur->getDplcCouleur()); ?>"/>
                    <input type="text" id="appellation_volume_dplc" readonly="readonly" class="<?php if ($couleur->getDplcCouleur()) echo 'alerte'; ?>" value="<?php echoFloat( $couleur->getDplcCouleur()); ?>"/></li>
                <?php endif; ?>
    <?php if ($couleur->getConfig()->hasRendementCepage()) : ?>
                <li>
		<input type="hidden" id="appellation_total_revendique_sum_orig" readonly="readonly" value="<?php echoFloat($couleur->getVolumeRevendiqueTotal()); ?>" />
		<input type="text" id="appellation_total_revendique_sum" readonly="readonly" value="Σ <?php echoFloat( $couleur->getVolumeRevendiqueTotal()); ?>" />
   </li>
                <li>
   <input type="hidden" id="appellation_total_dplc_sum_orig" value="<?php echoFloat($couleur->getDplcTotal()); ?>"/>
   <input type="text" id="appellation_total_dplc_sum" readonly="readonly" class="<?php if ($couleur->getDplcTotal()) echo 'alerte'; ?>" value="Σ <?php echoFloat($couleur->getDplcTotal()); ?>"/>
   </li>
            <?php endif; ?>
            <?php endif; ?>

        </ul>
    </div>
</div>