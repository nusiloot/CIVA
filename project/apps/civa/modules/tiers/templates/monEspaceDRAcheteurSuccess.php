<?php include_partial('global/actions', array('etape' => 0, 'help_popup_action' => $help_popup_action)) ?>

<?php include_partial('tiers/title') ?>

<div id="application_dr" class="mon_espace_civa clearfix">

    <?php include_partial('tiers/onglets', array('active' => 'recolte_acheteur')) ?>

     <div id="espace_acheteurs" class="contenu clearfix">
 	    <?php if($sf_user->hasFlash('confirmation')) : ?>
        	<p class="flash_message"><?php echo $sf_user->getFlash('confirmation'); ?></p>
		<?php endif; ?>

      	<?php include_component('acheteur', 'monEspace', array('formUploadCsv' => $formUploadCsv)) ?>
    </div>
</div>
        