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
 - 2i
 - Mapreduce (streaming using iterator)

TODO
 - Docblocks everywhere
 - Unit tests. Not super motivated to write these, volunteers needed ... Looking at you basho ....
 - Making the Riak_Client capability aware to make it easier to implement HTTP API properly. Not super motivated to actually go ahead and implement HTTP API.
 - Benchmark against Basho implementation. My guess would be that this one is faster (binary protocol, re-uses conneciton)

NOTES
 - If using a streaming method (key list, map reduce) you cannot interleave other requests to Riak. If you need to do this, open a new connection.
 - Feel free to grab large map reduce jobs and key list to your hearts desire. This implementation does not buffer the entire result set in memory on these operations so you won't hit memory limits in your client app.

Installation
============

Install protobuf-beta php module

     pear channel-discover pear.pollinimini.net
     pear install drslump/Protobuf-beta

Clone this repo, have a look at test.php for sample usage.

For the curious, here's the command I used to generate the protobuf php code:

      protoc-gen-php -o ./build -i ./ riak.proto

The astute reader will notice I've merged together the various proto files which are part of riak_pb (https://github.com/basho/riak_pb.git)
