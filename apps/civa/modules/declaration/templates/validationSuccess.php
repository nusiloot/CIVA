<?php include_partial('global/etapes', array('etape' => 3)) ?>
<?php include_partial('global/actions') ?>

<!-- #principal -->
  <form id="principal" action="" method="post" class="ui-tabs">

        <ul id="onglets_majeurs" class="clearfix">
                <li><a href="#acheteurs_caves">Acheteurs et Caves</a></li>
                <li><a href="#recolte_totale">Récolte totale</a></li>
        </ul>

        <!-- #application_dr -->
        <div id="application_dr" class="clearfix">


                <!-- #acheteurs_caves -->
                <div id="recolte_totale">

                        <div id="appelations">

                                <table cellpadding="0" cellspacing="0" class="table_donnees">
                                        <thead>
                                                <tr>
                                                        <th><img src="/images/textes/appelations.png" alt="Appelations" /></th>
  <?php foreach ($appellations as $a) : ?>
  <th><?php echo preg_replace('/(AOC|Vin de table)/', '<span>\1</span>', $libelle[$a]); ?></th>
<?php endforeach; ?>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <tr>
                                                        <td>Superficie (Ha)</td>
  <?php foreach ($appellations as $a) : ?>
<td><?php echo $superficie[$a]; ?></td>
<?php endforeach; ?>
                                                </tr>
                                                <tr>
                                                        <td>Volume Total (Hl)</td>
  <?php foreach ($appellations as $a) : ?>
<td><?php echo $volume[$a]; ?></td>
<?php endforeach; ?>
                                                </tr>
                                                <tr>
                                                        <td>Volume Revendiqué (Hl)</td>
  <?php foreach ($appellations as $a) : ?>
<td><?php echo $revendique[$a]; ?></td>
<?php endforeach; ?>
                                                </tr>
                                                <tr>
                                                        <td>DPLC (Hl)</td>
  <?php foreach ($appellations as $a) : ?>
<td><?php echo $dplc[$a]; ?></td>
<?php endforeach; ?>
                                                </tr>
                                        </tbody>
                                </table>

                        </div>

                        <div id="total_general">
                                <h2 class="titre_section">Total général</h2>
                                <ul class="contenu_section">
    <li><input type="text" value="<?php echo $total_superficie;?> Ha" readonly="readonly"></li>
    <li><input type="text" value="<?php echo $total_volume;?> Hl" readonly="readonly"></li>
    <li><input type="text" value="<?php echo $total_revendique;?> Hl" readonly="readonly"></li>
    <li><input type="text" value="<?php echo $total_dplc;?> Hl" readonly="readonly"></li>
                                </ul>
                        </div>
    <p>Jeunes Vignes : <?php echo $jeunes_vignes; ?>Ha</p>
    <p>Lies : <?php echo $lies; ?>Hl</p>
    <?php if (isset($superficie['VINTABLE'])) : ?>
	      <p>Vin de table : <?php echo $superficie['VINTABLE']; ?> Ha / <?php echo $volume['VINTABLE']; ?> Hl</p>
    <?php endif; ?>
                </div>
                <!-- fin #acheteurs_caves -->

        </div>
        <!-- fin #application_dr -->
<?php if ($annee == '2010') : ?>
       <?php include_partial('global/boutons', array('display' => array('precedent','previsualiser','suivant'))) ?>
<?php endif; ?>
</form>
<!-- fin #principal -->