<?php

namespace Tests\Support\Entities;

use CodeIgniter\Entity\Entity;
use Tatter\Relations\Traits\EntityTrait;

class Machine extends Entity
{
    use EntityTrait;

    protected $table      = 'machines';
    protected $primaryKey = 'id';
    protected $dates      = ['created_at', 'updated_at'];
}
