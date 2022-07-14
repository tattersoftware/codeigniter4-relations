<?php namespace Tests\Support\Entities;

use Tatter\Relations\Traits\EntityTrait;
use CodeIgniter\Entity\Entity;

class Lawyer extends Entity
{
	use EntityTrait;

	protected $table = 'lawyers';

	protected $primaryKey = 'id';
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
