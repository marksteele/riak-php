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
 - Search (via protobuf interface) ... buffers all results in memory (sorry! that's how the API is conceived)

TODO
 - Mapreduce (and MapReduce searching)
 - 2i
 - Test ;-)

NOTES
 - If using a streaming method (key list, eventually map reduce) you cannot interleave other requests to Riak. If you need to do this, open a new connection.

Installation
============

Install protobuf-beta php module

     pear channel-discover pear.pollinimini.net
     pear install drslump/Protobuf-beta

Clone this repo, have a look at test.php for sample usage.

For the curious, here's the command I used to generate the protobuf php code:

      protoc-gen-php -o ./build -i ./ riak.proto

The astute reader will notice I've merged together the various proto files which are part of riak_pb (https://github.com/basho/riak_pb.git)
