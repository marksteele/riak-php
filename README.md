riak-php
========

PHP client library supporting protocol buffers

Uses Drslump's PHP protocol buffer generator, so it's only PHP 5.3 at the moment. Feel free to improve the code, 
I know it's ugly and don't have the time to make it much prettier :-)

Implemented
 - ping
 - get/set bucket properties (the ones supported by PB API anyways)
 - key listing (streaming using iterator)
 - store/fetch/delete objects
 - getServerVersion
 - setClientId
 - list buckets

TODO
 - Mapreduce 
 - 2i, search
 - Test ;-)

One of the main design goals is to be able to handle streaming content. So things that are likely to return large sets (mapred, 2i, listkeys) are going to be implemented as iterators.

Installation
============

Install protobuf-beta php module

     pear channel-discover pear.pollinimini.net
     pear install drslump/Protobuf-beta

Clone this repo, have a look at test.php for sample usage.
