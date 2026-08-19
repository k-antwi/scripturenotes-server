Review the current branch against a base branch and produce a structured code review.

## Usage

`/review-branch [base-branch]`

If no base branch is given, resolve the direct parent branch (one hop only — do not climb further up the ancestry). If neither can be determined, default to staging. Do not use master unless explicitly specified.

## Steps

1. Run `git diff <base-branch>` to get the full diff.
2. Run `git diff <base-branch> --stat` for a file summary.
3. Read any changed files in full where the diff alone lacks context (e.g. when a new file is added or the surrounding code matters for judging correctness).
4. Run `npm run typecheck` and note any errors introduced by the diff (filter out pre-existing issues unrelated to the changed files).
5. Produce a structured review using the format below.

## Review format

### Summary
One short paragraph describing what the change does and the overall quality assessment.

---

### Must fix
Issues that will break CI, cause runtime errors, introduce security problems, or violate a hard project rule. Each item must:
- Identify the exact file and line.
- Explain *why* it is wrong.
- Show the fix or describe it precisely.

### Should fix
Issues that do not block CI but will cause problems in production or mislead future contributors.
- Bugs
- Logic errors
- Meaningful quality issues
- Less than 80% test coverage
- Main logic paths uncovered by tests

### Minor
Only include items worth spending review time on — do not pad this section.
- Style
- Naming
- Type precision
- Redundancy
- Minor edge cases not covered by tests

---

Omit any section that has no items.

## Standards to apply

Apply the standards from CLAUDE.md. Key things to watch for:

**Frontend (TypeScript / Vue)**
- No implicit `any`; `null` and `undefined` handled explicitly.
- Vue 3 Composition API with TypeScript for all new components.
- Props explicitly typed; boolean props named with `is`, `has`, `can`, `should`, `does`, `did`, or `will`.
- Event handlers named `onElementEvent`.
- Composables named `use…`.
- No unused imports or dead code.
- BCMath-equivalent string arithmetic for money/precision values — no floats.

**Backend (PHP / Laravel)**
- `declare(strict_types=1)` present.
- Typed parameters and return types everywhere.
- Business logic in services/actions/domain classes, not controllers.
- New permissions added only via migrations.
- Gate checks on route definitions, not inside controllers.
- Cursor pagination; never in-memory pagination.

**Tests**
- Given/When/Then test names.
- `test()` not `it()`.
- Frontend: mock only what cannot run in jsdom (e.g. IntersectionObserver); mount real child components where possible.
- Backend: no mocking the database unless unavoidable.
- At least 80% test coverage on new code and files with meaningful changes

**General**
- No speculative abstractions or helpers built for hypothetical future use.
- No backwards-compatibility shims for removed code.
- No explanatory comments on self-evident code.
- Prefer small, targeted edits over rewrites.
