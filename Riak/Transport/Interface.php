<?php

interface Riak_Transport_Interface
{
  public function ping();
  public function get(Riak_Object $riakObject, $r, $vtag = null);
  public function listBuckets();
  public function setBucketProperties($name, array $properties);
  public function getBucketProperties($name);
  public function store(Riak_Object &$obj, $w = null, $dw = null, $pw = null, $returnBody = false, $returnHead = false, $ifNotModified = false, $ifNoneMatch = false);
  public function fetch(Riak_Object &$obj, $r = null, $pr = null, $basic_quorum = false, $notfound_ok = false, $if_modified = null, $head = false, $deleted_vclock = false);
  public function delete(Riak_Object $obj, $dw = null);
  public function getServerVersion();
}
