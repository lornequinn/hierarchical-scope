<?php

use Livewire\Livewire;
use LorneQuinn\HierarchicalScope\Livewire\ScopeSwitcher;
use LorneQuinn\HierarchicalScope\Tests\Fixtures\Scope;

beforeEach(function () {
    $this->root = Scope::create(['name' => 'Acme', 'slug' => 'acme', 'type' => 'org']);
    $this->division = Scope::create(['name' => 'Engineering', 'slug' => 'eng', 'type' => 'division', 'parent_id' => $this->root->id]);
});

it('mounts with the current scope and tree populated', function () {
    Livewire::test(ScopeSwitcher::class)
        ->assertSet('currentId', $this->root->id)
        ->assertSet('currentName', 'Acme')
        ->assertSet('open', false)
        ->assertSeeHtml('Acme')
        ->tap(function ($component) {
            expect($component->get('tree'))->toBeArray();
            expect($component->get('tree')['name'])->toBe('Acme');
            expect($component->get('tree')['children'])->toHaveCount(1);
        });
});

it('switches scope, closes the panel, and dispatches scope-changed', function () {
    Livewire::test(ScopeSwitcher::class)
        ->set('open', true)
        ->call('switchTo', $this->division->id)
        ->assertSet('currentId', $this->division->id)
        ->assertSet('currentName', 'Engineering')
        ->assertSet('open', false)
        ->assertDispatched('scope-changed', scopeId: $this->division->id);

    expect(session('current_scope_id'))->toBe($this->division->id);
});

it('toggles the open state', function () {
    Livewire::test(ScopeSwitcher::class)
        ->assertSet('open', false)
        ->call('toggle')
        ->assertSet('open', true)
        ->call('toggle')
        ->assertSet('open', false);
});
