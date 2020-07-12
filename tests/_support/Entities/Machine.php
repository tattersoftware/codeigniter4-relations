<?php namespace Tests\Support\Entities;

use CodeIgniter\Entity;

class Machine extends Entity
{
	use \Tatter\Relations\Traits\EntityTrait;

	protected $table = 'machines';

	protected $primaryKey = 'id';
	
	protected $dates = ['created_at', 'updated_at'];
}
