<?php

require_once('/usr/share/pear/DrSlump/Protobuf.php');
use \DrSlump\Protobuf;
Protobuf::autoload();

class Riak_Transport_Pb extends Riak_Transport
{
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
  );

  private $_socket;
  private $_port;
  private $_host;

  public function __construct($host='127.0.0.1', $port=8087) 
  {
    $this->_port = $port;
    $this->_host = $host;
    return $this;
  }

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

  public function getServerVersion()
  {
    if (!$this->_serverVersion) {
      $req = new $this->_classMap[self::MSG_CODE_GET_SERVER_INFO_REQ]();
      $req->setClientId($clientId);
      $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_GET_SERVER_INFO_REQ));
      list ($messageCode, $response) = $this->_receiveMessage();
      if ($messageCode == self::MSG_CODE_GET_SERVER_INFO_REQ) {
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

  protected function _getConnection($force = null)
  {
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

  protected function _sendCode($msgCode)
  {
    $packed = pack("NC", 1, $msgCode);
    $this->_sendData($packed);
  }

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
        $obj = Protobuf::decode($this->_classMap[$messageCode], substr($message, 1));
        break;
      default:
        throw new Riak_Transport_Exception("Unknown code");
    }
    return array($messageCode, $obj);
  }

  protected function _encodeMessage($obj, $messageCode)
  {
    $message = Protobuf::encode($obj);
    return pack("NC", 1 + strlen($message), $messageCode) . $message;
  }

  protected function _decodeContent($content)
  {
    $metadata = array();
    $links = array();
    $userMetadata = array();

    // Handle metadata
    if ($content->hasContentType()) {
      $metadata['content-type'] = $content->getContentType();
    }
    if ($content->hasCharset()) {
      $metadata['charset'] = $content->getCharset();
    }
    if ($content->hasContentEncoding()) {
      $metadata['content-encoding'] = $content->getContentEncoding();
    }
    if ($content->hasVtag()) {
      $metadata['vtag'] = $content->getVtag();
    }

    foreach ($content->getLinks() as $link) {
      $bucket = null;
      $key = null;
      $tag = null;
      if ($link->hasBucket()) {
        $bucket = $link->getBucket();
      }
      if ($link->hasKey()) {
        $key = $link->getKey();
      }
      if ($link->hasTag()) {
        $tag = $link->getTag();
      }
      $links[] = new Riak_Link($bucket, $key, $tag);
    }
    if ($links) {
      $metadata['links'] = $links;
    }
    if ($content->hasLastMod()) {
      $metadata['last_mod'] = $content->getLastMod();
    }
    if ($content->hasLastModUsecs()) {
      $metadata['last_mod_usecs'] = $content->getLastModUsecs();
    }
    foreach ($content->getUserMetaList() as $userMeta) {
      $userMetadata[$userMeta->getKey()] = $userMeta->getValue();
    }
    if ($userMetadata) {
      $metadata['user-metadata'] = $userMetadata;
    }
    return array($metadata, $content->getValue());
  }

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

  public function store(Riak_Object &$obj, $w = null, $dw = null, $pw = null, $returnBody = false, $returnHead = false, $ifNotModified = false, $ifNoneMatch = false)
  {
    $req = new $this->_classMap[self::MSG_CODE_PUT_REQ]();
    $req->setBucket($obj->getBucket()->getName());
    if ($obj->getKey()) { // else server generated
      $req->setKey($obj->getKey()); 
    }
    if ($w) {
      $req->setW($w);
    }
    if ($dw) {
      $req->setDw($dw);
    }
    if ($pw) {
      $req->setPw($pw);
    }
    $req->setReturnBody($returnBody);
    $req->setReturnHead($returnHead);
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
      $link = new RpbLink();
      $link->setBucket($link->getBucket());
      $link->setKey($link->getKey());
      $link->setTag($link->getTag());
      $content->addLinks($link);
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
    return $obj;
  }

  public function fetch(Riak_Object &$obj, $r = null, $pr = null, $basic_quorum = false, $notfound_ok = false, $if_modified = null, $head = false, $deleted_vclock = false)
  {
    $req = new $this->_classMap[self::MSG_CODE_GET_REQ]();
    $req->setBucket($obj->getBucket()->getName());
    $req->setKey($obj->getKey()); 
    if ($r) {
      $req->setR($w);
    }
    if ($pr) {
      $req->setPr($pr);
    }
    $req->setHead($head);
    $req->setBasicQuorum($basic_quorum);
    $req->setNotfoundOk($notfound_ok);
    if ($if_modified) {
      $req->setIfModified($if_modified);
    }
    $req->setHead($head);
    $req->setDeletedVclock($deleted_vclock);
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
        $obj = $this->_populate(new Riak_Object($obj->getBucket()->getClient(), $obj->getBucket(), $obj->getKey()), array_pop($siblings));
        $obj->setVClock($response->getVclock());
        foreach ($siblings as $sibling) {
          $child = $this->_populate(new Riak_Object($obj->getBucket()->getClient(), $obj->getBucket(), $obj->getKey() ? $obj->getKey() : $response->getKey()), $sibling);
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

  public function delete(Riak_Object $obj, $dw = null)
  {
    $req = new $this->_classMap[self::MSG_CODE_DEL_REQ]();
    $req->setBucket($obj->getBucket()->getName());
    $req->setKey($obj->getKey()); 
    if ($dw) {
      $req->setDW($dw);
    }
    if ($obj->getVClock()) {
      $req->setVclock($obj->getVClock());
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

  public function listKeys(Riak_Bucket $bucket)
  {
    $req = new $this->_classMap[self::MSG_CODE_LIST_KEYS_REQ]();
    $req->setBucket($bucket->getName());
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_LIST_KEYS_REQ));
    return new Riak_Transport_KeyList($this);
  }

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

}
