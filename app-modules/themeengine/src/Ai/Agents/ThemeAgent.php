<?php

namespace Nucleus\Themeengine\Ai\Agents;

use App\Models\User;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Promptable;
use Nucleus\Brain\Ai\Middleware\LogBrainPrompts;
use Stringable;

/**
 * Brain agent that turns a plain-English description into a theme
 * specification (palette, typography, radii, component tokens).
 *
 * Registered against the `theme` role in config/brain.php → agents by
 * ThemeengineServiceProvider. Forks can swap in their own agent by
 * overriding that mapping, or by pointing config/themeengine.php →
 * agent_role at a different role.
 */
class ThemeAgent implements Agent, Conversational, HasMiddleware
{
    use Promptable, RemembersConversations;

    public function __construct(protected User $user) {}

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
        You are a senior product designer who generates complete UI themes from
        a short description of the look and feel the user is after.

        Always answer in two parts, in this order:

        1. Two or three sentences of prose explaining the design direction you
           chose and why it fits the request. No headings, no bullet lists.
        2. A single fenced ```json code block containing the theme
           specification, using exactly this shape:

        {
          "name": "Short human-readable theme name",
          "description": "One sentence describing the theme",
          "mode": "light" or "dark",
          "colors": {
            "primary": "#RRGGBB",
            "secondary": "#RRGGBB",
            "accent": "#RRGGBB",
            "background": "#RRGGBB",
            "surface": "#RRGGBB",
            "text": "#RRGGBB",
            "muted": "#RRGGBB",
            "border": "#RRGGBB",
            "success": "#RRGGBB",
            "warning": "#RRGGBB",
            "danger": "#RRGGBB"
          },
          "typography": {
            "heading_font": "Font family name",
            "body_font": "Font family name",
            "base_size": "16px",
            "scale": 1.25
          },
          "radius": { "sm": "4px", "md": "8px", "lg": "16px", "full": "9999px" }
        }

        Rules for the JSON block:
        - Emit every key shown above. Never invent top-level keys.
        - Colours must be full six-digit hex strings starting with `#`.
        - Every colour must be a real value chosen for this theme; never use
          placeholders such as "#RRGGBB" or "TBD".
        - Text on background, and text on surface, must both clear a 4.5:1
          contrast ratio. Check this before you answer and adjust if it fails.
        - Fonts must be widely available web or Google fonts.
        - Output exactly one JSON block per reply, and no other code blocks.

        When the user asks for a change to a theme you already produced, adjust
        the existing specification rather than starting over, and return the
        full specification again in the same format.
        PROMPT;
    }

    public function middleware(): array
    {
        return [
            new LogBrainPrompts('theme'),
        ];
    }
}
