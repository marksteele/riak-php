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
 * Riak object class
 *
 * Manage objects
 *
 * @author Mark Steele <mark@control-alt-del.org>
 * @version 1.0
 * @package Riak_Object
 * @copyright 2012 Mark Steele
 */
class Riak_Object 
{
  /**
   * @var array Array of metadata
   * @access private
   */
  private $_meta = array();
  /**
   * @var array Array of siblings
   * @access private
   */
  private $_siblings = array();
  /**
   * @var bool If this objects exists in the data store
   * @access private
   */
  private $_exists = false;
  /**
   * @var string Content type
   * @access private
   */
  private $_content_type = 'application/octet-stream';
  /**
   * @var string content encoding
   * @access private
   */
  private $_content_encoding;
  /**
   * @var string Data to be stored
   * @access private
   */
  private $_data;
  /**
   * @var string Vector clock
   * @access private
   */
  private $_vclock;
  /**
   * @var string last modified timestamp
   * @access private
   */
  private $_last_modified;
  /**
   * @var string last modified timestamp including usecs
   * @access private
   */
  private $_last_modified_usecs;
  /**
   * @var bool If this object is deleted
   * @access private
   */
  private $_deleted = false;
  /**
   * @var string character set
   * @access private
   */
  private $_charset;
  /**
   * @var string Vtag
   * @access private
   */
  private $_vtag;
  /**
   * @var string key
   * @access private
   */
  private $_key;
  /**
   * @var array array of linked objects
   * @access private
   */
  private $_links = array();
  /**
   * @var array Array of indices
   * @access private
   */
  private $_indices = array();
  /**
   * Class constructor
   *
   * @param Riak_Client $client the Riak client object
   * @param Riak_Bucket $bucket the Riak bucket object
   * @param string $key The optional key to use for storing this object. If none supplied server will generate.	
   */
  public function __construct(Riak_Client $client, Riak_Bucket $bucket, $key=NULL) 
  {
    $this->_client = $client;
    $this->_bucket = $bucket;
    $this->_key = $key;
    $this->_exists = FALSE;
  }
  /**
   * Clear this object out
   *
   * @return void
   */
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
  /**
   * Return bucket object
   *
   * @return Riak_Bucket
   */
  public function getBucket() 
  {
    return $this->_bucket;
  }
  /**
   * Return key
   *
   * @return string the key
   */
  public function getKey()  
  {
    return $this->_key;
  }
  /**
   * Set key
   * @param string $key key
   * @return Riak_Object
   */
  public function setKey($key)  
  {
    $this->_key = $key;
    return $this;
  }
  /**
   * Return the object's value
   *
   * @return string the value
   */
  public function getValue() 
  { 
    return $this->_data; 
  }
  /**
   * Set the value
   *
   * @param string $data
   * @return Riak_Object
   */
  public function setValue($data) 
  { 
    $this->_data = $data; 
    return $this;
  }
  /**
   * Check to see if this object exists
   *
   * @return bool Boolean true if this object exists
   */
  public function exists() 
  {
    return $this->_exists;
  }
  /**
   * Set the flag for object existance
   *
   * @param bool $value Boolean value
   */
  public function setExists($value)
  {
    $this->_exists = $value;
    return $this;
  }
  /**
   * Retrieve deleted value
   *
   * @return bool Boolean value
   */
  public function getDeleted()
  {
    return $this->_deleted;
  }
  /**
   * Set boolean value for deleted flag
   *
   * @param bool $d
   * @return Riak_Object
   */
  public function setDeleted($d)
  {
    $this->_deleted = $d;
    return $this;
  }
  /**
   * Retrieve content type
   *
   * @return string Content type
   */
  public function getContentType() 
  {  
    return $this->_content_type; 
  }
  /**
   * Set content type
   *
   * @param string $contentType
   * @return Riak_Object
   */
  public function setContentType($contentType) 
  {
    $this->_content_type = $contentType;
    return $this;
  }
  /**
   * Retrieve content encoding
   *
   * @return string content encoding
   */
  public function getContentEncoding()
  {
    return $this->_content_encoding;
  }
  /**
   * Set content encoding
   *
   * @param string $encoding the content encoding
   * @return Riak_Object
   */
  public function setContentEncoding($encoding)
  {
    $this->_content_encoding = $encoding;
    return $this;
  }
  /**
   * Retrieve last modified time
   *
   * @return string Last modified time
   */
  public function getLastModified() 
  {
    return $this->_last_modified;
  }
  /**
   * Set last modified time
   *
   * @param string $mod Last modified time
   * @return Riak_Object
   */
  public function setLastModified($mod) 
  {
    $this->_last_modified = $mod;
    return $this;
  }
  /**
   * Retrieve last modified time in usecs
   *
   * @return string Last modified time usecs
   */
  public function getLastModifiedUsecs() 
  {
    return $this->_last_modified_usecs;
  }
  /**
   * Set last modified time usecs
   *
   * @param string $mod Last modified time usecs
   * @return Riak_Object
   */
  public function setLastModifiedUsecs($mod) 
  {
    $this->_last_modified_usecs = $mod;
    return $this;
  }
  /**
   * Get character set
   *
   * @return string charset
   */
  public function getCharset()
  {
    return $this->_charset;
  }
  /**
   * Set character set
   *
   * @param string $charset charset
   * @return Riak_Object
   */
  public function setCharset($charset)
  {
    $this->_charset = $charset;
    return $this; 
  }
  /**
   * Get vtag
   *
   * @return string vtag
   */
  public function getVtag()
  {
    return $this->_vtag;
  }
  /**
   * Set vtag
   *
   * @param string $vtag vtag
   * @return Riak_Object
   */
  public function setVtag($vtag)
  {
    $this->_vtag = $vtag;
    return $this;
  }
  /**
   * Return metadata by key
   *
   * @param string $metaName The key to retrieve
   * @return string The metadata string
   */
  public function getMeta($metaName) 
  {
    $metaName = strtolower($metaName);
    return isset($this->_meta[$metaName]) ? $this->_meta[$metaName] : null;
  }
  /**
   * Set metadata by key
   *
   * @param string $metaName The metadata key
   * @param string $value The value
   * @return Riak_Object
   */  
  public function setMeta($metaName, $value) 
  {
    $this->_meta[strtolower($metaName)] = $value;
    return $this;
  }
  /**
   * Remove metadata by key 
   *  
   * @param string $metaName the key to remove
   * @return Riak_Object
   */
  public function removeMeta($metaName) 
  {
    unset ($this->_meta[strtolower($metaName)]);
    return $this;
  }
  /**
   * Return all metadata 
   *  
   * @return array Array of metadata
   */  
  public function getAllMeta() 
  {
    return $this->_meta;
  }
  /**
   * Remove all metadata 
   *  
   * @return Riak_Object
   */  
  public function removeAllMeta() 
  {
    $this->_meta = array();
    return $this;
  }

  /**
   * Store an object in Riak
   *
   * @param int|string $w The number of replicas to write to before returning success
   * @param int|string $dw The number of primary replicas to commit to durable storage before returning success
   * @param int|string $pw The number of primary replicas which must be up to attempt to store the value
   * @param bool $returnBody Retrieve the object that has just been stored on success (will populate siblings)
   * @param bool $returnHead Retrieve metadata after successful operation
   * @param bool $ifNotModified Only perform store operation if vclock passed matches the one stored in the data store
   * @param bool $ifNoneMatch Only perform the store operation if an object with this key/bucket does not exist.
   * @return Riak_Object|bool Boolean false to handle ifnonematch/ifnotmodified, otherwise a Riak object returned (possibly updated).
   */
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
  /**
   * Retrieve the vector clock
   *
   * @return string Vector clock
   */ 
  public function getVClock() 
  {
    return $this->_vclock;
  }
  /**
   * Set vector clock
   *
   * @param string $vclock
   * @return Riak_Object
   */   
  public function setVClock($vclock)
  {
    $this->_vclock = $vclock;
    return $this;
  }
  /**
   * Check to see if an object has siblings  
   *   
   * @return bool true when has siblings, false otherwise
   */   
  public function hasSiblings() {
    return ($this->getSiblingCount() > 0);
  }
  /**
   * Get the number of siblings  
   * 
   * @return int The number of siblings  
   */
  public function getSiblingCount() {
    return count($this->_siblings);
  }
  /**
   * Returns siblings
   *
   * @return array list of siblings
   */
  public function getSiblings()
  {
    return $this->_siblings;
  }
  /**
   * Sets the array of siblings
   *
   * @param array Array of siblings
   * @return Riak_Object
   *
   */
  public function setSiblings(array $siblings)
  {
    $this->_siblings = $siblings;
    return $this;
  }
  /**
   * Add a sibling to the list
   *
   * @param Riak_Object $sibling The sibling
   * @return Riak_Object
   */
  public function addSibling(Riak_Object $sibling) 
  {
    $this->_siblings[] = $sibling;
    return $this;
  }
  /**
   * Delete an object in Riak
   *
   * @param int|string $rw how many replicas to delete before returning a successful response
   * @param int|string $r The number of replicas to read from before returning success
   * @param int|string $w The number of replicas to write to before returning success
   * @param int|string $pr The number of primary replicas which must be up to attempt to retrieve the value
   * @param int|string $pw The number of primary replicas which must be up to attempt to delete the value
   * @param int|string $dw The number of primary replicas to commit to durable storage before returning success
   * @return bool True on success, false on failure
   */  
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
  /**
   * Add a link to this object
   *
   * @param Riak_Link|Riak_Object A riak object or link
   * @return Riak_Object
   */
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
  /**
   * Remove a link 
   *  
   * @param Riak_Link|Riak_Object A riak object or link
   * @return Riak_Object
   */
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
  /**
   * Return a list of links
   * @return array a list of link objects
   */
  public function getLinks() 
  {
    return $this->_links;
  }
  /**
   * Add a secondary index to this object 
   * 
   * @param string $field the field name (make sure you specify the type suffic eg: _bin, _int)
   * @param string $value The value
   * @return Riak_Object
   */
  public function addSecondaryIndex($field, $value)
  {
    $this->_indices[$field] = $value;
    return $this;
  }
  /**
   * Return the list of indices 
   *
   * @return array An array of indices
   */
  public function getIndices()
  {
    return $this->_indices;
  }
}
