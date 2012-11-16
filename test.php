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
require_once('Riak/Transport/Pb.php');
require_once('Riak/Transport/Exception.php');
$codec = new Protobuf\Codec\Binary();
Protobuf::setDefaultCodec($codec);
require_once('Riak/Transport/Pb/riakclient.proto.php');
$client = new Riak_Client(new Riak_Transport_Pb('127.0.0.1', '8087')); 
echo var_dump($client->isAlive());
$bucket = $client->getBucket('blargh');


var_dump($bucket->setProperty('allow_mult',true));
var_dump($bucket->getProperty('allow_mult'));

$obj = new Riak_Object($client, $bucket, 'asdf');
$obj->setValue('asd');
var_dump($obj->store());

var_dump($client->listBuckets()); // Doesn't appear to work as of yet... I'm probably doing something wrong...
