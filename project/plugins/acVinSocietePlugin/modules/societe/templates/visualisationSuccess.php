<?php use_helper('Compte') ?>
<?php use_helper('Date'); ?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('societe') ?>">Contacts</a></li>
    <li class="<?php echo (!isset($etablissement) && !isset($interlocuteur)) ? "active" : "" ?>"><a href="<?php echo url_for('societe_visualisation', array('identifiant' => $societe->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($societe->getRawValue()) ?>"></span> <?php echo $societe->raison_sociale; ?></a></li>
    <?php if (isset($etablissement)): ?>
        <li class="active"><a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $etablissement->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($etablissement->getRawValue()) ?>"></span> <?php echo $etablissement->nom; ?></a></li>
    <?php endif; ?>
    <?php if (isset($interlocuteur)): ?>
        <li class="active"><a href="<?php echo url_for('compte_visualisation', array('identifiant' => $interlocuteur->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($interlocuteur->getRawValue()) ?>"></span> <?php echo ($interlocuteur->nom_a_afficher) ? $interlocuteur->nom_a_afficher : $interlocuteur->nom; ?></a></li>
    <?php endif; ?>
</ol>

<section id="principal" class="societe row">
    <div class="col-xs-12" style="<?php if (isset($etablissement) || isset($interlocuteur)): ?>opacity: 0.6<?php endif; ?>">
        <div class="list-group">
            <div class="list-group-item">
                <div class="row">
                    <h2 style="margin-top: 5px; margin-bottom: 5px;" class="col-xs-10"><span class="<?php echo comptePictoCssClass($societe->getRawValue()) ?>"></span> <?php echo $societe->raison_sociale; ?>
                        <small class="text-muted">(n° de societe : <?php echo $societe->identifiant; ?>01)</small>
                        <?php if ($modification || $reduct_rights) : ?>

                        <?php endif; ?>
                    </h2>
                    <h2 style="margin-top: 5px; margin-bottom: 5px;" class="col-xs-2 text-right">
                        <a href="<?php echo url_for('societe_modification', array('identifiant' => $societe->identifiant)); ?>" class="btn btn-default" <?php echo ($societe->isSuspendu())? 'disabled="disabled"' : ''; ?> >Modifier</a>
                    </h2>
                </div>
                <div class="row">
                    <div class="col-xs-9">
                        <p class="lead" style="margin-bottom: 5px;">
                            <span class="label label-primary"><?php echo $societe->type_societe; ?></span>
                            <?php if ($societe->statut == SocieteClient::STATUT_SUSPENDU): ?>
                                <span class="label label-danger"><?php echo $societe->statut; ?></span>
                                <?php endif; ?>
                            <small><?php if ($societe->date_creation) : ?><span class="label label-default">Crée le <?php echo format_date($societe->date_creation, 'dd/MM/yyyy'); ?></span>&nbsp;<?php endif; ?>
<?php if ($societe->date_modification) : ?>
                                    <span class="label label-default">Dernière modification le <?php echo format_date($societe->date_modification, 'dd/MM/yyyy'); ?></span>&nbsp;<?php endif; ?></small>
                        </p>
                    </div>
                    <div class="col-xs-3 text-right">
                        <a href="<?php echo url_for('societe_switch_statut', array('identifiant' => $societe->identifiant)); ?>" class="btn btn-xs <?php echo ($societe->isActif()) ? 'btn-danger' : 'btn-success' ?> "><?php echo ($societe->isActif()) ? 'Suspendre' : 'Activer' ?></a>
                    </div>
                </div>
            </div>

<?php include_partial('compte/coordonneesVisualisation', array('compte' => $societe->getMasterCompte(), 'modification' => $modification, 'reduct_rights' => $reduct_rights)); ?>


                <?php if ($societe->getMasterCompte()->exist('droits')): ?>
                <div class="list-group-item">
    <?php if ($societe->getMasterCompte()->exist('droits') && $societe->getMasterCompte()->hasDroit(Roles::TELEDECLARATION)): ?>
                        <p>
                            <strong>Login de télédéclaration :</strong> <?php echo $societe->identifiant; ?>
                            <?php if ($societe->getMasterCompte()->getStatutTeledeclarant() == CompteClient::STATUT_TELEDECLARANT_NOUVEAU) : ?>
                                <span class="text-muted">(code de création : <?php echo str_replace('{TEXT}', '', $societe->getMasterCompte()->mot_de_passe); ?>)</span>
                            <?php else: ?>
                                <span class="text-muted">(code de création : Compte déjà crée</span>
                            <?php endif; ?>
                            <?php
                            if ($societe->isTransaction()):
                                if ($societe->getEtablissementPrincipal() && $societe->getEtablissementPrincipal()->getEmailTeledeclaration() && $societe->getMasterCompte()->isTeledeclarationActive()) :
                                    ?>
                                <li>Email de télédéclaration : <?php echo $societe->getEtablissementPrincipal()->getEmailTeledeclaration(); ?></li>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($societe->getEmailTeledeclaration() && $societe->getMasterCompte()->isTeledeclarationActive()) :
                                ?>
                                <li>Email de télédéclaration : <?php echo $societe->getEmailTeledeclaration(); ?></li>
                            <?php endif; ?>
        <?php endif; ?>

                        </ul>
                        </p>
    <?php endif; ?>
                    <p><?php if ($societe->getMasterCompte()->exist('droits')): ?>
                            <strong>Droits :</strong>
                            <?php foreach ($societe->getMasterCompte()->getDroits() as $droit) : ?>
                                <button class="btn btn-sm btn-default"><?php echo $droit; ?></button>
                            <?php endforeach; ?>
                <?php endif; ?></p>
                </div>
<?php endif; ?>
            <div class="list-group-item">
                <ul class="list-inline">
                    <li><attr>N° SIRET :</attr> <?php echo $societe->siret; ?></li>
                    <?php if ($societe->code_naf) : ?>
                        <li><attr>Code NAF :</attr> <?php echo $societe->code_naf; ?></li>
                    <?php endif; ?>
                    <?php if ($societe->code_comptable_client) : ?>
                        <li><attr>N° Compta Client :</attr> <?php echo $societe->code_comptable_client; ?></li>
                    <?php endif; ?>
                    <?php if ($societe->code_comptable_fournisseur) : ?>
                        <li><attr>N° Compta Fournisseur :</attr> <?php echo $societe->code_comptable_fournisseur; ?></li>
                    <?php endif; ?>
                        <?php if ($societe->no_tva_intracommunautaire) : ?>
                        <li>TVA intracom : <?php echo $societe->no_tva_intracommunautaire; ?>
                        <?php endif; ?>
                        <?php if ($societe->exist('type_fournisseur') && count($societe->type_fournisseur)) : ?>
                        <li>Type de Fournisseur : <?php foreach ($societe->type_fournisseur as $type_fournisseur) : ?> <?php echo $type_fournisseur; ?>&nbsp;<?php endforeach; ?>
<?php endif; ?>
                </ul>

                <?php if ($societe->commentaire) : ?>
                    <strong>Commentaires :</strong> <?php echo $societe->commentaire; ?>
<?php endif; ?>
            </div>

        </div>
    </div>
        <?php foreach ($etablissements as $etablissementId => $etb) : ?>
        <div class="col-xs-12" style="<?php if ((isset($etablissement) && $etablissement->_id != $etablissementId) || isset($interlocuteur)): ?>opacity: 0.6<?php endif; ?>">
    <?php include_partial('etablissement/visualisation', array('etablissement' => $etb->etablissement, 'ordre' => $etb->ordre, 'fromSociete' => true, 'modification' => $modification, 'reduct_rights' => $reduct_rights)); ?>
            <a name="<?php echo $etablissementId ?>"></a>
        </div>
    <?php endforeach; ?>

    <?php foreach ($interlocuteurs as $interlocuteurId => $compte) : ?>
            <?php if ($compte->isSocieteContact() || $compte->isEtablissementContact()): ?><?php continue; ?><?php endif; ?>
        <div class="col-xs-4" style="<?php if (isset($etablissement) || (isset($interlocuteur) && $interlocuteur->_id != $compte->_id)): ?>opacity: 0.6<?php endif; ?>">
    <?php include_partial('compte/visualisation', array('compte' => $compte, 'modification' => $modification, 'reduct_rights' => $reduct_rights)); ?>
            <a name="<?php echo $compte->_id ?>"></a>
        </div>
<?php endforeach; ?>

    <div class="col-xs-12 text-center">
        <div class="row">
            <?php if ($modification || $reduct_rights) : ?>
                <?php if (!$reduct_rights && $societe->canHaveChais()) : ?>
                    <div class="col-xs-6 text-right">
                        <a <?php echo ($societe->isSuspendu())? 'disabled="disabled"' : ''; ?> href="<?php echo url_for('etablissement_ajout', array('identifiant' => $societe->identifiant)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Créer un établissement</a>
                    </div>
                <?php endif; ?>
                <div class="col-xs-6 text-left">
                    <a <?php echo ($societe->isSuspendu())? 'disabled="disabled"' : ''; ?> href="<?php echo url_for('compte_ajout', array('identifiant' => $societe->identifiant)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Créer un interlocuteur</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
