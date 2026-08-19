## Working approach

- Inspect the surrounding code and repository conventions before making changes.
- Preserve existing user changes. Do not revert unrelated diffs.
- Prefer small, targeted edits over broad rewrites.
- Use ASCII by default unless the file already requires Unicode.
- Add comments sparingly. Prefer self-documenting code and only comment non-obvious intent.
- Use non-interactive git commands only.

## File formatting

- Follow `.editorconfig`.
- Use UTF-8, LF endings, a final newline, and trim trailing whitespace.
- Use 4 spaces by default.
- Use 2 spaces for `*.js`, `*.ts`, `*.vue`, `*.yml`, and `*.yaml`.
- Do not trim trailing whitespace in Markdown files.

## Pre-commit checks

- Prefer repository commands over ad hoc alternatives.
- Use Sail-prefixed commands for backend code. Frontend (`npm`) commands must always be run on the host machine — never via `sail npm`. Mixing environments causes native binding failures in packages like `rolldown` and `esbuild`.
- The repo-managed pre-commit flow is `./scripts/pre-commit-checks.sh --full`.
- Relevant checks include:
    - `sail composer run lint`
    - `sail composer run larastan`
    - `sail php artisan test`
    - `npm run typecheck`
    - `npm run test:ci`
    - `npm run pretty`

## Code style

- Code should communicate intent through precise naming, clear structure, and expressive types — comments explaining what the code does are rarely needed.
- Reserve comments for explaining why a decision exists, not how the code works. If you find yourself adding a comment, stop and ask if the code can be made clearer instead.
- Prefer intention-revealing names over explanatory comments.
- Use verb-led function names.
- Avoid abbreviated names such as `calc`, `doStuff`, `data`, or `tmp` — use the clearest domain name available.
- Name booleans so they read as predicates, with prefixes `is`, `has`, `can`, `should`, `does`, `did`, or `will`.
- Name all other variables and functions to clearly describe what they represent or do.
- Keep imports at the top of the file.
- Remove unused imports and dead code.
- Match existing architecture instead of introducing new patterns without a reason.

## Testing conventions

- Backend tests live under `tests/Unit` and `tests/Feature`.
- Frontend tests commonly sit next to the code they verify, for example `*.spec.ts`.
- New tests must be written in Pest using `test(...)`, not `it(...)`.
- Test names must follow the pattern `'Given [context] When [action] Then [expected outcome]'`.
- Prefer Laravel and PHPUnit-style assertions over fluent `expect(...)` assertions.
- Use `dispatchSync()` to run jobs in tests instead of manually calling `->handle()`.
- Keep tests explicit and readable, with focused setup and concrete assertions.
