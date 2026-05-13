<?php

use Livewire\Livewire;
use LorneQuinn\HierarchicalScope\Livewire\ScopeBreadcrumb;
use LorneQuinn\HierarchicalScope\Services\ScopeContext;
use LorneQuinn\HierarchicalScope\Tests\Fixtures\Scope;

beforeEach(function () {
    $this->root = Scope::create(['name' => 'Acme']);
    $this->division = Scope::create(['name' => 'Engineering', 'parent_id' => $this->root->id]);
    $this->team = Scope::create(['name' => 'Platform', 'parent_id' => $this->division->id]);
});

it('mounts with no breadcrumbs at root', function () {
    Livewire::test(ScopeBreadcrumb::class)
        ->assertSet('currentName', 'Acme')
        ->tap(fn ($component) => expect($component->get('breadcrumbs'))->toBe([]));
});

it('builds the ancestor breadcrumb chain when scoped down', function () {
    app(ScopeContext::class)->setCurrent($this->team);

    Livewire::test(ScopeBreadcrumb::class)
        ->assertSet('currentName', 'Platform')
        ->tap(function ($component) {
            $crumbs = $component->get('breadcrumbs');
            expect($crumbs)->toHaveCount(2);
            expect($crumbs[0]['name'])->toBe('Acme');
            expect($crumbs[1]['name'])->toBe('Engineering');
        });
});

it('navigateTo updates breadcrumbs and dispatches scope-changed', function () {
    Livewire::test(ScopeBreadcrumb::class)
        ->call('navigateTo', $this->division->id)
        ->assertSet('currentName', 'Engineering')
        ->assertDispatched('scope-changed', scopeId: $this->division->id)
        ->tap(function ($component) {
            $crumbs = $component->get('breadcrumbs');
            expect($crumbs)->toHaveCount(1);
            expect($crumbs[0]['name'])->toBe('Acme');
        });

    expect(session('current_scope_id'))->toBe($this->division->id);
});

it('refreshes on the scope-changed event', function () {
    $component = Livewire::test(ScopeBreadcrumb::class)
        ->assertSet('currentName', 'Acme');

    app(ScopeContext::class)->setCurrent($this->team);

    $component->dispatch('scope-changed', scopeId: $this->team->id)
        ->assertSet('currentName', 'Platform')
        ->tap(function ($c) {
            $crumbs = $c->get('breadcrumbs');
            expect($crumbs)->toHaveCount(2);
            expect($crumbs[0]['name'])->toBe('Acme');
            expect($crumbs[1]['name'])->toBe('Engineering');
        });
});
