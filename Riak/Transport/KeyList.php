<?php

class Riak_Transport_KeyList implements Iterator
{

  private $_stack;
  private $_transport;
  private $_lastStack = false;

  public function __construct(Riak_Transport_Interface $transport)
  {
    $this->_transport = $transport;
    list($this->_lastStack,$this->_stack) = $this->_transport->getNextKeyListStack();
  }

  function rewind() {
  }

  function current() {
    return array_shift($this->_stack);
  }

  function key() { return 0;}

  function next() {
    if (empty($this->_stack)) {
      list($this->_lastStack,$this->_stack) = $this->_transport->getNextKeyListStack();
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
