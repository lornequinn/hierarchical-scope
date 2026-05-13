<?php

use LorneQuinn\HierarchicalScope\Tests\Fixtures\Region;
use LorneQuinn\HierarchicalScope\Tests\Fixtures\Scope;

beforeEach(function () {
    $this->root = Scope::create(['name' => 'Acme', 'slug' => 'acme', 'type' => 'org']);
    $this->division = Scope::create(['name' => 'Engineering', 'slug' => 'eng', 'type' => 'division', 'parent_id' => $this->root->id]);
    $this->team = Scope::create(['name' => 'Platform', 'slug' => 'platform', 'type' => 'team', 'parent_id' => $this->division->id]);
    $this->sibling = Scope::create(['name' => 'Sales', 'slug' => 'sales', 'type' => 'division', 'parent_id' => $this->root->id]);
});

describe('relationships', function () {
    it('exposes parent as a BelongsTo to self', function () {
        expect($this->team->parent)->toBeInstanceOf(Scope::class);
        expect($this->team->parent->id)->toBe($this->division->id);
    });

    it('exposes children as a HasMany of self', function () {
        expect($this->root->children)->toHaveCount(2);
        expect($this->root->children->pluck('name')->all())->toContain('Engineering', 'Sales');
    });

    it('returns null parent for the root', function () {
        expect($this->root->parent)->toBeNull();
    });
});

describe('getDescendantIds', function () {
    it('includes self', function () {
        expect($this->team->getDescendantIds()->all())->toBe([$this->team->id]);
    });

    it('walks the full subtree', function () {
        $ids = $this->root->getDescendantIds()->all();

        expect($ids)->toHaveCount(4)
            ->and($ids)->toContain($this->root->id, $this->division->id, $this->team->id, $this->sibling->id);
    });

    it('returns a Collection', function () {
        expect($this->root->getDescendantIds())->toBeInstanceOf(\Illuminate\Support\Collection::class);
    });
});

describe('getAncestors', function () {
    it('returns empty for root', function () {
        expect($this->root->getAncestors()->all())->toBe([]);
    });

    it('returns ancestors in root-first order, excluding self', function () {
        $ancestors = $this->team->getAncestors();

        expect($ancestors)->toHaveCount(2);
        expect($ancestors->pluck('id')->all())->toBe([$this->root->id, $this->division->id]);
    });
});

describe('path attribute', function () {
    it('builds a slash-separated breadcrumb of names', function () {
        expect($this->team->path)->toBe('Acme / Engineering / Platform');
    });

    it('returns just the name for a root node', function () {
        expect($this->root->path)->toBe('Acme');
    });
});

describe('isRoot', function () {
    it('is true for nodes without a parent', function () {
        expect($this->root->isRoot())->toBeTrue();
    });

    it('is false for nodes with a parent', function () {
        expect($this->division->isRoot())->toBeFalse();
        expect($this->team->isRoot())->toBeFalse();
    });
});

describe('static root()', function () {
    it('returns the first node with no parent', function () {
        expect(Scope::root()->id)->toBe($this->root->id);
    });

    it('returns null when no root exists', function () {
        Scope::query()->delete();
        expect(Scope::root())->toBeNull();
    });
});

describe('scopeOfType', function () {
    it('filters by type column', function () {
        $divisions = Scope::ofType('division')->get();

        expect($divisions)->toHaveCount(2);
        expect($divisions->pluck('name')->all())->toContain('Engineering', 'Sales');
    });
});

describe('parent column override', function () {
    it('honours $parentColumn property on the model', function () {
        $rootRegion = Region::create(['name' => 'AU']);
        $childRegion = Region::create(['name' => 'NSW', 'owner_id' => $rootRegion->id]);

        expect($rootRegion->getParentColumnName())->toBe('owner_id');
        expect($childRegion->parent->id)->toBe($rootRegion->id);
        expect($rootRegion->children->pluck('id')->all())->toBe([$childRegion->id]);
        expect($rootRegion->isRoot())->toBeTrue();
        expect($childRegion->isRoot())->toBeFalse();
    });

    it('falls back to config when no override', function () {
        config()->set('hierarchical-scope.parent_column', 'parent_id');

        $scope = new Scope;
        expect($scope->getParentColumnName())->toBe('parent_id');
    });

    it('respects a config override when no model property is set', function () {
        config()->set('hierarchical-scope.parent_column', 'custom_parent');

        $scope = new Scope;
        expect($scope->getParentColumnName())->toBe('custom_parent');
    });
});
