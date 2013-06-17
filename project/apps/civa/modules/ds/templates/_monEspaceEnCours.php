<form id="form_ds" action="<?php echo url_for('ds_init', $ds) ?>" method="post">
    <h3 class="titre_section">Déclaration de l'année <a href="" class="msg_aide" rel="help_popup_mon_espace_civa_ma_ds" title="Message aide"></a></h3>
    <div class="contenu_section">        
        <?php if (!$ds->isNew()): ?>
            <p class="intro">Vous souhaitez :</p>
            <div class="ligne_form">
                <input type="radio" id="type_declaration_visualisation" name="ds[type_declaration]" value="visualisation"  />
                <label for="type_declaration_visualisation">Visualiser ma déclaration en cours</label>
            </div>
            <div class="ligne_form">
                <input type="radio" id="type_declaration_brouillon" name="ds[type_declaration]" value="brouillon" checked="checked" />
                <label for="type_declaration_brouillon">Continuer ma déclaration</label>
            </div>
            <div class="ligne_form">
                <input type="radio" id="type_declaration_suppr" name="ds[type_declaration]" value="supprimer" />
                <label for="type_declaration_suppr">Supprimer ma déclaration <?php echo $sf_user->getCampagne() ?> en cours</label>
            </div>
        <?php else: ?>
            <p class="intro">Commencer une déclaration de stock</p>
        <?php endif; ?>
        <div class="ligne_form ligne_btn">
           <input type="image" name="boutons[valider]" id="mon_espace_civa_valider" class="btn" src="/images/boutons/btn_valider.png" alt="Valider" />
        </div>
        <p class="intro msg_mon_espace_civa"><?php echo acCouchdbManager::getClient('Messages')->getMessage('intro_mon_espace_civa_dr'); ?></p>
    </div>
</form>