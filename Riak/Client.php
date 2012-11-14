<?php

/*

Refactored by Mark Steele (mark@control-alt-del.org) to use a protocol buffer interface.

*/

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
 * The Riak API for PHP allows you to connect to a Riak instance,
 * create, modify, and delete Riak objects, add and remove links from
 * Riak objects, run Javascript (and
 * Erlang) based Map/Reduce operations, and run Linkwalking
 * operations.
 *
 * See the unit_tests.php file for example usage.
 * 
 * @author Rusty Klophaus (@rklophaus) (rusty@basho.com)
 * @package RiakAPI
 */


class Riak_Client {
  private $_transport;
  private $_r = 2;
  private $_w = 2;
  private $_dw;

  public function __construct(Riak_Transport_Interface $transport) 
  {
    $this->_transport = $transport;
    $this->_r = 2;
    $this->_w = 2;
    $this->_dw = 2;
  }

  public function getTransport()
  {
    return $this->_transport;
  }

  /**
   * Get the R-value setting for this Riak_Client. (default 2)
   * @return integer
   */
  public function getR() { 
    return $this->_r; 
  }

  /**
   * Set the R-value for this RiakClient. This value will be used
   * for any calls to get(...) or getBinary(...) where where 1) no
   * R-value is specified in the method call and 2) no R-value has
   * been set in the RiakBucket.  
   * @param integer $r - The R value.
   * @return $this
   */
  public function setR($r) { 
    $this->_r = $r; 
    return $this; 
  }

  /**
   * Get the W-value setting for this RiakClient. (default 2)
   * @return integer
   */
   public function getW() { 
    return $this->_w; 
  }

  /**
   * Set the W-value for this RiakClient. See setR(...) for a
   * description of how these values are used.
   * @param integer $w - The W value.
   * @return $this
   */
   public function setW($w) { 
    $this->_w = $w; 
    return $this; 
  }

  /**
   * Get the DW-value for this ClientOBject. (default 2)
   * @return integer
   */
   public function getDW() { 
    return $this->_dw; 
  }

  /**
   * Set the DW-value for this RiakClient. See setR(...) for a
   * description of how these values are used.
   * @param  integer $dw - The DW value.
   * @return $this
   */
   public function setDW($dw) { 
    $this->_dw = $dw; 
    return $this; 
  }

  /**
   * Get the bucket by the specified name. Since buckets always exist,
   * this will always return a RiakBucket.
   * @return RiakBucket
   */
   public function getBucket($name) {
    return new Riak_Bucket($this, $name);
  }

  /**
   * Get all buckets.
   * @return array() of RiakBucket objects
   */
   public function listBuckets() {
     return $this->getTransport()->listBuckets();
  }

  /**
   * Check if the Riak server for this RiakClient is alive.
   * @return boolean
   */
  public function isAlive() {
    return $this->getTransport()->ping();
  }


  public function setBucketProperties($name, array $props) 
  {
    return $this->getTransport()->setBucketProperties($name, $props);
  }

  public function getBucketProperties($name)
  {
    return $this->getTransport()->getBucketProperties($name);    
  }

}
