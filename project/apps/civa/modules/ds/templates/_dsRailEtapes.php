<?php
use_helper('ds');
$dss = DSCivaClient::getInstance()->findDssByDS($ds);
$ds_principale = DSCivaClient::getInstance()->getDSPrincipaleByDs($ds);
$many_lieux = (count($dss) > 1);
$progression = progressionEdition($dss,$ds,$ds_principale->num_etape);
?>
<!-- .header_ds -->
<div class="header_ds clearfix">
    <ul id="etape_declaration" class="etapes_ds clearfix">
                        <?php 
                        $passe = isEtapePasse(1, $dss, $ds_principale); 
                        $to_linked = $passe || ($etape>=0); 
                        ?>
			<li class="<?php echo ($etape==1)? 'actif ' : ''; echo ($passe && $etape!=1)? 'passe' : ''; ?>" >
                            <?php if($to_linked) : ?>
					<a class="ajax" href="<?php echo  url_for('ds_exploitation',$tiers); ?>">
                            <?php endif; ?>
                                            <span>Exploitation</span> <em>Etape 1</em>
                            <?php if($to_linked) echo "</a>"; ?>       
			</li>
                        <?php 
                        $passe = isEtapePasse(2, $dss, $ds_principale);
                        $to_linked = $passe || ($etape>=1); 
                        ?>
			<li class="<?php echo ($etape==2)? 'actif ' : ''; echo ($passe && $etape!=2)? 'passe' : ''; ?>" >
                            <?php if($to_linked) : ?> 
					<a class="ajax" href="<?php echo url_for("ds_lieux_stockage", $tiers); ?>">
                            <?php endif; ?>               
                                            <span>Lieux de stockage</span> <em>Etape 2</em>
                            <?php if($to_linked) echo "</a>"; ?>
			</li>
                        <?php 
                        $passe = isEtapePasse(3, $dss, $ds_principale);
                        $to_linked = $passe || ($etape>=2); 
                        ?>
			<li class="<?php echo ($etape==3)? 'actif ' : ''; echo ($passe && $etape!=3)? 'passe ' : ''; ?> <?php echo (($etape==3) && ($many_lieux))? 'sous_menu' : '' ?>" >
                            <?php if($to_linked) : ?> 
                                <a class="ajax" href="<?php echo url_for('ds_edition_operateur', array('id' => $ds->_id));?>">
                            <?php endif; ?> 
                                <span>Stocks</span> <em>Etape 3<span class="lieu" ><?php echo getEtape3Label($etape,$many_lieux,$dss,$ds);?></span></em>
                            <?php if($to_linked) echo "</a>"; ?>
                            <?php if(($etape==3) && ($many_lieux)) : ?>
                                <ul>
                                <?php 
                                $num = 1;
                                foreach ($dss as $current_ds) : ?>
                                    <li class="<?php echo ($current_ds->_id == $ds->_id)? 'actif' : '' ?>">
                                            <a href="<?php echo url_for('ds_edition_operateur', array('id' => $current_ds->_id)); ?>">Lieu de stockage n°<?php echo $num; ?></a>
                                    </li>
                                <?php 
                                $num++;
                                endforeach; ?>
                                </ul>
                            <?php endif; ?>
			</li>
                        <?php 
                        $passe = isEtapePasse(4, $dss, $ds_principale); 
                        $to_linked = $passe || ($etape>=3); 
                        ?>                        
			<li class="<?php echo ($etape==4)? 'actif ' : ''; echo ($passe && $etape!=4)? 'passe' : ''; ?>" >
                        <?php if($to_linked) : ?> 
                            <a class="ajax" href="<?php echo url_for('ds_autre', $tiers);?>">
                        <?php endif; ?>         
                                <span>Autres Produits</span> <em>Etape 4</em>
                        <?php if($to_linked) echo "</a>"; ?>
			</li>
                        <?php 
                        $passe = isEtapePasse(5, $dss, $ds_principale);
                        $to_linked = $passe || ($etape>=4); 
                        ?>   
			<li class="<?php echo ($etape==5)? 'actif ' : ''; echo ($passe && $etape!=5)? 'passe' : ''; ?>" >
                        <?php if($to_linked) : ?> 
                            <a class="ajax" href="<?php echo url_for('ds_validation', $tiers);?>">
                       <?php endif; ?>  
                                <span>Validation</span> <em>Etape 5</em>
                         <?php if($to_linked) echo "</a>"; ?>
			</li>
	</ul>
		
	<div class="progression_ds">
			<p>Vous avez saisi <span><?php echo $progression.'%';?></span> de votre DS</p>

			<div class="barre_progression">
					<div class="progression" style="<?php echo "width: ".$progression."%;";?>"></div>
			</div>
	</div>
</div>
<!-- fin .header_ds -->