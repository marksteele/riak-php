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
 * Riak link class
 *
 * Manage links
 *
 * @author Mark Steele <mark@control-alt-del.org>
 * @version 1.0
 * @package Riak_Link
 * @copyright 2012 Mark Steele
 */
class Riak_Link {
  /**
   * @var string Bucket name
   */
  private $_bucket;
  /**
   * @var string key name
   */
  private $_key;
  /**
   * @var string tag
   */  
  private $_tag;
  /**
   * @var Riak_Client Riak client object
   */
  private $_client;
  /**
   * Class constructor
   * 
   * @param string $bucket Bucket name
   * @param string $key Key
   * @param string $tag Tag
   */
  public function __construct($bucket, $key, $tag=NULL) {
    $this->_bucket = $bucket;
    $this->_key = $key;
    $this->_tag = $tag;
  }
  /**
   * Set client
   *
   * @param Riak_Client $client The Riak client
   * @return Riak_Link
   */
  public function setClient(Riak_Client $client)
  {
    $this->_client = $client;
    return $this;
  }
  /**
   * Retrieve the linked object from Riak
   *
   * @param integer|string $r The integer or string value for the read quorum (number or one of: default, all, quorum, one)
   * @param integer|string $pr The integer or string value for the primary replica read quorum (number or one of: default, all, quorum, one)
   * @param bool $basicQuorum whether to return early in some failure cases (eg. when r=1 and you get 2 errors and a success basic_quorum=true would return an error)
   * @param bool $notfoundOk Whether to treat notfounds as successful reads for the purposes of R
   * @return Riak_Object|bool Returns a Riak_Object or boolean if the ifmodified flag set and the document is unchanged.
   */
  public function get($r = null, $pr = null, $basicQuorum = false,$notfoundOk = false) 
  {
    return $this->_client->getBucket($this->_bucket)->get($this->_key, $r, $pr, $basicQuorum, $notfoundOk);
  }
  /**
   * Retrieve bucket name
   *
   * @return string Bucket name
   */
  public function getBucket() 
  {
    return $this->_bucket;
  }
  /**
   * Set bucket name
   *
   * @param string $bucket the bucket name
   * @return Riak_Link
   */
  public function setBucket($bucket) 
  {
    $this->_bucket = $bucket;
    return $this;
  }
  /**
   * Retrieve the key string
   *
   * @return string The key
   */
  public function getKey() 
  {
    return $this->_key;
  }
  /**
   * Set the key
   *
   * @param string $key The key
   * @return Riak_Link
   */
  public function setKey($key) 
  {
    $this->_key = $key;
    return $this;
  }
  /**
   * Retrieve the tag for the link
   *
   * @return string The tag
   */
  public function getTag() {
    return $this->_tag;
  }
  /**
   * Set the tag
   *
   * @param string $tag the tag for the link
   * @return Riak_Link
   */
  public function setTag($tag = null) {
    $this->_tag = $tag;
    return $this;
  }
  /** 
   * Checks to see if two links are the same
   *
   * @param Riak_Link $link the link to test against
   * @return bool True when they are the same, false otherwise.
   */
  public function isEqual(Riak_Link $link) {
    return ($this->getBucket() == $link->getBucket()) && ($this->getKey() == $link->getKey()) && ($this->getTag() == $link->getTag());
  }
}
