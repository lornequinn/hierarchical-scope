<?php

use Illuminate\Support\ServiceProvider;
use LorneQuinn\HierarchicalScope\HierarchicalScopeServiceProvider;
use LorneQuinn\HierarchicalScope\Services\ScopeContext;

describe('config', function () {
    it('merges the package config under the hierarchical-scope key', function () {
        expect(config('hierarchical-scope.parent_column'))->toBe('parent_id');
        expect(config('hierarchical-scope.scope_column'))->toBe('scope_id');
        expect(config('hierarchical-scope.session_key'))->toBe('current_scope_id');
    });

    it('exposes the configured scope model class', function () {
        expect(config('hierarchical-scope.model'))
            ->toBe(\LorneQuinn\HierarchicalScope\Tests\Fixtures\Scope::class);
    });
});

describe('container bindings', function () {
    it('binds ScopeContext as a singleton', function () {
        $a = app(ScopeContext::class);
        $b = app(ScopeContext::class);

        expect($a)->toBe($b);
    });
});

describe('publish groups', function () {
    it('registers the config publish group', function () {
        $paths = ServiceProvider::pathsToPublish(HierarchicalScopeServiceProvider::class, 'hierarchical-scope-config');
        expect($paths)->not->toBeEmpty();

        $source = array_key_first($paths);
        expect($source)->toEndWith('config/hierarchical-scope.php');
        expect($paths[$source])->toEndWith('hierarchical-scope.php');
    });

    it('registers the migration publish group', function () {
        $paths = ServiceProvider::pathsToPublish(HierarchicalScopeServiceProvider::class, 'hierarchical-scope-migrations');
        expect($paths)->not->toBeEmpty();

        $source = array_key_first($paths);
        expect($source)->toEndWith('create_scopes_table.php.stub');
        expect($paths[$source])->toContain('migrations/');
        expect($paths[$source])->toEndWith('_create_scopes_table.php');
    });

    it('registers the views publish group', function () {
        $paths = ServiceProvider::pathsToPublish(HierarchicalScopeServiceProvider::class, 'hierarchical-scope-views');
        expect($paths)->not->toBeEmpty();

        $target = $paths[array_key_first($paths)];
        expect($target)->toContain('views/vendor/hierarchical-scope');
    });
});

describe('views', function () {
    it('loads the hierarchical-scope view namespace', function () {
        $finder = view()->getFinder();
        $hints = $finder->getHints();

        expect($hints)->toHaveKey('hierarchical-scope');
        expect($hints['hierarchical-scope'][0])->toEndWith('resources/views');
    });

    it('can resolve the scope-switcher view', function () {
        expect(view()->exists('hierarchical-scope::livewire.scope-switcher'))->toBeTrue();
    });

    it('can resolve the scope-breadcrumb view', function () {
        expect(view()->exists('hierarchical-scope::livewire.scope-breadcrumb'))->toBeTrue();
    });
});

describe('Livewire registration', function () {
    it('registers the scope-switcher component when Livewire is present', function () {
        $registered = app(\Livewire\Mechanisms\ComponentRegistry::class)->getClass('scope-switcher');
        expect($registered)->toBe(\LorneQuinn\HierarchicalScope\Livewire\ScopeSwitcher::class);
    });

    it('registers the scope-breadcrumb component when Livewire is present', function () {
        $registered = app(\Livewire\Mechanisms\ComponentRegistry::class)->getClass('scope-breadcrumb');
        expect($registered)->toBe(\LorneQuinn\HierarchicalScope\Livewire\ScopeBreadcrumb::class);
    });
});

describe('migration stub', function () {
    it('creates the scopes table with the documented columns', function () {
        Schema::dropIfExists('scopes_published');

        $stub = file_get_contents(__DIR__ . '/../database/migrations/create_scopes_table.php.stub');
        $stub = str_replace("Schema::create('scopes'", "Schema::create('scopes_published'", $stub);
        $stub = str_replace("'scopes'", "'scopes_published'", $stub);
        $stub = str_replace("Schema::dropIfExists('scopes_published')", "Schema::dropIfExists('scopes_published')", $stub);

        $migration = eval('?>' . $stub);
        $migration->up();

        expect(Schema::hasTable('scopes_published'))->toBeTrue();
        expect(Schema::hasColumns('scopes_published', [
            'id', 'parent_id', 'name', 'slug', 'type', 'description', 'created_at', 'updated_at',
        ]))->toBeTrue();

        $migration->down();
        expect(Schema::hasTable('scopes_published'))->toBeFalse();
    });
});
