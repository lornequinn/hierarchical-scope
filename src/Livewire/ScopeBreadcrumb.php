<?php

namespace LorneQuinn\HierarchicalScope\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use LorneQuinn\HierarchicalScope\Services\ScopeContext;

class ScopeBreadcrumb extends Component
{
    public array $breadcrumbs = [];
    public string $currentName = '';

    public function mount(ScopeContext $scopeContext): void
    {
        $this->updateBreadcrumbs($scopeContext);
    }

    #[On('scope-changed')]
    public function onScopeChanged(ScopeContext $scopeContext): void
    {
        $this->updateBreadcrumbs($scopeContext);
    }

    public function navigateTo(int|string $scopeId, ScopeContext $scopeContext): void
    {
        $scopeContext->setCurrentById($scopeId);
        $this->updateBreadcrumbs($scopeContext);
        $this->dispatch('scope-changed', scopeId: $scopeId);
    }

    private function updateBreadcrumbs(ScopeContext $scopeContext): void
    {
        $current = $scopeContext->current();
        $this->currentName = $current->name;

        $this->breadcrumbs = $current->getAncestors()->map(fn ($scope) => [
            'id' => $scope->getKey(),
            'name' => $scope->name,
        ])->toArray();
    }

    public function render()
    {
        return view('hierarchical-scope::livewire.scope-breadcrumb');
    }
}
