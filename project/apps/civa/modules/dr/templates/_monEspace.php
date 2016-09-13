<div id="nouvelle_declaration">
<?php if (DRSecurity::getInstance($etablissement, $sf_user->getDeclaration())->isAuthorized(DRSecurity::EDITION)): ?>
	<?php include_component('dr', 'monEspaceEnCours', array('dr' => $dr, 'etablissement' => $etablissement, 'campagne' => $campagne)) ?>
<?php elseif ($dr && $dr->isValideeTiers()): ?>
    <?php include_partial('dr/monEspaceValidee', array('dr' => $dr)) ?>
<?php elseif(CurrentClient::getCurrent()->exist('dr_non_ouverte') && CurrentClient::getCurrent()->dr_non_ouverte == 1): ?>
    <?php include_partial('dr/monEspaceNonOuverte') ?>
<?php else: ?>
    <?php include_partial('dr/monEspaceNonEditable') ?>
<?php endif; ?>
</div>
