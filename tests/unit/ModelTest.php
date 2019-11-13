<?php

class ModelTest extends CIModuleTests\Support\UnitTestCase
{
	public function testWithString()
	{
		$model = new \CIModuleTests\Support\Models\FactoryModel();
		$model->with('machines');
		
		$result = $this->getPrivateProperty($model, 'tmpWith');
        
        $this->assertEquals(['machines'], $result);
	}
	
	public function testWithArray()
	{
		$model = new \CIModuleTests\Support\Models\FactoryModel();
		$model->with(['machines', 'workers']);
		
		$result = $this->getPrivateProperty($model, 'tmpWith');
        
        $this->assertEquals(['machines', 'workers'], $result);
	}
	
	public function testWithMerges()
	{
		$model = new \CIModuleTests\Support\Models\MachineModel();
		$model->with('servicers');
		
		$result = $this->getPrivateProperty($model, 'tmpWith');
        
        $this->assertEquals(['factories', 'servicers'], $result);
	}
	
	public function testWithOverwrites()
	{
		$model = new \CIModuleTests\Support\Models\MachineModel();
		$model->with('servicers', true);
		
		$result = $this->getPrivateProperty($model, 'tmpWith');
        
        $this->assertEquals(['servicers'], $result);
	}
	
	public function testWithRepeats()
	{
		$model = new \CIModuleTests\Support\Models\FactoryModel();
		$model->with('machines')->with('workers');
		
		$result = $this->getPrivateProperty($model, 'tmpWith');
        
        $this->assertEquals(['workers'], $result);
	}
	
	public function testWithFalse()
	{
		$model = new \CIModuleTests\Support\Models\MachineModel();
		$model->with(false);
		
		$result = $this->getPrivateProperty($model, 'tmpWith');
        
        $this->assertEmpty($result);
	}
	
	public function testWithoutString()
	{
		$model = new \CIModuleTests\Support\Models\FactoryModel();
		$model->without('machines');
		
		$result = $this->getPrivateProperty($model, 'tmpWithout');
        
        $this->assertEquals(['machines'], $result);
	}
	
	public function testWithoutArray()
	{
		$model = new \CIModuleTests\Support\Models\FactoryModel();
		$model->without(['machines', 'workers']);
		
		$result = $this->getPrivateProperty($model, 'tmpWithout');
        
        $this->assertEquals(['machines', 'workers'], $result);
	}
}
