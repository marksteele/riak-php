<?php

require_once('/usr/share/pear/DrSlump/Protobuf.php');
use \DrSlump\Protobuf;
Protobuf::autoload();

class Riak_Transport_Pb implements Riak_Transport_Interface
{
        /**#@+
         * Class constants
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
        /**#@-*/
        /**
         * Create a map of generated protocol buffer msg_codes to classes
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


        /**
         * Return the client connection
         * @param bool $force
         * @return resource
         */
        protected function _getConnection($force = null)
        {
                if ($force || !is_resource($this->_socket)) {
                        $errno = null;
                        $errstr = null;
                        $this->_socket = stream_socket_client(
              'tcp://' . $this->_host . ':' . $this->_port, 
              $errno, 
              $errstr, 
              30
            );
                        stream_set_timeout($this->_socket, 86400);
                        if (!is_resource($this->_socket)) {
                                throw new Riak_Transport_Exception(
                  'Error creating socket. 
                  Error number :' . $errno . ' error string: '. $errstr
                );
                        }
                }
                return $this->_socket;
        }
        /**
         * Sends a payload of data on the socket
         * @param string $payload
         * @return void
         */
        protected function _sendData($payload)
        {
                for ($written = 0; $written < strlen($payload); $written += $fwrite) {
                    $fwrite = fwrite(
            $this->_getConnection(), 
            substr($payload, $written)
          );
                    if ($fwrite === false) {
                        $this->_socket = null;
                        throw new Riak_Transport_Exception('Error writing on socket');
                    }
                }
        }
	/**
         * Send a code on the socket. 
         * @param unknown_type $msgCode
         * @return unknown_type
         */
        protected function _sendCode($msgCode)
        {
                $packed = pack("NC", 1, $msgCode);
                $this->_sendData($packed);
        }
	/**
         * Receive a packet (header+pb message)
         * @return string
         */
        protected function _receivePacket()
        {
                $message = '';
                $header = fread($this->_getConnection(), 4);
                if ($header === false) {
                        // Read error?
                        $metadata = stream_get_meta_data($this->_getConnection());
                        if ($metadata['timed_out']) {
                                throw new Riak_Transport_Exception(
                  'Read timeout on socket'
                );
                        }
                        throw new Riak_Transport_Exception('Read error on socket' );
                }
                if (strlen($header) !== 4) {
                        throw new Riak_Transport_Exception(
              'Short read on header, read ' . strlen($header) . ' bytes'
            );
                }

                list($length) = array_values(unpack("N", $header));
                while (strlen($message) !== $length) {
                        $buffer = fread(
              $this->_getConnection(), 
              min(8192, $length - strlen($message))
            );
                        if (!strlen($buffer) || $buffer === false) {
                                $this->_socket = null;
                                throw new Riak_Transport_Exception('Error reading on socket');
                        }
                        $message .= $buffer;
                }
                return $message; // First character is message code...
        }
 	/**
         * Massage packet into a protocol buffer message and message code
         * @return array An array with message code and pb object
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
                                $obj = Protobuf::decode(
                  $this->_classMap[$messageCode], 
                  substr($message, 1)
                );
                                break;
                        default:
                                throw new Riak_Transport_Exception("Unknown code");
                }
                return array($messageCode, $obj);
        }
	/**
         * Encode a protocol buffer message and message code into a 
     	 * wire format message
         * @param mixed $obj Protocol buffer object
         * @param int $messageCode
         * @return string
         */
        protected function _encodeMessage($obj, $messageCode)
        {
                $message = Protobuf::encode($obj);
                return pack("NC", 1 + strlen($message), $messageCode) . $message;
        }
	/**
         * Decode content of a message
         * @param mixed $content
         * @return array An array of metadata, and the message content
         */
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
	/**
         * Encode a message for protocol buffers
         * @param array $metadata An array of metadata
         * @param string $data The data payload
         * @param mixed $content Protocol buffer object to be manipulated
         * @return void
         */
        protected function _protocolBufferEncodeContent($metadata, $data, &$req)
        {
                $content = $req->getContent();
                foreach ($metadata as $k => $v) {
                        switch($k) {
                                case 'content-type':
                                        $content->setContentType($v);
                                        break;
                                case 'charset':
                                        $content->setCharset($v);
                                        break;
                                case 'content-encoding':
                                        $content->setContentEncoding($v);
                                        break;
                                case 'user-metadata':
                                        foreach ($v as $uk => $uv) {
                                                $pair = new RpbPair();
                                                $pair->setKey($uk);
                                                $pair->setValue($uv);
                                                $content->addUserMeta($pair);
                                        }
                                        break;
                                case 'links':
                                        foreach ($v as $link) {
                                                $pbLink = new RpbLink();
                                                $pbLink->setKey($link->getKey());
                                                $pbLink->setTag($link->getTag());
                                                $pbLink->setBucket($link->getBucket());
                                                $content->addLinks($pbLink);
                                        }
                                        break;
                        }
                }
                $content->setValue($data);
        }
        /**
         * Ping the server
         * @return bool True on success
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
         * Retrieve an object
         * @param RiakObject $riakObject
         * @param int $r The number or replicas to use for quorum
         * @param string $vtag Not implemented...
         * @return array An array that contains the vector clock, and the contents of the object
         */
        public function get(Riak_Object $riakObject, $r, $vtag = null)
        {
                $req = new $this->_classMap[self::MSG_CODE_GET_REQ]();
                $req->setR($r);
                $req->setBucket($riakObject->getBucket()->getName());
                $req->setKey($riakObject->getKey());
                if ($vtag) {
                        throw new Riak_Transport_ProtocolBuffer_Exception(
              "Vtag not necessary for protocol buffer interface"
            );
                }
                $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_GET_REQ));
                list ($messageCode, $response) = $this->_receiveMessage();
                if ($messageCode == self::MSG_CODE_GET_RESP) {
                        $contents = array();
                        foreach ($response->getContentList() as $content) {
                               $contents[] = $this->_decodeContent($content);
                        }
                        return array($response->getVclock(), $contents);
                } else {
                        if ($messageCode == self::MSG_CODE_ERROR_RESP) {
                            if ($response->hasErrmsg()) {
                                throw new Riak_Transport_ProtocolBuffer_Exception("Protocol buffer error: " . $response->getErrmsg());
                            }
                	}
		        throw new Riak_Transport_ProtocolBuffer_Exception("Unexpected protocol buffer response code: " . $messageCode);    
        	}
	}
       /**
	 * Fetch list of buckets
         * @return array An array that contains the list of buckets
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


  public function store(Riak_Object &$obj, $w = null, $dw = null, $pw = null, $returnBody = null, $returnHead = null, $ifNotModified = null, $ifNoneMatch = null)
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
    if ($returnBody) {
      $req->setReturnBody(true);
    }
    if ($returnHead) {
      $req->setReturnHead(true);
    }
    if ($ifNotModified) {
      $req->setIfNotModified(true);
    }
    if ($ifNoneMatch) {
      $req->setIfNoneMatch(true);
    }
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
    if ($obj->getAllMeta()) {
      foreach ($obj->getAllMeta() as $k => $v) {
        $pair = new RpbPair();
        $pair->setKey($k);
        $pair->setValue($v);
        $content->addUserMeta($pair);
      }
    }
    
    $req->setContent($content);
    $this->_sendData($this->_encodeMessage($req, self::MSG_CODE_PUT_REQ));
    list ($messageCode, $response) = $this->_receiveMessage();
    if ($messageCode == self::MSG_CODE_PUT_RESP) {
      if ($response->hasVclock()) {
        $obj->setVClock($response->getVclock());
      }
      if ($obj->getKey() && !$returnBody && !$returnHead) {
        return true;
      } elseif (!$obj->getKey() && !$returnBody && !$returnHead) {
	$obj->setKey($response->getKey()); // We asked server to generate a key for us
        return true;
      } else {
        // We have to build a new populated object.
        $new = new Riak_Object($obj->getBucket()->getClient(), $obj->getBucket(), $obj->getKey() ? $obj->getKey() : $response->getKey());
        $new->setVClock($response->getVclock());
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

//  private function _populate(Riak_Object, array $content) 
//  {
//  }
}
