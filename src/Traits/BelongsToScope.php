<?php

namespace LorneQuinn\HierarchicalScope\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Makes a model filterable by a hierarchical scope.
 *
 * Expects a foreign key column pointing to the scope model (default: scope_id).
 * The scope model should use the IsHierarchical trait.
 */
trait BelongsToScope
{
    public function getScopeColumnName(): string
    {
        return $this->scopeColumn ?? config('hierarchical-scope.scope_column', 'scope_id');
    }

    public function getScopeModelClass(): string
    {
        return $this->scopeModel ?? config('hierarchical-scope.model');
    }

    /**
     * Relationship to the hierarchical scope.
     */
    public function hierarchicalScope(): BelongsTo
    {
        return $this->belongsTo($this->getScopeModelClass(), $this->getScopeColumnName());
    }

    /**
     * Filter by a scope and all its descendants.
     */
    public function scopeInScope(Builder $query, Model $scope): Builder
    {
        return $query->whereIn($this->getScopeColumnName(), $scope->getDescendantIds());
    }

    /**
     * Filter by pre-computed scope IDs (avoids re-calculating descendants).
     */
    public function scopeInScopeIds(Builder $query, array $ids): Builder
    {
        return $query->whereIn($this->getScopeColumnName(), $ids);
    }

    /**
     * Include rows where scope is null (broadcasts / unscoped items).
     */
    public function scopeWithNullScope(Builder $query): Builder
    {
        return $query->orWhereNull($this->getScopeColumnName());
    }

    /**
     * Filter by scope IDs OR null scope (common pattern for inclusive filtering).
     */
    public function scopeInScopeOrNull(Builder $query, array $ids): Builder
    {
        return $query->where(function ($q) use ($ids) {
            $q->whereIn($this->getScopeColumnName(), $ids)
              ->orWhereNull($this->getScopeColumnName());
        });
    }
}
