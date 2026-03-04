<?php

namespace LorneQuinn\HierarchicalScope;

use Illuminate\Support\ServiceProvider;
use LorneQuinn\HierarchicalScope\Services\ScopeContext;

class HierarchicalScopeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/hierarchical-scope.php', 'hierarchical-scope');

        $this->app->singleton(ScopeContext::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/hierarchical-scope.php' => config_path('hierarchical-scope.php'),
        ], 'hierarchical-scope-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_scopes_table.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_create_scopes_table.php'),
        ], 'hierarchical-scope-migrations');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hierarchical-scope');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/hierarchical-scope'),
        ], 'hierarchical-scope-views');

        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('scope-switcher', Livewire\ScopeSwitcher::class);
            \Livewire\Livewire::component('scope-breadcrumb', Livewire\ScopeBreadcrumb::class);
        }
    }
}
