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
 * Riak transport iterators
 *
 * A mechanism for fetching results in a streaming fashion from the underlying transport.
 *
 * @author Mark Steele <mark@control-alt-del.org>
 * @version 1.0
 * @package Riak_Transport_Iterator
 * @copyright 2012 Mark Steele
 */
class Riak_Transport_Iterator implements Iterator
{
  /**
   * @var array stack of results
   */
  private $_stack;
  /**
   * @var Riak_Transport
   */
  private $_transport;
  /**
   * @var bool Last stack indicator
   */
  private $_lastStack = false;
  /**
   * @var string callback to call to retrieve another stack of results
   */
  private $_callback;
  /**
   * Class constructor 
   *
   * @param Riak_Transport_Interface $transport The riak transport to use
   * @param string $callback The callback to use for fetching results
   */
  public function __construct(Riak_Transport_Interface $transport, $callback)
  {
    $this->_transport = $transport;
    $this->_callback = $callback;
    list($this->_lastStack,$this->_stack) = call_user_func(array($this->_transport,$this->_callback));
  }
  /**
   * Rewind, no-op
   *
   * @return void
   */
  function rewind() {
  }
  /**
   * Grab an element from the stack
   *
   * @return mixed a result set element
   */
  function current() {
    return array_shift($this->_stack);
  }
  /**
   * Key no-op
   *
   * @return int
   */
  function key() { return 0;}
  /**
   * Retrieve and return the next set of results
   *
   * @return array An array containing the last stack indicator and a result
   */
  function next() {
    if (empty($this->_stack)) {
      list($this->_lastStack,$this->_stack) = call_user_func(array($this->_transport, $this->_callback));
    }
  }
  /**
   * Checks to see if we're done reading
   *
   * @return bool True if we still have data, false otherwise.
   */
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
