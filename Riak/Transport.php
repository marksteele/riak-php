<?php

abstract class Riak_Transport implements Riak_Transport_Interface
{

  protected $_serverVersion;

  public function hasPhaselessMapred()
  {
    return version_compare($this->getServerVersion(), '1.1.0','>=');
  }
  public function hasPbIndexes()
  {
    return version_compare($this->getServerVersion(), '1.2.0','>=');
  }
  public function hasPbSearch()
  {
    return version_compare($this->getServerVersion(),'1.2.0','>=');
  }
  public function hasPbConditionals()
  {
    return version_compare($this->getServerVersion(),'1.0.0','>=');
  }
  public function hasQuorumControls()
  {
    return version_compare($this->getServerVersion(),'1.0.0','>=');
  }
  public function hasTombstoneVclocks()
  {
    return version_compare($this->getServerVersion(),'1.0.0','>=');
  }
  public function hasPbHead()
  {
    return version_compare($this->getServerVersion(),'1.0.0','>=');
  }
}
