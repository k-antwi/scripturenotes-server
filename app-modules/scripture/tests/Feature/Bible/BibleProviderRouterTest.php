<?php

use Nucleus\Scripture\Bible\BibleProviderRouter;
use Nucleus\Scripture\Bible\Providers\ApiBibleProvider;
use Nucleus\Scripture\Bible\Providers\BibleBrainProvider;
use Nucleus\Scripture\Bible\Providers\BollsProvider;
use Nucleus\Scripture\Bible\Providers\FreeUseBibleProvider;
use Nucleus\Scripture\Bible\Providers\YouVersionProvider;

beforeEach(function () {
    $this->router = app(BibleProviderRouter::class);
});

// ─── Public-domain routing ────────────────────────────────────────────────────

it('routes KJV to FreeUseBibleProvider', function () {
    $provider = $this->router->forPassage('KJV');
    expect($provider)->toBeInstanceOf(FreeUseBibleProvider::class);
});

it('routes ASV to FreeUseBibleProvider', function () {
    $provider = $this->router->forPassage('ASV');
    expect($provider)->toBeInstanceOf(FreeUseBibleProvider::class);
});

it('routes WEB to FreeUseBibleProvider', function () {
    $provider = $this->router->forPassage('WEB');
    expect($provider)->toBeInstanceOf(FreeUseBibleProvider::class);
});

it('routes YLT to FreeUseBibleProvider', function () {
    $provider = $this->router->forPassage('YLT');
    expect($provider)->toBeInstanceOf(FreeUseBibleProvider::class);
});

it('routes DARBY to FreeUseBibleProvider', function () {
    $provider = $this->router->forPassage('DARBY');
    expect($provider)->toBeInstanceOf(FreeUseBibleProvider::class);
});

it('never routes public-domain versions to ApiBibleProvider', function (string $version) {
    $provider = $this->router->forPassage($version);
    expect($provider)->not->toBeInstanceOf(ApiBibleProvider::class);
})->with(['KJV', 'ASV', 'WEB', 'YLT', 'DARBY', 'BBE', 'WNT']);

// ─── Licensed-text routing ────────────────────────────────────────────────────

it('routes NIV to ApiBibleProvider', function () {
    $provider = $this->router->forPassage('NIV');
    expect($provider)->toBeInstanceOf(ApiBibleProvider::class);
});

it('routes ESV to ApiBibleProvider', function () {
    $provider = $this->router->forPassage('ESV');
    expect($provider)->toBeInstanceOf(ApiBibleProvider::class);
});

it('routes NASB to ApiBibleProvider', function () {
    $provider = $this->router->forPassage('NASB');
    expect($provider)->toBeInstanceOf(ApiBibleProvider::class);
});

// ─── Capability routing ───────────────────────────────────────────────────────

it('routes audio capability to BibleBrainProvider', function () {
    $provider = $this->router->forCapability('audio');
    expect($provider)->toBeInstanceOf(BibleBrainProvider::class);
});

it('routes video capability to BibleBrainProvider', function () {
    $provider = $this->router->forCapability('video');
    expect($provider)->toBeInstanceOf(BibleBrainProvider::class);
});

it('routes semantic_search to BollsProvider', function () {
    $provider = $this->router->forCapability('semantic_search');
    expect($provider)->toBeInstanceOf(BollsProvider::class);
});

it('routes dictionary to BollsProvider', function () {
    $provider = $this->router->forCapability('dictionary');
    expect($provider)->toBeInstanceOf(BollsProvider::class);
});

it('throws InvalidArgumentException for unknown capability', function () {
    $this->router->forCapability('telepathy');
})->throws(\InvalidArgumentException::class);

// ─── Fallback chain ───────────────────────────────────────────────────────────

it('falls back to next provider on QuotaExceededException', function () {
    $called = [];

    $apiBible  = Mockery::mock(ApiBibleProvider::class);
    $freeUse   = Mockery::mock(FreeUseBibleProvider::class);
    $bibleBrain = Mockery::mock(BibleBrainProvider::class);
    $bolls     = Mockery::mock(BollsProvider::class);
    $youVersion = Mockery::mock(YouVersionProvider::class);

    $apiBible->shouldReceive('getChapter')
        ->once()
        ->andThrow(new \Nucleus\Scripture\Bible\Exceptions\QuotaExceededException('api_bible'));

    $youVersion->shouldReceive('getChapter')
        ->once()
        ->andReturn(['data' => ['verses' => []]]);

    $router = new BibleProviderRouter($apiBible, $freeUse, $bibleBrain, $bolls, $youVersion);

    $result = $router->withFallback('text', fn ($p) => $p->getChapter('JHN', 3, 'NIV'));

    expect($result)->toHaveKey('data');
});

it('re-throws ProviderUnavailableException when entire chain fails', function () {
    $apiBible   = Mockery::mock(ApiBibleProvider::class);
    $freeUse    = Mockery::mock(FreeUseBibleProvider::class);
    $bibleBrain = Mockery::mock(BibleBrainProvider::class);
    $bolls      = Mockery::mock(BollsProvider::class);
    $youVersion = Mockery::mock(YouVersionProvider::class);

    $apiBible->shouldReceive('getChapter')->andThrow(new \Nucleus\Scripture\Bible\Exceptions\QuotaExceededException('api_bible'));
    $youVersion->shouldReceive('getChapter')->andThrow(new \Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException('youversion'));
    $freeUse->shouldReceive('getChapter')->andThrow(new \Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException('free_use'));

    $router = new BibleProviderRouter($apiBible, $freeUse, $bibleBrain, $bolls, $youVersion);

    $router->withFallback('text', fn ($p) => $p->getChapter('JHN', 3, 'NIV'));
})->throws(\Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException::class);
