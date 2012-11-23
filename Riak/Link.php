<?php
class Riak_Link {

  private $_bucket;
  private $_key;
  private $_tag;
  private $_client;

  public function __construct($bucket, $key, $tag=NULL) {
    $this->_bucket = $bucket;
    $this->_key = $key;
    $this->_tag = $tag;
  }

  public function setClient(Riak_Client $client)
  {
    $this->_client = $client;
    return $this;
  }

  public function get($r=NULL) 
  {
    return $this->_client->getBucket($this->_bucket)->get($this->_key, $r);
  }

  public function getBucket() 
  {
    return $this->_bucket;
  }

  public function setBucket($bucket) 
  {
    $this->_bucket = $bucket;
    return $this;
  }

  public function getKey() 
  {
    return $this->_key;
  }

  public function setKey($key) 
  {
    $this->_key = $key;
    return $this;
  }

  public function getTag() {
    return $this->_tag;
  }

  public function setTag($tag) {
    $this->_tag = $tag;
    return $this;
  }

  public function isEqual(Riak_Link $link) {
    return ($this->getBucket() == $link->getBucket()) && ($this->getKey() == $link->getKey()) && ($this->getTag() == $link->getTag());
  }
}
