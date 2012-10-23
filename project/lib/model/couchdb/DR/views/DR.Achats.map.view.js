function(doc) {

  return; /* à adapter */

  if (!(doc.type && doc.type == "DR" && doc.campagne == "2011" && doc.cvi.match('^(67|68)') && doc.validee && doc.modifiee )) {
    return;
  }

  for(appellation_key in doc.recolte) {
    if(appellation_key.match('^appellation')) {
      var superficies = new Array();
      superficies['negoces'] = new Array();
      superficies['cooperatives'] = new Array();
      var dontdplcs = new Array();
      dontdplcs['negoces'] = new Array();
      dontdplcs['cooperatives'] = new Array();
      var volumes = new Array();
      volumes['negoces'] = new Array();
      volumes['cooperatives'] = new Array();

      for(lieu_key in doc.recolte[appellation_key]) {
        if(lieu_key.match('^lieu')) {
          /*for(type_acheteur_key in doc.recolte[appellation_key][lieu_key].acheteurs) {
                  for(acheteur_key in doc.recolte[appellation_key][lieu_key].acheteurs[type_acheteur_key]) {
                      if(!volumes[type_acheteur_key][acheteur_key]) {
                        volumes[type_acheteur_key][acheteur_key] = 0;
                      }
                  }
          }*/
          for(couleur_key in doc.recolte[appellation_key][lieu_key]) {
            if(couleur_key.match('^couleur')) {
              for(cepage_key in doc.recolte[appellation_key][lieu_key][couleur_key]) {
                if(cepage_key.match('^cepage') && cepage_key != 'cepage_RB') {
                  for(detail_key in doc.recolte[appellation_key][lieu_key][couleur_key][cepage_key].detail) {
                    for(acheteur_key in doc.recolte[appellation_key][lieu_key][couleur_key][cepage_key].detail[detail_key].negoces) {
                      var acheteur = doc.recolte[appellation_key][lieu_key][couleur_key][cepage_key].detail[detail_key].negoces[acheteur_key];
                      if (!volumes['negoces'][acheteur.cvi]) {
                        volumes['negoces'][acheteur.cvi] = 0;
                      }
                      volumes['negoces'][acheteur.cvi] = volumes['negoces'][acheteur.cvi] + acheteur.quantite_vendue;
                    }
                    for(acheteur_key in doc.recolte[appellation_key][lieu_key][couleur_key][cepage_key].detail[detail_key].cooperatives) {
                      var acheteur = doc.recolte[appellation_key][lieu_key][couleur_key][cepage_key].detail[detail_key].cooperatives[acheteur_key];
                      if (!volumes['cooperatives'][acheteur.cvi]) {
                        volumes['cooperatives'][acheteur.cvi] = 0;
                      }
                      volumes['cooperatives'][acheteur.cvi] = volumes['cooperatives'][acheteur.cvi] + acheteur.quantite_vendue; 
                    }
                  }
                }
              }
            }
          }
          for(acheteur_key in doc.recolte[appellation_key][lieu_key].acheteurs.negoces) {
            var detail = doc.recolte[appellation_key][lieu_key].acheteurs.negoces[acheteur_key];
            if (!superficies['negoces'][acheteur_key]) {
                superficies['negoces'][acheteur_key] = 0;
            }
      superficies['negoces'][acheteur_key] = superficies['negoces'][acheteur_key] + detail.superficie;
            if (!dontdplcs['negoces'][acheteur_key]) {
                dontdplcs['negoces'][acheteur_key] = 0;
            }
      dontdplcs['negoces'][acheteur_key] = dontdplcs['negoces'][acheteur_key] + detail.dontdplc;
          }
    for(acheteur_key in doc.recolte[appellation_key][lieu_key].acheteurs.cooperatives) {
            var detail = doc.recolte[appellation_key][lieu_key].acheteurs.cooperatives[acheteur_key];
            if (!superficies['cooperatives'][acheteur_key]) {
                superficies['cooperatives'][acheteur_key] = 0;
            }
      superficies['cooperatives'][acheteur_key] = superficies['cooperatives'][acheteur_key] + detail.superficie;
            if (!dontdplcs['cooperatives'][acheteur_key]) {
                dontdplcs['cooperatives'][acheteur_key] = 0;
            }
      dontdplcs['cooperatives'][acheteur_key] = dontdplcs['cooperatives'][acheteur_key] + detail.dontdplc;
          }
        }
      }
      for(acheteur_type_key in volumes) {
        for(acheteur_key in volumes[acheteur_type_key]) {
          //emit([doc.cvi], 2);
          emit([doc.campagne, doc.cvi, appellation_key, acheteur_type_key, acheteur_key], [Math.round(volumes[acheteur_type_key][acheteur_key]*100)/100, Math.round(superficies[acheteur_type_key][acheteur_key]*100)/100, Math.round(dontdplcs[acheteur_type_key][acheteur_key]*100)/100]);
        }
      }
    }
  }
}
