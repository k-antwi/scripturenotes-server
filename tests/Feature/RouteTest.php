<?php

// tests/Feature/RouteResponseTest.php

use function Pest\Laravel\get;

it('responds with 200 for all routes', function (string $route) {
    $response = get($route);
    $response->assertStatus(200);
})->with('routes');

test('responds with 200 for all auth routes', function ($url) {
    // We need a verified admin user to access /admin routes and /dashboard
    $user = \App\Models\User::where('email', 'admin@admin.com')->first() ?? \App\Models\User::factory()->create(['role_id' => 1]);
    $user->email_verified_at = now();
    $user->save();

    $this->actingAs($user);

    $response = $this->get($url);

    if ($response->status() !== 200) {
        dump("URL: $url, Status: " . $response->status() . ", Redirect: " . $response->headers->get('Location'));
    }

    $response->assertStatus(200);
})->with('authroutes');
