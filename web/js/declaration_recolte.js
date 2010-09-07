/**
 * Fichier : declaration_recolte.js
 * Description : fonctions JS spécifiques à la déclaration de récolte
 * Auteur : Hamza Iqbal - hiqbal[at]actualys.com
 * Copyright: Actualys
 ******************************************/

/**
 * Initialisation
 ******************************************/
$(document).ready( function()
{
    initMsgAide();
    $('#onglets_majeurs').ready( function() {
        initOngletsMajeurs();
    });
    $('#precedentes_declarations').ready( function() {
        accordeonPrecDecla();
    });
    $('#nouvelle_declaration').ready( function() {
        choixPrecDecla();
    });
    if ($('#exploitation_administratif').length > 0) {
        $('#exploitation_administratif').ready( function() {
            formExploitationAdministratif();
        });
    }
    if ($('#modification_compte').length > 0) {
        $('#modification_compte').ready( function() {
            formModificationCompte();
        });
    }
    $('.table_donnees').ready( function() {
        initTablesDonnes();
    });
	
    $('input.num').live('keypress',function(e)
    {
        var val = $(this).val();

        val = val.replace(',', '.');
        $(this).val(val);
		
        if(e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57))
            return false;
        else
        if(e.which == 46 && val.indexOf('.') != -1)
            return false;
    });
    if ($('#exploitation_acheteurs').length > 0) {
        $('#exploitation_acheteurs').ready( function() {
            initTablesAcheteurs();
        });
    }
    if ($('#gestion_recolte').length > 0) {
        $('#gestion_recolte').ready( function() {
            initGestionRecolte();
        });
    }
	
    var annee = new Date().getFullYear();
	
    $('.datepicker').datepicker(
    {
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd/mm/yy',
        dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
        firstDay: 1,
        monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        monthNamesShort: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        yearRange: '1900:'+annee
    });

    $(document).find('a.btn_inactif').click(function() {
       return false;
    });

});


/**
 * Onglets majeurs
 ******************************************/
var initOngletsMajeurs = function()
{
    var onglets = $('#onglets_majeurs');
	
    hauteurEgale(onglets.find('>li>a'));
    hauteurEgale(onglets.find('ul.sous_onglets li a'));
    if(onglets.hasClass('ui-tabs-nav')) $("#principal").tabs();
};

/**
 * Messages d'aide
 ******************************************/
var initMsgAide = function()
{
    var liens = $('a.msg_aide');
    var popup = $('#popup_msg_aide');
	
    liens.live('click', function()
    {
        var id_msg_aide = $(this).attr('id');
		
        $.getJSON(
            url_ajax_msg_aide,
            {
                action: "popup_msg_aide",
                id_msg_aide: id_msg_aide
            },
            function(json)
            {
                var titre = json.titre;
                var message = json.message;
                popup.find('p').text(message);
				
                popup.dialog(
                {
                    draggable: false,
                    minHeight: 200,
                    modal: true,
                    resizable: false,
                    title: titre,
                    width: 375
                });
            }
            );
		
        return false;
    });
};

/**
 * Choix d'un précédente déclaration
 ******************************************/
var choixPrecDecla = function()
{
    var nouvelle_decla = $('#nouvelle_declaration');
    var liste_prec_decla = nouvelle_decla.find('select');
    var type_decla = nouvelle_decla.find('input[name="dr[type_declaration]"]');
	
    liste_prec_decla.hide();
	
    type_decla.change(function()
    {
        if(type_decla.filter(':checked').val() == 'vierge') liste_prec_decla.hide();
        else liste_prec_decla.show();
    });
};

/**
 * Accordéon précédentes déclarations
 ******************************************/
var accordeonPrecDecla = function()
{
    $('#precedentes_declarations ul.ui-accordion').accordion(
    {
        autoHeight: false,
        active: 0
    });
};

/**
 * Formulaire de modification des infos
 * de l'exploitation
 ******************************************/
var formExploitationAdministratif = function()
{
    var blocs = $('#infos_exploitation, #gestionnaire_exploitation');
    var btns_modifier = blocs.find('a.modifier');
	
    blocs.each(function()
    {
        var bloc = $(this);
        var presentation_infos = bloc.find('.presentation');
        var modification_infos = bloc.find('.modification');
        var btn = bloc.find('.btn');
        var btn_modifier = btn.find('a.modifier');
        var btn_annuler = btn.find('a.annuler');

        modification_infos.hide();
		
        btn_modifier.click(function()
        {
            presentation_infos.hide();
            modification_infos.show();
            btns_modifier.hide();
            bloc.addClass('edition');
            return false;
        });
		
        btn_annuler.click(function()
        {
            presentation_infos.show();
            modification_infos.hide();
            btns_modifier.show();
            bloc.removeClass('edition');
            return false;
        });
    });
};

/**
 * Formulaire de modification des identifiants
 ******************************************/
var formModificationCompte = function()
{
    var bloc = $('#modification_compte');

    var presentation_infos = bloc.find('.presentation');
    var modification_infos = bloc.find('.modification');
    var btn = bloc.find('.btn');
    var btn_modifier = btn.find('a.modifier');
    var btn_annuler = btn.find('a.annuler');

    // modification_infos.hide();

    btn_modifier.click(function()
    {
        presentation_infos.hide();
        modification_infos.show();
        $("a.modifier").hide();
        bloc.addClass('edition');
        return false;
    });

    btn_annuler.click(function()
    {
        presentation_infos.show();
        modification_infos.hide();
        $("a.modifier").show();
        bloc.removeClass('edition');
        return false;
    });

};

/**
 * Initialise les fonctions des tables 
 * de données
 ******************************************/
var initTablesDonnes = function()
{
    var tables = $('table.table_donnees');
	
    tables.each(function()
    {
        var table = $(this);
        styleTables(table);
    });
};


/**
 * Ajoute les classes nécessaires pour la
 * mise en forme des tables
 ******************************************/
var styleTables = function(table)
{
    var tr = table.find('tbody tr');
	
    tr.each(function()
    {
        $(this).find('td:odd').addClass('alt');
    });
};

/**
 * Initialise les fonctions des tables 
 * d'acheteurs
 ******************************************/
var initTablesAcheteurs = function()
{
    var tables_acheteurs = $('#exploitation_acheteurs table.tables_acheteurs');
	
    tables_acheteurs.each(function()
    {
        var table_achet = $(this);
		
        var bloc = table_achet.parent();
        var form_ajout = bloc.next(".form_ajout");
        var btn_ajout = bloc.children('.btn');
		
        if(bloc.attr('id') != 'vol_sur_place')
        {
            toggleTrVide(table_achet);
            supprimerLigneTable(table_achet);
			
            initTableAjout(table_achet, form_ajout, btn_ajout);
            masquerTableAjout(table_achet, form_ajout, 0);
			
            btn_ajout.children('a.ajouter').click(function()
            {
                afficherTableAjout(table_achet, form_ajout, btn_ajout);
                return false;
            });
        }
    });

    initPopup($('#btn_etape li.prec input, #btn_etape li.suiv input'), $('#popup_msg_erreur'),
                    function() {
                        return (tables_acheteurs.find('input[type=checkbox]:checked').length < 1);
                    }
    );
};


/**
 * Affiche/masque la première ligne
 * d'un tableau
 ******************************************/
var toggleTrVide = function(table_achet)
{	
    var tr = table_achet.find('tbody tr');
    var tr_vide = tr.filter('.vide');
    tr_vide.next('tr').addClass('premier');

    if(tr.size()>1) tr_vide.hide();
    else tr_vide.show();
};

/**
 * Supprime une ligne de la table courante
 ******************************************/
var supprimerLigneTable = function(table_achet)
{
    var btn = table_achet.find('tbody tr a.supprimer');
	
    btn.live('click', function()
    {
        var choix = confirm('Confirmez-vous la suppression de cette ligne ?');
        if(choix)
        {
            $(this).parents('tr').remove();
            toggleTrVide(table_achet);
        }
        return false;
    });
};


var filtrer_source = function(i)
{
    return i['value'].split('|@');
};

/**
 * Initialise les fonctions des tables 
 * d'ajout
 ******************************************/
var initTableAjout = function(table_achet, form_ajout, btn_ajout)
{
    var table_ajout = form_ajout.find('table');
    var source_autocompletion = eval(table_ajout.attr('rel'));
    var champs = table_ajout.find('input');
    var nom = table_ajout.find('td.nom input');
    var cvi = table_ajout.find('td.cvi');
    var commune = table_ajout.find('td.commune');
    var btn = form_ajout.find('.btn a');
    var acheteur_mouts = 0;
    var qualite_name = '';
	
    nom.autocomplete(
    {
        minLength: 0,
        source: source_autocompletion,
        focus: function(event, ui)
        {
            nom.val(ui.item[0]);
            cvi.find('span').text(ui.item[1]);
            cvi.find('input').val(ui.item[1]);
            commune.find('span').text(ui.item[2]);
            commune.find('input').val(ui.item[2]);
			
            return false;
        },
        select: function(event, ui)
        {
            nom.val(ui.item[0]);
            cvi.find('span').text(ui.item[1]);
            cvi.find('input').val(ui.item[1]);
            commune.find('span').text(ui.item[2]);
            commune.find('input').val(ui.item[2]);
				
            return false;
        }
    });
	
	
    nom.data('autocomplete')._renderItem = function(ul, item)
    {
        var tab = item['value'].split('|@');
		
        return $('<li></li>')
        .data("item.autocomplete", tab)
        .append('<a><span class="nom">'+tab[0]+'</span><span class="cvi">'+tab[1]+'</span><span class="commune">'+tab[2]+'</span></a>' )
        .appendTo(ul);
    };
	
    btn.click(function()
    {
        if(table_achet.parent().attr('id') == 'acheteurs_mouts') acheteur_mouts = 1;

        qualite_name = form_ajout.attr('rel');
		
        if($(this).hasClass('valider'))
        {
            if(cvi.find('input').val() == '')
            {
                alert("Veuillez renseigner le nom de l'acheteur");
                return false;
            }
            else
            {
                var donnees = Array();
				
                champs.each(function()
                {
                    var chp = $(this)
                    if(chp.attr('type') == 'text' || chp.attr('type') == 'hidden') donnees.push(chp.val());
                    else
                    {
                        if(chp.is(':checked')) donnees.push("1");
                        else donnees.push("0");
                    }
                });
				
                $.post(url_ajax,
                {
                    action: "ajout_ligne_table",
                    donnees: donnees,
                    acheteur_mouts: acheteur_mouts,
                    qualite_name: qualite_name
                },
                function(data)
                {
                    var tr = $(data);
                    tr.appendTo(table_achet);
                    toggleTrVide(table_achet);
                    styleTables(table_achet);
                });
            }
        }
		
        masquerTableAjout(table_achet, form_ajout, 1);
        btn_ajout.show();
		
        return false;
    });
};

/**
 * Masque les tables d'ajout
 ******************************************/
var masquerTableAjout = function(table_achet, form_ajout, nb)
{
    var table = form_ajout.find('table');
    var spans = form_ajout.find('tbody td span');
    var champs_txt = table.find('input:text,input[type=hidden]');
    var champs_cb = table.find('input:checkbox');
	
    spans.text('');
    
    champs_txt.attr('value','');
    champs_cb.attr('checked','');
    champs_cb.filter('.cremant_alsace').attr('checked','checked');
	
    form_ajout.hide();
    if(nb == 1) etatChampsTableAcht('');
};

/**
 * Afficher table ajout
 ******************************************/
var afficherTableAjout = function(table_achet, form_ajout, btn_ajout)
{
    form_ajout.show();
    btn_ajout.hide();
    etatChampsTableAcht('disabled')
};

/**
 * Active/Désactive tous les champs des
 * tables d'acheteurs
 ******************************************/
var etatChampsTableAcht = function(type)
{
    var tables_acheteurs = $('#exploitation_acheteurs table.tables_acheteurs');
    var champs = tables_acheteurs.find('input:checkbox');
    var btns_supprimer = tables_acheteurs.find('a.supprimer');
    var btns = tables_acheteurs.next('.btn');
	
    if(type == 'disabled')
    {
        champs.attr('disabled', 'disabled');
        btns_supprimer.hide();
        btns.hide();
    }
    else
    {
        champs.attr('disabled', '');
        btns_supprimer.show();
        btns.show();
    }
};





/**
 * Initialise les fonctions de la gestion
 * de récolte
 ******************************************/
var initGestionRecolte = function(type)
{
    /*var col_intitules = $('#colonne_intitules');
    var col_scroller = $('#col_scroller');
    var col_scroller_cont = col_scroller.find('#col_scroller_cont');
    var col_recolte = col_scroller.find('.col_recolte');
    var col_cepage_total = $('#col_cepage_total');
    var col_recolte_totale = $('#col_recolte_totale');
	
    var btn_ajout_col = col_scroller.find('a#ajout_col');
    */

    //etatBtnAjoutCol();
    hauteurEgaleColRecolte();
    largeurColScrollerCont();
	
    /*btn_ajout_col.click(function()
    {
        if(!$(this).hasClass('inactif')) ajouterColRecolte($(this), col_scroller_cont);
        return false;
    });*/
	
    /*col_recolte.each(function()
    {
        var col = $(this);
        initColRecolte(col);
    });*/
	
    initDRPopups();
    //Calcule auto du volume
    $('input.volume').change(volumeOnChange);
    $('#recolte_superficie').change(superficieOnChange);
};
var updateElementRows = function (inputObj, totalObj) {
    totalObj.val(0);
    inputObj.each(function() {
	    var total = parseFloat(totalObj.val());
	    var element = parseFloat($(this).val());
	    if (element)
		totalObj.val(total + element);
	});
};
var updateAppellationTotal = function (cepageCssId, appellationCssId) {
    var app_orig = parseFloat($(appellationCssId+'_orig').val());
    if (!app_orig)
	app_orig = 0;
    var cep_orig = parseFloat($(cepageCssId+'_orig').val());
    if (!cep_orig)
	cep_orig = 0;
    var cep_now  = parseFloat($(cepageCssId).val());
    if (!cep_now)
	cep_now = 0;
    $(appellationCssId).val(app_orig - cep_orig + cep_now);
}
var superficieOnChange = function() {
    updateElementRows($('input.superficie'), $('#cepage_total_superficie'));
    updateAppellationTotal('#cepage_total_superficie', '#appellation_total_superficie');
    $('#detail_max_volume').val(parseFloat($('#recolte_superficie').val())/100 * parseFloat($('#detail_rendement').val()));
    $('#appellation_max_volume').val(parseFloat($('#appellation_total_superficie').val())/100 * parseFloat($('#appellation_rendement').val()));
    volumeOnChange();
};
var updateRevendiqueDPLC = function (totalRecolteCssId, elementCssId) {
    if (parseFloat($(totalRecolteCssId).val()) > parseFloat($(elementCssId+'_max_volume').val()))
	$(elementCssId+'_volume_revendique').val($(elementCssId+'_max_volume').val());
    else 
	$(elementCssId+'_volume_revendique').val($(totalRecolteCssId).val());
    $(elementCssId+'_volume_dplc').val(parseFloat($(totalRecolteCssId).val()) - parseFloat($(elementCssId+'_volume_revendique').val()));
};

var addClassAlerteIfNeeded = function (inputObj) 
{
    inputObj.removeClass('alerte');
    if (inputObj.val() != '0')
	inputObj.addClass('alerte');
};

var volumeOnChange = function() {
    updateElementRows($('input.volume'), $('#detail_vol_total_recolte'));
    updateRevendiqueDPLC('#detail_vol_total_recolte', '#detail');

    $('ul.acheteurs li').each(function () {
	    var class = $(this).attr('class');
	    updateElementRows($('#col_scroller input.'+class), $('#col_cepage_total input.'+class));
	    updateAppellationTotal('#col_cepage_total input.'+class, '#col_recolte_totale input.'+class);
	});

    updateElementRows($('input.cave'), $('#cepage_total_cave'));
    updateAppellationTotal('#cepage_total_cave', '#appellation_total_cave');

    updateElementRows($('input.total'), $('#cepage_total_volume'));
    updateAppellationTotal('#cepage_total_volume', '#appellation_total_volume');

    updateElementRows($('input.revendique'), $('#cepage_total_revendique'));
    updateAppellationTotal('#cepage_total_revendique', '#appellation_total_revendique_sum');

    updateElementRows($('input.dplc'), $('#cepage_total_dplc'));
    updateAppellationTotal('#cepage_total_dplc', '#appellation_total_dplc_sum');

    updateRevendiqueDPLC('#appellation_total_volume', '#appellation');

    addClassAlerteIfNeeded($('#cepage_total_dplc'));
    addClassAlerteIfNeeded($('#appellation_total_dplc_sum'));
    addClassAlerteIfNeeded($('#appellation_volume_dplc'));
	
    $('#appellation_total_dplc_sum').val('Σ '+$('#appellation_total_dplc_sum').val());
    $('#appellation_total_revendique_sum').val('Σ '+$('#appellation_total_revendique_sum').val());

    $('#cepage_current_rendement').html(parseFloat($('#cepage_total_volume').val()) / (parseFloat($('#cepage_total_superficie').val()/100)));
    $('#appellation_current_rendement').html(parseFloat($('#appellation_total_volume').val()) / (parseFloat($('#appellation_total_superficie').val()/100)));

};

/**
 * Egalise les hauteurs des colonnes
 ******************************************/
var hauteurEgaleColRecolte = function()
{	
    var col_intitules = '#colonne_intitules';
	
    hauteurEgaleLignesRecolte(col_intitules+' p', 'p');
    hauteurEgaleLignesRecolte(col_intitules+' li', 'li');
    $(col_intitules + ', #col_scroller .col_recolte .col_cont, #gestion_recolte .col_total .col_cont').height('auto');
    hauteurEgale(col_intitules + ', #col_scroller .col_recolte .col_cont, #gestion_recolte .col_total .col_cont');
};

var hauteurEgaleLignesRecolte = function(intitule, elem)
{
    var col_recolte = '#col_scroller .col_recolte';
    var col_total = '#gestion_recolte .col_total'
	
    $(intitule).each(function(i)
    {
        var s = intitule+':eq('+i+')';
		
        $(col_recolte).each(function(j)
        {
            s += ', '+col_recolte+':eq('+j+') .col_cont '+elem+':eq('+i+')';
        });
		
        $(col_total).each(function(j)
        {
            s += ', '+col_total+':eq('+j+') .col_cont '+elem+':eq('+i+')';
        });
		
        hauteurEgale(s);
    });
};

/**
 * Etat du bouton d'ajout de colonne
 ******************************************/
/*var etatBtnAjoutCol = function()
{
    var col_recolte = $('#col_scroller .col_recolte');
    var btn = $('a#ajout_col');
	
    if(col_recolte.filter('.col_active').size() > 0) btn.addClass('inactif');
    else btn.removeClass('inactif');
};*/

/**
 * Initialise les fonctions des colonnes
 ******************************************/
/*var initColRecolte = function(col)
{
    var contenu = col.find('.col_cont');
    var champs = contenu.find('input:text, select');
	
    var btn = col.find('.col_btn');
    var btn_modifier = btn.find('a.modifier');
    var btn_supprimer = btn.find('a.supprimer');
    var btn_annuler = btn.find('a.annuler');
    var btn_valider = btn.find('a.valider');
	
	
    btn_modifier.click(function()
    {
        col.addClass('col_active').removeClass('col_validee');
        champs.attr('disabled', '');
        etatBtnAjoutCol();
        return false;
    });
	
    btn_supprimer.click(function()
    {
        col.remove();
        largeurColScrollerCont();
        return false;
    });
	
    btn_annuler.click(function()
    {
        return false;
    });
	
    btn_valider.click(function()
    {
        col.addClass('col_validee').removeClass('col_active');
        champs.attr('disabled', 'disabled');
        etatBtnAjoutCol();
        return false;
    });
};*/

/**
 * Ajoute une colonne pour la déclaration
 ******************************************/
/*var ajouterColRecolte = function(btn, cont)
{	
    $.post(url_ajax,
    {
        action: "ajout_col_recolte"
    },
    function(data)
    {
        var col = $(data);
        col.insertBefore(btn);
        hauteurEgaleColRecolte();
        initColRecolte(col);
        etatBtnAjoutCol();
        largeurColScrollerCont();
    });
};*/

/**
 * Largeur colonne scroll conteneur
 ******************************************/
var largeurColScrollerCont = function()
{
    var cont = $('#col_scroller_cont');
    var cols = cont.find('.col_recolte');
    var btn = cont.find('a#ajout_col');
	
    var largeur = btn.width();
	
    cols.each(function()
    {
        largeur += $(this).width() + parseInt($(this).css('marginRight'));
    });

    cont.width(largeur);
    cont.scrollTo( {
        left:largeur
    }, 800 );
//cont.parent().scrollTo('+='+largeur_cont+'px');
};



/**
 * Initalise les popups de DR
 ******************************************/
var initDRPopups = function()
{
    var onglets = $('#onglets_majeurs');
    var btn_ajout_appelation = onglets.find('li.ajouter_appelation a');
    var btn_ajout_lieu = onglets.find('li.ajouter_lieu a');
    var col_recolte_cont = $('#col_scroller_cont');
    var btn_ajout_acheteur = col_recolte_cont.find('a.ajout_acheteur');
    var btn_ajout_cave = col_recolte_cont.find('a.ajout_cave');
    var btn_ajout_mout = col_recolte_cont.find('a.ajout_mout');
    var btn_ajout_motif = col_recolte_cont.find('a.ajout_motif');
	
    var popup_ajout_appelation = $('#popup_ajout_appelation');
    var popup_ajout_lieu = $('#popup_ajout_lieu');
    var popup_ajout_acheteur = $('#popup_ajout_acheteur');
    var popup_ajout_cave = $('#popup_ajout_cave');
    var popup_ajout_mout = $('#popup_ajout_mout');
    var popup_ajout_motif = $('#popup_ajout_motif');

    var config_default = {
        ajax: false,
        auto_open: false
    };
    initPopupAjout(btn_ajout_appelation, popup_ajout_appelation, config_default);
    initPopupAjout(btn_ajout_lieu, popup_ajout_lieu, config_default);
    initPopupAjout(btn_ajout_acheteur, popup_ajout_acheteur,config_default, var_liste_acheteurs);
    initPopupAjout(btn_ajout_cave, popup_ajout_cave, config_default, var_liste_caves);
    initPopupAjout(btn_ajout_mout, popup_ajout_mout, config_default, var_liste_acheteurs);
    initPopupAjout(btn_ajout_motif, popup_ajout_motif, var_config_popup_ajout_motif);
    
};

var initPopupAjout = function(btn, popup, config, source_autocompletion)
{
    popup.dialog
    ({
        autoOpen: false,
        draggable: false,
        resizable: false,
        width: 375,
        modal: true
    });
	
    btn.live('click', function()
    {
	if(config.ajax == true) {
            loadContentPopupAjax(popup, btn.attr('href'), config);
        }
        popup.dialog('open');
        return false;
    });
    if (config.auto_open == true) {
        loadContentPopupAjax(popup, config.auto_open_url, config);
        popup.dialog('open');
    }
    if(source_autocompletion) initPopupAutocompletion(popup, source_autocompletion);
};

var loadContentPopupAjax = function(popup, url, config)
{
    $(popup).html('<div class="ui-autocomplete-loading popup-loading"></div>');
    $(popup).load(url);
}

var initPopupAutocompletion = function(popup, source_autocompletion)
{
    var nom = popup.find('input.nom');
    var cvi = popup.find('input.cvi');
    var commune = popup.find('input.commune');
    var type_cssclass = popup.find('input[name=type_cssclass]').val();
    var type_name_field = popup.find('input[name=type_name_field]').val();
    var btn = popup.find('input[type=image]');
    var form = popup.find('form');

    $(popup).bind( "dialogclose", function(event, ui) {
        nom.val('');
        cvi.val('');
        commune.val('');
    });
	
    nom.autocomplete(
    {
        minLength: 0,
        source: source_autocompletion,
        focus: function(event, ui)
        {
            nom.val(ui.item[0]);
            cvi.val(ui.item[1]);
            commune.val(ui.item[2]);
			
            return false;
        },
        select: function(event, ui)
        {
            nom.val(ui.item[0]);
            cvi.val(ui.item[1]);
            commune.find('input').val(ui.item[2]);
				
            return false;
        }
    });
	
    nom.data('autocomplete')._renderItem = function(ul, item)
    {
        var tab = item['value'].split('|@');
		
        return $('<li></li>')
        .data("item.autocomplete", tab)
        .append('<a><span class="nom">'+tab[0]+'</span><span class="cvi">'+tab[1]+'</span><span class="commune">'+tab[2]+'</span></a>' )
        .appendTo(ul);
    };

    btn.click(function()
    {
        if(cvi.val()=='')
        {
            alert("Veuillez renseigner le nom de l'acheteur");
            return false;
        }
        else
        {
            $.post(form.attr('action'),
            {
                cvi: cvi.val(),
                form_name: type_name_field
            },
            function(data)
            {
                var html_header_item = $('#acheteurs_header_empty').clone();
                var css_class_acheteur = 'acheteur_' + type_name_field + '_' + cvi.val();
                html_header_item.find('li').
                html(nom.val()).
                addClass(css_class_acheteur);
                $('#colonne_intitules').
                find('.'+type_cssclass+' ul').
                append(html_header_item.html());
                $('.col_recolte.col_validee, .col_recolte.col_cepage_total, .col_recolte.col_total').
                find('.'+type_cssclass+' ul').
                append($('#acheteurs_item_empty').html()).
                find('input').addClass(css_class_acheteur);
                $('.col_recolte.col_active').
                find('.'+type_cssclass+' ul').
                append(data);
                popup.dialog('close');
                hauteurEgaleColRecolte();
		$('input.volume').change(volumeOnChange);

            });

            return false;
        }
		
    });
};

/**
 * Initialise une popup
 ******************************************/
var openPopup = function(popup, fn_open_if) {
    
    popup.dialog
    ({
        autoOpen: false,
        draggable: false,
        resizable: false,
        width: 375,
        modal: true
    });


    if (!fn_open_if || fn_open_if()) {
	popup.dialog('open');
	return false;
    }

    return true;
};

var initPopup = function(btn, popup, fn_open_if)
{
    btn.live('click', function()
    {
	return openPopup(popup, fn_open_if);
    });
};
