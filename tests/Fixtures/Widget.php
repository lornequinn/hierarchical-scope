<?php

namespace LorneQuinn\HierarchicalScope\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LorneQuinn\HierarchicalScope\Traits\BelongsToScope;

class Widget extends Model
{
    use BelongsToScope;

    protected $table = 'widgets';

    protected $guarded = [];

    protected ?string $scopeColumn = 'division_id';

    protected ?string $scopeModel = Region::class;
}
