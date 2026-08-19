# Starterkit Brand Guide Skill

## Trigger Conditions
Use this skill whenever:
- Styling a new UI component, page, or layout
- Choosing colors, typography, or spacing
- Creating buttons, cards, forms, or navigation elements
- Adding dark mode support to any component
- Building new authenticated-area screens

---

## Brand Identity

**Product**: PensionTrack
**Tagline**: Find and manage your UK pensions
**Aesthetic**: Professional, trustworthy, modern fintech — clean and calm with confident orange accents

### Logo
- Icon: Orange square (`bg-orange-500 w-8 h-8 rounded-lg`) containing a Phosphor piggy-bank icon in white
- Wordmark: "PensionTrack" — `font-bold text-sm text-white` (on dark sidebar), `text-gray-900 dark:text-white` elsewhere
- Always pair the icon with the wordmark; never use alone in the app header

---

## Color Palette

### Brand Orange (Primary Action Color)
| Token | Value | Usage |
|---|---|---|
| `orange-500` | `#FF8C00` | CTA buttons, active states, progress bars, brand accent |
| `orange-600` | `#FF7518` | Hover state for primary buttons/links |
| `orange-50` | very light orange | Icon background tints, card highlights |
| `orange-100` | light orange | Hover backgrounds on icon containers |
| `orange-300` | mid orange | Secondary progress bar fills |

**Rule**: Orange is used exclusively for primary interactive elements and brand moments — never for text beyond links.

### Sidebar / Navigation
| Token | Value | Usage |
|---|---|---|
| `slate-800` | `#1E293B` | Sidebar background, mobile header |
| `white` / `text-white` | — | Primary nav text |
| `slate-300` | — | Inactive nav link text |
| `white/10` | — | Hover overlay on nav links |
| `white/15` | — | Active nav link background |

### Content Area (Light Mode)
| Token | Value | Usage |
|---|---|---|
| `gray-50` | `#FAFAFA` | Page/content background |
| `white` | — | Card backgrounds |
| `gray-900` | `#111827` | Primary body text |
| `gray-500` | `#6B7280` | Secondary/muted text |
| `gray-400` | `#9CA3AF` | Tertiary text, table headers |
| `gray-200` | `#E5E7EB` | Borders (often at /80 or /50 opacity) |

### Content Area (Dark Mode)
| Token | Value | Usage |
|---|---|---|
| `zinc-900` | `#18181B` | Page background |
| `zinc-800` | `#27272A` | Card backgrounds |
| `zinc-700` | — | Borders |
| `zinc-400` | — | Secondary text |
| `zinc-300` | — | Primary body text |
| `white` | — | Headings |

### Status Colors
| State | Background | Text / Icon |
|---|---|---|
| Success | `green-100` | `green-700` |
| Warning | `amber-50` / `amber-100` | `amber-500` / `amber-600` |
| Error / Danger | `red-100` | `red-700` |
| Info | `blue-50` | `blue-600` |

---

## Typography

### Font Stack
Default Tailwind system-UI sans-serif (no custom fonts). Rely on the OS native font stack for maximum legibility.

### Type Scale
| Element | Classes | Size |
|---|---|---|
| Page H1 | `text-3xl font-bold` | 1.875rem |
| Page H2 | `text-2xl font-bold` | 1.5rem |
| Page H3 | `text-xl font-bold` | 1.25rem |
| Section heading | `text-base font-bold` | 1rem |
| Body / label | `text-sm font-medium` | 0.875rem |
| Helper / meta | `text-xs font-semibold uppercase tracking-wider text-gray-400` | 0.75rem |
| Links | `text-orange-500 hover:text-orange-600` | — |

### Line Height
- Body: `leading-6` (1.5rem)
- Headings: `leading-tight`

---

## Spacing & Layout

### Containers
- App max-width: `max-w-6xl mx-auto`
- Sidebar: `w-64` (256px), fixed on desktop
- Mobile sidebar: hidden (`-translate-x-full`), toggled with Alpine.js

### Padding Tokens
| Context | Classes |
|---|---|
| Standard card | `p-5` |
| Large card (desktop) | `lg:p-8` |
| Section block | `p-6` or `px-6 py-4` |
| Inline gap | `gap-3` / `gap-4` / `gap-5` |
| Vertical stack | `space-y-3` / `space-y-6` |

### Border Radius
| Scale | Class | Use |
|---|---|---|
| Standard | `rounded-lg` | Cards, inputs, buttons |
| Large | `rounded-xl` | Dashboard cards |
| Full | `rounded-full` | Pills, progress bars, toggle switches |
| Medium | `rounded-md` | Filament buttons |

### Responsive Breakpoints
- Mobile-first; primary desktop breakpoint: `lg:`
- Grid: `grid-cols-2` → `lg:grid-cols-4` for stat cards

---

## Component Patterns

### Buttons

**Primary**
```html
<button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
    Label
</button>
```

**Secondary**
```html
<button class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
    Label
</button>
```

**Destructive**
```html
<button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
    Delete
</button>
```

### Cards

**Standard**
```html
<div class="bg-white dark:bg-zinc-800 border border-gray-200/50 dark:border-zinc-700 rounded-lg p-5 lg:p-8">
    <!-- content -->
</div>
```

**Dashboard Stat Card** (interactive)
```html
<div class="bg-white dark:bg-zinc-800 rounded-xl border border-gray-200/80 dark:border-zinc-700 shadow-sm hover:shadow-md transition-shadow p-5">
    <!-- stat content -->
</div>
```

**Icon Container** (within cards)
```html
<div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center">
    <x-phosphor-icon class="w-5 h-5 text-orange-500" />
</div>
```

### Navigation / Sidebar Links

**Active**
```html
<a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-white/15 text-white font-medium text-sm transition-colors">
    <x-phosphor-icon class="w-5 h-5" />
    Label
</a>
```

**Inactive**
```html
<a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white text-sm transition-colors">
    <x-phosphor-icon class="w-5 h-5" />
    Label
</a>
```

### Progress Bars
```html
<div class="w-full bg-gray-100 dark:bg-zinc-700 rounded-full h-2">
    <div class="bg-orange-500 h-2 rounded-full" style="width: 65%"></div>
</div>
```

### Status Badges
```html
<!-- Success -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>

<!-- Warning -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-600">Dormant</span>

<!-- Danger -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Lost</span>
```

### Alerts / Banners
```html
<!-- Info -->
<div class="relative pl-5 pr-10 py-4 w-full rounded-md border bg-blue-50 border-blue-200 text-blue-600 text-sm">
    Message here.
</div>

<!-- Warning -->
<div class="relative pl-5 pr-10 py-4 w-full rounded-md border bg-amber-50 border-amber-200 text-amber-600 text-sm">
    Message here.
</div>
```

### Data Tables
```html
<table class="w-full">
    <thead>
        <tr>
            <th class="text-xs font-semibold text-gray-400 uppercase text-left pb-3">Column</th>
        </tr>
    </thead>
    <tbody>
        <tr class="border-b border-gray-50 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
            <td class="py-3.5 text-sm text-gray-900 dark:text-zinc-100">Value</td>
        </tr>
    </tbody>
</table>
```

### Section Headers
```html
<p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Section Title</p>
```

---

## Iconography

- **Library**: Phosphor Icons — use `<x-phosphor-{icon-name}>` (regular) or `<x-phosphor-{icon-name}-duotone>` (secondary/decorative UI)
- **Sizing**: `w-4 h-4` (small/inline), `w-5 h-5` (standard), `w-10 h-10` (large/card icons)
- **Color**: Inherit text color by default; use `text-orange-500` for primary brand moments

### Common Icon Names in Use
`house`, `list-bullets`, `magnifying-glass`, `plus-circle`, `gear`, `sign-out`, `bell-duotone`, `credit-card-duotone`, `piggy-bank`

---

## Shadows
| Level | Class | Usage |
|---|---|---|
| Subtle | `shadow-sm` | Resting cards |
| Elevated | `shadow-md` | Hovered / focused cards |
| Strong | `dark:shadow-xl` | Dropdowns, modals in dark mode |

---

## Dark Mode

### Implementation Rules
1. Dark mode is toggled via `document.documentElement.classList.add('dark')` and stored in `localStorage` under key `'theme'` (`'light'` | `'dark'`)
2. Re-apply on `livewire:navigated` events to persist across Livewire navigation
3. **Every** new component must include `dark:` variants for: background, text, border, and shadow

### Dark Mode Color Mapping
| Light | Dark |
|---|---|
| `bg-white` | `dark:bg-zinc-800` |
| `bg-gray-50` | `dark:bg-zinc-900` |
| `text-gray-900` | `dark:text-white` |
| `text-gray-500` | `dark:text-zinc-400` |
| `border-gray-200` | `dark:border-zinc-700` |
| `hover:bg-gray-50` | `dark:hover:bg-zinc-700/50` |

---

## Dos and Don'ts

### Do
- Use orange exclusively for primary CTAs and brand accents
- Use Phosphor icons consistently throughout
- Include `transition-colors` on all interactive elements
- Always pair `rounded-lg` with `border border-gray-200/50 dark:border-zinc-700`
- Support dark mode from the start — never add it as an afterthought
- Use `text-xs font-semibold uppercase tracking-wider text-gray-400` for all section labels

### Don't
- Don't use orange for body text or non-interactive decorative elements
- Don't use custom colors outside the palette — extend via Tailwind config if needed
- Don't hard-code pixel values — use Tailwind spacing tokens
- Don't skip dark mode variants on cards, inputs, or text
- Don't use Heroicons — this project uses Phosphor only
- Don't add drop shadows beyond `shadow-md` in light mode
