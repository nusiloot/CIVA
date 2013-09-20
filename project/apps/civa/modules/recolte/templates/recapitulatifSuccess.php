<?php use_helper('Float') ?>
<?php include_partial('global/etapes', array('etape' => 3)) ?>
<?php include_partial('global/actions', array('etape' => 3, 'help_popup_action'=>$help_popup_action)) ?>

<?php include_partial('global/errorMessages', array('form' => $form)); ?>

<!-- #principal -->
			<form id="principal" action="" method="post" onsubmit="return valider_can_submit();">
            <?php echo $form->renderHiddenFields(); ?>
            <?php include_partial('ongletsAppellations', array('declaration' => $declaration,
                                                               'onglets' => $onglets)); ?>
				<!-- #application_dr -->
				<div id="application_dr" class="clearfix">
				
					<!-- #gestion_recolte -->
					<div id="gestion_recolte" class="clearfix gestion_recolte_recapitulatif">
						<?php include_partial('ongletsCepages', array('declaration' => $declaration,
                                                                                              'onglets' => $onglets,
                                                                                              'recapitulatif' => true)); ?>

						<div class="recapitualtif clearfix" id="donnees_recolte_sepage">
					       <p class="intro"></p>
				            <div id="total_appelation">
								<h2 class="titre_section">
                                    <?php if($isGrandCru){ ?>
                                    Total Lieu-dit
                                    <?php }else{ ?>
                                    Total Appellation
                                    <?php } ?>
                                </h2>
								<div class="contenu_section">
									<div class="bloc_gris">
										<table cellspacing="0" cellpadding="0" class="table_donnees">
											<tbody>
                                                <?php if (count($form->getEmbeddedForms()) > 1): ?>
                                                <tr>
                                                    <td></td>
                                                    <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                                        <td class="valeur"><?php echo $form_item->getObject()->getLibelle() ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                                <?php endif; ?>
												<tr>
													<td>Superficie <span class="unites">(ares)</span> :</td>
                                                    <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                                    <td class="valeur"><?php echoFloat($form_item->getObject()->getTotalSuperficie()); ?></td>
                                                    <?php endforeach; ?>
												</tr>
												<tr>
													<td>Volume total récolté <span class="unites">(hl)</span> :</td>
                                                    <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                                    <td class="valeur"><?php echoFloat($form_item->getObject()->getTotalVolume()) ;?></td>
                                                    <?php endforeach; ?>
												</tr>
                                                <?php if($appellationlieu->getConfig()->existRendement()): ?>
                                                    <tr>
                                                        <td>Volume revendiqué <span class="unites">(hl)</span> :</td>
                                                        <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                                        <td class="valeur"><?php echoFloat($form_item->getObject()->getVolumeRevendique()); ?></td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php if($appellationlieu->getConfig()->existRendement()): ?>
                                <h2 class="titre_section" style="margin-top: 15px;">
                                    Usages industriels
                                </h2>
                                <div class="contenu_section">
                                    <div class="bloc_gris">
                                        <table cellspacing="0" cellpadding="0" class="table_donnees">
                                            <tbody>
                                                <?php if (count($form->getEmbeddedForms()) > 1): ?>
                                                <tr>
                                                    <td></td>
                                                    <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                                        <td class="valeur"><?php echo $form_item->getObject()->getLibelle() ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <td>Dépassement <span class="unites">(hl)</span> :</td>
                                                    <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                                        <td class="valeur"><?php echoFloat($form_item->getObject()->getDplc()) ; ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                                <tr>
                                                    <td>Usages industriels saisis <span class="unites">(hl)</span> : <a href="" class="msg_aide" rel="help_popup_DR_recap_appellation_usage_industriel" title="Message aide"></a></td>
                                                    <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                                        <td class="valeur saisi">
                                                                <?php if(isset($form[$key]['lies'])): ?>
                                                                    <?php echo $form[$key]['lies']->render(array('class' => 'num recapitulatif_lies')) ?>
                                                                <?php else: ?>
                                                                    <input type="text" readonly="readonly" class="num readonly recapitulatif_lies" value="<?php echoFloat($form_item->getObject()->lies) ?>" />
                                                                <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                                <tr>
                                                    <td>Usages industriels final <span class="unites">(hl)</span> :</td>
                                                    <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                                        <td class="valeur"><?php echoFloat($form_item->getObject()->getUsagesIndustriels()) ?> hl</td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <?php if ($form->isLiesSaisisables()): ?>
                                        <div class="btn">
                                            <input type="image" src="/images/boutons/btn_valider_2.png" alt="Valider" type="submit">
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            <?php endif; ?>
                            </div>                  
							<div id="recap_ventes">
								<h2 class="titre_section">Récapitulatif des ventes <a href="" class="msg_aide" rel="help_popup_DR_recap_vente" title="Message aide"></a></h2>
								<div class="contenu_section">
                                    <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                    <?php if (count($form->getEmbeddedForms()) > 1): ?>    
                                    <p><?php echo $form_item->getObject()->getLibelle(); ?></p>
                                    <?php endif; ?>
									<div class="bloc_gris">
                                        <?php if($form_item->getObject()->hasAcheteurs() > 0): ?>
										<table id="table_ventes_<?php echo $key ?>" cellspacing="0" cellpadding="0" class="table_donnees">
											<thead>
												<tr>
													<th><img alt="Acheteurs et caves" src="/images/textes/acheteurs_caves.png"></th>
													<th class="cvi">n°CVI</th>
													<th class="commune"><span>Commune</span></th>				
													<th><span>Superficie</span></th>
													<th><span>Volume total</span></th>
													<th><span>Dont dépassement</span></th>
												</tr>
											</thead>
											<tbody>
                                            <?php foreach($form_item->getObject()->acheteurs as $type => $acheteurs_type) : ?>
                                                <?php foreach($acheteurs_type as $cvi => $info): ?>
                                                    <tr>
                                                            <td class="nom">
                                                                <?php echo $info->getNom();?>
                                                                <?php if ($type == 'mouts'): ?>
                                                                <br />
                                                                <small>(Acheteur de mouts)</small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="cvi alt"><?php echo $cvi; ?></td>
                                                            <td class="commune"><?php echo $info->getCommune(); ?></td>
                                                            <?php if($form_item->getObject()->getConfig()->existRendement()): ?>
                                                                <td class="superficie alt <?php echo ($form[$key]['acheteurs'][$type][$cvi]['superficie']->hasError()) ? sfConfig::get('app_css_class_field_error') : null ?>"><?php echo $form[$key]['acheteurs'][$type][$cvi]['superficie']->render(array("class" => 'num')); ?> ares</td>
                                                            <?php else: ?>
                                                                <td class="superficie"></td>
                                                            <?php endif; ?>
                                                            <td class="volume"><?php echoFloat($info->getVolume()); ?> hl</td>
                                                            <?php if($form_item->getObject()->getConfig()->existRendement()) : ?>
                                                                <td class="dplc alt <?php echo ($form[$key]['acheteurs'][$type][$cvi]['dontdplc']->hasError()) ? sfConfig::get('app_css_class_field_error') : null ?>"><?php echo $form[$key]['acheteurs'][$type][$cvi]['dontdplc']->render(array("class" => 'num')); ?> hl</td>
                                                            <?php else: ?>
                                                                <td class="dplc"></td>
                                                            <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
											</tbody>
										</table>
										<div class="btn">
											<input type="image" alt="Valider" src="/images/boutons/btn_valider_2.png">
										</div>
                                        <?php else: ?>
                                        <p> Aucune vente </p>
                                        <?php endif; ?>
									</div>
                                    <?php endforeach; ?>
                                </div>
							</div>
						</div>
                    </div>
					<!-- fin #gestion_recolte -->
					<?php include_partial('boutonAppellation', array('onglets' => $onglets, 'is_recap'=>true)) ?>
			    </div>
				<!-- fin #application_dr -->
				
				<?php include_partial('boutons') ?>

                <?php include_partial('initRendementsMax') ?>

                <?php include_partial('popupDrPrecedentes' , array('campagnes'=>$campagnes)) ?>

				
			</form>
			<!-- fin #principal -->

                        <?php include_partial('recolte/popupAjoutOnglets', array('onglets' => $onglets,
                                                         'form_appellation' => $form_ajout_appellation,
                                                         'form_lieu' => $form_ajout_lieu,
                                                         'url_lieu' => $url_ajout_lieu)) ?>

                        <script type="text/javascript">
                            function valider_can_submit()
                            {
                                <?php foreach($form->getEmbeddedForms() as $key => $form_item): ?>
                                    <?php if($form_item->getObject()->acheteurs->count() > 0 && $form_item->getObject()->getConfig()->existRendement()): ?>
                                    var total_superficie = <?php echoFloat( $form_item->getObject()->getTotalSuperficie()); ?>;
                                    var total_dontdplc = <?php echoFloat( $form_item->getObject()->getDplc()); ?>;
                                    var sum_superficie = 0;
                                    var sum_dont_dplc = 0;
                                    $('#recap_ventes table#table_ventes_<?php echo $key ?> tr td.superficie input.num').each(function() {
                                        if ($(this).val()) {
                                            sum_superficie += parseFloat($(this).val());
                                        }
                                    });
                                    sum_superficie = trunc(sum_superficie, 2);

                                    $('#recap_ventes table#table_ventes_<?php echo $key ?> tr td.dplc input.num').each(function() {
                                        if ($(this).val()) {
                                            sum_dont_dplc += parseFloat($(this).val());
                                        }
                                    });
                                    sum_dont_dplc = trunc(sum_dont_dplc, 2);

                                    var dplc_sup_volume = false;
                                    $('#recap_ventes table#table_ventes_<?php echo $key ?> tr td.dplc input.num').each(function() {
                                        if (!$(this).val()) {
                                            return;
                                        }
                                        volume_achete = parseFloat($(this).parent().parent().find('td.volume').html().replace(' hl', ''));
                                        if(parseFloat($(this).val()) <= parseFloat(volume_achete)) {

                                            return;
                                        }

                                        dplc_sup_volume = true;
                                    });

                                    if (sum_superficie > total_superficie) {
                                        $('#popup_msg_erreur').html('<p><?php include_partial('global/message', array('id'=>'err_dr_recap_vente_popup_superficie_trop_eleve')); ?></p>');
                                        openPopup($('#popup_msg_erreur'), 0);
                                        return false;
                                    }
                                    if (sum_dont_dplc > total_dontdplc) {
                                        $('#popup_msg_erreur').html('<p><?php include_partial('global/message', array('id'=>'err_dr_recap_vente_popup_dplc_trop_eleve')); ?></p>');
                                        openPopup($('#popup_msg_erreur'), 0);
                                        return false;
                                    }
                                    if (dplc_sup_volume) {
                                        $('#popup_msg_erreur').html('<p><?php include_partial('global/message', array('id'=>'err_dr_recap_vente_popup_dplc_superieur_volume')); ?></p>');
                                        openPopup($('#popup_msg_erreur'), 0);
                                        return false;
                                    }
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                return true;
                            }
                        </script>
                        <div id="popup_msg_erreur" class="popup_ajout" title="Erreur !">
                        </div>
