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
require_once('Riak/Transport/Interface.php');
require_once('Riak/Transport/KeyList.php');
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
// Note: deleted key still around! not_founds aren't filtered out... I think...

$obj1 = $client1->getBucket('testlinks')->get('source');
$obj2 = $client1->getBucket('testlinks')->get('destination')->setValue('testdest')->store();

$obj1->setValue('testlinks')->addLink($obj2)->store();
$links = $client1->getBucket('testlinks')->get('source')->getLinks();
foreach ($links as $link) {
  var_dump($link->get()->getValue());
}
