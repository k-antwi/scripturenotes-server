<?php

it('Home returns a successful response', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

it('Home hero has I\'m a Financial Advisor button linking to register with fa=1 param', function () {
    $heroPath = resource_path('themes/anchor/components/marketing/sections/hero.blade.php');
    $contents = file_get_contents($heroPath);
    expect($contents)->toContain('fa=1');
    expect($contents)->toContain("I'm a Financial Advisor");
    // Verify it links to the registration page (route('register') call)
    expect($contents)->toContain("route('register')");
});
