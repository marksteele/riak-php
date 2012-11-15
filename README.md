riak-php
========

PHP client library supporting protocol buffers

Uses Drslump's PHP protocol buffer generator, so it's only PHP 5.3 at the moment. Feel free to improve the code, 
I know it's ugly and don't have the time to make it much prettier :-)

Work in progress. I should have the basic get/put/delete done soon as well as set/get bucket properties.

The plan after that is to support features in the following order:
 - Key listing 
 - Mapreduce 
 - 2i, search

I'm going to shoot for handling result sets in a streaming fashion (for things like key list, mapred, etc...). 
This means that it definitely won't be safe to interleave requests/responses on the same client connection.


Installation
============

Install protobuf-beta php module

pear channel-discover pear.pollinimini.net
pear install drslump/Protobuf-beta

Clone this repo, have a look at test.php for sample usage.