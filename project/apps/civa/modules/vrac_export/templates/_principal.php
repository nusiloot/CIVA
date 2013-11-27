<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<?php  use_helper('vracExport'); ?>
<html class="no-js">
	<head>
	
	</head>
	<body>
<?php  include_partial("vrac_export/soussignes", array('vrac' => $vrac));  ?>
<small><br /></small>
<span style="background-color: black; color: white; font-weight: bold;">&nbsp;Transactions vrac&nbsp;</span><br/>
<?php $widthProduit = 260; ?>
<?php $widthProduit = (!$odg)? $widthProduit : ($widthProduit + 70); ?>
<?php      $nb_ligne = 30;
           $nb_ligne -= (!$odg)? 0 : 2; 
?>

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="text-align: right; border-collapse: collapse;">
	<tr>
		<th width="65px" style="font-weight: bold; text-align: center; border: 1px solid black;">AOC
		</th>
		<th width="<?php echo $widthProduit ?>px" style="font-weight: bold; text-align: center; border: 1px solid black;">Produit</th>
		<th width="42px" style="font-weight: bold; text-align: center; border: 1px solid black;">Mill.</th>  
		<?php if (!$odg): ?>
		<th width="58px" style="font-weight: bold; text-align: center; border: 1px solid black;">Prix*<br/><small>(en &euro;/HL)</small></th>
		<?php endif; ?>
		<th width="75px" style="font-weight: bold; text-align: center; border: 1px solid black;">Volume estimé<br/><small>(en HL)</small></th>
		<th width="75px" style="font-weight: bold; text-align: center; border: 1px solid black;">Volume réel<br/><small>(en HL)</small></th>
                <th width="62px" style="font-weight: bold; text-align: center; border: 1px solid black;">Date<br/>de Chargt</th>
	</tr>
	<?php 
        $cptDetail = 0;
        foreach ($vrac->declaration->getProduitsDetailsSorted() as $product): 
			$productLine = $product->getRawValue();
					foreach ($productLine as $detailKey => $detailLine): 
                                            $nb_ligne--;
							$backgroundColor = getColorRowDetail($detailLine);
			$libelle_produit = $detailLine->getCepage()->getLibelle()." ".$detailLine->getLieuLibelle()." ".$detailLine->getLieuDit()." ".$detailLine->getVtsgn()." ".$detailLine->getDenomination();
                        $isOnlyOneRetiraison = $vrac->isCloture() && (count($detailLine->retiraisons) === 1);
                        $dateRetiraison = "";                        
                        $lastDetail = ((count($vrac->declaration->getProduitsDetailsSorted()) - 1) == $cptDetail);
                        if($isOnlyOneRetiraison){
                            $retiraisons = $detailLine->retiraisons->toArray(true,false);
                            $dateRetiraison = getDateFr($retiraisons[0]['date']);
                        }
                        ?>
	<tr>
			<td width="65px" style="border: 1px solid black; <?php echo $backgroundColor ?> text-align: center;"><span style="font-size: 6pt;"><?php echo $detailLine->getCepage()->getAppellation()->getCodeCiva(); ?></span></td>
			<td width="<?php echo $widthProduit ?>px" style="border: 1px solid black; <?php echo $backgroundColor ?> text-align: left; font-size: 8pt;">&nbsp;<?php echo truncate_text($libelle_produit,49);  ?></td>
			<td width="42px" style="border: 1px solid black; <?php echo $backgroundColor ?> text-align: center;"><?php echo $detailLine->getMillesime(); ?>&nbsp;</td>    
			<?php if (!$odg): ?>
			<td width="58px" style="border: 1px solid black; <?php echo $backgroundColor ?> text-align: right;"><?php echoPrix($detailLine->getPrixUnitaire()); ?></td>
			<?php endif; ?>
			<td width="75px" style="border: 1px solid black; <?php echo $backgroundColor ?> text-align: right;"><?php echoVolume($detailLine->volume_propose); ?></td>
			<td width="75px" style="border: 1px solid black; <?php echo $backgroundColor ?> text-align: right;<?php if (!$vrac->isCloture()): ?> background-color: grey;<?php endif; ?>"><?php if ($vrac->isCloture()): ?><?php echoVolume($detailLine->volume_enleve); ?><?php endif; ?></td>
                        <td width="62px" style="border: 1px solid black; <?php echo $backgroundColor ?> text-align: center;<?php if (!$isOnlyOneRetiraison): ?> background-color: grey;<?php endif; ?>"><?php echo $dateRetiraison; ?></td>
	
        </tr>
        <?php 
        $cptDetail++;
        if($vrac->isCloture() && (count($detailLine->retiraisons) > 1)):
        $cpt = 0;
        foreach ($detailLine->retiraisons as $retiraison): 
            $border_bottom = (((count($detailLine->retiraisons) - 1 ) == $cpt) && $lastDetail)? "border-bottom: 1px solid black; border-bottom: 1px solid black;" : "border-bottom: 1px solid grey;";
            $nb_ligne--;
            ?>
                <tr>
                    <td colspan="5" style="border-left: 1px solid black; <?php echo $border_bottom; ?>  background-color: grey;"></td>
                    <td width="75px" style="border: 1px solid black; text-align: right;"><?php echoVolume($retiraison->volume); ?></td>
                    <td width="62px" style="border: 1px solid black;  text-align: center;"><?php echoDateFr($retiraison->date); ?></td>
                </tr>
        <?php 
        $cpt++;
                        endforeach;
                endif;
		endforeach;
        endforeach; 
	?>

	<tr>
			<td style="text-align: left;" colspan="<?php if (!$odg): ?>4<?php else: ?>3<?php endif; ?>" >&nbsp;</td>
			<td style="border: 1px solid black;"><?php echoVolume($vrac->getTotalVolumePropose(),true); ?></td>
                        <td style="border: 1px solid black; <?php if (!$vrac->isCloture()): ?> background-color: grey;<?php endif; ?>"><?php if ($vrac->isCloture()): ?><?php echoVolume($vrac->getTotalVolumeEnleve(),true); ?><?php endif; ?></td>
	</tr>
	<?php if (!$odg): ?>
	<tr>
		<td style="text-align: left;" colspan="4" >*&nbsp;<span style="font-size: 6pt; padding-left: 20px"><?php echo getExplicationEtoile(); ?></span></td>
		<td>&nbsp;</td>
		<?php if ($vrac->isCloture()): ?>
		<td>&nbsp;</td>
                <td>&nbsp;</td>
		<?php endif; ?>
	</tr>
	<?php endif; ?>
</table>
<?php if ($vrac->conditions_paiement): $nb_ligne-=2; ?><p>Conditions de paiement : <?php echo $vrac->conditions_paiement; ?></p><?php endif; ?>
<?php if ($vrac->conditions_particulieres): $nb_ligne-=2; ?><p>Conditions particulières : <?php echo $vrac->conditions_particulieres; ?></p><?php endif; ?>

<?php for($i=0;$i<$nb_ligne;$i++): ?>
<br />&nbsp;
<?php endfor;?>
                
<div style="display: absolute; bottom: 5px;">
<?php if($vrac->hasCourtier()) {$widthSignataire = 33.33;} else {$widthSignataire = 50; } ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%" style="text-align: left; border-collapse: collapse;">
	<tr>
		<td width="<?php echo $widthSignataire ?>%" valign="top" style="border: 1px solid #000;">
			<table cellspacing="0" cellpadding="5" border="0" width="100%">
				<tr>
					<th style="background-color: grey; text-align: center; color: #FFF; font-weight: bold;">LE VENDEUR</th>
				</tr>
				<tr>
					<td><?php if ($vrac->valide->date_validation_vendeur): ?>le <?php echo preg_replace('/^(\d+)\-(\d+)\-(\d+)$/', '\3/\2/\1', $vrac->valide->date_validation_vendeur); ?>,<?php endif; ?></td>
				</tr>
				<tr>
					<td><?php if ($vrac->valide->date_validation_vendeur): ?>signé éléctroniquement<?php endif; ?></td>
				</tr>
			</table>
		</td>
		<?php if($vrac->hasCourtier()): ?>
		<td width="<?php echo $widthSignataire ?>%" valign="top" style="border: 1px solid #000;">
			<table cellspacing="0" cellpadding="5" border="0" width="100%">
				<tr>
					<th style="background-color: grey; text-align: center; color: #FFF; font-weight: bold;">VU, le Courtier</th>
				</tr>
				<tr>
					<td><?php if ($vrac->valide->date_validation_mandataire): ?>le <?php echo preg_replace('/^(\d+)\-(\d+)\-(\d+)$/', '\3/\2/\1', $vrac->valide->date_validation_mandataire); ?>,<?php endif; ?></td>
				</tr>
				<tr>
					<td><?php if ($vrac->valide->date_validation_mandataire): ?>signé éléctroniquement<?php endif; ?></td>
				</tr>
			</table>
		</td>
		<?php endif; ?>
		<td width="<?php echo $widthSignataire ?>%" valign="top" style="border: 1px solid #000;">
			<table cellspacing="0" cellpadding="5" border="0" width="100%">
				<tr>
					<th style="background-color: grey; text-align: center; color: #FFF; font-weight: bold;">L'ACHETEUR</th>
				</tr>
				<tr>
					<td><?php if ($vrac->valide->date_validation_acheteur): ?>le <?php echo preg_replace('/^(\d+)\-(\d+)\-(\d+)$/', '\3/\2/\1', $vrac->valide->date_validation_acheteur); ?>,<?php endif; ?></td>
				</tr>
				<tr>
					<td><?php if ($vrac->valide->date_validation_acheteur): ?>signé éléctroniquement<?php endif; ?></td>
				</tr>
			</table>
		</td>
	</tr>

</table>
<table cellspacing="0" cellpadding="0" border="0" style="text-align: right;">
	<tr>
		<td style="text-align: left; font-size: 6pt;"><?php echo getLastSentence(); ?></td>
	</tr>
</table>
</div>
</body>
</html>
