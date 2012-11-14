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


/**
 * The Riak_Bucket object allows you to access and change information
 * about a Riak bucket, and provides methods to create or retrieve
 * objects within the bucket.
 * @package RiakBucket
 */
class Riak_Bucket {
  private $_client;
  private $_name;
  private $_r;
  private $_w;
  private $_dw;

  public function __construct($client, $name) {
    $this->_client = $client;
    $this->_name = $name;
  }

  public function getClient()
  {
    return $this->_client;
  }

  /**
   * Get the bucket name.
   */
  public function getName() {
    return $this->_name;
  }

  /** 
   * Get the R-value for this bucket, if it is set, otherwise return
   * the R-value for the client.
   * @return integer
   */
  public function getR() { 
    return isset($this->_r) ? $this->_r : $this->getClient()->getR();
  }

  /**
   * Set the R-value for this bucket. get(...) and getBinary(...)
   * operations that do not specify an R-value will use this value.
   * @param integer $r - The new R-value.
   * @return $this
   */
  public function setR($r)
  { 
    $this->_r = $r; 
    return $this;
  }

  /**
   * Get the W-value for this bucket, if it is set, otherwise return
   * the W-value for the client.
   * @return integer
   */
  public function getW()
  { 
    return isset($this->_w) ? $this->_w : $this->getClient()->getW();
  }

  /**
   * Set the W-value for this bucket. See setR(...) for more information.
   * @param  integer $w - The new W-value.
   * @return $this
   */
  public function setW($w)   
  { 
    $this->_w = $w; 
    return $this;
  }

  /**
   * Get the DW-value for this bucket, if it is set, otherwise return
   * the DW-value for the client.
   * @return integer
   */
   public function getDW()    
  { 
    return isset($this->_dw) ? $this->_dw : $this->getClient()->getDW();
  }

  /**
   * Set the DW-value for this bucket. See setR(...) for more information.
   * @param  integer $dw - The new DW-value
   * @return $this
   */
   public function setDW($dw) { 
    $this->_dw = $dw; 
    return $this;
  }

  /**
   * Set a bucket property. This should only be used if you know what
   * you are doing.
   * @param  string $key - Property to set.
   * @param  mixed  $value - Property value.
   */
   public function setProperty($key, $value) {
    return $this->getClient()->setBucketProperties($this->getName(),array($key=>$value));
  }

  /**
   * Retrieve a bucket property.
   * @param string $key - The property to retrieve.
   * @return mixed
   */
   public function getProperty($key) {
    $props = $this->getClient()->getBucketProperties($this->getName());
    if (array_key_exists($key, $props)) {
      return $props[$key];
    } else {
      return NULL;
    }
  }

  public function listKeys() 
  {
    // Reimplement using iterator
  }
  
  /**
   * Retrieve an object from Riak.
   * @param  string $key - Name of the key.
   * @param  int    $r   - R-Value of the request (defaults to bucket's R)
   * @return RiakObject
   */
   public function get($key, $r=NULL) {
    $obj = new Riak_Object($this->getClient(), $this, $key);
    return $obj->reload($r ? $r : $this->getR());
  }
}
