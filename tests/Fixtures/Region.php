<?php

namespace LorneQuinn\HierarchicalScope\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LorneQuinn\HierarchicalScope\Traits\IsHierarchical;

class Region extends Model
{
    use IsHierarchical;

    protected $table = 'regions';

    protected $guarded = [];

    protected ?string $parentColumn = 'owner_id';
}
