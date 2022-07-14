<?php namespace Tests\Support\Entities;

use Tatter\Relations\Traits\EntityTrait;
use CodeIgniter\Entity\Entity;

class Propertyless extends Entity
{
	use EntityTrait;
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
