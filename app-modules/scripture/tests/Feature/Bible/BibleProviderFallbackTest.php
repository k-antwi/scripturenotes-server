<?php

use Nucleus\Scripture\Bible\BibleProviderRouter;
use Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException;
use Nucleus\Scripture\Bible\Exceptions\QuotaExceededException;
use Nucleus\Scripture\Bible\Providers\ApiBibleProvider;
use Nucleus\Scripture\Bible\Providers\BibleBrainProvider;
use Nucleus\Scripture\Bible\Providers\BollsProvider;
use Nucleus\Scripture\Bible\Providers\FreeUseBibleProvider;
use Nucleus\Scripture\Bible\Providers\YouVersionProvider;

/**
 * Tests that the fallback chain for licensed text degrades gracefully:
 *
 *   ApiBibleProvider (quota exceeded)
 *     → YouVersionProvider (unavailable)
 *       → FreeUseBibleProvider (succeeds)
 */
it('falls back from ApiBible to YouVersion when quota exceeded', function () {
    $apiBible   = Mockery::mock(ApiBibleProvider::class);
    $youVersion = Mockery::mock(YouVersionProvider::class);
    $freeUse    = Mockery::mock(FreeUseBibleProvider::class);
    $bibleBrain = Mockery::mock(BibleBrainProvider::class);
    $bolls      = Mockery::mock(BollsProvider::class);

    $apiBible->shouldReceive('getChapter')
        ->once()
        ->andThrow(new QuotaExceededException('api_bible'));

    $youVersion->shouldReceive('getChapter')
        ->once()
        ->andReturn(['data' => ['reference' => 'JHN 3', 'verses' => []]]);

    $router = new BibleProviderRouter($apiBible, $freeUse, $bibleBrain, $bolls, $youVersion);
    $result = $router->withFallback('text', fn ($p) => $p->getChapter('JHN', 3, 'NIV'));

    expect($result)->toHaveKey('data');
    expect($result['data']['reference'])->toBe('JHN 3');
});

it('falls back all the way to FreeUse when both primary and YouVersion fail', function () {
    $apiBible   = Mockery::mock(ApiBibleProvider::class);
    $youVersion = Mockery::mock(YouVersionProvider::class);
    $freeUse    = Mockery::mock(FreeUseBibleProvider::class);
    $bibleBrain = Mockery::mock(BibleBrainProvider::class);
    $bolls      = Mockery::mock(BollsProvider::class);

    $apiBible->shouldReceive('getChapter')
        ->once()
        ->andThrow(new QuotaExceededException('api_bible'));

    $youVersion->shouldReceive('getChapter')
        ->once()
        ->andThrow(new ProviderUnavailableException('youversion'));

    $freeUse->shouldReceive('getChapter')
        ->once()
        ->andReturn(['reference' => 'John 3', 'verses' => [['verse' => 16, 'text' => 'For God so loved...']]], );

    $router = new BibleProviderRouter($apiBible, $freeUse, $bibleBrain, $bolls, $youVersion);
    $result = $router->withFallback('text', fn ($p) => $p->getChapter('JHN', 3, 'NIV'));

    expect($result)->toHaveKey('verses');
});

it('throws ProviderUnavailableException when entire chain is exhausted', function () {
    $apiBible   = Mockery::mock(ApiBibleProvider::class);
    $youVersion = Mockery::mock(YouVersionProvider::class);
    $freeUse    = Mockery::mock(FreeUseBibleProvider::class);
    $bibleBrain = Mockery::mock(BibleBrainProvider::class);
    $bolls      = Mockery::mock(BollsProvider::class);

    $apiBible->shouldReceive('getChapter')->andThrow(new QuotaExceededException('api_bible'));
    $youVersion->shouldReceive('getChapter')->andThrow(new ProviderUnavailableException('youversion'));
    $freeUse->shouldReceive('getChapter')->andThrow(new ProviderUnavailableException('free_use'));

    $router = new BibleProviderRouter($apiBible, $freeUse, $bibleBrain, $bolls, $youVersion);

    expect(fn () => $router->withFallback('text', fn ($p) => $p->getChapter('JHN', 3, 'NIV')))
        ->toThrow(ProviderUnavailableException::class);
});

it('does not call further providers when primary succeeds', function () {
    $apiBible   = Mockery::mock(ApiBibleProvider::class);
    $youVersion = Mockery::mock(YouVersionProvider::class);
    $freeUse    = Mockery::mock(FreeUseBibleProvider::class);
    $bibleBrain = Mockery::mock(BibleBrainProvider::class);
    $bolls      = Mockery::mock(BollsProvider::class);

    $apiBible->shouldReceive('getChapter')
        ->once()
        ->andReturn(['data' => ['verses' => []]]);

    $youVersion->shouldNotReceive('getChapter');
    $freeUse->shouldNotReceive('getChapter');

    $router = new BibleProviderRouter($apiBible, $freeUse, $bibleBrain, $bolls, $youVersion);
    $router->withFallback('text', fn ($p) => $p->getChapter('JHN', 3, 'NIV'));
});

it('verse-of-day fallback chain tries YouVersion first then FreeUse', function () {
    $apiBible   = Mockery::mock(ApiBibleProvider::class);
    $youVersion = Mockery::mock(YouVersionProvider::class);
    $freeUse    = Mockery::mock(FreeUseBibleProvider::class);
    $bibleBrain = Mockery::mock(BibleBrainProvider::class);
    $bolls      = Mockery::mock(BollsProvider::class);

    $youVersion->shouldReceive('getVerseOfDay')
        ->once()
        ->andThrow(new ProviderUnavailableException('youversion'));

    $freeUse->shouldReceive('getPassage')
        ->once()
        ->andReturn(['reference' => 'John 3:16', 'verses' => []]);

    $router = new BibleProviderRouter($apiBible, $freeUse, $bibleBrain, $bolls, $youVersion);

    $result = $router->withFallback('verse_of_day', function ($provider) use ($youVersion, $freeUse) {
        if ($provider instanceof YouVersionProvider) {
            return $provider->getVerseOfDay();
        }

        return $provider->getPassage('John 3:16', 'KJV');
    });

    expect($result)->toHaveKey('verses');
});
