<?php

class Riak_Transport_MapReduce implements Iterator
{

  private $_stack;
  private $_transport;
  private $_lastStack = false;

  public function __construct(Riak_Transport_Interface $transport)
  {
    $this->_transport = $transport;
    list($this->_lastStack,$this->_stack) = $this->_transport->getNextMapReduceStack();
  }

  function rewind() {
  }

  function current() {
    $ret = $this->_stack;
    $this->_stack = array();
    return $ret;
  }

  function key() { return 0;}

  function next() {
    if (empty($this->_stack)) {
      list($this->_lastStack,$this->_stack) = $this->_transport->getNextMapReduceStack();
    }
  }

  function valid() {
    if (!empty($this->_stack)) {
      return true;
    } else {
      if ($this->_lastStack) {
        return false;
      }
    }
  }
}
