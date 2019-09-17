<?php

use Tatter\Schemas\Handlers\CacheHandler;
use Tatter\Schemas\Handlers\DatabaseHandler;

class LiveTest extends CIModuleTests\Support\DatabaseTestCase
{
	// Probably the most likely scenario for use
	public function testBasic()
	{
		//$builder = $this->db->table('factories');
		
		$result = $this->db->table('factories')
			->select('workers.*')
            ->join('factories_workers', 'factories_workers.factory_id = factories.id', 'inner')
            ->join('workers', 'factories_workers.worker_id = workers.id', 'inner')
			->select('machines.*')
            ->join('machines', 'factories.id = machines.factory_id', 'inner')
            ->where('factories.id', 1)
            ->get()->getResultArray();
            
        d($result);
        d((string)$this->db->getLastQuery());
        
        $this->assertTrue(true);
	}
}
