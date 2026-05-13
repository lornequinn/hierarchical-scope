<?php

namespace LorneQuinn\HierarchicalScope\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LorneQuinn\HierarchicalScope\Traits\IsHierarchical;

class Scope extends Model
{
    use IsHierarchical;

    protected $table = 'scopes';

    protected $guarded = [];
}
