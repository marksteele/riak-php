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
require_once('Riak/Transport/Interface.php');
require_once('Riak/Transport/Pb.php');
require_once('Riak/Transport/Exception.php');
$codec = new Protobuf\Codec\Binary();
Protobuf::setDefaultCodec($codec);
require_once('Riak/Transport/Pb/riakclient.proto.php');
$client = new Riak_Client(new Riak_Transport_Pb('127.0.0.1', '8087')); 
echo var_dump($client->isAlive());

//$bucket = $client->getBucket('blargh');
//$obj = $bucket->get('asdf');
//$obj->setData('blargh' . time())->store();
//$obj->reload();
//var_dump($obj->getData());