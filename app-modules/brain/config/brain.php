<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Couchbase Conversation Storage
    |--------------------------------------------------------------------------
    |
    | Agent conversations are persisted to Couchbase when available. Each
    | conversation is a single document with an embedded messages array.
    |
    | If Couchbase is unreachable at runtime the store automatically falls
    | back to the relational database (agent_conversations table).
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Assistant Title
    |--------------------------------------------------------------------------
    |
    | Page title for the user-facing chat at /brain.
    |
    */

    'assistant_title' => 'Assistant',

    'couchbase' => [
        'connection_string' => env('COUCHBASE_CONNECTION_STRING', 'couchbase://localhost'),
        'bucket'            => env('COUCHBASE_BUCKET', 'brain_conversations'),
        'username'          => env('COUCHBASE_USERNAME', 'Administrator'),
        'password'          => env('COUCHBASE_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-to-Agent Mapping
    |--------------------------------------------------------------------------
    |
    | BrainService::promptAs($user, $role, ...) resolves the agent class for
    | a given role from this map. Each agent must implement
    | Laravel\Ai\Contracts\Agent. Constructors that accept a User receive the
    | acting user as their first argument.
    |
    | Example:
    |   'agents' => [
    |       'provider' => \App\Ai\Agents\ProviderAgent::class,
    |       'consumer' => \App\Ai\Agents\ConsumerAgent::class,
    |   ],
    |
    */

    'agents' => [
        // Register a role → agent class mapping here.
        // BrainService::promptAs($user, $role, $prompt) resolves from this map.
        // Remove or replace ExampleAgent with your own domain-specific agent.
        'consumer' => \Nucleus\Brain\Ai\Agents\ExampleAgent::class,

        // The 'theme' role is registered by the Themeengine module at boot
        // (see config/themeengine.php). Setting it explicitly here overrides
        // that default.
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Agent
    |--------------------------------------------------------------------------
    |
    | Used by BrainService::promptAsUser() when the user holds none of the
    | roles mapped above.
    |
    */

    'default_agent' => \Nucleus\Brain\Ai\Agents\ExampleAgent::class,

];
