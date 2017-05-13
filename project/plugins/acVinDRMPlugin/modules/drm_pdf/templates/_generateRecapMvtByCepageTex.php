<?php
$nb_produits_total = 0;
$nb_pages_total = 0;
foreach($produitsDetailsByCertifications->produitsByAppellation as $produitsAppellation){
  $nb_produits_total += count($produitsAppellation);
  $nb_pages_total += (int) (count($produitsAppellation) / DRMLatex::NB_PRODUITS_PER_PAGE) + 1;
}


foreach ($produitsDetailsByCertifications->produitsByAppellation as $libelleAppellation => $produitsDetailsByAppellations):
$libelleCertif = $produitsDetailsByCertifications->certification_libelle;
$libelleAppellationTitre = $libelleAppellation;
$nb_produits = count($produitsDetailsByAppellations);
if ($nb_produits == 0) {
    continue;
}
$nb_pages = (int) ($nb_produits / DRMLatex::NB_PRODUITS_PER_PAGE) + 1;
$nb_produits_per_page = (int) ($nb_produits_total / $nb_pages_total) + 1;
$nb_produits_displayed = 0;
$produits_for_appellation = array_values($produitsDetailsByAppellations->getRawValue());
$index_page_total = 0;
?>
\quad{\setlength{\extrarowheight}{1pt}
\begin{center}
      \begin{large}
      \textbf{DRM en Droits <?php echo $libelleDetail; ?>}
      \end{large}
\end{center}
\\~
\\~
<?php
for ($index_page = 0; $index_page < $nb_pages; $index_page++):
    $index_page_total++;
    $index_first_produit = $index_page * $nb_produits_per_page;
    if ($index_page == $nb_pages - 1) {
        $nb_produits_per_page = $nb_produits - $nb_produits_displayed;
    }
    $size_col = 30;
    $entete = '\begin{tabular}{ C{68mm} |';
    for ($cpt_col = 0; $cpt_col < $nb_produits_per_page; $cpt_col++) {
        $entete .='C{' . $size_col . 'mm}|';
    }
    $entete .='}';
    if ($index_page > 0) {
        $libelleAppellationTitre .= ' (Suite)';
    }
    $maxCol = 1 + $nb_produits_per_page;
    $index_last_produit = $index_first_produit + $nb_produits_per_page - 1;
    $produits_for_page = array();
    foreach (range($index_first_produit, $index_last_produit) as $indexProduit) {
        $produits_for_page[] = $produits_for_appellation[$indexProduit];
        $nb_produits_displayed++;
    }
    ?>

    <?php
    /*
     * Début du Tabular
     */
    ?>
    <?php echo $entete; ?>


    <?php
    /*
     * Entête des Produits
     */
    ?>
    \cline{2-<?php echo $maxCol; ?>}
    
    \begin{large}
    \textbf{<?php echo $libelleAppellationTitre; ?>}
    \end{large} &
    <?php
    foreach ($produits_for_page as $counter => $produit): ?>
        \multicolumn{1}{>{\columncolor[rgb]{0,0,0}}C{<?php echo $size_col; ?>mm}|}{ \small{\color{white}{\textbf{<?php echo  $produit->getCepage()->getLibelle("%l% %co% %ce%"); ?>}}}}
        <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
    <?php endforeach; ?>
    \\
    \hline

    <?php
    /*
     * STOCK DÉBUT DE MOIS
     */
    ?>
    \rowcolor{gray}
    \multicolumn{1}{|c|}{ \small{\color{white}{\textbf{STOCK DÉBUT DE MOIS}} }} &
    <?php foreach ($produits_for_page as $counter => $produit): ?>
        \multicolumn{1}{r|}{  \small{\color{white}{\textbf{<?php echoFloatWithHl($produit->total_debut_mois); ?>}}}}
        <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
    <?php endforeach; ?>
    \\
    \hline

    <?php
    /*
     * LES ENTREES
     */
    ?>
    <?php foreach ($mvtsEnteesForPdf as $cpt_entree => $entree): ?>
        <?php $entreeKey = $entree->key; ?>
        <?php if (!$cpt_entree): ?>
            \multicolumn{1}{|c}{\multirow{<?php echo count($mvtsEnteesForPdf); ?>}{20mm}{\small{\textbf{ENTREES DU MOIS}}}} &
        <?php endif; ?>

        \multicolumn{1}{|l|}{  \small{<?php echo $entree->libelle; ?>} } &
        <?php foreach ($produits_for_page as $counter => $produit):  ?>
            \multicolumn{1}{r|}{  \small{<?php echoFloatWithHl($produit->entrees->$entreeKey); ?>}}
            <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
        <?php endforeach; ?>
        \\
        <?php if ((count($mvtsEnteesForPdf) - 1) != $cpt_entree): ?>
            \cline{1-<?php echo $maxCol; ?>}
        <?php endif; ?>
    <?php endforeach; ?>
    \hline

    <?php
    /*
     * TOTAL ENTREES
     */
    ?>
    \rowcolor{lightgray}
    \multicolumn{1}{|r|}{ \small{\textbf{TOTAL ENTREES}} } &
    <?php foreach ($produits_for_page as $counter => $produit): ?>
        \multicolumn{1}{r|}{   \small{\textbf{<?php echoFloatWithHl($produit->total_entrees); ?>}} }
        <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
    <?php endforeach; ?>
    \\
    \hline

    <?php
    /*
     * LES SORTIES
     */
    ?>
    <?php foreach ($mvtsSortiesForPdf as $cpt_sortie => $sortie): ?>
        <?php $sortieKey = $sortie->key; ?>
        <?php if (!$cpt_sortie): ?>
            \multicolumn{1}{|c}{\multirow{<?php echo count($mvtsSortiesForPdf); ?>}{20mm}{\small{\textbf{SORTIES DU MOIS}}}} &
        <?php endif; ?>

        \multicolumn{1}{|l|}{  \small{<?php echo $sortie->libelle; ?>} } &
        <?php foreach ($produits_for_page as $counter => $produit): ?>
            \multicolumn{1}{r|}{  \small{\color{black}{<?php echoFloatWithHl($produit->sorties->$sortieKey); ?>}}}
            <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
        <?php endforeach; ?>
        \\
        <?php if ((count($mvtsSortiesForPdf) - 1) != $cpt_entree): ?>
            \cline{1-<?php echo $maxCol; ?>}
        <?php endif; ?>
    <?php endforeach; ?>
    \hline

    <?php
    /*
     * TOTAL SORTIES
     */
    ?>
    \rowcolor{lightgray}
    \multicolumn{1}{|r|}{ \small{\textbf{TOTAL SORTIES}} } &
    <?php foreach ($produits_for_page as $counter => $produit): ?>
        \multicolumn{1}{r|}{   \small{\textbf{<?php echoFloatWithHl($produit->total_sorties); ?>}} }
        <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
    <?php endforeach; ?>
    \\
    \hline

    <?php
    /*
     * STOCK FIN DE MOIS
     */
    ?>
    \rowcolor{gray}
    \multicolumn{1}{|c|}{ \small{\color{white}{\textbf{STOCK FIN DE MOIS}} }} &
    <?php foreach ($produits_for_page as $counter => $produit): ?>
        \multicolumn{1}{r|}{  \small{\color{white}{\textbf{<?php echoFloatWithHl($produit->stocks_fin->final); ?>}}}}
        <?php echo ($counter < count($produits_for_page) - 1) ? "&" : ''; ?>
    <?php endforeach; ?>
    \\
    \hline
    \end{tabular}
    <?php if (($nb_pages > 1) && (($nb_pages_total - 1) == $index_page_total)) : ?>
        \newpage
    <?php endif; ?>
<?php endfor; ?>
    \newpage
<?php endforeach; ?>