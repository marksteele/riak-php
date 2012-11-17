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

class Riak_Bucket {
  private $_client;
  private $_name;
  private $_r;
  private $_w;
  private $_dw;
  private $_pw;
  private $_pr;

  public function __construct($client, $name) 
  {
    $this->_client = $client;
    $this->_name = $name;
  }

  public function getClient()
  {
    return $this->_client;
  }

  public function getName() 
  {
    return $this->_name;
  }

  public function getR() 
  { 
    return isset($this->_r) ? $this->_r : $this->getClient()->getR();
  }

  public function setR($r)
  { 
    $this->_r = $r; 
    return $this;
  }

  public function getW()
  { 
    return isset($this->_w) ? $this->_w : $this->getClient()->getW();
  }

  public function setW($w)   
  { 
    $this->_w = $w; 
    return $this;
  }

  public function getDW()    
  { 
    return isset($this->_dw) ? $this->_dw : $this->getClient()->getDW();
  }

  public function getPW()    
  { 
    return isset($this->_pw) ? $this->_pw : $this->getClient()->getPW();
  }

  public function getPR()    
  { 
    return isset($this->_pr) ? $this->_pr : $this->getClient()->getPR();
  }

  public function setDW($dw) 
  { 
    $this->_dw = $dw; 
    return $this;
  }

  public function setPW($pw) 
  { 
    $this->_pw = $pw; 
    return $this;
  }

  public function setPR($pr) 
  { 
    $this->_pr = $pr; 
    return $this;
  }

  public function setProperty($key, $value) 
  {
    return $this->getClient()->setBucketProperties($this->getName(),array($key=>$value));
  }

   public function getProperty($key) 
   {
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
  
  public function get($key, $r = null, $pr = null, $basic_quorum = false, $notfound_ok = false, $if_modified = null, $head = false, $deleted_vclock = false) 
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
}
