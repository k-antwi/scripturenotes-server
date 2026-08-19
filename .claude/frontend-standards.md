## TypeScript, JavaScript, and Vue

- TypeScript is strict. Avoid implicit `any` and handle `null` and `undefined` explicitly.
- Use the `@/` alias for imports from `resources/js`.
- Follow ESLint flat config for Vue + TypeScript and Standard-style rules.
- Always use TypeScript for new frontend code. JavaScript is allowed for legacy areas but may be converted for extensive changes.
- Type parameters should be prefixed with `T`, for example `TRoles`.
- Interfaces should be prefixed with `I`, for example `IApiResponse`.
- Run `npm run typecheck` for frontend type safety.
- Run `npm run test:ci` for frontend test verification when frontend behavior changes.
- Run `npm run pretty` to format frontend files

## Vue and frontend conventions

- Always use Vue 3 Composition API for new code, typically with composables named `use...`.
- Always use TypeScript for new code.
- Preserve existing Options API components/vanilla JS unless there is a clear reason to migrate them.
- Vue components must be PascalCase and multi-word, for example `SomeComponent`. Layouts and pages may be single word.
- Keep shared UI in `resources/js/Components` and feature code grouped under `resources/js/Composables` or other existing domain folders.
- Continue using Tailwind utility classes in templates.
- HTML attributes in templates should be hyphenated.
- Props must be explicitly typed, use camelCase in script, and hyphenated attributes in templates. Prefer explicit types for helper inputs and exported values too.
- Use small composables for reusable frontend behavior.
- State management via Pinia. New stores must be TypeScript (`.ts`) and use the Composition API style: `defineStore('id', () => { ... })`. Existing Options API stores may be left as-is.
- Boolean props follow the same rules as boolean variables.
- Event handlers are `onElementEvent`, where `Element` is a logical identifier and `Event` is the event type.

## Content Security Policy

- Never use inline `style="..."` attributes or `:style="..."` bindings for static values — use Tailwind utility classes instead.
- Dynamic `:style=` bindings are only acceptable when the value is genuinely computed at runtime (e.g. a percentage width from data). Document the reason with a comment.
- Do not use third-party packages that inject `<style>` tags at runtime. If a package bundles CSS with its JavaScript (an all-in-one build), import the CSS-less build and import the stylesheet separately so Vite can bundle it as an external file.
- Fonts must be self-hosted under `resources/fonts/` and referenced via relative paths in `resources/css/font.css`. Do not load fonts from external CDNs.
