<?php

namespace Tests\Support\Entities;

use CodeIgniter\Entity\Entity;
use Tatter\Relations\Traits\EntityTrait;

class Lawyer extends Entity
{
    use EntityTrait;

    protected $table      = 'lawyers';
    protected $primaryKey = 'id';
    protected $dates      = ['created_at', 'updated_at', 'deleted_at'];
}
