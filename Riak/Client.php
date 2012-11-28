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
 * Riak Client class
 *
 * Manage connection to backend via transport API.
 *
 * @author Mark Steele <mark@control-alt-del.org>
 * @version 1.0
 * @package Riak_Client
 * @copyright 2012 Mark Steele
 */
class Riak_Client 
{
  /**
   * @var Riak_Transport_Interface
   * @access private
   *
  private $_transport;
  /**#@+
   * Durability, persistence, replica, quorum properties
   *
   * @access private
   * @var mixed Integer or text version of desired value. Valid text options are: default, all, quorum, one. 
   */
  private $_r;
  private $_rw;
  private $_w;
  private $_dw;
  private $_pw;
  private $_pr;
  /**#@-*/
  /**
   * @var string client identifier
   */
  private $_clientId;
  /**
   * Class constructor
   * 
   * @param Riak_Transport_Interface $transport The transport to use for communication
   */
  public function __construct(Riak_Transport_Interface $transport) 
  {
    $this->_transport = $transport;
    $this->_clientId = md5('phpclient' . mt_rand(1,1000) . mt_rand(1,1000) . time());
    $transport->setClientId($this->_clientId);
    $transport->getServerVersion();
  }
  /**
   * Retrieve transport object
   *
   * @return Riak_Transport_Interface
   */
  public function getTransport()
  {
    return $this->_transport;
  }
  /**
   * Retrieve R setting (how many replicas need to agree when retrieving the object)
   *
   * @return string|int Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   */
  public function getR() 
  { 
    return $this->_r; 
  }
  /**
   * Set R value (how many replicas need to agree when retrieving the object)
   *
   * @param string|int $r Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   * @return Riak_Client
   */
  public function setR($r) 
  { 
    $this->_r = $r; 
    return $this; 
  }
  /**
   * Retrieve RW setting (how many replicas to delete before returning a successful response)
   *
   * @return string|int Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   */
  public function getRW() 
  { 
    return $this->_rw; 
  }
  /**
   * Set RW value (how many replicas to delete before returning a successful response)
   *
   * @param string|int $rw Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   * @return Riak_Client
   */
  public function setRW($rw) 
  { 
    $this->_rw = $rw; 
    return $this; 
  }
  /**
   * Retrieve W setting (how many replicas to write before returning a successful response)
   *
   * @return string|int Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   */
  public function getW() 
  { 
    return $this->_w; 
  }
  /**
   * Set W value (how many replicas to write before returning a successful response)
   *
   * @param string|int $w Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   * @return Riak_Client
   */
  public function setW($w) 
  { 
    $this->_w = $w; 
    return $this; 
  }
  /**
   * Retrieve DW setting (how many replicas to commit to durable storage before returning a successful response)
   *
   * @return string|int Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   */
  public function getDW() 
  { 
    return $this->_dw; 
  }
  /**
   * Set DW value (how many replicas to commit to durable storage before returning a successful response)
   *
   * @param string|int $dw Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   * @return Riak_Client
   */
  public function setDW($dw) 
  { 
    $this->_dw = $dw; 
    return $this; 
  }
  /**
   * Retrieve PW setting (how many primary replicas must be up when write is attempted)
   *
   * @return string|int Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   */
  public function getPW() { 
    return $this->_pw; 
  }
  /**
   * Set PW value (how many primary replicas must be up when write is attempted)
   *
   * @param string|int $pw Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   * @return Riak_Client
   */
  public function setPW($pw) 
  { 
    $this->_pw = $pw; 
    return $this; 
  }
  /**
   * Retrieve PR setting (how many primary replicas must be up when read is attempted)
   *
   * @return string|int Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   */
  public function getPR() 
  { 
    return $this->_pr; 
  }
  /**
   * Set PR value (how many primary replicas must be up when read is attempted)
   *
   * @param string|int $pr Strings 'one', 'quorum', 'all', 'default', or any integer <= N 
   * @return Riak_Client
   */
  public function setPR($pr) 
  { 
    $this->_pr = $pr; 
    return $this; 
  }
  /**
   * Return bucket object
   *
   * @param string $name the name of the bucket
   * @return Riak_Bucket The Riak bucket object
   */
  public function getBucket($name) 
  {
    return new Riak_Bucket($this, $name);
  }
  /**
   * Retrieve the list of buckets. Use with caution...
   *
   * @return array The list of buckets.
   */
  public function listBuckets() 
  {
     return $this->getTransport()->listBuckets();
  }
  /**
   * Check to see if the connection is working
   *
   * @return bool True on success
   */
  public function isAlive() 
  {
    return $this->getTransport()->ping();
  }
  /**
   * Set properties for a bucket
   * 
   * @param string $name Bucket name
   * @param array $props Array of properties
   * @return bool True on success
   */
  public function setBucketProperties($name, array $props) 
  {
    return $this->getTransport()->setBucketProperties($name, $props);
  }
  /**
   * Get bucket properties
   *
   * @param string $name Bucket name
   * @return array An array of bucket properties
   */
  public function getBucketProperties($name)
  {
    return $this->getTransport()->getBucketProperties($name);    
  }
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
  public function store(Riak_Object &$obj, $w = null, $dw = null, $pw = null, $returnBody = false, $returnHead = false, $ifNotModified = false, $ifNoneMatch = false) 
  {
    return $this->getTransport()->store($obj, $w, $dw, $pw, $returnBody, $returnHead, $ifNotModified, $ifNoneMatch);
  }
  /**
   * Retrieve an object in Riak
   *
   * @param Riak_Object $obj The Riak object to retrieve
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
  public function fetch(Riak_Object &$obj, $r = null, $pr = null, $basicQuorum = false, $notfoundOk = false, $ifModified = null, $head = false, $deletedVclock = false)
  {
    return $this->getTransport()->fetch($obj, $r, $pr, $basicQuorum, $notfoundOk, $ifModified, $head, $deletedVclock); 
  }
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
  public function delete(Riak_Object $obj, $rw = null, $r = null, $w = null, $pr = null, $pw = null, $dw = null)
  {
    return $this->getTransport()->delete($obj, $rw, $r, $w, $pr, $pw, $dw);
  }
  /**
   * Retrieve a list of keys
   *
   * @param Riak_Bucket $bucket Bucket object
   * @return Riak_Transport_Iterator The key listing iterator
   */
  public function listKeys(Riak_Bucket $bucket)
  {
    return $this->getTransport()->listKeys($bucket);
  }
  /**
   * Send a mapreduce job
   *
   * @param string $request The mapreduce request
   * @param string $contentType The content type ('application/json', 'application/erlang')
   * @return Riak_Transport_Iterator The map reduce iterator
   */
  public function mapReduce($request, $contentType) 
  {
    return $this->getTransport()->mapReduce($request, $contentType);
  }
  /**
   * Returns the server version number
   *
   * @return string Server version string
   */
  public function getServerVersion()
  {
    return $this->getTransport()->getServerVersion();
  }
}
