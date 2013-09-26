/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


(function($)
{
	$.fn.initBlocCondition = function()
	{
		$(this).find('.bloc_condition').each(function() {
			checkUncheckCondition($(this));
		});
	}

	var checkUncheckCondition = function(blocCondition)
    {
    	var input = blocCondition.find('input');
    	var blocs = blocCondition.attr('data-condition-cible').split('|');
    	var traitement = function(input, blocs) {
		if(input.is(':checked'))
            {
        	   for (bloc in blocs) {
        		   if ($(blocs[bloc]).length>0) {
            		   var values = $(blocs[bloc]).attr('data-condition-value').split('|');
            		   for(key in values) {
            			   if (values[key] == input.val()) {
            				   $(blocs[bloc]).show();
            			   }
            		   }
        		   }
        	   }
            }
    	}
    	if(input.length == 0) {
     	   for (bloc in blocs) {
  				$(blocs[bloc]).show();
     	   }
    	} else {
     	   for (bloc in blocs) {
  				$(blocs[bloc]).hide();
     	   }
    	}
    	input.each(function() {
    		traitement($(this), blocs);
    	});

        input.click(function()
        {
      	   for (bloc in blocs) {
 				$(blocs[bloc]).hide();
    	   }
      	   if($(this).is(':checkbox')) {
          	   $(this).parent().find('input').each(function() {
	        	   traitement($(this), blocs);
          	   });
      	   } else {
      		   traitement($(this), blocs);
      	   }
        });
	}
	
	var initCollectionAddTemplate = function(element, regexp_replace, callback)
	{
		
	    $(element).live('click', function()
	    {
	    	var lien = $(this);
	        var ligneParent = lien.parents('tr');
	    	var bloc_html = $(lien.attr('data-template')).html().replace(regexp_replace, UUID.generate());
	    	var selecteurLigne = lien.attr('data-container-last-brother');
	    	var bloc;

	        try {
				var params = jQuery.parseJSON(lien.attr('data-template-params'));
			} catch (err) {

	        }

			for(key in params) {
				bloc_html = bloc_html.replace(new RegExp(key, "g"), params[key]);
			}
			
			
			if (selecteurLigne) {

				if(ligneParent.find('~ '+selecteurLigne).first().size() > 0)
				{
					bloc = ligneParent.find('~ '+selecteurLigne).first().before(bloc_html);
				}
				else
				{
					bloc = ligneParent.parent().append(bloc_html);
				}
				
			} else {
				bloc = $($(this).attr('data-container')).append(bloc_html);
			}

	        if(callback) {
	        	callback(bloc);
	        }
	        return false;
	    });
	};
	
	var initCollectionDeleteTemplate = function()
	{
		$('.btn_supprimer_ligne_template').live('click',function()
	    {
	    	var element = $(this).attr('data-container');
	        $(this).parents(element).remove();
	
	        return false;
	    });
	}
        
        
        
        /**
 * Initalise la popup previsualisation des Contrat
 ******************************************/
var initValidContratPopup = function()
{
    $('#previsualiserContrat').click(function() {
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
	
    var callbackAddTemplate = function(bloc) 
    {

    }

    /**
	*  Gère les champs du tableaux de 
	*  l'étape produits de la création de contrats
	******************************************/
	var initChampsTableauProduits = function()
	{
		var tableau = $('#contrats_vrac .table_donnees'),
			lignes_tableau = tableau.find('tr');

		// On parcourt chaque ligne
		lignes_tableau.each(function()
		{
			var ligne_courante = $(this),
				champs = ligne_courante.find('input'),
				champs_requis = champs.filter('.volume input').add(champs.filter('.prix_unitaire input')),
				btn_balayette = ligne_courante.find('a.balayette'),
				champs_vides = true; 

			// Ligne active
			champs.focus(function()
			{
				ligne_courante.addClass('actif');
			});

			// Affichage du picto coche
			champs.blur(function()
			{
				champs.each(function(i)
				{
					var champ_volume = ligne_courante.find('.volume input'),
						champ_prix = ligne_courante.find('.prix_unitaire input');

					if($.trim(champ_volume.val()) !== '' && $.trim(champ_prix.val()) !== '')
					{
						ligne_courante.addClass('coche');
					}else
					{
						ligne_courante.removeClass('coche');
					}

					if($.trim($(this).val()) !== '')
					{
						champs_vides = false;
					}
				});

				if(champs_vides)
				{
					ligne_courante.removeClass('actif');
				}
			});

			// Affichage du picto balayette
			ligne_courante.hover
			(
				function()
				{
					champs.each(function()
					{
						if($.trim($(this).val()) !== '')
						{
							ligne_courante.addClass('effacable');
						}
					});
				},

				function()
				{
					$(this).removeClass('effacable')
				}
			);

			btn_balayette.click(function()
			{
				champs.val('');
				ligne_courante.removeClass('coche effacable actif');
			});
		});
	};

	$(document).ready(function()
	{
		 $(this).initBlocCondition();
		 initCollectionAddTemplate('.btn_ajouter_ligne_template', /var---nbItem---/g, callbackAddTemplate);
		 initCollectionDeleteTemplate();
                 initValidContratPopup();
         initChampsTableauProduits();
         hauteurEgale('#contrats_vrac .soussignes .cadre');

         hauteurEgale('#contrats_vrac .bloc_annuaire .bloc');
	});
})(jQuery);