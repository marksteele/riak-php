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
require_once('/usr/share/pear/DrSlump/Protobuf.php');
use \DrSlump\Protobuf;
Protobuf::autoload();

/*
  This implementation is based on the API developed by Basho's Rusty Klophaus (@rklophaus) (rusty@basho.com) and others.
  See here for more information: https://github.com/basho/riak-php-client
*/

/**
 * Riak protocol buffer transport
 *
 * Protocol buffer Riak transport implementation
 *
 * @author Mark Steele <mark@control-alt-del.org>
 * @version 1.0
 * @package Riak_Transport_Pb
 * @copyright 2012 Mark Steele
 */
class Riak_Transport_Pb extends Riak_Transport
{
  /**#@+
   * Class constants
   * @var int
   */
  const MSG_CODE_ERROR_RESP           =  0;
  const MSG_CODE_PING_REQ             =  1; // 0 length response
  const MSG_CODE_PING_RESP            =  2; // 0 length response
  const MSG_CODE_GET_CLIENT_ID_REQ    =  3;
  const MSG_CODE_GET_CLIENT_ID_RESP   =  4;
  const MSG_CODE_SET_CLIENT_ID_REQ    =  5;
  const MSG_CODE_SET_CLIENT_ID_RESP   =  6;
  const MSG_CODE_GET_SERVER_INFO_REQ  =  7;
  const MSG_CODE_GET_SERVER_INFO_RESP =  8;
  const MSG_CODE_GET_REQ              =  9;
  const MSG_CODE_GET_RESP             = 10;
  const MSG_CODE_PUT_REQ              = 11;
  const MSG_CODE_PUT_RESP             = 12;
  const MSG_CODE_DEL_REQ              = 13;
  const MSG_CODE_DEL_RESP             = 14;
  const MSG_CODE_LIST_BUCKETS_REQ     = 15;
  const MSG_CODE_LIST_BUCKETS_RESP    = 16;
  const MSG_CODE_LIST_KEYS_REQ        = 17;
  const MSG_CODE_LIST_KEYS_RESP       = 18;
  const MSG_CODE_GET_BUCKET_REQ       = 19;
  const MSG_CODE_GET_BUCKET_RESP      = 20;
  const MSG_CODE_SET_BUCKET_REQ       = 21;
  const MSG_CODE_SET_BUCKET_RESP      = 22;
  const MSG_CODE_MAPRED_REQ           = 23;
  const MSG_CODE_MAPRED_RESP          = 24;
  const MSG_CODE_INDEX_REQ	      = 25;
  const MSG_CODE_INDEX_RESP           = 26;
  const MSG_CODE_SEARCH_QUERY_REQ     = 27;
  const MSG_CODE_SEARCH_QUERY_RESP    = 28;
  /**#@-*/
  /**
   * Mapping of message code to PB class names
   * @access private
   * @var array
   */
  private $_classMap = array(
    self::MSG_CODE_ERROR_RESP => 'RpbErrorResp',
    self::MSG_CODE_GET_CLIENT_ID_REQ => 'RpbGetClientIdReq',
    self::MSG_CODE_GET_CLIENT_ID_RESP => 'RpbGetClientIdResp',
    self::MSG_CODE_SET_CLIENT_ID_REQ => 'RpbSetClientIdReq',
    self::MSG_CODE_SET_CLIENT_ID_RESP => 'RpbSetClientIdResp',
    self::MSG_CODE_GET_SERVER_INFO_REQ => 'RpbGetServerInfoReq',
    self::MSG_CODE_GET_SERVER_INFO_RESP => 'RpbGetServerInfoResp',
    self::MSG_CODE_GET_REQ => 'RpbGetReq',
    self::MSG_CODE_GET_RESP => 'RpbGetResp',
    self::MSG_CODE_PUT_REQ => 'RpbPutReq',
    self::MSG_CODE_PUT_RESP => 'RpbPutResp',
    self::MSG_CODE_DEL_REQ => 'RpbDelReq',
    self::MSG_CODE_DEL_RESP => 'RpbDelResp',
    self::MSG_CODE_LIST_BUCKETS_REQ => 'RpbListBucketsReq',
    self::MSG_CODE_LIST_BUCKETS_RESP => 'RpbListBucketsResp',
    self::MSG_CODE_LIST_KEYS_REQ => 'RpbListKeysReq',
    self::MSG_CODE_LIST_KEYS_RESP => 'RpbListKeysResp',
    self::MSG_CODE_GET_BUCKET_REQ => 'RpbGetBucketReq',
    self::MSG_CODE_GET_BUCKET_RESP => 'RpbGetBucketResp',
    self::MSG_CODE_SET_BUCKET_REQ => 'RpbSetBucketReq',
    self::MSG_CODE_SET_BUCKET_RESP => 'RpbSetBucketResp',
    self::MSG_CODE_MAPRED_REQ => 'RpbMapRedReq',
    self::MSG_CODE_MAPRED_RESP => 'RpbMapRedResp',
    self::MSG_CODE_INDEX_REQ => 'RpbIndexReq',
    self::MSG_CODE_INDEX_RESP => 'RpbIndexResp',
    self::MSG_CODE_SEARCH_QUERY_REQ => 'RpbSearchQueryReq',
    self::MSG_CODE_SEARCH_QUERY_RESP => 'RpbSearchQueryResp',
  );

  /**
   * Mapping of quorum text values to integers, used for convenience
   * @access private
   * @var array
   */
  private $_quorumNames = array(
    'default' => 4294967291,
    'all' => 4294967292,
    'quorum' => 4294967293,
    'one' => 4294967294
  );

  /**
   * Socket used for communications
   * @var resource
   * @access private
   */
  private $_socket;
  /**
   * Port to connect to
   * @access private
   * @var int
   */
  private $_port;
  /**
   * Host to connect to
   * @access private
   * @var string
   */
  private $_host;
  /**
   * Class constructor
   *
   * @param string $host The host to connect to
   * @param int $port The tcp port
   */
  public function __construct($host='127.0.0.1', $port=8087) 
  {
    $this->_port = $port;
    $this->_host = $host;
  }
  /**
   * Translate a quorum name to it's integer value
   *
   * @param string $item
   * @return mixed The value if it's found in the map, otherwise returns input
  public static function translateQuorum($item)
  {
    return isset($this->_quorumNames[$item]) ? $this->_quorumNames[$item] : $item;
  }
  /**
   * Set the client identifier, used to ease vector clock handling
   *
   * @param string The client id
   * @return bool True on success
   * @throws Riak_Transport_Exception
   */ 
  public function setClientId($clientId)
  {
    $req = new $this->_classMap[self::MSG_CODE_SET_CLIENT_ID_REQ]();
    $req->setClientId($clientId);
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_SET_CLIENT_ID_REQ));
    list ($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_SET_CLIENT_ID_RESP) {
      return true;
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception(sprintf("Protocol buffer error: $s" . $response->getErrmsg()));
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer response code: " . $messageCode);
    }   
  }
  /**
   * Returns the server version number
   *
   * @return string Server version string
   * @throws Riak_Transport_Exception
   */ 
  public function getServerVersion()
  {
    if (!$this->_serverVersion) {
      $this->_sendCode(self::MSG_CODE_GET_SERVER_INFO_REQ);
      list ($messageCode, $response) = $this->_receiveMessage();
      if ($messageCode == self::MSG_CODE_GET_SERVER_INFO_RESP) {
        $this->_serverVersion = $response->getServerVersion();
      } else {
        if ($messageCode == self::MSG_CODE_ERROR_RESP) {
          if ($response->hasErrmsg()) {
            throw new Riak_Transport_Exception(sprintf("Protocol buffer error: $s" . $response->getErrmsg()));
          }
        }
        throw new Riak_Transport_Exception("Unexpected protocol buffer response code: " . $messageCode);
      }   
    }
    return $this->_serverVersion;
  }
  /**
   * Establish connection to server
   *
   * @param bool $force Force a new connection
   * @return resource Socket connection to server
   * @throws Riak_Transport_Exception
   */
  protected function _getConnection($force = null)
  {
    // NOTE TO SELF: Re-write this to be able to disable NAGLE (TCP_NODELAY)
    if ($force || !is_resource($this->_socket)) {
      $errno = null;
      $errstr = null;
      $this->_socket = stream_socket_client('tcp://' . $this->_host . ':' . $this->_port, $errno, $errstr, 30);
      stream_set_timeout($this->_socket, 86400);
      if (!is_resource($this->_socket)) {
        throw new Riak_Transport_Exception('Error creating socket. Error number :' . $errno . ' error string: '. $errstr);
      }
    }
    return $this->_socket;
  }
  /**
   * Handle writing data payload over socket
   *
   * @param string $payload The data to send
   * @throws Riak_Transport_Exception
   */
  protected function _sendData($payload)
  {
    for ($written = 0; $written < strlen($payload); $written += $fwrite) {
      $fwrite = fwrite($this->_getConnection(),substr($payload, $written));
      if ($fwrite === false) {
        $this->_socket = null;
        throw new Riak_Transport_Exception('Error writing on socket');
      }
    }
  }
  /**
   * Send a message code on the wire
   * @param int The message code to send
   * @return void
   */
  protected function _sendCode($msgCode)
  {
    $packed = pack("NC", 1, $msgCode);
    $this->_sendData($packed);
  }
  /**
   * Receive packets on the wire, snarfle in a full message
   *
   * @return string A string containing the PB encoded message
   * @throws Riak_Transport_Exception
   */
  protected function _receivePacket()
  {
    $message = '';
    $header = fread($this->_getConnection(), 4);
    if ($header === false) {
      // Read error?
      $metadata = stream_get_meta_data($this->_getConnection());
      if ($metadata['timed_out']) {
        throw new Riak_Transport_Exception('Read timeout on socket');
      }
      throw new Riak_Transport_Exception('Read error on socket' );
    }
    if (strlen($header) !== 4) {
      throw new Riak_Transport_Exception('Short read on header, read ' . strlen($header) . ' bytes');
    }
    list($length) = array_values(unpack("N", $header));
    while (strlen($message) !== $length) {
      $buffer = fread($this->_getConnection(), min(8192, $length - strlen($message)));
      if (!strlen($buffer) || $buffer === false) {
        $this->_socket = null;
        throw new Riak_Transport_Exception('Error reading on socket');
      }
      $message .= $buffer;
    }
    return $message; // First character is message code...
  }
  /**
   * Read and decode a message
   * 
   * @throws Riak_Transport_Exception
   * @return array An array containing the message code and the PB decoded object
   */
  protected function _receiveMessage()
  {
    $message = $this->_receivePacket();
    list($messageCode) = array_values(unpack("C", $message{0}));
    switch($messageCode) {
      case self::MSG_CODE_PING_RESP:
      case self::MSG_CODE_SET_CLIENT_ID_RESP:
      case self::MSG_CODE_DEL_RESP:
      case self::MSG_CODE_SET_BUCKET_RESP:
        $obj = null;
        break;
      case self::MSG_CODE_ERROR_RESP:
      case self::MSG_CODE_GET_BUCKET_RESP:
      case self::MSG_CODE_GET_CLIENT_ID_RESP:
      case self::MSG_CODE_GET_RESP:
      case self::MSG_CODE_GET_SERVER_INFO_RESP:
      case self::MSG_CODE_LIST_BUCKETS_RESP:
      case self::MSG_CODE_LIST_KEYS_RESP:
      case self::MSG_CODE_MAPRED_RESP:
      case self::MSG_CODE_PUT_RESP:
      case self::MSG_CODE_SEARCH_QUERY_RESP:
      case self::MSG_CODE_INDEX_RESP:
        $obj = Protobuf::decode($this->_classMap[$messageCode], substr($message, 1));
        break;
      default:
        throw new Riak_Transport_Exception("Unknown code");
    }
    return array($messageCode, $obj);
  }
  /**
   * Encode a message for transport
   * 
   * @param mixed A PB object
   * @param int A PB message code
   * @return string A binary string payload that's ready for transmission over the wire
   */
  protected function _encodeMessage($obj, $messageCode)
  {
    $message = Protobuf::encode($obj);
    return pack("NC", 1 + strlen($message), $messageCode) . $message;
  }
  /**
   * Check to see if the connection is working
   *
   * @return bool True on success
   * @throws Riak_Transport_Exception
   */
  public function ping()
  {
    $this->_sendCode(self::MSG_CODE_PING_REQ);
    list ($messageCode, $obj) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_PING_RESP) {
      return true;
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception(sprintf("Protocol buffer error: $s" . $response->getErrmsg()));
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer response code: " . $messageCode);
    }
  }
  /**
   * Retrieve the list of buckets. Use with caution...
   *
   * @return array The list of buckets.
   * @throws Riak_Transport_Exception
   */
  public function listBuckets()
  {
    $this->_sendCode(self::MSG_CODE_LIST_BUCKETS_REQ);
    list ($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_LIST_BUCKETS_RESP) {
      if (!$response->hasBuckets()) {
        return array();
      } else {
        return $response->getBucketsList();
      }
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer response code: " . $messageCode);    
    }
  }
  /**
   * Set properties for a bucket
   *
   * @param string $name Bucket name
   * @param array $props Array of properties
   * @return bool True on success
   * @throws Riak_Transport_Exception
   */
  public function setBucketProperties($name, array $props)
  {
    $properties = new RpbBucketProps();
    if (isset($props['allow_mult'])) {
      $properties->setAllowMult($props['allow_mult']);
    }
    if (isset($props['n_val'])) {
      $properties->setNVal($props['n_val']);
    }
    $req = new $this->_classMap[self::MSG_CODE_SET_BUCKET_REQ]();
    $req->setBucket($name);
    $req->setProps($properties);	   
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_SET_BUCKET_REQ));
    list ($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_SET_BUCKET_RESP) {
      return true;
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer response code: " . $messageCode);
    }
  }
  /**
   * Get bucket properties
   *
   * @param string $name Bucket name
   * @return array An array of bucket properties
   * @throws Riak_Transport_Exception
   */
  public function getBucketProperties($name)
  {
    $req = new $this->_classMap[self::MSG_CODE_GET_BUCKET_REQ]();
    $req->setBucket($name);
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_GET_BUCKET_REQ));
    list ($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_GET_BUCKET_RESP) {
      $data = array();
      if ($response->hasProps()) {
        $props = $response->getProps();               
        if ($props->hasNVal()) {
          $data['n_val'] = $props->getNVal();
        }
        if ($props->hasAllowMult()) {
          $data['allow_mult'] = $props->getAllowMult();
        }
      }
      return $data;
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer response code: " . $messageCode);
    }
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
   * @throws Riak_Transport_Exception
   */
  public function store(Riak_Object &$obj, $w = null, $dw = null, $pw = null, $returnBody = false, $returnHead = false, $ifNotModified = false, $ifNoneMatch = false)
  {
    $req = new $this->_classMap[self::MSG_CODE_PUT_REQ]();
    $req->setBucket($obj->getBucket()->getName());
    if ($obj->getKey()) { // else server generated
      $req->setKey($obj->getKey()); 
    }
    if ($w) {
      $req->setW(self::translateQuorumNames($w));
    }
    if ($dw) {
      $req->setDw(self::translateQuorumNames($dw));
    }
    if ($pw && $this->hasQuorumControls()) {
      $req->setPw(self::translateQuorumNames($pw));
    }
    
    $req->setReturnBody($returnBody);

    if ($returnHead && !$this->hasPbHead()) {
      throw new Riak_Transport_Exception("Head not supported on this version of Riak: " . $this->getServerVersion());
    }
    $req->setReturnHead($returnHead);
    if (($ifNotModified || $ifNoneMatch) && !$this->hasPbConditionals()) {
      throw new Riak_Transport_Exception("Conditional store not supported on this version of Riak: " . $this->getServerVersion());
    }
    $req->setIfNotModified($ifNotModified);
    $req->setIfNoneMatch($ifNoneMatch);
    if ($obj->getVClock()) {
      $req->setVclock($obj->getVClock());
    }
    $content = new RpbContent();
    $content->setValue($obj->getValue());
    if ($obj->getContentType()) {
      $content->setContentType($obj->getContentType());
    }
    if ($obj->getCharset()) {
      $content->setCharset($obj->getCharset());
    }
    if ($obj->getContentEncoding()) {
      $content->setContentEncoding($obj->getContentEncoding());
    }
    if ($obj->getVtag()) {
      $content->setVtag($obj->getVtag());
    }
    foreach ($obj->getAllMeta() as $k => $v) {
      $pair = new RpbPair();
      $pair->setKey($k);
      $pair->setValue($v);
      $content->addUserMeta($pair);
    }

    foreach ($obj->getLinks() as $link) {
      $l = new RpbLink();
      $l->setBucket($link->getBucket());
      $l->setKey($link->getKey());
      if ($link->getTag()) {
        $l->setTag($link->getTag());
      }
      $content->addLinks($l);
    }

    foreach ($obj->getIndices() as $field => $value) {
      $i = new RpbPair();
      $i->setKey($field);
      $i->setValue($value);
      $content->addIndexes($i);
    }
    
    $req->setContent($content);
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_PUT_REQ));
    list ($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_PUT_RESP) {
      if ($response->hasVclock()) {
        $obj->setVClock($response->getVclock());
      }
      if (!$obj->getKey()) {
	$obj->setKey($response->getKey()); // We asked server to generate a key for us
      }
      if ($response->hasContent()) {
        $siblings = $response->getContentList();
        // We have to build a new populated object.
        $this->_populate($obj, array_pop($siblings));
        foreach ($siblings as $sibling) {
          $child = new Riak_Object($obj->getBucket()->getClient(), $obj->getBucket(), $obj->getKey());
          $this->_populate($child, $sibling);
          $child->setVClock($response->getVclock());
          $obj->addSibling($child);
        }
      }
      return $obj;
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer response code: " . $messageCode);
    }
  }
  /**
   * Populate a Riak_Object object with data from a RpbContent PB class
   *
   * @param Riak_Object $obj The riak object to update
   * @param RpbContent $content the PB class object
   * @return void
   */
  private function _populate(Riak_Object &$obj, RpbContent $content) 
  {
    $obj->clear();
    $obj->setExists(true);
    if ($content->hasContentType()) {
      $obj->setContentType($content->getContentType());
    }
    if ($content->hasCharset()) {
      $obj->setCharset($content->getCharset());
    }
    if ($content->hasContentEncoding()) {
      $obj->setContentEncoding($content->getContentEncoding());
    }
    if ($content->hasVtag()) {
      $obj->setVtag($content->getVtag());
    }
    if ($content->hasLastMod()) {
      $obj->setLastModified($content->GetLastMod());
    }
    if ($content->hasLastModUsecs()) {
      $obj->setLastModifiedUsecs($content->getLastModUsecs());
    }
    if ($content->hasUserMeta()) {
      foreach ($content->getUserMetaList() as $rpbpair) {
        $obj->setMeta($rpbpair->getKey(), $rpbpair->getValue());
      }
    }
    if ($content->hasDeleted()) {
      $obj->setDeleted($content->getDeleted());
    }
    if ($content->hasLinks()) {
      foreach ($content->getLinksList() as $link) {
       $obj->addLink(new Riak_Link($link->getBucket(),$link->getKey(),$link->hasTag() ? $link->getTag() : null));
      }
    }
    $obj->setValue($content->getValue());
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
   * @throws Riak_Transport_Exception
   */
  public function fetch(Riak_Object &$obj, $r = null, $pr = null, $basicQuorum = false, $notfoundOk = false, $ifModified = null, $head = false, $deletedVclock = false)
  {
    $req = new $this->_classMap[self::MSG_CODE_GET_REQ]();
    $req->setBucket($obj->getBucket()->getName());
    $req->setKey($obj->getKey()); 
    if ($r) {
      $req->setR(self::translateQuorumNames($r));
    }
    if ($this->hasQuorumControls() && $pr) {
      $req->setPr(self::translateQuorumNames($pr));
    }
    $req->setHead($head);
    $req->setBasicQuorum($basicQuorum);
    $req->setNotfoundOk($notfoundOk);
    if ($ifModified) {
      $req->setIfModified($ifModified);
    }
    $req->setHead($head);
    if ($this->hasTombstoneVclocks()) {
      $req->setDeletedVclock($deletedVclock);
    }
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_GET_REQ));
    list($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_GET_RESP) {
      if ($response->hasUnchanged()) {
        return $response->getUnchanged();
      }
      if ($response->hasVclock()) {
        $obj->setVClock($response->getVclock());
      }
      if ($response->hasContent()) {
        // We have to build a new populated object.
        $siblings = $response->getContentList();
        $this->_populate($obj, array_pop($siblings));
        $obj->setVClock($response->getVclock());
        foreach ($siblings as $sibling) {
          $child = new Riak_Object($obj->getBucket()->getClient(), $obj->getBucket(), $obj->getKey());
          $this->_populate($child, $sibling);
          $child->setVClock($response->getVclock());
          $obj->addSibling($child);
        }
      }
      return $obj;
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer response code: " . $messageCode);
    }    
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
   * @throws Riak_Transport_Exception
   */
  public function delete(Riak_Object $obj, $rw = null, $r = null, $w = null, $pr = null, $pw = null, $dw = null)
  {
    $req = new $this->_classMap[self::MSG_CODE_DEL_REQ]();
    $req->setBucket($obj->getBucket()->getName());
    $req->setKey($obj->getKey()); 
    if ($obj->getVClock()) {
      $req->setVclock($obj->getVClock());
    }
    if ($rw) {
      $req->setRw(self::translateQuorumNames($rw));
    }
    if ($r) {
      $req->setR(self::translateQuorumNames($r));
    }
    if ($w) {
      $req->setW(self::translateQuorumNames($w));
    }

    if ($dw) {
      $req->setDW(self::translateQuorumNAmes($dw));
    }

    if (($pr || $pw) && !$this->hasQuorumControls()) {
      throw new Riak_Transport_Exception("Quorum controls not supported on this server verison: " . $this->getServerVersion());
    }
    if ($pr) {
      $this->setPr(self::translateQuorumNames($pr));
    }
    if ($pw) {
      $this->setPw(self::translateQuorumNames($pw));
    }
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_DEL_REQ));
    list($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_DEL_RESP) {
      return true;
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer response code: " . $messageCode);
    }    
  }
  /**
   * Retrieve a list of keys
   *
   * @param Riak_Bucket $bucket Bucket object
   * @return Riak_Transport_Iterator The key listing iterator
   */
  public function listKeys(Riak_Bucket $bucket)
  {
    $req = new $this->_classMap[self::MSG_CODE_LIST_KEYS_REQ]();
    $req->setBucket($bucket->getName());
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_LIST_KEYS_REQ));
    return new Riak_Transport_Iterator($this,'getNextKeyListStack');
  }
  /**
   * Fetch a stack of keys from the sever
   *
   * @return array An array consisting of a flag to indicate the end of results, and an array of results.
   * @throws Riak_Transport_Exception
   */
  public function getNextKeyListStack()
  {
    $lastStack = false;
    list($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_LIST_KEYS_RESP) {
      if ($response->hasDone() && $response->getDone()) {
        $lastStack = true; // This signals our iterator to stop.
      }
      $results = $response->getKeysList();
      return array($lastStack, $results);
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer message code: " . $messageCode);
    }
  }
  /**
   * Perform a search
   *
   * @param string $query The query to send
   * @param string $index The index to search
   * @param int $rows the number of rows to fetch
   * @param int $start the starting offset
   * @param string $sort sorting field
   * @param string $filter filters search with additional query scoped to inline fields
   * @param string $df override the default_field setting in the schema file
   * @param string $op 'and' or 'or', to override the default_op operation setting in the schema file
   * @param array $fl return the fields limit
   * @param string $presort presort (key/score)
   * @return array An array of search results
   * @throws Riak_Transport_Exception
   */
  public function search($query, $index, $rows = null, $start = null, $sort = null, $filter = null, $df = null, $op = null, $fl = array(), $presort = null)
  {
    $req = new $this->_classMap[self::MSG_CODE_SEARCH_QUERY_REQ]();
    $req->setQ($query);
    $req->setIndex($index);
    if ($rows) {
      $req->setRows($rows);
    }
    if ($start) {
      $req->setStart($start);
    }
    if ($sort) {
      $req->setSort($sort);
    }
    if ($filter) {
      $req->setFilter($filter);
    }
    if ($df) {
      $req->setDf($df);
    }
    if ($op) {
      $req->setOp($op);
    }
    if ($fl) {
      foreach ($fl as $item) {
        $req->addFl($item);
      }
    }
    if ($presort) {
      $req->setPresort($presort);
    }
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_SEARCH_QUERY_REQ));
    list($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_SEARCH_QUERY_RESP) {
      $results = array();
      if ($response->hasMaxScore()) {
        $results['max_score'] = $response->getMaxScore();
      }
      if ($response->hasNumFound()) {
        $results['num_found'] = $response->getNumFound();
      }
      if ($response->hasDocs()) {
        foreach ($response->getDocsList() as $doc) {
          $data = array();
          foreach ($doc->getFieldsList() as $pair) {
            $data[$pair->getKey()] = $pair->getValue();
          }
          $results['docs'][] = $data;
        }
      }
      return $results;
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer message code: " . $messageCode);
    }
  }
  /**
   * Perform a 2i search
   *
   * @param string $bucket The bucket name
   * @param string $index The index to search
   * @param int $queryType the query type: 0 (eq) or 1 (range)
   * @param string $key The key to search for (for 'eq' searches)
   * @param int $rangeMin The starting range, for range queries
   * @param int $rangeMax The ending range, for range queries
   * @return array an array of search results
   */
  public function search2i($bucket, $index, $queryType = 0, $key = null, $rangeMin = null, $rangeMax = null) 
  {
    $req = new $this->_classMap[self::MSG_CODE_INDEX_REQ]();
    $req->setBucket($bucket);
    $req->setIndex($index);
    $req->setQtype($queryType);
    if ($key) {
      $req->setKey($key);
    }
    if ($rangeMin) {
      $req->setRangeMin($rangeMin);
    }
    if ($rangeMax) {
      $req->setRangeMax($rangeMax);
    }
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_INDEX_REQ));
    list($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_INDEX_RESP) {
      if ($response->hasKeys()) {
        return $response->getKeysList();
      } else {
        return array();
      }
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer message code: " . $messageCode);
    }
  }
  /**
   * Send a mapreduce job
   *
   * @param string $request The mapreduce request
   * @param string $contentType The content type ('application/json', 'application/erlang')
   * @return Riak_Transport_Iterator The map reduce iterator
   * @throws Riak_Transport_Exception
   */
  public function mapReduce($request,$contentType)
  {
    $req = new $this->_classMap[self::MSG_CODE_MAPRED_REQ]();
    $req->setRequest($request);
    $req->setContentType($contentType);
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_MAPRED_REQ));
    return new Riak_Transport_Iterator($this,'getNextMapReduceStack');
  }
  /**
   * Grab a stack of map reduce results
   *
   * @return array An array consisting of the flag to indicate end of results, and an array of results.
   * @throws Riak_Transport_Exception
   */
  public function getNextMapReduceStack()
  {
    $lastStack = false;
    list($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_MAPRED_RESP) {
      if ($response->hasDone() && $response->getDone()) {
        $lastStack = true; // This signals our iterator to stop.
      }
      $ret = array();
      if ($response->hasPhase()) {
        $ret['phase'] = $response->getPhase();
      }
      if ($response->hasResponse()) {
        $ret['response'] = $response->getResponse();
      }
      return array($lastStack, $ret);
    } else {
      if ($messageCode == self::MSG_CODE_ERROR_RESP) {
        if ($response->hasErrmsg()) {
          throw new Riak_Transport_Exception("Protocol buffer error: " . $response->getErrmsg());
        }
      }
      throw new Riak_Transport_Exception("Unexpected protocol buffer message code: " . $messageCode);
    }
  }
}
