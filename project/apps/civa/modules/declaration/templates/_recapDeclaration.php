<?php use_helper('civa') ?>

<div id="recolte_totale" class="clearfix">

    <div id="appelations">

        <table cellpadding="0" cellspacing="0" class="table_donnees">
            <thead>
                <tr>
                    <th><img src="/images/textes/appelations.png" alt="Appellations" /></th>
   <?php foreach ($appellations as $a) if (!isset($ignore[$a]) || !$ignore[$a]) :?>
                    <th id="recap_th_<?php echo $a ?>"><?php echo preg_replace('/(AOC|Vin de table)/', '<span>\1</span>', $libelle[$a]); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Superficie (ares)</td>
                    <?php foreach ($appellations as $a)  if (!isset($ignore[$a]) || !$ignore[$a]) : ?>
                    <td><?php echoFloat( $superficie[$a]); ?></td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td>Volume sur place (hl)</td>
                    <?php foreach ($appellations as $a)  if (!isset($ignore[$a]) || !$ignore[$a]) : ?>
                    <td><?php echoFloat( $volume_sur_place[$a]); ?></td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td>Volume Total (hl)</td>
                    <?php foreach ($appellations as $a) if (!isset($ignore[$a]) || !$ignore[$a]) : ?>
                    <td><?php echoFloat( $volume[$a]); ?></td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td>Volume Revendiqué (hl)</td>
                    <?php foreach ($appellations as $a) if (!isset($ignore[$a]) || !$ignore[$a]) : ?>
                    <td><?php echoFloat( $revendique[$a]); ?></td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td>
                        <?php if($has_no_usages_industriels): ?>
                            DPLC (hl)
                        <?php else: ?>
                            Usages industriels (hl)
                        <?php endif; ?>
                    </td>
                    <?php foreach ($appellations as $a) if (!isset($ignore[$a]) || !$ignore[$a]) : ?>
                    <td>
                        <?php if($has_no_usages_industriels): ?>
                            <?php echoFloat($usages_industriels[$a]); ?>
                        <?php elseif($usages_industriels[$a] != 0): ?>
                            <?php echoFloat($usages_industriels[$a]); ?>
                        <?php endif; ?>
                    </td>
                    <?php endif;  ?>
                </tr>
            </tbody>
        </table>
   </div>

    <div id="total_general">
        <h2 class="titre_section">Total général</h2>
        <ul class="contenu_section">
            <li><input type="text" value="<?php echoFloat( $total_superficie);?> ares" readonly="readonly"></li>
            <li><input type="text" value="<?php echoFloat( $total_volume_sur_place);?> hl" readonly="readonly"></li>
            <li><input type="text" value="<?php echoFloat( $total_volume);?> hl" readonly="readonly"></li>
            <li><input type="text" value="<?php echoFloat( $total_revendique);?> hl" readonly="readonly"></li>
            <li>
                <?php if($has_no_usages_industriels): ?>
                    <input type="text" value="<?php echoFloat( $total_usages_industriels);?> hl" readonly="readonly">
                <?php elseif($total_usages_industriels != 0): ?>
                    <input type="text" value="<?php echoFloat( $total_usages_industriels);?> hl" readonly="readonly">
                <?php else: ?>
                    <input type="text" value="" readonly="readonly">
                <?php endif; ?>
            </li>
        </ul>
    </div>
	<div id="recap_autres">
		<table cellpadding="0" cellspacing="0" class="table_donnees autres_infos">
			<thead>
				<tr>
					<th><img src="/images/textes/autres_infos.png" alt="Appellations" /></th>
				</tr>
			</thead>
			<tbody>
                <?php if($has_no_usages_industriels): ?>
                <tr>
                    <td class="premiere_colonne">Lies : </td><td><?php echoFloat($lies); ?>&nbsp;<small>hl</small></td>
                </tr>
                <?php endif; ?>
				<tr>
                    <td class="premiere_colonne">Jeunes Vignes : </td><td><?php echoFloat( $jeunes_vignes); ?>&nbsp;<small>ares</small></td>
				</tr>
			    <?php if (isset($vintable['superficie'])) : ?>
				<tr>
				   <td class="premiere_colonne">Vins sans IG : </td><td><?php echoFloat( $vintable['superficie']); ?>&nbsp;<small>ares</small> / <?php echoFloat( $vintable['volume']); ?> hl</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>