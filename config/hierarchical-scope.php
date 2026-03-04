<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scope Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model that represents your hierarchical scope tree.
    | This model should use the IsHierarchical trait.
    |
    */
    'model' => \App\Models\Scope::class,

    /*
    |--------------------------------------------------------------------------
    | Parent Column
    |--------------------------------------------------------------------------
    |
    | The column name used for the parent-child relationship on the
    | hierarchical model. Defaults to 'parent_id'.
    |
    */
    'parent_column' => 'parent_id',

    /*
    |--------------------------------------------------------------------------
    | Scope Column
    |--------------------------------------------------------------------------
    |
    | The foreign key column name on scoped models that references
    | the hierarchical scope. Defaults to 'scope_id'.
    |
    */
    'scope_column' => 'scope_id',

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    |
    | The session key used to persist the current scope selection.
    |
    */
    'session_key' => 'current_scope_id',

];
