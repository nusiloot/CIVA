<!-- #application_ds -->
<form action="<?php echo url_for('ds_autre', array('cvi' => $tiers->cvi)); ?>" id="form_autre_<?php echo $ds->_id; ?>" method="post" >
<?php
echo $form->renderHiddenFields();
echo $form->renderGlobalErrors();
include_partial('dsRailEtapes',array('tiers' => $tiers, 'ds' => $ds, 'etape' => 4));
?>

<h2 class="titre_page">Autres Produits : saisissez ici vos rebêche, dépassements de rendement, lies et moûts.<br><span class="titre_autre">(tous lieux de stockage confondus)<span></h2>
<!-- #application_ds --></h2>
<div id="ajax_error"></div>
	<div id="application_ds" class="clearfix">
		<div class="bloc_autres">
		<h2 class="titre_page">Autres Produits</h2>
		<!-- #gestion_stock -->
		<div id="gestion_stock" class="clearfix gestion_stock_donnees">
			<ul id="liste_cepages">
				<li><?php echo $form['mouts']->renderLabel() ?></li>
				<li><?php echo $form['rebeches']->renderLabel() ?></li>
				<li><?php echo $form['dplc']->renderLabel() ?></li>
				<li><?php echo $form['lies']->renderLabel() ?></li>
			</ul>
			
			<div id="donnees_stock_cepage">
				<div id="col_hl" class="colonne">
					<h2>hl</h2>
	
					<div class="col_cont">
						<ul>
							<li><?php echo $form['mouts']->render(array('class' => 'num')) ?></li>
							<li><?php echo $form['rebeches']->render(array('class' => 'num')) ?></li>
							<li><?php echo $form['dplc']->render(array('class' => 'num')) ?></li>
							<li><?php echo $form['lies']->render(array('class' => 'num')) ?></li>
						</ul>
					</div>
				</div>
			</div>
			
		</div>
		<!-- fin #gestion_stock -->
		
		
	</div>
	</div>
	<!-- fin #application_ds -->
	
	<ul id="btn_etape" class="btn_prev_suiv clearfix">
		<li class="prec">
			<a href="<?php echo url_for('ds_edition_operateur', $ds); ?>">
				<img src="/images/boutons/btn_retourner_etape_prec.png" alt="Retourner à l'étape précédente" />
			</a>
		</li>
		<li class="suiv">
			<input type="image" src="/images/boutons/btn_passer_etape_suiv.png" alt="Continuer à l'étape suivante" />
		</li>
	</ul>
	
</div>
<!-- fin #application_ds -->


</form>
