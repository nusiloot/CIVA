<?php

class importMessages2012Task extends sfBaseTask
{

	 
	protected function configure()
	{

		$this->addOptions(array(
		new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
		new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
		new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
		// add your own options here
		new sfCommandOption('import', null, sfCommandOption::PARAMETER_REQUIRED, 'import type [couchdb|stdout]', 'couchdb'),
		));

		$this->namespace = 'import';
		$this->name = 'Messages2012';
		$this->briefDescription = 'import messages 2012';
		$this->detailedDescription = <<<EOF
The [import|INFO] task does things.
Call it with:

  [php symfony import|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		ini_set('memory_limit', '512M');
		set_time_limit('3600');
		// initialize the database connection
		$databaseManager = new sfDatabaseManager($this->configuration);
		$connection = $databaseManager->getDatabase($options['connection'])->getConnection();

		$json = new stdClass();
		$json->_id = 'MESSAGES';
		$json->type = 'Messages';

		$json->msg_declaration_ecran_warning = "A définir";
		$json->msg_compte_index_intro  = "Pour créer votre compte, merci d'indiquer votre numéro CVI et votre code de création  (ceux que le CIVA vous a communiqués par courrier )";
		$json->msg_tiers_index_intro  = "Votre compte semble relié à plusieurs entités ayant des roles identiques. Afin d'éviter toute confusion, veuillez sélectionner celui que vous souhaitez utiliser lors de cette session.";
		$json->telecharger_pdf_mon_espace  = "Cette notice est au format PDF. Pour la visualiser, veuillez utiliser un <a href='http://pdfreaders.org/' >lecteur PDF</a>.";
		$json->telecharger_pdf  = "Le fichier généré est au format PDF. Pour le visualiser, veuillez utiliser un <a href='http://pdfreaders.org/' >lecteur PDF</a>.";
		$json->err_exploitation_acheteurs_popup_no_required  = "Veuillez cocher au moins une case pour continuer !";
		$json->err_exploitation_lieudits_popup_no_required  = "Veuillez séléctionner au moins un lieu-dit !";
		$json->err_dr_popup_no_lieu  = "Vous n'avez pas saisi de Lieu.";
		$json->err_dr_popup_no_superficie  = "Vous n'avez pas saisi de superficie. La surface est obligatoire sauf dans l'Edel";
		$json->err_dr_popup_min_quantite  = "Les rebêches doivent représenter au minimum 2 % du volume total produit";
		$json->err_dr_popup_max_quantite  = "Les rebêches doivent représenter au maximum 10% du volume total produit";
		$json->err_dr_popup_dest_rebeches  = "Vous n'avez pas respecté la répartition des rebêches.";
		$json->err_dr_popup_unique_lieu_denomination_vtsgn  = "Il faut distinguer la colonne par une mention complémentaire ou par une mention VT/SGN.";
		$json->err_dr_recap_vente_popup_superficie_trop_eleve  = "La somme des superficies des acheteurs ne peut pas être superieure au total de l'appellation";
		$json->err_dr_recap_vente_popup_dplc_trop_eleve  = "La somme des 'volumes en dépassement' des acheteurs ne peut pas être superieure au 'volume en dépassement' total de l'appellation";
		$json->err_log_lieu_non_saisie  = "Lieu non saisi";
		$json->err_log_cepage_non_saisie  = "Cépage non saisi";
		$json->err_log_detail_non_saisie  = "Details non saisis";
		$json->err_log_ED_non_saisie  = "Vous nous avez indiqué comme motif de non récolte \"assemblage Edel\" mais vous n'avez pas saisi d'Edel";
		$json->err_log_cremant_pas_rebeches  = "Vous avez oublié de saisir les rebêches";
		$json->err_log_cremant_min_quantite  = "Les rebêches doivent représenter au minimum 2% du volume total produit";
		$json->err_log_cremant_max_quantite  = "Les rebêches doivent représenter au maximum 10% des volumes produits";
		$json->err_log_superficie_zero  = "Vous n'avez pas renseigné de detail pour cette appellation";
		$json->err_log_dplc  = "Vous dépassez le rendement butoir de ce cépage. Vous pouvez le replier en Edel. Si vous livrez votre raisin, ce repli ne peut se faire qu'en accord avec votre acheteur.";
		$json->err_log_recap_vente_non_saisie  = "Vous n'avez pas complété la superficie dans le récapitulatif des ventes";
		$json->err_log_recap_vente_non_saisie_dplc  = "Vous n'avez pas complété de dplc dans le récapitulatif des ventes";
		$json->err_log_recap_vente_invalide  = "La surface et/ou le volume en dépassement du récapitulatif des ventes est supérieur au total de l'appellation";
		$json->help_popup_exploitation_administratif  = "Identification de l'exploitation : les renseignements affichés correspondent aux données que nous connaissons vous concernant. Vous pouvez procéder à des modifications, mais elles ne seront définitivement prises en compte, qu'après validation par le Bureau de la Viticulture des Douanes de Colmar";
		$json->help_popup_exploitation_administratif_exploitation  = "Nom déclaré auprès de l'Administration des Douanes (nom de l'exploitant ou nom déclaré de la Société)";
		$json->help_popup_exploitation_administratif_gestionnaire  = "Nom de la personne désignée pour être l'interlocutrice de l'Administration";
		$json->help_popup_exploitation_administratif_siret  = "N° SIRET obligatoire pour les SA, SARL, SCA, GAEC, EARL…";
		$json->help_popup_mon_espace_civa  = "Vous êtes ici sur votre espace personnel totalement sécurisé . Vous pouvez y souscrire votre déclaration de récolte en toute sécurité et en toute confidentialité et consulter celles des années précédentes.<br />  Si vous disposez d'un n° d'accises  vous pouvez également accéder à l'espace AlsaceGamm@ pour faire vos DAE et DSA. <br /> Vous disposez d'une plateforme TEST pour vous entraîner.<br /> Si vous rencontrez des difficultés lors de l'établissement de vos DAE/DSA vous pouvez appeler la HOTLINE 03 80 24 41 95 (il s'agit d'une hotline entièrement prise en charge par le CIVA (il ne vous en coûtera que le prix de la communication) Il vous suffit de vous présenter comme opérateur alsacien et de donner votre n° d'accises.";
		$json->help_popup_mon_espace_civa_ma_dr  = "Vous pouvez saisir votre déclaration de récolte : <br /> 1) soit à partir d' une déclaration totalement vierge <br />     2) soit à partir de la déclaration d'une année précédente préremplie des surfaces (cette  option n'effacera en aucun cas les données de l'année sélectionnée qui seront toujours conservées) <br />     3) soit à partir des données préremplies par votre (vos) cave(s) coopérative(s) ou acheteurs de raisins. <br />  Une fois que vous avez commencez à saisir, vous  pouvez à tout moment supprimer la déclaration en cours, et recommencer.";
		$json->help_popup_mon_espace_civa_visualiser  = "Vous pouvez ici uniquement visualiser vos déclarations des années précédentes. Lorsque vous sélectionnerez une année, le logiciel générera un fichier au format pdf que vous pourrez consulter à l'écran ou imprimer.";
		$json->help_popup_mon_espace_civa_gamma  = "Vous pouvez ici accéder à l'espace \"AlsaceGamm@\", soit en temps réel (si vous avez déjà adhéré à Gamm@) soit  en environnement  TEST pour vous familiariser avec l'application\"<br/> Attention lorsque vous êtes dans l'environnement TEST, vous ne pouvez pas émettre des DAE en temps réel.\" Si vous rencontrez des difficultés lors de l'établissement de vos DAE/DSA vous pouvez appeler la HOTLINE 03 80 24 41 95 (il s'agit d'une hotline entièrement prise en charge par le CIVA (il ne vous en coûtera que le prix de la communication) Il vous suffit de vous présenter comme opérateur alsacien et de donner votre n° d'accises. Pour tout autre renseignement concernant cet espace vous pouvez appeler au CIVA 03 89 20 16 20 demander Dominique WOLFF ou Béatrice FISCHER";
		$json->help_popup_exploitation_acheteur  = "Vous renseignez ici la répartition de votre récolte par appellation. Vous identifiez également ici vos acheteurs de raisins et/ou les caves coopératives auprès desquelles vous êtes adhérent. <br />  Les renseignements de cet écran sont très importants. En effet, les écrans suivants seront générés en fonction de ce que vous cocherez ici. Cependant en cas d'oubli à ce stade, vous pourrez toujours rajouter un acheteur ou une appellation dans les étapes suivantes.  Utilisez les boutons d'aide  (?)  à côté de chaque rubrique pour vous faciliter la saisie&<br/&>. Attention les 'Alsace lieux-dits' et 'Alsace communales' revendiquées doivent être conformes aux cahiers des charges de l'AOC Alsace. Si vous ne souhaitez pas les revendiquer, vous déclarez les surfaces et les volumes correspondants dans l'AOC Alsace, mais dans ce cas vous ne précisez pas le nom du lieu-dit ou de la communale dans la rubrique 'dénomination complémentaire'.";
		$json->help_popup_exploitation_acheteur_vol_sur_place  = "Vous cochez ici les appellations qui concernent la partie de la récolte que vous avez gardée sur place (chez vous). Vous pouvez facilement cocher ou décocher les cases.\n";
		$json->help_popup_exploitation_acheteur_acheteurs_raisin  = "Vous sélectionnez ici vos acheteurs et vous renseignez les appellations pour chacun d'eux.  Pour sélectionner un acheteur de raisins, cliquez sur le bouton vert à droite \"AJOUTER UN ACHETEUR\". Puis à gauche dans la zone de saisie, vous tapez  les premières lettres du nom de l'acheteur, ou du village ou le n° CVI. Une liste déroulante apparaitra. Vous pourrez sélectionner l'acheteur concerné et cocher les appellations qui correspondent à votre livraison. Puis cliquez sur le bouton vert \"VALIDEZ\". <br />  La Cave BESTHEIM à Bennwihr a un statut de Négociant. Elle fait partie de la liste des \"acheteurs de raisins\" (elle ne figure pas dans la liste des \"Caves Coopératives\"). <br /> En cours de saisie, si vous vous êtes trompé vous pouvez à tout moment annuler (bouton rouge ANNULER) . Si votre acheteur n'est pas présent dans la liste vous pouvez nous appeler au 03 89 20 16 20 demander Dominique WOLFF ou Béatrice FISCHER.";
		$json->help_popup_exploitation_acheteur_caves_cooperatives  = "Vous sélectionnez ici la ou les caves coopératives auprès desquelles vous êtes adhérent et vous renseignez les appellations pour chacune d'elle. Pour sélectionner une Cave, cliquez sur le bouton vert à droite \"AJOUTER UNE CAVE\". Puis à gauche dans la zone de saisie, vous tapez  les premières lettres du nom de cave ou la commune ou le n° CVI. Une liste déroulante apparaitra. Vous pourrez sélectionner la Cave Coopérative concernée et cocher les appellations qui correspondent à votre livraison. Puis cliquez sur le bouton vert \"VALIDEZ\". <br />  La Cave BESTHEIM à Bennwihr a un statut de Négociant. Vous ne la trouverez pas dans la liste des Caves Coopératives. Elle fait partie de la liste des \"acheteurs de raisins\". <br /> En cours de saisie, si vous vous êtes trompé vous pouvez à tout moment annuler (bouton rouge ANNULER) . Une fois que vous avez validé la cave si vous voulez la supprimer, cliquez sur la croix rouge.";
		$json->help_popup_exploitation_acheteur_acheteurs_mouts  = "Si vous avez vendu des môuts destinés à l'élaboration de Crémant d' Alsace vous renseignez cette zone. Vous sélectionnez l'acheteur (négociant ou cave coopérative). Pour sélectionner un acheteur, cliquez sur le bouton vert à droite \"AJOUTER UN ACHETEUR\". Puis à gauche dans la zone de saisie, vous tapez  les premières lettres du nom ou la commune ou le n° CVI. Une liste déroulante apparaitra. Vous pourrez sélectionner le négociant ou la Cave Coopérative. Ici vous n'avez pas besoin de cocher l'appellation (l'appellation Crémant sera cochée par défaut).  Puis cliquez sur le bouton vert \"VALIDEZ\". En cours de saisie, si vous vous êtes trompé vous pouvez à tout moment annuler (bouton rouge ANNULER ) ou supprimer (croix rouge) .Attention si vous vendez des moûts de Crémant d'ALSACE, la rebêche restera chez vous. ";
		$json->help_popup_exploitation_lieu  = "Si dans l'écran précédent vous avez coché \"AOC Grand Cru\" ou \"AOC Alsace communale\" vous devez ici sélectionner les \"lieux-dits Grand Cru\" et/ou les \"communales\" concernés.";
		$json->help_popup_DR  = "Vous saisissez maintenant ici les surfaces et les volumes par cépage et par destination, dans les différentes appellations que vous avez sélectionnées dans l'écran précédent . En cas d'oubli vous pouvez à tout moment rajouter une appellation (cliquez sur + ajoutez une appellation) . Vous pouvez également rajouter un acheteur ou une cave coopérative. Utilisez pour cela les options (+ Acheteur) et (+ Cave).";
		$json->help_popup_DR_denomination  = "Dénomination complémentaire. Vous pouvez saisir ici une dénomination complémentaire autre  que les \"communales\" ou \"lieux-dits géographiques\". Indiquer également dans cette rubrique les \"replis\" et les \"vins sous charte\" par ex. Edel Gentil s'il fait l'objet d'un assemblage avant vinification.";
		$json->help_popup_DR_mention  = "A renseigner si vous avez récolté des Vendanges Tardives ou des Sélections de Grains Nobles . Sélectionner \"VT\" pour vendanges Tardives ou \"SGN\" pour Sélections de Grains Nobles. Attention toutes les parcelles en production doivent obligatoirement être récoltées à la date de souscription de la déclaration de récolte. Seules les Vendanges Tardives et Sélections de Grains Nobles ayant fait l'objet d'une déclaration préalable pourront déroger à cette obligation. SI c'est le cas vous devez indiquer la surface correspondante avec un volume estimatif (saisir \"estimation\" dans la rubrique \"dénomination complémentaire\". Une déclaration de récolte rectificative devra ensuite être effectuée auprès du Bureau de la Viticulture des Douanes de Colmar qui transmettront automatiquement une copie au CIVA. Pour cela il vous faut imprimer votre déclaration sous format pdf auquel vous pouvez accéder en utilisant le bouton \"prévisualiser\".Il est important également que vous rectifiiez votre DRM en conséquence.";
		$json->help_popup_DR_superficie  = "La surface est obligatoire. A  saisir avec 2 décimales (ares,ca). Les surfaces déclarées doivent obligatoirement correspondre au relevé parcellaire enregistré dans votre casier viticole (fiche de compte du CVI).Cas particulier l'Edelzwicker :  vous laissez  la surface dans le cépage d'origine sauf pour les parcelles complantées en cépages en mélange. <br /> Si vous déclarez de l'edel \"repli butoir\" vous n'indiquez pas de surface (vous laisser les surfaces dans les cépages d'origine). En revanche pour l'Edel \"repli pinot noir\"  la surface est obligatoire. <br />  Il est possible de déclarer des surfaces sans volume. Mais lorsque vous validerez la colonne, le logiciel vous demandera de sélectionner un motif de \"non volume\" :  assemblage Edel, problèmes climatiques, maladie de la vigne, vendanges vertes, déclaration en cours, motifs personnels.";
		$json->help_popup_DR_vente_raisins  = "Vous indiquez ici pour chacun des acheteurs que vous aviez sélectionnés lors de l'étape précédente, le volume correspondant aux quantités de raisins livrés. A saisir avec 2 décimales (hectolitres/litres). Si votre acheteur vous communique les quantités en kilos, vous avez l'obligation de les convertir en hl/l  selon les coefficients forfaitaires suivants : AOC Alsace et Alsace Grand Cru (130 kgs = 1 Hl). AOC Crémant d'Alsace (150 kgs = 1 Hl). <br />  Si vous avez oublié de sélectionner un acheteur lors de l'étape précédente, vous pouvez le rajouter ici en cliquant sur  l'option \" + acheteur\". Saisir les premiers caractères du nom de l'acheteur, ou du village, ou son n° CVI d'acheteur si vous le connaissez. Un menu déroulant apparait dans lequel vous pouvez sélectionner l'acheteur.";
		$json->help_popup_DR_caves  = "Vous indiquez ici pour chacune des caves que vous aviez sélectionnées lors de l'étape précédente, le volume qui vous a été communiqué par la Cave Coopérative. A saisir avec 2 décimales (hectolitres/litres).  <br /> Si vous avez oublié de sélectionner une Cave coopérative lors de l'étape précédente, vous pouvez la rajouter ici en cliquant sur l'option \" + Cave\". Saisir les premiers caractères du nom de la Cave, ou du village, ou son n° CVI si vous le connaissez. Un menu déroulant apparait dans lequel vous pouvez sélectionner la Cave Coopérative.\n";
		$json->help_popup_DR_mouts  = "Ventes de moûts destinés à l'élaboration de Crémant d'Alsace : à répartir par acheteur (hl/l). <br /> Les rebêches produites au titre des ventes de moûts restent chez le producteur. Vous devrez donc les inscrire en volume sur place (dans la rubrique rebêches). <br /> Sur votre DRM du mois de novembre, vous indiquerez <br /> 1) dans la colonne AOC Crémant d'Alsace, en entrée (ligne 10) le volume de Crémant d'Alsace que vous avez produit PLUS le volume de moûts que vous avez vendu. En ligne (c) sorties vrac : vous inscrirez le volume de moûts vendu. <br /> 2) Dans la colonne \"rebêches\" de la DRM vous inscrirez en entrée le volume total de rebêches que vous avez obtenu (comprenant les rebêches produites au titre des ventes de moûts).";
		$json->help_popup_DR_vol_place  = "vous indiquez ici le volume que vous vinifiez sur place (chez vous). A saisir avec 2 décimales (Hectolitres/litres). <br />  Le volume à inscrire est le volume total récolté même si vous avez déjà effectué des soutirages \n";
		$json->help_popup_DR_vol_total_recolte  = "Le volume total se calcule automatiquement en fonction de ce que vous avez saisi plus haut. Vous n'avez donc rien à saisir dans cette rubrique.";
		$json->help_popup_DR_vol_revendique  = "Correspond au volume maximum que vous pouvez revendiqué dans le cépage ou l'appellation";
		$json->help_popup_DR_dplc  = "Nous vous indiquons ici votre volume en dépassement en fonction de ce que vous avez saisi. <br />  Nous vous rappelons que dans l'AOC Alsace, en cas de dépassement d'un butoir cépage, c'est le dépassement de ce cépage qu'il faudra obligatoirement livrer en distillerie même si vous avez moins (ou pas du tout) de dépassements sur le total de l'appellation. Voir les exemples dans la notice explicative complète  que vous pouvez télécharger .\n";
		$json->help_popup_DR_total_cepage  = "Nous vous indiquons ici, en fonction de ce que vous avez saisi, la répartition de votre rendement dans le total cépage (volume revendiqué et dépassement éventuel). <br /> Dans l'AOC Alsace, si le rendement à l'ha apparaît en rouge c'est que vous dépassez le rendement maximum butoir de ce cépage.<br />  Dans l'AOC Grand Cru le rendement se calcule  par cépage dans chacun des lieux-dits. S'il apparaît en rouge c'est que vous dépassez le maximum autorisé de ce cépage dans le lieu-dit Grand Cru concerné.\n";
		$json->help_popup_DR_total_couleur  = "total couleur";
		$json->help_popup_DR_total_couleur_alternatif  = "Dans cette appellation le rendement s'entend par couleur (blanc ou rouge). Cette colonne est inactive parce que vous êtes en train de saisir une autre couleur";
		$json->help_popup_DR_total_appellation  = "Nous vous indiquons ici sur le total appellation, le rendement à l'ha, le volume revendiqué et le dépassement éventuel. <br />Si vous êtes en dépassement, le rendement apparaît en rouge. <br />  ATTENTION : dans l'AOC Alsace, les zones \"volume revendiqué\" et \"volume en dépassement \" sont doublées. Les 2 premières correspondent au calcul du rendement de l'appellation. Les 2 suivantes correspondent à la somme des volumes revendiqués et des dépassements de butoirs cépages. Voir les exemples dans la notice explicative complète que vous pouvez télécharger. <br />  Dans l'AOC Alsace \"communale\" le rendement se calcule par couleur,  pour chaque communale distinctement . <br /> Dans l'AOC Alsace \"lieux-dits\" le rendement se calcule par couleur, tous lieux-dits confondus\n";
		$json->help_popup_DR_recap_vente  = "Il s'agit du récapitulatif des ventes en fonction de ce que vous avez saisi dans les écrans précédents. Vous complétez ici, la surface correspondante à chaque négociant et/ou cave coopérative ainsi que, le cas échéant, le volume en dépassement qui revient à chacun. Si vous décidez de livrer vous même le dépassement vous ne mettez rien dans la zone \"dont dépassement\"\n";
		$json->help_popup_recapitulatif_ventes  = "Cet écran affiche le récapitulatif de l’appellation. <br /> L’onglet orange, dans le haut de l’écran, vous indique dans quelle appellation vous vous trouvez. <br /> Si vous constatez une erreur, vous pouvez retourner à la saisie des cépages de l’appellation et effectuer les corrections. <br /> <br /> Cet écran reprend également  la répartition des ventes et des apports caves (si vous en avez saisis. <br /> Vous renseignez  dans la rubrique \"récapitulatif des ventes\" la surface et le dépassement éventuel correspondants à chaque vente ou apport.\n";
		$json->help_popup_autres  = "Vous  indiquez  ici la superficie globale de vos jeunes vignes\n";
		$json->help_popup_autres_lies  = "Le terme lies comprend à la fois les lies et les bourbes (définition communautaire du règlement 1493/99 du conseil du 17/5/99). <br /> Vous indiquez ici le volume global de lies (toutes appellations confondues) <u>déjà soutirées</u> qu'elles aient été livrées ou non. A saisir avec 2 décimales (hectolitres/litres).  <br /> Si vous êtes vendeur de raisins ou adhérent à une cave coopérative vous êtes dispensé d'indiquer les lies correspondant aux raisins livrés. En revanche vous devez déclarer celles relatives au volume vinifié sur place (ce sont vos acheteurs et vos caves coopératives qui déclareront, globalement, les lies ultérieurement). <br /> Les lies que vous déclarez ici ne devront plus transiter par la DRM, même si vous ne les avez pas encore livrées (le document d'accompagnement fera foi).<br />  Pour les exploitations ayant déclarer du DPLC sur leur déclaration de récolte, les lies générées après la souscription de la déclaration de récolte pourront venir en déduction du DPLC. Ces volumes seront suivis sur la DRM";
		$json->help_popup_autres_jv  = "Indiquez ici la surface globale (ares,ca) de jeunes vignes (toutes appellations confondues). Les jeunes vignes correspondent aux 2 premières années de plantation, avant la 3e feuille).";
		$json->help_popup_validation  = "Dans cet écran vous trouvez le récapitulatif de votre récolte par appellation en fonction de ce que vous avez saisi dans les étapes précédentes. Pour toute modification vous pouvez retournez à l'étape précédente, soit par le bouton  \"retournez à l'étape précédente\" soit en cliquant sur l'étape 2 \"récolte\" en haut de l'écran";
		$json->help_popup_validation_log_erreur  = "Le logiciel vous informe ici des problèmes rencontrés sur votre déclaration. Ces erreurs sont bloquantes. Vous ne pourrez donc pas valider définitivement votre déclaration si elles ne sont pas règlées.<br />  En cliquant sur le message vous retournez automatiquement à l'endroit où se trouve le problème et vous pouvez effectuer la modification";
		$json->help_popup_validation_log_vigilance  = "Le logiciel vous informe ici des problèmes rencontrés sur votre déclaration. Ces erreurs ne sont pas bloquantes et ne vous empêcheront pas de valider votre déclaration de récolte. <br /> En cliquant sur le message vous retournez automatiquement à l'endroit où se trouve le problème et vous pouvez effectuer la modification si besoin";
		$json->intro_mon_espace_civa_dr  = "Vous pouvez interrompre la saisie de votre déclaration à tout moment, vos données seront conservées.";
		$json->intro_mon_espace_civa_dr_validee  = "Vous avez déjà validé votre déclaration. Vous ne pouvez plus la modifier. Vous pouvez uniquement la visualiser et l'imprimer. En cas de problème contactez au CIVA  Dominique WOLFF ou Béatrice FISCHER";
		$json->intro_mon_espace_civa_dr_non_disponible  = "Le service est momentanément indisponible. Essayez de vous reconnecter ultérieurement.";
		$json->intro_mon_espace_civa_dr_non_editable  = "La date limite pour la souscription des déclarations de récolte est dépassée (10 décembre). Pour toute question, veuillez contacter directement le CIVA.";
		$json->intro_mon_espace_civa_dr_non_ouverte  = "Le Téléservice pour la déclaration de récolte 2012 sera ouvert à partir du 1er novembre.\n";
		$json->intro_gamma  = "Vous avez accès ici à l'application AlsaceGamm@.";
		$json->intro_doc_aide  = "En cas de besoin n'hésitez pas à consulter la notice d'aide complete au format pdf.";
		$json->intro_exploitation_administratif  = "Données administratives, n'hésitez pas à les modifier en cas de changement.";
		$json->intro_exploitation_acheteurs  = "Veuillez cocher les cases correspondantes à la répartition de votre récolte.";
		$json->intro_exploitation_lieu_grdcru  = "Dans l'écran précédent vous avez cochez  AOC Alsace Grand Cru. Vous devez maintenant ici sélectionner les lieux-dits pour lesquels vous avez récolté de l'AOC Alsace Grand Cru. Vous pouvez à tout moment supprimer la sélection à l'aide de la croix rouge.";
		$json->intro_exploitation_lieu_communale  = "Dans l'écran précédent vous avez coché \"Alsace communale\". Vous devez maintenant ici sélectionner les appellations communales concernées. Vous pouvez à tout moment supprimer la sélection à l'aide de la croix rouge.";
		$json->intro_exploitation_lieu_txt_gris_grdcru  = "Lieux-dits Grand Cru :";
		$json->intro_exploitation_lieu_txt_gris_communale  = "Communales :";
		$json->intro_declaration_recolte  = "Saisissez ici votre récolte par cépage dans chacune des appellations que vous avez sélectionnée au début de votre déclaration";
		$json->intro_exploitation_autres  = "Saisissez ici vos jeunes vignes sans production.\n";
		$json->intro_validation  = "Veuillez vérifier les informations saisies avant de valider votre déclaration. Vous pouvez à tout moment visualiser votre déclaration de récolte au format pdf en cliquant sur le bouton \"prévisualiser\" en bas de l'écran";
		$json->msg_declaration_ecran_warning_precedente  = "<u>Attention :</u> afin de respecter les dispositions prévues dans le cahier des charges de l'AOC Alsace en matière de revendication des  nouvelles appellations  \"communales\" et \"lieux-dits géographiques\" :<ul><li>le Klevener de Heiligenstein doit être déclaré maintenant dans l'onglet \"Alsace Communale\". Si vous démarrez votre déclaration 2012 à partir de la déclaration d'une année précédente dans laquelle vous aviez déclaré du Klevener de Heiligenstein, les données correspondantes au KdeH ne seront pas récupérées (lié à des difficultés techniques)</li><li>les \"lieux-dits géographiques\" revendiqués seraient à saisir dans l'onglet \"Alsace lieu-dit\"</li></ul></li>\n";
		$json->msg_declaration_ecran_warning_pre_import  = "Ci-dessous la liste de vos caves ou acheteurs qui ont pré-rempli à ce jour les données vous concernant :";
		$json->msg_declaration_ecran_warning_post_import  = "Vous pourrez aisément compléter, rectifier et valider les données pré-remplies.";
		$json->intro_exploitation_lieu_txt_consigne_communale  = "Sélectionnez une commune dans la liste suivante :";
		$json->intro_exploitation_lieu_txt_label_grdcru  = "Ajoutez un lieu-dit Grand Cru :";
		$json->help_popup_DR_lieu_dit = "Vous avez coché une case \"AOC Alsace lieu-dit\" dans l'écran \"répartition de la récolte\". Vous devez renseigner ici le nom du ou des lieux-dits géographiques concernés";
		$json->notice_evolutions_2012  = "<h2>Lies </h2><p>Sur la déclaration de récolte, la rubrique « LIES » a été supprimée. La rubrique DPLC est remplacée par « volumes à envoyer à la distillation et aux usages industriels » dans laquelle vous totalisez à la fois les lies et les éventuels dépassements de rendements  (instructions DGDDI du 9/5/2012).</p><p>Vous devez déclarer en entrée  le volume total récolté par cépage (même si vous avez déjà effectué des soutirages).</p><p>Il n’y a pas de perte de volume commercialisable puisque vous pouvez revendiquer dans la rubrique volume revendiqué,  le maximum autorisé en vin clair dans la catégorie concernée.</p><p>Les lies connues et les dépassements éventuels sont déclarés sans distinction dans la rubrique « usages industriels ». Si vous êtes en dessous du rendement maximum et que vous souhaitez déclarer vos soutirages,  vous les déclarez globalement,  pour chaque appellation, dans la rubrique correspondante de l’écran<br/> « récapitulatif des sorties ».</p><p>Toutes les lies doivent désormais transiter par la  DRM. Vous continuez à gérer distinctement  les 2 produits (lies et DPLC )  à la fois dans votre cave et sur votre DRM. Pour plus de précisions sur cette nouveauté voir page 9 de la notice explicative que vous pouvez télécharger <b><a id='telecharger_notice_evolutions' href='#'>Télécharger ici</a></b></p><br/><h2>Rendements VT et SGN </h2><p>pas de changement</p><br/><h2>Rendement maximum Crémant d’Alsace </h2><p>80 hl/ha tous cépages et couleurs confondues (sauf instructions contraires)</p><br/>";
		$json->err_log_lieu_has_too_many_usages_industriels  = "Vous avez déclaré trop de volume dans les usages industriels par rapport à votre volume sur place";
		$json->help_popup_DR_recap_appellation_usage_industriel  = "A définir";

		$docs[] = $json;
		if ($options['import'] == 'couchdb') {
			foreach ($docs as $data) {
				$doc = sfCouchdbManager::getClient()->retrieveDocumentById($data->_id);
				if ($doc) {
					// $doc->delete();
				}
				$doc = sfCouchdbManager::getClient()->createDocumentFromData($data);
				$doc->save();
			}
			return;
		}
		echo '{"docs":';
		echo json_encode($docs);
		echo '}';
	}
}

