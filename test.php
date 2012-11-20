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
require_once('Riak/Transport/Interface.php');
require_once('Riak/Transport.php');
require_once('Riak/Transport/Pb.php');
require_once('Riak/Transport/Exception.php');
$codec = new Protobuf\Codec\Binary();
Protobuf::setDefaultCodec($codec);
require_once('Riak/Transport/Pb/riakclient.proto.php');

$client = new Riak_Client(new Riak_Transport_Pb('127.0.0.1', '8087')); 
echo "Client alive?\n";
var_dump($client->isAlive());
$bucket = $client->getBucket('blargh');
$bucket->setProperty('allow_mult',true);
echo "Allow mult?\n";
var_dump($bucket->getProperty('allow_mult'));

echo "delete key?\n";
var_dump($bucket->get('asdf')->delete());

$obj = $bucket->get('asdf');
echo "exists?\n";
var_dump($obj->exists());
echo "siblings?\n";
var_dump($obj->hasSiblings());

$lwwbucket = $client->getBucket('lww');
$lwwbucket->setProperty('allow_mult',false);
$obj = $lwwbucket->get('asdf');
$obj->setValue('lww1')->store(null,null,null,true);
$obj->setValue('lww2')->store(null,null,null,true);
echo "LWW siblings?\n";
var_dump($obj->hasSiblings());
