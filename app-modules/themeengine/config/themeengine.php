<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brain Agent Role
    |--------------------------------------------------------------------------
    |
    | The theme chat resolves its agent through the Brain module, using
    | BrainService::promptAs($user, $role, ...). This is the role key it looks
    | up in config('brain.agents'). Point it at a different role to swap in
    | your own agent without touching the Livewire component.
    |
    */

    'agent_role' => env('THEMEENGINE_AGENT_ROLE', 'theme'),

    /*
    |--------------------------------------------------------------------------
    | Default Agent
    |--------------------------------------------------------------------------
    |
    | Registered into config('brain.agents.{agent_role}') at boot, but only if
    | that role has not already been mapped. Setting `brain.agents.theme` in
    | config/brain.php always wins.
    |
    */

    'agent' => \Nucleus\Themeengine\Ai\Agents\ThemeAgent::class,

    /*
    |--------------------------------------------------------------------------
    | Prompt Length
    |--------------------------------------------------------------------------
    |
    | Maximum number of characters accepted in the theme description box.
    |
    */

    'max_prompt_length' => 2000,

    /*
    |--------------------------------------------------------------------------
    | Website Designer Agent Role
    |--------------------------------------------------------------------------
    |
    | The website chat resolves its agent through the Brain module, using
    | BrainService::promptAs($user, $role, ...). Point it at a different role
    | to swap in your own agent without touching the Livewire component.
    |
    */

    'website_agent_role' => env('WEBSITE_AGENT_ROLE', 'website'),

    'website_agent' => \Nucleus\Themeengine\Ai\Agents\WebsiteDesignerAgent::class,

];
