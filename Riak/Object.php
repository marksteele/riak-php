<?php

class Riak_Object 
{
  private $_meta = array();
  private $_siblings = array();
  private $_exists = false;
  private $_content_type = 'application/octet-stream';
  private $_content_encoding;
  private $_data;
  private $_vclock;
  private $_last_modified;
  private $_last_modified_usecs;
  private $_deleted = false;
  private $_charset;
  private $_vtag;
  private $_key;
  private $_links = array();
  private $_indices = array();

  public function __construct(Riak_Client $client, Riak_Bucket $bucket, $key=NULL) 
  {
    $this->_client = $client;
    $this->_bucket = $bucket;
    $this->_key = $key;
    $this->_exists = FALSE;
  }

  public function clear()
  {
    $this->_meta = array();
    $this->_siblings = array();
    $this->_exists = false;
    $this->_content_type = 'application/octet-stream';
    $this->_content_encoding = null;
    $this->_data = null;
    $this->_vclock = null;
    $this->_last_modified = null;
    $this->_last_modified_usecs = null;
    $this->_deleted = false;
    $this->_charset = null;
    $this->_vtag = null;
    $this->_links = array();
    $this->_indices = array();
  }

  public function getBucket() 
  {
    return $this->_bucket;
  }

  public function getKey()  
  {
    return $this->_key;
  }

  public function getValue() 
  { 
    return $this->_data; 
  }

  public function setValue($data) 
  { 
    $this->_data = $data; 
    return $this;
  }

  public function exists() 
  {
    return $this->_exists;
  }

  public function setExists($value)
  {
    $this->_exists = $value;
    return $this;
  }

  public function getDeleted()
  {
    return $this->_deleted;
  }
  public function setDeleted($d)
  {
    $this->_deleted = $d;
    return $this;
  }

  public function getContentType() 
  {  
    return $this->_content_type; 
  }

  public function setContentType($content_type) 
  {
    $this->_content_type = $content_type;
    return $this;
  }

  public function getContentEncoding()
  {
    return $this->_content_encoding;
  }

  public function setContentEncoding($encoding)
  {
    $this->_content_encoding = $encoding;
    return $this;
  }

  public function getLastModified() 
  {
    return $this->_last_modified;
  }

  public function setLastModified($mod) 
  {
    $this->_last_modified = $mod;
    return $this;
  }

  public function getLastModifiedUsecs() 
  {
    return $this->_last_modified_usecs;
  }

  public function setLastModifiedUsecs($mod) 
  {
    $this->_last_modified_usecs = $mod;
    return $this;
  }

  public function getCharset()
  {
    return $this->_charset;
  }
  public function setCharset($charset)
  {
    $this->_charset = $charset;
    return $this; 
  }

  public function getVtag()
  {
    return $this->_vtag;
  }

  public function setVtag($vtag)
  {
    $this->_vtag = $vtag;
    return $this;
  }

  public function getMeta($metaName) 
  {
    $metaName = strtolower($metaName);
    return isset($this->_meta[$metaName]) ? $this->_meta[$metaName] : null;
  }
  
  public function setMeta($metaName, $value) 
  {
    $this->meta[strtolower($metaName)] = $value;
    return $this;
  }
  
  public function removeMeta($metaName) 
  {
    unset ($this->meta[strtolower($metaName)]);
    return $this;
  }
  
  public function getAllMeta() 
  {
    return $this->_meta;
  }
  
  public function removeAllMeta() 
  {
    $this->meta = array();
    return $this;
  }

  public function store($w = null, $dw=null, $pw = null, $returnBody = false, $returnHead = false, $ifNotModified = false,$ifNoneMatch = false) 
  {
    return $this
      ->getBucket()
      ->getClient()
      ->store(
        $this,
        $w ? $w : $this->getBucket()->getW(),
        $dw ? $dw : $this->getBucket()->getDW(),
        $pw ? $pw : $this->getBucket()->getPW(),
        $returnBody,
        $returnHead,
        $ifNotModified,
        $ifNoneMatch
      );
  }
 
  public function getVClock() 
  {
    return $this->_vclock;
  }

  public function setVClock($vclock)
  {
    $this->_vclock = $vclock;
    return $this;
  }
  
  public function hasSiblings() {
    return ($this->getSiblingCount() > 0);
  }

  public function getSiblingCount() {
    return count($this->_siblings);
  }

  public function getSiblings()
  {
    return $this->_siblings;
  }
  public function setSiblings($siblings)
  {
    $this->_siblings = $siblings;
  }

  public function addSibling($sibling) 
  {
    $this->_siblings[] = $sibling;
  }

  public function delete($rw = null, $r = null, $w = null, $pr = null, $pw = null, $dw = null)
  {
    if ($this
          ->getBucket()
          ->getClient()
          ->delete(
            $this, 
            $rw ? $rw : $this->getBucket()->getClient()->getRW(),
            $r ? $r : $this->getBucket()->getR(),
            $w ? $w : $this->getBucket()->getW(),
            $pr ? $pr : $this->getBucket()->getPR(),
            $pw ? $pw : $this->getBucket()->getPW(),
            $dw ? $dw : $this->getBucket()->getDW()
    ) == true) {
      $this->clear();
      return true;
    }
    return false;
  }

  public function addLink($obj, $tag=NULL) 
  {
    if ($obj instanceof Riak_Link) {
      $newlink = $obj;
    } else {
      $newlink = new Riak_Link($obj->getBucket()->getName(), $obj->getKey(), $tag);
    }
    $newlink->setClient($this->getBucket()->getClient());
    $this->removeLink($newlink);
    $this->_links[] = $newlink;
    return $this;
  }
  
  public function removeLink($obj, $tag=NULL) 
  {
    if ($obj instanceof Riak_Link) {
      $oldlink = $obj;
    } else {
      $oldlink = new Riak_Link($obj->getBucket()->getName(), $obj->getKey(), $tag);
    }
    $a = array();
    foreach ($this->_links as $link) {
      if (!$link->isEqual($oldlink)) 
        $a[] = $link;
    }

    $this->_links = $a;
    return $this;
  }

  public function getLinks() 
  {
    return $this->_links;
  }


  public function addSecondaryIndex($field, $value)
  {
    $this->_indices[$field] = $value;
    return $this;
  }

  public function getIndices()
  {
    return $this->_indices;
  }

}
