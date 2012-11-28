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
 * Riak transport abstract class
 *
 * Searching via 2i and Search
 *
 * @author Mark Steele <mark@control-alt-del.org>
 * @version 1.0
 * @package Riak_Transport
 * @abstract
 * @copyright 2012 Mark Steele
 */
abstract class Riak_Transport implements Riak_Transport_Interface
{
  /**
   * @var string Server version
   */
  protected $_serverVersion;
  /**
   * Check to see if server supports phaseless mapreduce
   * @return bool True if supported
   */
  public function hasPhaselessMapred()
  {
    return version_compare($this->getServerVersion(), '1.1.0','>=');
  }
  /**
   * Check to see if server supports pb indexes
   * @return bool True if supported
   */
  public function hasPbIndexes()
  {
    return version_compare($this->getServerVersion(), '1.2.0','>=');
  }
  /**
   * Check to see if server supports pb search
   * @return bool True if supported
   */
  public function hasPbSearch()
  {
    return version_compare($this->getServerVersion(),'1.2.0','>=');
  }
  /**
   * Check to see if server supports pb conditionals
   * @return bool True if supported
   */
  public function hasPbConditionals()
  {
    return version_compare($this->getServerVersion(),'1.0.0','>=');
  }
  /**
   * Check to see if server supports quorum controls
   * @return bool True if supported
   */
  public function hasQuorumControls()
  {
    return version_compare($this->getServerVersion(),'1.0.0','>=');
  }
  /**
   * Check to see if server supports tombstone vclocks
   * @return bool True if supported
   */
  public function hasTombstoneVclocks()
  {
    return version_compare($this->getServerVersion(),'1.0.0','>=');
  }
  /**
   * Check to see if server supports pb head
   * @return bool True if supported
   */
  public function hasPbHead()
  {
    return version_compare($this->getServerVersion(),'1.0.0','>=');
  }
}
