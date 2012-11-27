<?php

interface Riak_Transport_Interface
{
  public function ping();
  public function listBuckets();
  public function setBucketProperties($name, array $properties);
  public function getBucketProperties($name);
  public function store(Riak_Object &$obj, $w = null, $dw = null, $pw = null, $returnBody = false, $returnHead = false, $ifNotModified = false, $ifNoneMatch = false);
  public function fetch(Riak_Object &$obj, $r = null, $pr = null, $basicQuorum = false, $notfoundOk = false, $ifModified = null, $head = false, $deletedVclock = false);
  public function delete(Riak_Object $obj, $rw = null, $r = null, $w = null, $pr = null, $pw = null, $dw = null);
  public function getServerVersion();
  public function listKeys(Riak_Bucket $bucket);
  public function getNextKeyListStack();
  public function search($query, $index, $rows = null, $start = null, $sort = null, $filter = null, $df = null, $op = null, $fl = array(), $presort = null);
  public function search2i($bucket, $index, $queryType = 0, $key = null, $rangeMin = null, $rangeMax = null);
  public function mapReduce($request, $contentType);
  public function getNextMapReduceStack();
}
