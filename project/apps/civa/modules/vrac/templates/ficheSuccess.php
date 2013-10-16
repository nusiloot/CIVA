<?php if ($vrac->isSupprimable($user->_id)): ?>
	<div class="btn_header">
		<a id="btn_precedent" href="<?php echo url_for('vrac_supprimer', $vrac) ?>">
			<img alt="Retourner à l'étape précédente" src="/images/boutons/btn_supprimer_contrat.png">
		</a>
	</div>
<?php endif; ?>

<div id="contrats_vrac" class="fiche_contrat">
	<div class="fond">		
		<?php use_helper('Date') ?>

		<?php if ($vrac->isValide() || $vrac->isAnnule()): ?>
			<h2 class="statut_contrat clearfix">Contrat en vrac<?php if ($vrac->numero_archive): ?> numéro <?php echo $vrac->numero_archive ?><?php endif; ?><span><?php echo VracClient::getInstance()->getStatutLibelle($vrac->valide->statut) ?></span></h2>
		<?php else: ?>
			<h2 class="statut_contrat clearfix">Validation de votre contrat en vrac</h2>
		<?php endif; ?>

		<?php include_partial('vrac/soussignes', array('vrac' => $vrac)) ?>

		<?php 
			if($validation->hasPoints()) {
				include_partial('global/validation', array('validation' => $validation)); 
			}
		?>
		
		<?php include_partial('vrac/produits', array('vrac' => $vrac, 'form' => $form)) ?>

		<?php if (!$vrac->isValide()): ?>
			<?php if ($vrac->hasValide($user->_id)): ?>
				<p>Vous avez validé le contrat le <strong><?php echo format_date($vrac->getUserDateValidation($user->_id), 'p', 'fr') ?></strong></p>
			<?php else: ?>
				<a href="<?php echo url_for('vrac_validation', array('sf_subject' => $vrac)) ?>">
					<img alt="Valider le contrat" src="/images/boutons/btn_valider_final.png">
				</a>
				<p>En cas d'erreur sur le contrat, veuillez contacter votre interlocuteur commercial.</p>
			<?php endif; ?>
		<?php endif; ?>
		<?php if($vrac->isCloture()): ?>
			<p>Contrat en vrac numéro <?php echo $vrac->numero_archive ?> cloturé le <strong><?php echo format_date($vrac->valide->date_cloture, 'p', 'fr') ?></strong></p>
		<?php endif; ?>
	</div>
	<input type="image" src="/images/boutons/btn_previsualiser.png" alt="Prévisualiser" name="boutons[previsualiser]" id="previsualiserContrat">
	<?php include_partial('vrac/generationPdf', array('vrac' => $vrac)); ?>
</div>