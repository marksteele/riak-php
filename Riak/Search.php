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
 * Riak search class
 *
 * Searching via 2i and Search
 *
 * @author Mark Steele <mark@control-alt-del.org>
 * @version 1.0
 * @package Riak_Search
 * @copyright 2012 Mark Steele
 */
class Riak_Search
{
  /**
   * @var Riak_Client
   * @access private
   */
  private $_client;
  /**
   * Class constructor
   *
   * @param Riak_Client $client the riak client
   */
  public function __construct(Riak_Client $client)
  {
    $this->_client = $client;
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
   */
  public function search($query, $index, $rows = null, $start = null, $sort = null, $filter = null, $df = null, $op = null, $fl = array(), $presort = null)
  {
    return $this->_client->getTransport()->search($query,$index,$rows,$start,$sort,$filter,$df,$op,$fl,$presort);
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
    return $this->_client->getTransport()->search2i($bucket, $index, $queryType, $key, $rangeMin, $rangeMax);
  }
}
