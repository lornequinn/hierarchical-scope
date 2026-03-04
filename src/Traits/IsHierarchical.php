<?php

namespace LorneQuinn\HierarchicalScope\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Makes a model act as a hierarchical scope tree.
 *
 * Expects a self-referencing parent column (default: parent_id).
 * Apply this to whatever model represents your scope hierarchy.
 */
trait IsHierarchical
{
    public function getParentColumnName(): string
    {
        return $this->parentColumn ?? config('hierarchical-scope.parent_column', 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->getParentColumnName());
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->getParentColumnName());
    }

    /**
     * Get all descendant IDs (inclusive of self) for filtering.
     */
    public function getDescendantIds(): Collection
    {
        $ids = collect([$this->getKey()]);

        foreach ($this->children as $child) {
            $ids = $ids->merge($child->getDescendantIds());
        }

        return $ids;
    }

    /**
     * Get ancestor chain (for breadcrumbs). Does not include self.
     */
    public function getAncestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Full path as breadcrumb-style string.
     */
    public function getPathAttribute(): string
    {
        return $this->getAncestors()
            ->push($this)
            ->pluck('name')
            ->join(' / ');
    }

    /**
     * Check if this is a root node (no parent).
     */
    public function isRoot(): bool
    {
        return $this->{$this->getParentColumnName()} === null;
    }

    /**
     * Get the root node.
     */
    public static function root(): ?static
    {
        $instance = new static;

        return static::whereNull($instance->getParentColumnName())->first();
    }

    /**
     * Query scope to filter by a "type" column (if your hierarchy uses types).
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
