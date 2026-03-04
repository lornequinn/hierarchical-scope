<?php

namespace LorneQuinn\HierarchicalScope\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class ScopeContext
{
    /**
     * Get the current scope (defaults to root).
     */
    public function current(): Model
    {
        $scopeId = Session::get($this->sessionKey());

        if ($scopeId) {
            $scope = $this->modelClass()::find($scopeId);
            if ($scope) {
                return $scope;
            }
        }

        return $this->root();
    }

    /**
     * Set the current scope.
     */
    public function setCurrent(Model $scope): void
    {
        Session::put($this->sessionKey(), $scope->getKey());
    }

    /**
     * Set current scope by ID.
     */
    public function setCurrentById(int|string $id): void
    {
        $scope = $this->modelClass()::findOrFail($id);
        $this->setCurrent($scope);
    }

    /**
     * Get the root scope.
     */
    public function root(): Model
    {
        return $this->modelClass()::root()
            ?? throw new \RuntimeException('Root scope not found. Ensure your scope table is seeded.');
    }

    /**
     * Check if currently at root scope.
     */
    public function isAtRoot(): bool
    {
        return $this->current()->isRoot();
    }

    /**
     * Get scope IDs for filtering (current + all descendants).
     */
    public function getFilterIds(): array
    {
        return $this->current()->getDescendantIds()->toArray();
    }

    /**
     * Get the scope tree for UI switchers.
     */
    public function getTree(): array
    {
        return $this->buildTree($this->modelClass()::root());
    }

    private function buildTree(?Model $scope): array
    {
        if (! $scope) {
            return [];
        }

        $node = [
            'id' => $scope->getKey(),
            'name' => $scope->name,
            'children' => [],
        ];

        // Include slug and type if they exist on the model
        if (isset($scope->slug)) {
            $node['slug'] = $scope->slug;
        }
        if (isset($scope->type)) {
            $node['type'] = $scope->type;
        }

        foreach ($scope->children()->orderBy('name')->get() as $child) {
            $node['children'][] = $this->buildTree($child);
        }

        return $node;
    }

    private function modelClass(): string
    {
        return config('hierarchical-scope.model');
    }

    private function sessionKey(): string
    {
        return config('hierarchical-scope.session_key', 'current_scope_id');
    }
}
