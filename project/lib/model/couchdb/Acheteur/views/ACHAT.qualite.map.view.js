function(doc) { if (doc['type'] == 'Acheteur') { emit([doc.qualite, doc.nom], {cvi: doc.cvi, nom: doc.nom, commune: doc.commune});}}