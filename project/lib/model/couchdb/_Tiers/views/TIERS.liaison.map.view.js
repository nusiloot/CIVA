function(doc) {
  if (doc.type == "Recoltant" || doc.type == "MetteurEnMarche" || doc.type == "Acheteur" || doc.type == "Courtier") {
    emit([doc.db2.no_stock, doc.db2.num, doc._id], 1);
  }
}