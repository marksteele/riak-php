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
 * Bucket class
 *
 * Handles bucket operation.
 *
 * @author Mark Steele <mark@control-alt-del.org>
 * @version 1.0
 * @package Riak_Bucket
 * @copyright 2012 Mark Steele
 */
class Riak_Bucket {
  /**
   * Riak client
   *	
   * @access private
   * @var Riak_Client
   */
  private $_client;
  /**
   * Bucket name
   *
   * @access private
   * @var string Bucket name
   */
  private $_name;
  /**#@+
   * Durability, persistence, replica, quorum properties
   *
   * @access private
   * @var mixed Integer or text version of desired value. Valid text options are: default, all, quorum, one
   */  
  private $_r;
  private $_w;
  private $_dw;
  private $_pw;
  private $_pr;
  /**#@-*/

  /**
   * Class constructor
   *
   * @param Riak_Client $client
   * @param string $name The bucket name
   */
  public function __construct(Riak_Client $client, $name) 
  {
    $this->_client = $client;
    $this->_name = $name;
  }
  /**
   * Get client
   *
   * @return Riak_Client The Riak client
   */
  public function getClient()
  {
    return $this->_client;
  }
  /**
   * Get name
   *
   * @return string The bucket name
   */
  public function getName() 
  {
    return $this->_name;
  }
  /**
   * Get read quorum
   * 
   * @return mixed Integer or text version of quorum value
   */
  public function getR() 
  { 
    return isset($this->_r) ? $this->_r : $this->getClient()->getR();
  }
  /**
   * Set read quorum
   * 
   * @param mixed $r Integer or text version of quorum value
   * @return Riak_Bucket
   */
  public function setR($r)
  { 
    $this->_r = $r; 
    return $this;
  }
  /**
   * Get write quorum
   * 
   * @return mixed Integer or text version of quorum value
   */  
  public function getW()
  { 
    return isset($this->_w) ? $this->_w : $this->getClient()->getW();
  }
  /**
   * Set write quorum
   * 
   * @param mixed $w Integer or text version of quorum value
   * @return Riak_Bucket
   */
  public function setW($w)   
  { 
    $this->_w = $w; 
    return $this;
  }
  /**
   * Get how many replicas to commit to durable storage before returning a successful response
   * 
   * @return mixed Integer or text version of quorum value
   */
  public function getDW()    
  { 
    return isset($this->_dw) ? $this->_dw : $this->getClient()->getDW();
  }
  /**
   * Get number of primary vnodes must write to in order to consider an operation successful
   * 
   * @return mixed Integer or text version of quorum value
   */
  public function getPW()    
  { 
    return isset($this->_pw) ? $this->_pw : $this->getClient()->getPW();
  }
  /**
   * Get number of primary vnodes must read from before considering it a success
   * 
   * @return mixed Integer or text version of quorum value
   */
  public function getPR()    
  { 
    return isset($this->_pr) ? $this->_pr : $this->getClient()->getPR();
  }
  /**
   * Set how many replicas to commit to durable storage before returning a successful response
   * 
   * @param mixed $dw Integer or text version of quorum value
   * @return Riak_Bucket
   */
  public function setDW($dw) 
  { 
    $this->_dw = $dw; 
    return $this;
  }
  /**
   * Set number of primary vnodes must acknowledge write operation before considering it a success
   * 
   * @param mixed $pw Integer or text version of quorum value
   * @return Riak_Bucket
   */
  public function setPW($pw) 
  { 
    $this->_pw = $pw; 
    return $this;
  }
  /**
   * Set number of primary vnodes must read from before considering it a success
   * 
   * @param mixed $pr Integer or text version of quorum value
   * @return Riak_Bucket
   */
  public function setPR($pr) 
  { 
    $this->_pr = $pr; 
    return $this;
  }
  /**
   * Set bucket property
   * 
   * @return bool True on success
   * @param string $key Key to set
   * @param string $value Value for that key
   * @return Riak_Bucket
   */
  public function setProperty($key, $value) 
  {
    return $this->getClient()->setBucketProperties($this->getName(),array($key=>$value));
  }
  /**
   * Retrieve a bucket property by key
   *
   * @param string $key The property to retrieve
   * @return string|null The riak property, null if the key does not exist.
   */
  public function getProperty($key) 
  {
    $props = $this->getClient()->getBucketProperties($this->getName());
    if (array_key_exists($key, $props)) {
      return $props[$key];
    } else {
      return NULL;
    }
  }
  /**
   * List keys
   *
   * @return Riak_KeyList An iterator containing the list of keys. Note: You should not interleave requests to Riak until you've finished retrieving the result set as Riak will get confused.
   */
  public function listKeys() 
  {
    return $this->getClient()->listKeys($this);
  }
  /**
   * Retrieve an object from Riak
   *
   * @param string $key The key name
   * @param integer|string $r The integer or string value for the read quorum (number or one of: default, all, quorum, one)
   * @param integer|string $pr The integer or string value for the primary replica read quorum (number or one of: default, all, quorum, one)
   * @param bool $basicQuorum whether to return early in some failure cases (eg. when r=1 and you get 2 errors and a success basic_quorum=true would return an error)
   * @param bool $notfoundOk Whether to treat notfounds as successful reads for the purposes of R
   * @param string $ifModified Optional vector clock. When a vclock is supplied as this option only return the object if the vclocks don't match
   * @param bool $head return the object with the value(s) set as empty - allows you to get the metadata without a potentially large value
   * @param bool $deletedVclock return the tombstone's vclock, if applicable
   * @return Riak_Object|bool Returns a Riak_Object or boolean if the ifmodified flag set and the document is unchanged.
   */
  public function get($key, $r = null, $pr = null, $basicQuorum = false, $notfoundOk = false, $ifModified = null, $head = false, $deletedVclock = false) 
  {
    $obj = new Riak_Object($this->getClient(), $this, $key);
    return $this
      ->getClient()
      ->fetch(
        $obj, 
        $r ? $r : $this->getR(), 
        $pr ? $pr : $this->getPR(), 
        $basic_quorum, 
        $notfound_ok, 
        $if_modified, 
        $head, 
        $deleted_vclock
    );
  }
  /**
   * Create a new object for this bucket
   *
   * @param string $key The optional key for this object.
   * @return Riak_Object The new Riak object.
   */
  public function newObject($key = null)
  {
    return new Riak_Object($this->getClient(), $this, $key);
  }
}
