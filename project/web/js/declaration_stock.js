/**
 * Fichier : declaration_stock.js
 * Description : fonctions JS spécifiques à la déclaration de stock
 * Auteur : Mikaël Guillin - mguillin[at]actualys.com
 * Copyright: Actualys
 ******************************************/

var appDS = $('#application_ds');
var champsSommes = appDS.find('.ligne_total input.somme');

/**
 * Initialisation
 ******************************************/

var ajax_post_url = null;

$(document).ready(function()
{	
    //navOngletsStock();
    choixPrecDS();
    initDSSommesCol();
    initLieuxStockage();
    initLieuxStockageNeant();
    var ajaxForm = $('form.ajaxForm');
    if(ajaxForm.length > 0) {
        ajaxForm.ajaxPostForm();
    }
    initValidDSPopup();
	
    initConfirmeValidationDs();

    if ($('#validation_ds').length > 0) {
        $('#validation_ds').ready( function() {
            initValidationDs();
            initSendDSPopup();
        });
    }

    if ($('#confirmation_fin_stock').length > 0) {
        $('#confirmation_fin_stock').ready( function() {
            initValidationDs();
            initSendDSPopup();
        });
    }

	initStocks();
	
	scrollLieuxStockage();
});


/**
 * Initialise les fonctions de la validation
 * de récolte
 ******************************************/
var initValidationDs = function(type)
{
    initValidDSPopup();
    initConfirmeValidationDs();
}

var initLieuxStockage = function()
{
    var checkboxes = $(".table_donnees input");
    checkboxes.each(function(){
        $(this).click(function(){
            majLieuNeant();
            });
    });
    $("#ds_lieu_neant").change(function(){
        if($(this).attr("readonly")){
            return false;
        }
        majCheckboxesAppellation();
    });
};

// Donne le focus sur le premier select
var initStocks = function()
{
	if(appDS.find('#ds_add_produit_hashref').length > 0)
	{
		appDS.find('#ds_add_produit_hashref').focus();
	}
};

var majCheckboxesAppellation = function(){    
        if($("#ds_lieu_neant").is(":checked")){
            $(".table_donnees input").each(function(){
                $(this).attr("disabled","disabled");
            });
        }else{
        $(".table_donnees input").each(function(){
                $(this).removeAttr("disabled");
            }); 
        }
};

var majLieuNeant = function(){
    var one_checked = false;
    $(".table_donnees input").each(function(){
        if($(this).is(":checked")){
            one_checked = true;
        }
    });
    if(one_checked){
        $("#ds_lieu_neant").attr("readonly",true);
        initLieuxStockageNeant();
    }
    else
        $("#ds_lieu_neant").removeAttr("readonly");
            
};


/**
 * Calcul des sommes des colonnes de stocks
 *********************************************************/
var initDSSommesCol = function()
{
	// Parcours des champs
	champsSommes.each(function()
	{
		var champSomme = $(this);
		var col = $(champSomme.attr('data-somme-col'));
		var valDefaut = champSomme.attr('data-val-defaut');
		var somme = col.calculSommeCol();
		
		// Si une valeur par défaut existe
		if(valDefaut)
		{
			valDefaut = parseFloat(valDefaut);
			somme += valDefaut;
		}
		
		champSomme.val(somme.toFixed(2));
		
		// Initialisation de la somme automatique au blur
		if(!col.hasClass('init_somme_ok')) col.initDSColChamps();
	});
};

/**
 * Calcul des sommes automatiquement
 *********************************************************/
$.fn.initDSColChamps = function()
{
	var col = $(this);
	var champs = col.find('input.num');
	var somme = 0;
	
	champs.blur(function()
	{
		col.majDSSommesCol();
	});
	
	col.addClass('init_somme_ok');
};

/**
 * Calcul de la somme d'une colonne
 *********************************************************/
$.fn.calculSommeCol = function()
{
	var col = $(this);
	var champs = col.find('input.num');
	var somme = 0;
	
	champs.each(function()
	{
		var champ = $(this);
		var val = champ.val();
		
		if(!val) val = 0;
		val = parseFloat(val);
		
		somme += val;
	});
	
	return somme;
};



/**
 * Met à jour les sommes des colonnes de stocks
 *********************************************************/
$.fn.majDSSommesCol = function()
{
	var col = $(this);
	var id = col.attr('id');
	var champsSommesAssoc = champsSommes.filter('[data-somme-col=#'+id+']')
	var somme = col.calculSommeCol();
	
	champsSommesAssoc.each(function()
	{
		var champSomme = $(this);
		var valDefaut = champSomme.attr('data-val-defaut');
		
		// Si une valeur par défaut existe
		if(valDefaut)
		{
			valDefaut = parseFloat(valDefaut);
			somme += valDefaut;
		}
		
		champSomme.val(somme);
		champSomme.verifNettoyageChamp();
	});
};

$.fn.ajaxPostForm = function(){
        var form = $(this);
        var form_id = $(this).attr('id');
            
        $('#'+form_id+' .ajax').each(function(){
                $(this).click(function(e){
                    ajax_post_url = $(this).attr('href');
                    formPost(form);
                    e.preventDefault()
            }); 
        });
    
};

var formPost = function(form)
{
        appDS.find('input.num').each(function(){
            $(this).verifNettoyageChamp();
        });
        
        $.ajax({
            url: $(form).attr('action'),
            type: "POST",
            data: $(form).serializeArray(),
            dataType: "json",
            async : true,
            success: function(msg){if(ajax_post_url) {
                document.location.href=ajax_post_url;
            }},  
            error: function(textStatus){  
                form.submit();
            }
    });
};

/**
 * Gère la navigation des onglets
 *********************************************************/
var navOngletsStock = function()
{
	var onglets = $('#onglets_majeurs.onglets_stock > li');
	var sousMenus = onglets.find('.sous_onglets');
	
	onglets.each(function()
	{
		var ongletCourant = $(this);
		var sousMenuCourant = ongletCourant.find('.sous_onglets');
		
		ongletCourant.hover(function()
		{
			onglets.removeClass('ui-tabs-selected');
			sousMenus.addClass('invisible');
			
			if(!ongletCourant.hasClass('ui-tabs-selected'))
			{
				ongletCourant.addClass('ui-tabs-selected');
				sousMenuCourant.removeClass('invisible');
			}
		});
	});
};

	// Scroll automatique sur les lieux de stockage s'ils existent
var scrollLieuxStockage = function()
{
	var listeLieuxStockage = $('#liste_lieux_stockage');
	
	if(listeLieuxStockage.length > 0)
	{
		$.scrollTo(listeLieuxStockage, 800);
	} else if($('#onglets_majeurs').length > 0) {
		$.scrollTo('#onglets_majeurs', 800);
	}
};

var choixPrecDS = function()
{   
    $('#form_ds #mon_espace_civa_valider').click(function() {
        console.log('nop');
        if($('#type_ds_suppr:checked').length > 0) {
            return confirm('Etes vous sûr(e) de vouloir supprimer cette déclaration ?');
        }
    });


};

/**
 * Initalise la popup previsualisation de DS
 ******************************************/
var initValidDSPopup = function()
{
    $('#previsualiserDS').click(function() {
        openPopup($("#popup_loader"));
        $.ajax({
            url: ajax_url_to_print,
            success: function(data) {
                $('.popup-loading').empty();
                $('.popup-loading').css('background', 'none');
                $('.popup-loading').css('padding-top', '10px');
                $('.popup-loading').append('<p>Le PDF de votre déclaration de stock à bien été généré, vous pouvez maintenant le télécharger.<br /><br/><a href="'+data+'" class="telecharger-ds" title="Télécharger la DS"></a></p>');
                openPopup($("#popup_loader"));

            }
        });
        return false;
    });
};

/* Confirmation de la validation */

var initConfirmeValidationDs = function()
{
    $('#valideDS').click(function() {
        openPopup($("#popup_confirme_validationDS"));
        return false;
    });
    $('#valideDS_OK').click(function() {
        $("#popup_confirme_validationDS").dialog('close');
        $("#principal").submit();
        return false;
    });
}

var initSendDSPopup = function()
{
    $('#btn-email').click(function() {
        openPopup($("#popup_confirme_mail"));
        return false;
    });
}


var initLieuxStockageNeant = function()
{
    $('#ds_lieu_neant').click(function() {
        var lien = $(this);
        
        if(lien.attr('readonly')){
            openPopup($("#popup_ds_neant"));
            return false;
        } 
    });
}
