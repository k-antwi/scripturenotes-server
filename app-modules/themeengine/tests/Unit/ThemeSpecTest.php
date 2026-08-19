<?php

use Nucleus\Themeengine\Support\ThemeSpec;

/** Build an artifact array in the shape BrainService returns. */
function jsonArtifact(array $spec): array
{
    return [['type' => 'json', 'content' => json_encode($spec)]];
}

function validSpec(array $overrides = []): array
{
    return array_merge([
        'name'        => 'Midnight Harbour',
        'description' => 'A deep navy dashboard theme.',
        'mode'        => 'dark',
        'colors'      => [
            'primary'    => '#3B82F6',
            'background' => '#0F172A',
            'text'       => '#F8FAFC',
        ],
        'typography' => ['heading_font' => 'Inter', 'body_font' => 'Inter', 'base_size' => '16px', 'scale' => 1.25],
        'radius'     => ['sm' => '4px', 'md' => '8px'],
    ], $overrides);
}

describe('fromArtifacts()', function () {
    it('parses a valid spec out of a json artifact', function () {
        $spec = ThemeSpec::fromArtifacts(jsonArtifact(validSpec()));

        expect($spec['name'])->toBe('Midnight Harbour')
            ->and($spec['description'])->toBe('A deep navy dashboard theme.')
            ->and($spec['mode'])->toBe('dark')
            ->and($spec['colors'])->toBe([
                'primary'    => '#3b82f6',
                'background' => '#0f172a',
                'text'       => '#f8fafc',
            ]);
    });

    it('returns an empty array when there are no artifacts', function () {
        expect(ThemeSpec::fromArtifacts([]))->toBe([]);
    });

    it('ignores artifacts that are not json', function () {
        expect(ThemeSpec::fromArtifacts([
            ['type' => 'css', 'content' => ':root { --primary: #fff; }'],
        ]))->toBe([]);
    });

    it('skips unusable artifacts and uses the first parseable one', function () {
        $spec = ThemeSpec::fromArtifacts([
            ['type' => 'text', 'content' => 'not json at all'],
            ['type' => 'json', 'content' => json_encode(['colors' => ['primary' => 'not-a-colour']])],
            ['type' => 'json', 'content' => json_encode(validSpec())],
        ]);

        expect($spec['name'])->toBe('Midnight Harbour');
    });

    it('rejects a spec with no valid colours', function () {
        expect(ThemeSpec::fromArtifacts(jsonArtifact(validSpec([
            'colors' => ['primary' => '#RRGGBB', 'text' => 'navy'],
        ]))))->toBe([]);
    });
});

describe('normalise()', function () {
    it('orders known colour keys ahead of extra ones', function () {
        $spec = ThemeSpec::normalise(validSpec([
            'colors' => [
                'brand_extra' => '#111111',
                'text'        => '#222222',
                'primary'     => '#333333',
            ],
        ]));

        expect(array_keys($spec['colors']))->toBe(['primary', 'text', 'brand_extra']);
    });

    it('accepts 3, 6, and 8 digit hex values', function () {
        $spec = ThemeSpec::normalise(validSpec([
            'colors' => ['primary' => '#abc', 'text' => '#aabbcc', 'accent' => '#aabbccdd'],
        ]));

        expect($spec['colors'])->toHaveCount(3);
    });

    it('falls back to a placeholder name and light mode', function () {
        $spec = ThemeSpec::normalise(['colors' => ['primary' => '#123456']]);

        expect($spec['name'])->toBe('Untitled theme')
            ->and($spec['mode'])->toBe('light')
            ->and($spec['description'])->toBe('')
            ->and($spec['typography'])->toBe([])
            ->and($spec['radius'])->toBe([]);
    });

    it('keeps only recognised typography keys and casts the scale', function () {
        $spec = ThemeSpec::normalise(validSpec([
            'typography' => ['heading_font' => 'Lora', 'scale' => '1.5', 'letter_spacing' => '0.1em'],
        ]));

        expect($spec['typography'])->toBe(['heading_font' => 'Lora', 'scale' => 1.5]);
    });

    it('tolerates non-array typography and radius values', function () {
        $spec = ThemeSpec::normalise(validSpec(['typography' => 'sans-serif', 'radius' => 8]));

        expect($spec['typography'])->toBe([])
            ->and($spec['radius'])->toBe([]);
    });

    it('exposes the original spec as pretty-printed json', function () {
        $spec = ThemeSpec::normalise(validSpec());

        expect(json_decode($spec['json'], true)['name'])->toBe('Midnight Harbour')
            ->and($spec['json'])->toContain("\n");
    });
});

describe('stripCodeBlocks()', function () {
    it('removes fenced blocks and keeps the prose', function () {
        $content = "Here is your theme.\n\n```json\n{\"a\": 1}\n```";

        expect(ThemeSpec::stripCodeBlocks($content))->toBe('Here is your theme.');
    });

    it('leaves content without code blocks untouched', function () {
        expect(ThemeSpec::stripCodeBlocks('Just prose.'))->toBe('Just prose.');
    });
});

describe('isHexColor()', function () {
    it('accepts valid hex strings', function (string $value) {
        expect(ThemeSpec::isHexColor($value))->toBeTrue();
    })->with(['#fff', '#FFF', '#a1b2c3', '#A1B2C3FF']);

    it('rejects anything else', function (mixed $value) {
        expect(ThemeSpec::isHexColor($value))->toBeFalse();
    })->with(['fff', '#ffff', '#RRGGBB', 'rebeccapurple', '', null, 123]);

    // Kept out of the dataset above: Pest spreads a nested array into
    // separate arguments, so it would arrive here as the string '#fff'.
    it('rejects a non-scalar value', function () {
        expect(ThemeSpec::isHexColor(['#fff']))->toBeFalse();
    });
});
