<?php

define('PROJECT_ROOT', dirname(__FILE__));
set_include_path(
    PROJECT_ROOT . PATH_SEPARATOR .
    '/usr/share/php' . PATH_SEPARATOR .
    get_include_path()
);

error_reporting(E_ALL);
require_once('/usr/share/pear/DrSlump/Protobuf.php');
use \DrSlump\Protobuf;
Protobuf::autoload();
// Could do autoloader, but I'm lazy!
// See how pretty with the files nice and split up?
require_once('Riak/Client.php');
require_once('Riak/Bucket.php');
require_once('Riak/Object.php');
require_once('Riak/Link.php');
require_once('Riak/Search.php');
require_once('Riak/Transport/Interface.php');
require_once('Riak/Transport/Iterator.php');
require_once('Riak/Transport.php');
require_once('Riak/Transport/Pb.php');
require_once('Riak/Transport/Exception.php');
$codec = new Protobuf\Codec\Binary();
Protobuf::setDefaultCodec($codec);
require_once('Riak/Transport/Pb/riakclient.proto.php');

$client1 = new Riak_Client(new Riak_Transport_Pb('127.0.0.1', '8087')); 
$client2 = new Riak_Client(new Riak_Transport_Pb('127.0.0.1', '8087')); 
echo "Clients alive?\n";
var_dump($client1->isAlive());
var_dump($client2->isAlive());

$bucket1 = $client1->getBucket('conflicts');
$bucket2 = $client2->getBucket('conflicts');
$bucket1->setProperty('allow_mult',true);
echo "bucket allow_mult?\n";
var_dump($bucket1->getProperty('allow_mult'));

$bucket1->get('testkey')->setValue('test1')->store();
$o = new Riak_Object($client2, $bucket2, 'testkey');
$o->setValue('test2')->store(null,null,null,true);
echo "Object should have returned body and noticed siblings...\n";
var_dump($o->hasSiblings());

$conflict = $bucket1->get('testkey');
echo "Conflict has siblings?\n";
var_dump($conflict->hasSiblings());
foreach ($conflict->getSiblings() as $s) {
  var_dump($s->getValue());
}

echo "Deleted?\n";
var_dump($conflict->delete());
echo "Still exists?\n";
var_dump($bucket1->get('testkey')->exists());

foreach ($bucket1->listKeys() as $k) {
  echo $k . "\n";
}
echo "Key list done\n";
// Note: deleted key still around! not_founds aren't filtered out... I think...

// Links
$client1->getBucket('argle')->newObject('link1')->setValue(rand(1,1000))->addLink($client1->getBucket('argle')->newObject('test2'),'tagtest' . rand(1,1000))->store();
foreach ($client1->getBucket('argle')->get('link1')->getLinks() as $link) {
  echo $link->getBucket() . "\n";
  echo $link->getKey() . "\n";
  echo $link->getTag() . "\n";
}

//Mapred
$client1->getBucket('Mapredtest')->newObject('mapredkey1')->setValue(10)->store();
$client1->getBucket('Mapredtest')->newObject('mapredkey2')->setValue(10)->store();
$client1->getBucket('Mapredtest')->newObject('mapredkey3')->setValue(10)->store();
$client1->getBucket('Mapredtest')->newObject('mapredkey4')->setValue(10)->store();
$client1->getBucket('Mapredtest')->newObject('mapredkey5')->setValue(10)->store();

foreach ($client1->MapReduce(
  '
    {
      "inputs": "Mapredtest", 
      "query": [
        {
          "map": {
            "arg": null,
            "name": "Riak.mapValuesJson",
            "language": "javascript",
            "keep": false
          }
        },
        {
          "reduce": {
            "arg": null,
            "name": "Riak.reduceSum",
            "language": "javascript",
            "keep": true
          }
        }
      ]
    }','application/json') as $res) {
  var_dump($res);
}
echo exec('/usr/sbin/search-cmd install searchtest') . "\n";

$client1->getBucket('searchtest')->newObject('searchkey1')->setValue('{"name": "Mark Steele", "alias": "parent"}')->setContentType('application/json')->addSecondaryIndex("alias_bin","parent")->store();
$client1->getBucket('searchtest')->newObject('searchkey2')->setValue('{"name": "Wyle E. Coyote", "alias": "splat"}')->setContentType('application/json')->store();
$client1->getBucket('searchtest')->newObject('searchkey3')->setValue('{"name": "Kimberly Steele", "alias": "offspring"}')->setContentType('application/json')->addSecondaryIndex("alias_bin","offspring")->store();
$client1->getBucket('searchtest')->newObject('searchkey4')->setValue('{"name": "Michael Steele", "alias": "offspring"}')->setContentType('application/json')->addSecondaryIndex("alias_bin","offspring")->store();

$search = new Riak_Search($client1);
var_dump($search->search("name:Steele", 'searchtest'));

var_dump($search->search2i('searchtest', 'alias_bin', 0, 'offspring'));

var_dump($client1->getServerVersion());

var_dump($client1->getTransport()->hasPhaselessMapred());
var_dump($client1->getTransport()->hasPbIndexes());
var_dump($client1->getTransport()->hasPbSearch());
var_dump($client1->getTransport()->hasPbConditionals());
var_dump($client1->getTransport()->hasQuorumControls());
var_dump($client1->getTransport()->hasTombstoneVclocks());
var_dump($client1->getTransport()->hasPbHead());
