<?php

namespace Nucleus\Themeengine\Support;

/**
 * Parses the theme specification out of a Brain response.
 *
 * BrainService already extracts fenced code blocks into `artifacts`; this
 * class picks the one that looks like a theme spec, validates it, and
 * normalises it into a predictable shape the chat view can render.
 */
class ThemeSpec
{
    /** Keys the view knows how to render, in display order. */
    public const COLOR_KEYS = [
        'primary',
        'secondary',
        'accent',
        'background',
        'surface',
        'text',
        'muted',
        'border',
        'success',
        'warning',
        'danger',
    ];

    /**
     * Build a normalised spec from a set of BrainService artifacts.
     *
     * Returns an empty array when no artifact contains a usable spec, which
     * is the signal to the view that there is nothing to preview.
     *
     * @param  array<int, array{type: string, content: string}>  $artifacts
     */
    public static function fromArtifacts(array $artifacts): array
    {
        foreach ($artifacts as $artifact) {
            $decoded = json_decode($artifact['content'] ?? '', true);

            if (! is_array($decoded)) {
                continue;
            }

            $spec = static::normalise($decoded);

            if ($spec !== []) {
                return $spec;
            }
        }

        return [];
    }

    /**
     * Normalise a decoded spec, dropping anything that isn't usable.
     *
     * A spec with no valid colours is not a theme, so it is rejected outright
     * rather than surfaced as an empty swatch grid.
     */
    public static function normalise(array $decoded): array
    {
        $colors = static::colors($decoded['colors'] ?? []);

        if ($colors === []) {
            return [];
        }

        return [
            'name'        => static::text($decoded['name'] ?? null) ?: 'Untitled theme',
            'description' => static::text($decoded['description'] ?? null),
            'mode'        => strtolower(static::text($decoded['mode'] ?? null)) === 'dark' ? 'dark' : 'light',
            'colors'      => $colors,
            'typography'  => static::typography($decoded['typography'] ?? []),
            'radius'      => static::stringMap($decoded['radius'] ?? []),
            'json'        => json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '',
        ];
    }

    /**
     * Remove the fenced spec blocks from the prose so the chat bubble shows
     * the explanation and the swatch preview carries the values.
     */
    public static function stripCodeBlocks(string $content): string
    {
        return trim((string) preg_replace('/```\w*\n.*?```/s', '', $content));
    }

    public static function isHexColor(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value) === 1;
    }

    /**
     * Keep the known colour keys in display order, then any extra colours the
     * agent supplied, so a fork that extends the palette still previews.
     */
    private static function colors(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $colors = [];

        foreach (static::COLOR_KEYS as $key) {
            if (static::isHexColor($raw[$key] ?? null)) {
                $colors[$key] = strtolower($raw[$key]);
            }
        }

        foreach ($raw as $key => $value) {
            if (! is_string($key) || isset($colors[$key]) || ! static::isHexColor($value)) {
                continue;
            }

            $colors[$key] = strtolower($value);
        }

        return $colors;
    }

    private static function typography(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $typography = [];

        foreach (['heading_font', 'body_font', 'base_size'] as $key) {
            if ($text = static::text($raw[$key] ?? null)) {
                $typography[$key] = $text;
            }
        }

        if (is_numeric($raw['scale'] ?? null)) {
            $typography['scale'] = (float) $raw['scale'];
        }

        return $typography;
    }

    private static function stringMap(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $map = [];

        foreach ($raw as $key => $value) {
            if (is_string($key) && ($text = static::text($value))) {
                $map[$key] = $text;
            }
        }

        return $map;
    }

    private static function text(mixed $value): string
    {
        return is_string($value) || is_numeric($value) ? trim((string) $value) : '';
    }
}
