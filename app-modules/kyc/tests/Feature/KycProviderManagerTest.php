<?php

use Nucleus\Kyc\Contracts\KycProviderInterface;
use Nucleus\Kyc\Services\KycProviderManager;
use Nucleus\Kyc\Services\Providers\JumioKycProvider;
use Nucleus\Kyc\Services\Providers\ManualKycProvider;
use Nucleus\Kyc\Services\Providers\OnfidoKycProvider;
use Nucleus\Kyc\Services\Providers\StripeKycProvider;
use Nucleus\Kyc\Services\Providers\VeriffKycProvider;

beforeEach(function () {
    $this->manager = new KycProviderManager();
});

it('resolves the manual provider by default', function () {
    config(['kyc.default_provider' => 'manual']);
    expect($this->manager->provider())->toBeInstanceOf(ManualKycProvider::class);
});

it('resolves stripe provider by name', function () {
    expect($this->manager->make('stripe'))->toBeInstanceOf(StripeKycProvider::class);
});

it('resolves onfido provider by name', function () {
    expect($this->manager->make('onfido'))->toBeInstanceOf(OnfidoKycProvider::class);
});

it('resolves jumio provider by name', function () {
    expect($this->manager->make('jumio'))->toBeInstanceOf(JumioKycProvider::class);
});

it('resolves veriff provider by name', function () {
    expect($this->manager->make('veriff'))->toBeInstanceOf(VeriffKycProvider::class);
});

it('caches resolved provider instances', function () {
    $first  = $this->manager->make('manual');
    $second = $this->manager->make('manual');
    expect($first)->toBe($second);
});

it('throws on unknown provider name', function () {
    $this->manager->make('unknown_provider');
})->throws(InvalidArgumentException::class);

it('returns all built-in provider names', function () {
    $names = $this->manager->providerNames();
    expect($names)->toContain('manual', 'stripe', 'onfido', 'jumio', 'veriff');
});

it('returns a provider list with names and labels', function () {
    $list = $this->manager->providerList();
    expect($list)->toBeArray();

    $names = array_column($list, 'name');
    expect($names)->toContain('manual', 'stripe', 'onfido', 'jumio', 'veriff');

    foreach ($list as $item) {
        expect($item)->toHaveKeys(['name', 'label']);
        expect($item['label'])->not->toBeEmpty();
    }
});

it('allows extending with a custom provider', function () {
    $custom = new class extends \Nucleus\Kyc\Services\Providers\BaseKycProvider {
        public function name(): string { return 'custom'; }
        public function label(): string { return 'Custom Provider'; }
        public function initiateVerification(\Nucleus\Kyc\Models\KycVerification $v): array { return []; }
        public function handleWebhook(array $p, \Nucleus\Kyc\Models\KycVerification $v): void {}
        public function checkStatus(string $ref): array { return ['status' => 'pending', 'raw' => []]; }
        public function supportsLiveness(): bool { return false; }
        public function supportsDocumentCollection(): bool { return false; }
        protected function mapStatus(string $s): string { return 'pending'; }
    };

    $this->manager->extend('custom', $custom);

    expect($this->manager->make('custom'))->toBe($custom);
    expect($this->manager->providerNames())->toContain('custom');
});

it('resolves the configured default provider', function () {
    config(['kyc.default_provider' => 'stripe']);
    expect($this->manager->provider())->toBeInstanceOf(StripeKycProvider::class);
});
