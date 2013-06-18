<h3 class="titre_section">Déclaration de l'année <a href="" class="msg_aide" rel="help_popup_mon_espace_civa_ma_ds" title="Message aide"></a></h3>
<div class="contenu_section">
    <p class="intro"><?php echo acCouchdbManager::getClient('Messages')->getMessage('intro_mon_espace_civa_ds_validee'); ?></p>
    <div class="ligne_form ligne_btn">
        <?php echo link_to('<img src="/images/boutons/btn_visualiser.png" alt="" class="btn" />', 'ds_visualisation',$ds); ?>
        <?php if ($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <a href="<?php echo url_for('ds_invalider_civa',$ds) ?>" onclick="return confirm('Si vous éditez cette DS, pensez à la revalider.')"><img src="../images/boutons/btn_editer_dr.png" alt="" class="btn" id="rendreEditable"  /></a>
            <a href="<?php echo url_for('ds_invalider_recoltant',$ds) ?>" onclick="return confirm('Etes-vous sûr de vouloir dévalider cette DS ?')"><img src="../images/boutons/btn_devalider_dr.png" alt="" class="btn" id=""  /></a>
        <?php endif; ?>
    </div>
</div>