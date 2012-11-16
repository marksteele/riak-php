<?php

interface Riak_Transport_Interface
{
  public function ping();
  public function get(Riak_Object $riakObject, $r, $vtag = null);
  public function listBuckets();
  public function setBucketProperties($name, array $properties);
  public function getBucketProperties($name);
  public function store(Riak_Object &$obj, $w = null, $dw = null, $pw = null, $returnBody = null, $returnHead = null, $ifNotModified = null, $ifNoneMatch = null);
}
