<?php

class Riak_Search
{
  private $_client;
  public function __construct(Riak_Client $client)
  {
    $this->_client = $client;
  }

  public function search($query, $index, $rows = null, $start = null, $sort = null, $filter = null, $df = null, $op = null, $fl = array(), $presort = null)
  {
    return $this->_client->getTransport()->search($query,$index,$rows,$start,$sort,$filter,$df,$op,$fl,$presort);
  }
  public function search2i($bucket, $index, $queryType = 0, $key = null, $rangeMin = null, $rangeMax = null)
  {
    return $this->_client->getTransport()->search2i($bucket, $index, $queryType, $key, $rangeMin, $rangeMax);
  }

}
