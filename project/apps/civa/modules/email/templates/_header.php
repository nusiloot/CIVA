Contrat vrac du <?php echo strftime('%d/%m/%Y', strtotime($vrac->valide->date_saisie)) ?>

Vendeur : <?php echo ($vrac->vendeur->intitule)? $vrac->vendeur->intitule.' '.$vrac->vendeur->raison_sociale : $vrac->vendeur->raison_sociale ?>
Acheteur : <?php echo ($vrac->acheteur->intitule)? $vrac->acheteur->intitule.' '.$vrac->acheteur->raison_sociale : $vrac->acheteur->raison_sociale ?><?php if (!$vrac->hasCourtier() && $vrac->interlocuteur_commercial->nom): ?> (votre interlocuteur : <?php echo $vrac->interlocuteur_commercial->nom ?>)<?php endif; ?>
<?php if ($vrac->hasCourtier()): ?>
Courtier : <?php echo ($vrac->acheteur->intitule)? $vrac->acheteur->intitule.' '.$vrac->acheteur->raison_sociale : $vrac->acheteur->raison_sociale ?> (votre interlocuteur : M. Chichery)
<?php endif; ?>
