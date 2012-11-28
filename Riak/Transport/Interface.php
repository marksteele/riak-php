<?php
/*
   This file is provided to you under the Apache License,
   Version 2.0 (the "License"); you may not use this file
   except in compliance with the License.  You may obtain
   a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing,
   software distributed under the License is distributed on an
   "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
   KIND, either express or implied.  See the License for the
   specific language governing permissions and limitations
   under the License.
*/

/*
  This implementation is based on the API developed by Basho's Rusty Klophaus (@rklophaus) (rusty@basho.com) and others.
  See here for more information: https://github.com/basho/riak-php-client
*/

/**
 * Riak transport interface
 *
 * Interface transports need to implement
 *
 * @author Mark Steele <mark@control-alt-del.org>
 * @version 1.0
 * @package Riak_Transport_Interface
 * @copyright 2012 Mark Steele
 */
interface Riak_Transport_Interface
{
  /**
   * Check to see if the connection is working
   *
   * @return bool True on success
   */
  public function ping();
  /**
   * Retrieve the list of buckets. Use with caution...
   *
   * @return array The list of buckets.
   */
  public function listBuckets();
  /**
   * Set properties for a bucket
   *
   * @param string $name Bucket name
   * @param array $props Array of properties
   * @return bool True on success
   */
  public function setBucketProperties($name, array $properties);
  /**
   * Get bucket properties
   *
   * @param string $name Bucket name
   * @return array An array of bucket properties
   */
  public function getBucketProperties($name);
  /**
   * Store an object in Riak
   *
   * @param Riak_Object $obj The Riak object to store
   * @param int|string $w The number of replicas to write to before returning success
   * @param int|string $dw The number of primary replicas to commit to durable storage before returning success
   * @param int|string $pw The number of primary replicas which must be up to attempt to store the value
   * @param bool $returnBody Retrieve the object that has just been stored on success (will populate siblings)
   * @param bool $returnHead Retrieve metadata after successful operation
   * @param bool $ifNotModified Only perform store operation if vclock passed matches the one stored in the data store
   * @param bool $ifNoneMatch Only perform the store operation if an object with this key/bucket does not exist.
   * @return Riak_Object|bool Boolean false to handle ifnonematch/ifnotmodified, otherwise a Riak object returned (possibly updated).
   */
  public function store(Riak_Object &$obj, $w = null, $dw = null, $pw = null, $returnBody = false, $returnHead = false, $ifNotModified = false, $ifNoneMatch = false);
  /**
   * Retrieve an object in Riak
   *
   * @param Riak_Object $obj The Riak object to store
   * @param int|string $r The number of replicas to read from before returning success
   * @param int|string $pr The number of primary replicas which must be up to attempt to read the value
   * @param bool $basicQuorum  whether to return early in some failure cases (eg. when r=1 and you get 2 errors and a success basic_quorum=true would return an error)
   * @param bool $notfoundOk whether to treat notfounds as successful reads for the purposes of R
   * @param string $ifModified  when a vclock is supplied as this option only return the object if the vclocks don't match
   * @param bool $ifNoneMatch Only perform the store operation if an object with this key/bucket does not exist.
   * @param bool $head return the object with the value(s) set as empty - allows you to get the metadata without a potentially large value
   * @param bool $deletedVclock return the tombstone's vclock, if applicable
   * @return Riak_Object|bool Boolean false to handle ifmodified, otherwise a Riak object returned (possibly updated).
   */
  public function fetch(Riak_Object &$obj, $r = null, $pr = null, $basicQuorum = false, $notfoundOk = false, $ifModified = null, $head = false, $deletedVclock = false);
  /**
   * Delete an object in Riak
   *
   * @param Riak_Object $obj The Riak object to store
   * @param int|string $r The number of replicas to read from before returning success
   * @param int|string $rw how many replicas to delete before returning a successful response
   * @param int|string $pr The number of primary replicas which must be up to attempt to retrieve the value
   * @param int|string $w The number of replicas to write to before returning success
   * @param int|string $dw The number of primary replicas to commit to durable storage before returning success
   * @param int|string $pw The number of primary replicas which must be up to attempt to delete the value
   * @return bool True on success
   */
  public function delete(Riak_Object $obj, $rw = null, $r = null, $w = null, $pr = null, $pw = null, $dw = null);
  /**
   * Retrieve server version
   *
   * @return string Server version string
   */
  public function getServerVersion();
  /**
   * Retrieve a list of keys
   *
   * @param string $bucket Bucket name
   * @return Riak_Transport_Iterator The key listing iterator
   */
  public function listKeys(Riak_Bucket $bucket);
  /**
   * Get the next stack of key list values
   *
   * @return array An array consisting of a boolean flag to indicate that this is the last stack, and an array of values
   */
  public function getNextKeyListStack();
  /**
   * Perform a search
   *
   * @param string $query The query to send
   * @param string $index The index to search
   * @param int $rows the number of rows to fetch
   * @param int $start the starting offset
   * @param string $sort sorting field
   * @param string $filter filters search with additional query scoped to inline fields
   * @param string $df override the default_field setting in the schema file
   * @param string $op 'and' or 'or', to override the default_op operation setting in the schema file
   * @param array $fl return the fields limit
   * @param string $presort presort (key/score)
   * @return array An array of search results
   */
  public function search($query, $index, $rows = null, $start = null, $sort = null, $filter = null, $df = null, $op = null, $fl = array(), $presort = null);
  /**
   * Perform a 2i search
   *
   * @param string $bucket The bucket name
   * @param string $index The index to search
   * @param int $queryType the query type: 0 (eq) or 1 (range)
   * @param string $key The key to search for (for 'eq' searches)
   * @param int $rangeMin The starting range, for range queries
   * @param int $rangeMax The ending range, for range queries
   * @return array an array of search results
   */
  public function search2i($bucket, $index, $queryType = 0, $key = null, $rangeMin = null, $rangeMax = null);
  /**
   * Issue a map reduce operation
   *
   * @param string $request A map reduce request
   * @param string $contentType The content type of the request (eg: application/json, application/erlang)
   * @return Riak_Transport_Iterator An iterator containing the results 
  public function mapReduce($request, $contentType);
  /**
   * Get the next stack of map reduce results
   *
   * @return array An array consisting of a boolean flag to indicate that this is the last stack, and an array of values. Each value consists of a phase and a value.
   */
  public function getNextMapReduceStack();
}
