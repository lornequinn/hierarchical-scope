<?php

use LorneQuinn\HierarchicalScope\Tests\Fixtures\Item;
use LorneQuinn\HierarchicalScope\Tests\Fixtures\Region;
use LorneQuinn\HierarchicalScope\Tests\Fixtures\Scope;
use LorneQuinn\HierarchicalScope\Tests\Fixtures\Widget;

beforeEach(function () {
    $this->root = Scope::create(['name' => 'Root']);
    $this->divisionA = Scope::create(['name' => 'Division A', 'parent_id' => $this->root->id]);
    $this->divisionB = Scope::create(['name' => 'Division B', 'parent_id' => $this->root->id]);
    $this->teamA1 = Scope::create(['name' => 'Team A1', 'parent_id' => $this->divisionA->id]);

    $this->itemRoot = Item::create(['name' => 'Item @ Root', 'scope_id' => $this->root->id]);
    $this->itemA = Item::create(['name' => 'Item @ Division A', 'scope_id' => $this->divisionA->id]);
    $this->itemA1 = Item::create(['name' => 'Item @ Team A1', 'scope_id' => $this->teamA1->id]);
    $this->itemB = Item::create(['name' => 'Item @ Division B', 'scope_id' => $this->divisionB->id]);
    $this->itemUnscoped = Item::create(['name' => 'Item unscoped', 'scope_id' => null]);
});

describe('hierarchicalScope relation', function () {
    it('returns the related scope model', function () {
        expect($this->itemA->hierarchicalScope)->toBeInstanceOf(Scope::class);
        expect($this->itemA->hierarchicalScope->id)->toBe($this->divisionA->id);
    });

    it('returns null when scope is unset', function () {
        expect($this->itemUnscoped->hierarchicalScope)->toBeNull();
    });
});

describe('scopeInScope', function () {
    it('includes the scope itself and all descendants', function () {
        $names = Item::inScope($this->divisionA)->pluck('name')->all();

        expect($names)->toHaveCount(2);
        expect($names)->toContain('Item @ Division A', 'Item @ Team A1');
    });

    it('returns the entire tree from root', function () {
        $names = Item::inScope($this->root)->pluck('name')->all();

        expect($names)->toHaveCount(4);
        expect($names)->not->toContain('Item unscoped');
    });

    it('returns only the leaf when called on a leaf node', function () {
        $names = Item::inScope($this->teamA1)->pluck('name')->all();

        expect($names)->toBe(['Item @ Team A1']);
    });

    it('excludes sibling subtrees', function () {
        $names = Item::inScope($this->divisionB)->pluck('name')->all();

        expect($names)->toBe(['Item @ Division B']);
    });
});

describe('scopeInScopeIds', function () {
    it('filters by a pre-computed id list', function () {
        $names = Item::inScopeIds([$this->divisionA->id, $this->teamA1->id])->pluck('name')->all();

        expect($names)->toHaveCount(2);
        expect($names)->toContain('Item @ Division A', 'Item @ Team A1');
    });

    it('returns no rows for an empty id list', function () {
        expect(Item::inScopeIds([])->count())->toBe(0);
    });
});

describe('scopeWithNullScope', function () {
    it('adds a null-scope OR clause to the existing query', function () {
        $names = Item::where('scope_id', $this->divisionA->id)
            ->withNullScope()
            ->pluck('name')
            ->all();

        expect($names)->toHaveCount(2);
        expect($names)->toContain('Item @ Division A', 'Item unscoped');
    });
});

describe('scopeInScopeOrNull', function () {
    it('matches the given ids OR rows with null scope', function () {
        $names = Item::inScopeOrNull([$this->divisionB->id])->pluck('name')->all();

        expect($names)->toHaveCount(2);
        expect($names)->toContain('Item @ Division B', 'Item unscoped');
    });

    it('returns just unscoped items when ids list is empty', function () {
        $names = Item::inScopeOrNull([])->pluck('name')->all();

        expect($names)->toBe(['Item unscoped']);
    });
});

describe('column and model overrides', function () {
    it('honours $scopeColumn property on the model', function () {
        $widget = new Widget;
        expect($widget->getScopeColumnName())->toBe('division_id');
    });

    it('honours $scopeModel property on the model', function () {
        $widget = new Widget;
        expect($widget->getScopeModelClass())->toBe(Region::class);
    });

    it('uses overridden column and model end-to-end', function () {
        $rootRegion = Region::create(['name' => 'AU']);
        $childRegion = Region::create(['name' => 'NSW', 'owner_id' => $rootRegion->id]);

        $widget = Widget::create(['name' => 'AU widget', 'division_id' => $rootRegion->id]);
        $childWidget = Widget::create(['name' => 'NSW widget', 'division_id' => $childRegion->id]);

        expect($widget->hierarchicalScope)->toBeInstanceOf(Region::class);

        $names = Widget::inScope($rootRegion)->pluck('name')->all();
        expect($names)->toHaveCount(2)->toContain('AU widget', 'NSW widget');

        $childOnly = Widget::inScope($childRegion)->pluck('name')->all();
        expect($childOnly)->toBe(['NSW widget']);
    });

    it('falls back to config defaults when no override', function () {
        config()->set('hierarchical-scope.scope_column', 'scope_id');
        config()->set('hierarchical-scope.model', Scope::class);

        $item = new Item;
        expect($item->getScopeColumnName())->toBe('scope_id');
        expect($item->getScopeModelClass())->toBe(Scope::class);
    });

    it('respects a config override when no model property is set', function () {
        config()->set('hierarchical-scope.scope_column', 'custom_scope');

        $item = new Item;
        expect($item->getScopeColumnName())->toBe('custom_scope');
    });
});
