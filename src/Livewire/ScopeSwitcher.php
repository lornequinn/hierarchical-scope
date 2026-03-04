<?php

namespace LorneQuinn\HierarchicalScope\Livewire;

use Livewire\Component;
use LorneQuinn\HierarchicalScope\Services\ScopeContext;

class ScopeSwitcher extends Component
{
    public bool $open = false;
    public array $tree = [];
    public int|string|null $currentId = null;
    public string $currentName = '';

    public function mount(ScopeContext $scopeContext): void
    {
        $this->tree = $scopeContext->getTree();
        $this->updateCurrent($scopeContext);
    }

    public function switchTo(int|string $scopeId, ScopeContext $scopeContext): void
    {
        $scopeContext->setCurrentById($scopeId);
        $this->updateCurrent($scopeContext);
        $this->open = false;

        $this->dispatch('scope-changed', scopeId: $scopeId);
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    private function updateCurrent(ScopeContext $scopeContext): void
    {
        $current = $scopeContext->current();
        $this->currentId = $current->getKey();
        $this->currentName = $current->name;
    }

    public function render()
    {
        return view('hierarchical-scope::livewire.scope-switcher');
    }
}
