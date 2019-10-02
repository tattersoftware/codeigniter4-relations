<?php namespace CIModuleTests\Support\Database\Seeds;

class TestSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		// Factories
		$factories = [
			[
				'name'       => 'Test Factory',
				'uid'        => 'test001',
				'class'      => 'Factories\Tests\NewFactory',
				'icon'       => 'fas fa-puzzle-piece',
				'summary'    => 'Longer sample text for testing',
			],
			[
				'name'       => 'Widget Factory',
				'uid'        => 'widget',
				'class'      => 'Factories\Tests\WidgetPlant',
				'icon'       => 'fas fa-puzzle-piece',
				'summary'    => 'Create widgets in your factory',
				'deleted_at' => date('Y-m-d H:i:s'),
			],
			[
				'name'       => 'Evil Factory',
				'uid'        => 'evil-maker',
				'class'      => 'Factories\Evil\MyFactory',
				'icon'       => 'fas fa-book-dead',
				'summary'    => 'Abandon all hope, ye who enter here',
			],
			[
				'name'       => 'Empty Factory',
				'uid'        => 'empty-maker',
				'class'      => 'Retired\Factory\Empty',
				'icon'       => 'fas fa-building',
				'summary'    => 'Where did everybody go?',
			],
		];
		
		$builder = $this->db->table('factories');
		foreach ($factories as $factory)
		{
			$builder->insert($factory);
		}
		
		// Workers
		$workers = [
			[
				'firstname'   => 'Joe',
				'lastname'    => 'DePatrio',
				'role'        => 'assembler',
				'age'         => 52,
			],
			[
				'firstname'   => 'Jill',
				'lastname'    => 'DePatrio',
				'role'        => 'producer',
				'age'         => 48,
			],
			[
				'firstname'   => 'Steven',
				'lastname'    => 'Barghast',
				'role'        => 'manager',
				'age'         => 33,
			],
			[
				'firstname'   => 'Susan',
				'lastname'    => 'Delgado',
				'role'        => 'owner',
				'age'         => 59,
			],
			[
				'firstname'   => 'Lollie',
				'lastname'    => 'Kittman',
				'role'        => 'manager',
				'age'         => 50,
			],
			[
				'firstname'   => 'Erick',
				'lastname'    => 'Geradine',
				'role'        => 'unassigned',
				'age'         => 40,
			],
			[
				'firstname'   => 'Jason',
				'lastname'    => 'Burbanks',
				'role'        => 'unassigned',
				'age'         => 22,
				'deleted_at'  => date('Y-m-d H:i:s'),
			],
			[
				'firstname'   => 'Connie',
				'lastname'    => 'Ferman',
				'role'        => 'assemler',
				'age'         => 66,
				'deleted_at'  => date('Y-m-d H:i:s'),
			],
			[
				'firstname'   => 'Joseph',
				'lastname'    => 'Singleton',
				'role'        => 'assembler',
				'age'         => 38,
			],
		];
		
		$builder = $this->db->table('workers');
		foreach ($workers as $worker)
		{
			$builder->insert($worker);
		}

		// Factories-Workers
		$rows = [
			['factory_id' => 1, 'worker_id'  => 1],
			['factory_id' => 1, 'worker_id'  => 2],
			['factory_id' => 1, 'worker_id'  => 3],
			['factory_id' => 1, 'worker_id'  => 4],
			['factory_id' => 2, 'worker_id'  => 4],
			['factory_id' => 2, 'worker_id'  => 5],
			['factory_id' => 2, 'worker_id'  => 8],
			['factory_id' => 3, 'worker_id'  => 5],
			['factory_id' => 3, 'worker_id'  => 9],
		];
		
		$builder = $this->db->table('factories_workers');
		foreach ($rows as $row)
		{
			$builder->insert($row);
		}
		
		// Machines
		$machines = [
			[
				'type'       => 'Roller',
				'serial'     => '0A087FF9',
				'factory_id' => 1,
			],
			[
				'type'       => 'Stamper',
				'serial'     => 'B42E9CA1',
				'factory_id' => 1,
			],
			[
				'type'       => 'Cutter',
				'serial'     => 'FD449C8C',
				'factory_id' => 1,
			],
			[
				'type'       => 'Roller',
				'serial'     => 'DF6A8BB0',
				'factory_id' => 2,
			],
			[
				'type'       => 'Stamper',
				'serial'     => '54DF802F',
				'factory_id' => 2,
			],
			[
				'type'       => 'Cutter',
				'serial'     => '793699A9',
				'factory_id' => 3,
			],
			[
				'type'       => 'Cutter',
				'serial'     => 'BFED08AA',
				'factory_id' => 3,
			],
		];
		
		$builder = $this->db->table('machines');
		foreach ($machines as $machine)
		{
			$builder->insert($machine);
		}
		
		// Servicers
		$servicers = [
			['company' => 'Harmon Electronics'],
			['company' => 'Cutter Enterprises'],
			['company' => 'Dream Machines'],
			['company' => 'Widgets-R-Us', 'deleted_at' => date('Y-m-d H:i:s')],
		];
		
		$builder = $this->db->table('servicers');
		foreach ($servicers as $servicer)
		{
			$builder->insert($servicer);
		}
		
		// Machines-Servicers
		$rows = [
			['machine_id' => 1, 'servicer_id' => 1],
			['machine_id' => 2, 'servicer_id' => 1],
			['machine_id' => 3, 'servicer_id' => 1],
			['machine_id' => 6, 'servicer_id' => 1],
			['machine_id' => 7, 'servicer_id' => 1],
			['machine_id' => 3, 'servicer_id' => 2],
			['machine_id' => 6, 'servicer_id' => 2],
			['machine_id' => 7, 'servicer_id' => 2],
			['machine_id' => 2, 'servicer_id' => 3],
			['machine_id' => 4, 'servicer_id' => 3],
			['machine_id' => 6, 'servicer_id' => 3],
			['machine_id' => 4, 'servicer_id' => 4],
			['machine_id' => 5, 'servicer_id' => 4],
		];
		
		$builder = $this->db->table('machines_servicers');
		foreach ($rows as $row)
		{
			$builder->insert($row);
		}
		
		// Lawyers
		$lawyers = [
			['servicer_id' => 1, 'name' => 'Harmon Miller'],
			['servicer_id' => 2, 'name' => 'Smooth Bill'],
			['servicer_id' => 2, 'name' => 'Slick Rick'],
			['servicer_id' => 2, 'name' => 'Dirty Dan'],
			['servicer_id' => 4, 'name' => 'Inita Jobe'],
		];
		
		$builder = $this->db->table('lawyers');
		foreach ($lawyers as $lawyer)
		{
			$builder->insert($lawyer);
		}
		
		// Lawsuits
		$lawsuits = [
			['client' => 2, 'factory_id' => 1, 'lawyer_id' => 1],
			['client' => 8, 'factory_id' => 2, 'lawyer_id' => 5],
			['client' => 4, 'factory_id' => 3, 'lawyer_id' => 2],
			['client' => 5, 'factory_id' => 3, 'lawyer_id' => 5, 'deleted_at' => date('Y-m-d H:i:s')],
		];
		
		$builder = $this->db->table('lawsuits');
		foreach ($lawsuits as $lawsuit)
		{
			$builder->insert($lawsuit);
		}
	}
}
