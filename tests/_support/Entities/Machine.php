<?php namespace Tests\Support\Entities;

use Tatter\Relations\Traits\EntityTrait;
use CodeIgniter\Entity\Entity;

class Machine extends Entity
{
	use EntityTrait;

	protected $table = 'machines';

	protected $primaryKey = 'id';
	
	protected $dates = ['created_at', 'updated_at'];
}
