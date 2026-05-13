<?php

use LorneQuinn\HierarchicalScope\Services\ScopeContext;
use LorneQuinn\HierarchicalScope\Tests\Fixtures\Scope;

beforeEach(function () {
    $this->context = app(ScopeContext::class);

    $this->root = Scope::create(['name' => 'Acme', 'slug' => 'acme', 'type' => 'org']);
    $this->divisionA = Scope::create(['name' => 'Engineering', 'slug' => 'eng', 'type' => 'division', 'parent_id' => $this->root->id]);
    $this->divisionB = Scope::create(['name' => 'Sales', 'slug' => 'sales', 'type' => 'division', 'parent_id' => $this->root->id]);
    $this->team = Scope::create(['name' => 'Platform', 'slug' => 'platform', 'type' => 'team', 'parent_id' => $this->divisionA->id]);
});

describe('current()', function () {
    it('defaults to root when no session value is set', function () {
        expect($this->context->current()->id)->toBe($this->root->id);
    });

    it('returns the session-stored scope when set', function () {
        $this->context->setCurrent($this->team);
        expect($this->context->current()->id)->toBe($this->team->id);
    });

    it('falls back to root when the session scope no longer exists', function () {
        $this->context->setCurrent($this->team);
        $this->team->delete();

        expect($this->context->current()->id)->toBe($this->root->id);
    });
});

describe('setCurrent / setCurrentById', function () {
    it('persists the scope id into the session', function () {
        $this->context->setCurrent($this->divisionA);
        expect(session('current_scope_id'))->toBe($this->divisionA->id);
    });

    it('looks up by id and persists', function () {
        $this->context->setCurrentById($this->divisionB->id);
        expect($this->context->current()->id)->toBe($this->divisionB->id);
    });

    it('throws when id does not exist', function () {
        $this->context->setCurrentById(999999);
    })->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

    it('uses the configured session key', function () {
        config()->set('hierarchical-scope.session_key', 'my_custom_key');

        $this->context->setCurrent($this->divisionA);
        expect(session('my_custom_key'))->toBe($this->divisionA->id);
    });
});

describe('root()', function () {
    it('returns the root scope', function () {
        expect($this->context->root()->id)->toBe($this->root->id);
    });

    it('throws when no root exists', function () {
        Scope::query()->delete();

        $this->context->root();
    })->throws(RuntimeException::class, 'Root scope not found');
});

describe('isAtRoot', function () {
    it('is true by default (no session selection)', function () {
        expect($this->context->isAtRoot())->toBeTrue();
    });

    it('is false after switching to a non-root scope', function () {
        $this->context->setCurrent($this->divisionA);
        expect($this->context->isAtRoot())->toBeFalse();
    });
});

describe('getFilterIds', function () {
    it('returns current scope ids when at root (all)', function () {
        $ids = $this->context->getFilterIds();
        expect($ids)->toHaveCount(4);
    });

    it('returns subtree ids when scoped down', function () {
        $this->context->setCurrent($this->divisionA);

        $ids = $this->context->getFilterIds();

        expect($ids)->toHaveCount(2);
        expect($ids)->toContain($this->divisionA->id, $this->team->id);
    });
});

describe('getTree', function () {
    it('returns a recursive structure starting at root', function () {
        $tree = $this->context->getTree();

        expect($tree['id'])->toBe($this->root->id);
        expect($tree['name'])->toBe('Acme');
        expect($tree['children'])->toHaveCount(2);
    });

    it('orders children alphabetically', function () {
        $tree = $this->context->getTree();

        expect($tree['children'][0]['name'])->toBe('Engineering');
        expect($tree['children'][1]['name'])->toBe('Sales');
    });

    it('nests grandchildren under their parents', function () {
        $tree = $this->context->getTree();

        $engineering = $tree['children'][0];
        expect($engineering['children'])->toHaveCount(1);
        expect($engineering['children'][0]['name'])->toBe('Platform');
    });

    it('includes slug and type when present on the model', function () {
        $tree = $this->context->getTree();

        expect($tree['slug'])->toBe('acme');
        expect($tree['type'])->toBe('org');
    });
});

describe('service provider binding', function () {
    it('binds ScopeContext as a singleton', function () {
        expect(app(ScopeContext::class))->toBe(app(ScopeContext::class));
    });
});
