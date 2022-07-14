<?php

namespace Tests\Support\Entities;

use CodeIgniter\Entity\Entity;
use Tatter\Relations\Traits\EntityTrait;

class Propertyless extends Entity
{
    use EntityTrait;

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
