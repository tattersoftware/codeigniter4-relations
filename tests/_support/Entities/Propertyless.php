<?php namespace Tests\Support\Entities;

use CodeIgniter\Entity;

class Propertyless extends Entity
{
	use \Tatter\Relations\Traits\EntityTrait;
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
