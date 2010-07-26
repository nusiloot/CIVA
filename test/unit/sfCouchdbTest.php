<?php
require_once dirname(__FILE__).'/../bootstrap/unit.php';
{}
$t = new lime_test(27);

$configuration = ProjectConfiguration::getApplicationConfiguration( 'civa', 'test', true);
$databaseManager = new sfDatabaseManager($configuration);

if (!sfCouchdbManager::getClient()->databaseExists()) {
        sfCouchdbManager::getClient()->createDatabase();
 }

$doc = new DR();
$doc->_id = 'TESTCOUCHDB';
try{
$t->ok($doc->save(), 'save an empty document');
}catch(Exception $e) {
  $t->fail('save an empty document '.$e);
 }
/*** NEW TEST ****/
$t->is($doc->_id, 'TESTCOUCHDB', 'id is the good one');
/*** NEW TEST ****/
$t->ok($doc->_rev, 'should have now a rev number');
/*** NEW TEST ****/
/*** TEST 1 ****/
$t->is(sfCouchdbManager::getClient()->getDoc('TESTCOUCHDB')->_rev, $doc->_rev, 'retrieve the new doc');
/*** NEW TEST ****/
/*** TEST 1 ****/
try {
$t->is($doc->type, 'DR', 'should have a type');
}catch(Exception $e) {
$t->fail('should have a type');
 }
/*** NEW TEST ****/
$rev = $doc->_rev;
$doc->cvi = "TEST";
$doc->campagne = "2009";
$doc->save();

$t->is(sfCouchdbManager::getClient()->getDoc('TESTCOUCHDB')->cvi, 'TEST', 'cvi number saved');
/*** NEW TEST ****/
$t->is(sfCouchdbManager::getClient()->getDoc('TESTCOUCHDB')->campagne, '2009', 'campagne saved');
/*** NEW TEST ****/
$t->isnt(sfCouchdbManager::getClient()->getDoc('TESTCOUCHDB')->_rev, $rev, 'revision changed');

/*** NEW TEST ****/
$detail = new DRRecolteAppellationCepageDetail();
$detail->setAppellation("1");
$detail->setCepage("PB");
$detail->setCodeLieu("lieu");
$detail->setSurface(100);
$detail->setVolume(10);
$detail->setCaveParticuliere(5);
$acheteur = $detail->getAcheteurs()->add();
$acheteur->setCvi("CVI_FICTIF");
$acheteur->setQuantiteVendue(5);
$t->ok($doc->addRecolte($detail), 'add detail');

$obj = $doc->getRecolte()->get('appellation_1')->get('lieu')->get('cepage_PB');
$t->ok($obj, 'can retrieve detail object');

/*** NEW TEST ****/
$t->is($val = $doc->get('recolte/appellation_1/lieu/cepage_PB/detail/0/surface'), 100, 'can retrieve surface by hash');

/*** NEW TEST ****/
$doc->set('recolte/appellation_1/lieu/cepage_PB/detail/0/surface', 150);
$rev = $doc->_rev;
$t->is($obj_hash = $doc->get('recolte/appellation_1/lieu/cepage_PB/detail/0/surface'), 150, 'can change value in the tree');
/*** NEW TEST ****/
$doc->save();
$t->isnt($rev, $doc->_rev, 'revision number has changed after saving');
/*** NEW TEST ****/

$doc->getRecolte()->addAppellation(2);
$iterator_ok = true;
$iterator_nb = 0;
foreach($doc->getRecolte() as $key => $item) {
    if (!($item instanceof sfCouchdbJson)) {
        $iterator_ok = false;
    } else {
        $iterator_nb++;
    }
}
$t->ok($iterator_ok && $iterator_nb == 2, 'Iterate : can foreach');
/*** NEW TEST ****/
$t->ok($obj_array_access = $doc['recolte']['appellation_1']['lieu']['cepage_PB']['detail'][0], 'ArrayAccess : can get value in the tree');
/*** NEW TEST ****/
$obj_array_access['denomination'] = 'test';
$t->is($obj_array_access->get('denomination'), 'test', 'ArrayAccess : can set value in the tree');
/*** NEW TEST ****/
$t->is($doc->getRecolte()->count(), 2, 'ArrayAccess : can count');

/*** NEW TEST ****/
$t->ok($doc->remove('recolte/appellation_1/lieu/cepage_PB/detail/0/surface'), 'remove a field');
/*** NEW TEST ****/
 try{
   $t->ok(!$doc->get('recolte/appellation_1/lieu/cepage_PB/detail/0/surface'), 'field removed');
}catch(Exception $e) {
  $t->pass('field removed');
 }

/*** NEW TEST ****/
try {
$t->is($doc->get('/recolte/appellation_1/lieu/cepage_PB')->getCouchdbDocument(), $doc, 'can access couchdb doc from an sfcouchdbJson object');
}catch(Exception $e) {
  $t->fail('can access couchdb doc from an sfcouchdbJson object : '.$e);
 }
/*** NEW TEST ****/
$t->is($doc->get('/recolte/appellation_1/lieu/cepage_PB')->getHash(), '/recolte/appellation_1/lieu/cepage_PB', 'can access field hash from an sfcouchdbJson object');
/*** NEW TEST ****/
$t->is($doc->get('/recolte/appellation_2')->getHash(), '/recolte/appellation_2', 'can access field hash from an sfcouchdbJson object issued of a collection');

$doc->addRecolte($detail);
$t->is($doc->get('/recolte/appellation_1/lieu/cepage_PB/detail/1')->getHash(), '/recolte/appellation_1/lieu/cepage_PB/detail/1', 'can access field hash from a array collection');


/*** NEW TEST ****/

$t->ok($doc->remove('/recolte/appellation_1/lieu/cepage_PB/detail/0'), 'remove a multifield');
/*** NEW TEST ****/
 try{
   $t->ok(!$doc->get('/recolte/appellation_1/lieu/cepage_PB/detail/0'), 'multifield removed');
}catch(Exception $e) {
  $t->pass('multifield removed');
 }

/*** NEW TEST ****/
$t->ok($doc->delete(), 'delete the document');
/*** NEW TEST ****/
try
{
  sfCouchdbManager::getClient()->getDoc('TESTCOUCHDB');
  $t->fail('cannot retrieve delete doc');
}catch(Exception $e) 
{
  $t->pass('cannot retrieve delete doc');
}
