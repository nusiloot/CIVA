<ul id="btn_etape" class="btn_prev_suiv clearfix">
    <?php if(in_array('suivant', $display->getRawValue())): ?>
        <li class="suiv"><input type="image" src="/images/boutons/btn_passer_etape_suiv.png" alt="Passer à l'étape suivante" name="boutons[next]" /></li>
    <?php endif; ?>
    <?php if(in_array('precedent', $display->getRawValue())): ?>
        <li class="prec"><input type="image" src="/images/boutons/btn_retourner_etape_prec.png" alt="Retourner à l'étape précédente" name="boutons[previous]" /></li>
    <?php endif; ?>
    <?php if(in_array('valider', $display->getRawValue())): ?>
        <li class="suiv"><input type="image" src="/images/boutons/btn_valider_final.png" alt="Valider votre déclaration" name="boutons[next]" /></li>
    <?php endif; ?>
    <?php if(in_array('previsualiser', $display->getRawValue())): ?>
        <li class="previsualiser"><input type="image" src="/images/boutons/btn_previsualiser.png" alt="Prévisualiser" name="boutons[previsualiser]" id="previsualiser" /></li>
    <?php endif; ?>

</ul>