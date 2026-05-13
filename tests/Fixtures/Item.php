<?php

namespace LorneQuinn\HierarchicalScope\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LorneQuinn\HierarchicalScope\Traits\BelongsToScope;

class Item extends Model
{
    use BelongsToScope;

    protected $table = 'items';

    protected $guarded = [];
}
