<?php

class Riak_Client 
{
  private $_transport;
  private $_r;
  private $_rw;
  private $_w;
  private $_dw;
  private $_pw;
  private $_pr;
  private $_clientId;

  public function __construct(Riak_Transport_Interface $transport) 
  {
    $this->_transport = $transport;
    $this->_clientId = md5('phpclient' . mt_rand(1,1000) . mt_rand(1,1000) . time());
    $transport->setClientId($this->_clientId);
    $transport->getServerVersion();
  }

  public function getTransport()
  {
    return $this->_transport;
  }

  public function getR() 
  { 
    return $this->_r; 
  }

  public function setR($r) 
  { 
    $this->_r = $r; 
    return $this; 
  }

  public function getRW() 
  { 
    return $this->_rw; 
  }

  public function setRW($rw) 
  { 
    $this->_rw = $rw; 
    return $this; 
  }

  public function getW() 
  { 
    return $this->_w; 
  }

  public function setW($w) 
  { 
    $this->_w = $w; 
    return $this; 
  }

  public function getDW() 
  { 
    return $this->_dw; 
  }

  public function getPW() { 
    return $this->_pw; 
  }
   public function getPR() 
  { 
    return $this->_pr; 
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

  public function getBucket($name) 
  {
    return new Riak_Bucket($this, $name);
  }

  public function listBuckets() 
  {
     return $this->getTransport()->listBuckets();
  }

  public function isAlive() 
  {
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

  public function store(Riak_Object &$obj, $w = null, $dw = null, $pw = null, $returnBody = false, $returnHead = false, $ifNotModified = false, $ifNoneMatch = false) 
  {
    return $this->getTransport()->store($obj, $w, $dw, $pw, $returnBody, $returnHead, $ifNotModified, $ifNoneMatch);
  }

  public function fetch(Riak_Object &$obj, $r = null, $pr = null, $basic_quorum = false, $notfound_ok = false, $if_modified = null, $head = false, $deleted_vclock = false)
  {
    return $this->getTransport()->fetch($obj, $r, $pr, $basic_quorum, $notfound_ok, $if_modified, $head, $deleted_vclock); 
  }

  public function delete(Riak_Object $obj, $rw = null, $r = null, $w = null, $pr = null, $pw = null, $dw = null)
  {
    return $this->getTransport()->delete($obj, $rw, $r, $w, $pr, $pw, $dw);
  }

  public function listKeys(Riak_Bucket $bucket)
  {
    return $this->getTransport()->listKeys($bucket);
  }

  public function mapReduce($request, $contentType) 
  {
    return $this->getTransport()->mapReduce($request, $contentType);
  }
  
  public function getServerVersion()
  {
    return $this->getTransport()->getServerVersion();
  }

}
