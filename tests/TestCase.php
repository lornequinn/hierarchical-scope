<?php

namespace LorneQuinn\HierarchicalScope\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use LorneQuinn\HierarchicalScope\HierarchicalScopeServiceProvider;
use LorneQuinn\HierarchicalScope\Tests\Fixtures\Scope;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            HierarchicalScopeServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('hierarchical-scope.model', Scope::class);
    }

    protected function setUpDatabase(): void
    {
        Schema::create('scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('scopes')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scope_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }
}
