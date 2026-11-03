# Tasks: Fast Track Support

Input: spec.md, plan.md, research.md, data-model.md, contracts/, quickstart.md.
All task paths are relative to the repository root. All tasks start unchecked.
Tests precede the corresponding implementation. Obtain approval for each concrete
implementation batch before editing files.

## Phase 1: Setup

- [X] T001 Verify installed dependencies and baseline commands against composer.json, package.json and specs/001-support-ticket-triage/quickstart.md; preserve existing lockfiles and record results in docs/verification.md.
- [X] T002 Configure documented local defaults and optional provider variables in .env.example and config/urgency.php; never include a real secret or overwrite the working .env.

## Phase 2: Foundations

- [X] T003 Create typed priority/status enums in app/Enums/TicketPriority.php and app/Enums/TicketStatus.php, plus immutable validated app/Data/UrgencyResult.php.
- [X] T004 Create database/migrations/2026_11_03_000001_create_tickets_table.php, app/Models/Ticket.php and database/factories/TicketFactory.php using data-model.md: recommendation fields, casts, allowed writes, derived reference and three indexes.
- [X] T005 Define typed contracts in app/Contracts/TicketRepositoryInterface.php and app/Contracts/UrgencyAnalyzerInterface.php; implement persistence in app/Repositories/EloquentTicketRepository.php with explicit fields, find and filtered pagination.
- [X] T006 Configure dependency bindings and the configurable creation limiter in app/Providers/AppServiceProvider.php; preserve web middleware and configure safe French error responses in bootstrap/app.php and resources/views/errors/404.blade.php, resources/views/errors/419.blade.php, resources/views/errors/422.blade.php and resources/views/errors/429.blade.php.
- [X] T007 Build the Figma layout, tokens and shared controls in resources/css/app.css and resources/views/components/layouts/app.blade.php, resources/views/components/ui/button.blade.php, resources/views/components/ui/alert.blade.php, resources/views/components/ui/empty-state.blade.php, resources/views/components/forms/field.blade.php, resources/views/components/forms/input.blade.php, resources/views/components/forms/select.blade.php and resources/views/components/forms/textarea.blade.php; follow contracts/ui-components.md.

Checkpoint: shared contracts, schema and presentation foundations ready. Interface bindings
may name planned implementations, but do not resolve them until their story is implemented.

## Phase 3: US1, Enregistrer une demande (P1)

Goal: persist and consult a valid request using genuine local analysis.
Independent test: create from an empty database without a key, open the detail,
refresh, and verify persistence; invalid submissions create no ticket.

### Tests first

- [X] T008 [P] [US1] Write failing creation tests in tests/Feature/TicketCreationTest.php for defaults, trimmed boundaries, enums, rejected recommendation input, 303 redirect, persisted detail, 404 and recoverable write failure.
- [X] T009 [P] [US1] Write failing classifier tests in tests/Unit/RuleBasedUrgencyAnalyzerTest.php covering all impact classes, unknown text, accents, mixed signals and the exact negation examples in spec.md.

### Implementation

- [X] T010 [US1] Implement deterministic impact rules and French explanations in app/Services/Urgency/RuleBasedUrgencyAnalyzer.php; satisfy T009 without treating every occurrence of urgent or ne as decisive.
- [X] T011 [US1] Implement server validation and normalization in app/Http/Requests/StoreTicketRequest.php; accept only title, description, priority and status before analysis.
- [X] T012 [US1] Implement creation/detail orchestration in app/Services/TicketService.php; analyze before the atomic insert, preserve human priority and isolate persistence errors without logging sensitive bindings.
- [X] T013 [US1] Implement create/store/show in app/Http/Controllers/TicketController.php and routes/web.php; retain Controller → Service → Repository → Model, numeric IDs, CSRF, throttling and explicit redirects.
- [X] T014 [US1] Build resources/views/tickets/create.blade.php, resources/views/tickets/show.blade.php and resources/views/components/tickets/form.blade.php, resources/views/components/tickets/detail.blade.php, resources/views/components/tickets/priority-badge.blade.php, resources/views/components/tickets/status-badge.blade.php and resources/views/components/tickets/urgency-analysis.blade.php with escaped output, old input, field errors and separate recommendation provenance.
- [ ] T015 [US1] Prevent simultaneous submissions and focus the first validation error in resources/js/forms.js, imported by resources/js/app.js; run creation/classifier tests and record browser persistence, retry and double-click results in docs/verification.md.

Checkpoint: US1 works without a provider or JavaScript. Run T008/T009 to confirm the
expected behavioral failures before implementation, then verify they pass after integration.
Use isolated SQLite and RefreshDatabase in database-backed feature tests.

## Phase 4: US2, Consulter et trier la file (P1)

Goal: find tickets through a stable, bounded and filterable queue.
Independent test: load fixtures without creating tickets; verify every status
and priority combination, pagination, reset, ordering and empty states.

### Tests first

- [X] T016 [P] [US2] Write failing tests in tests/Feature/TicketListingTest.php for nine filter combinations, individual filters, invalid values, empty states, equal timestamps, more than 40 records and pagination query preservation.

### Implementation

- [X] T017 [US2] Implement validated optional filters and positive page numbers in app/Http/Requests/TicketFilterRequest.php; distinguish invalid filters from valid empty results.
- [X] T018 [US2] Connect index/home routes through app/Http/Controllers/TicketController.php, app/Services/TicketService.php and routes/web.php to repository pagination; use validated parameters, 20 results and descending date/id order.
- [X] T019 [US2] Build resources/views/tickets/index.blade.php with semantic desktop table, mobile cards, filtered total, GET filters, Apply/reset actions and pagination; reuse badge components and canonical detail links.
- [ ] T020 [US2] Run tests/Feature/TicketListingTest.php and verify filtering, reset, detail navigation and pagination without JavaScript; record results in docs/verification.md.

Checkpoint: the queue is testable with factories, independently of POST creation.

## Phase 5: US3, Comprendre l’urgence malgré les pannes (P1)

Goal: integrate the optional external analyzer without sacrificing local resilience.
Independent test: simulate provider success and every failure category;
verify persisted source/reason and unchanged human priority after refresh.

### Tests first

- [X] T021 [P] [US3] Write failing adapter tests in tests/Feature/OpenAiUrgencyAnalyzerTest.php for the fixed Responses request, description-only payload, strict schema, bounded timeouts, disabled redirects/retries and no unexpected outbound calls.
- [X] T022 [P] [US3] Write failing integration tests in tests/Feature/UrgencyAnalysisTest.php for missing key, local mode, HTTP errors, timeout, refusal, incomplete/invalid/oversized output and unchanged human priority; use HTTP fakes and preventStrayRequests.

### Implementation

- [X] T023 [US3] Implement app/Services/Urgency/OpenAiUrgencyAnalyzer.php against contracts/urgency-analysis.md, including store=false, completed-response parsing, enum/reason validation and controlled failure categories.
- [X] T024 [US3] Implement app/Services/Urgency/ResilientUrgencyAnalyzer.php and update app/Providers/AppServiceProvider.php to select local mode or external-with-fallback; reject invalid configuration without exposing secrets.
- [X] T025 [US3] Complete safe analysis logging in app/Services/Urgency/ResilientUrgencyAnalyzer.php and provenance/disclosure in resources/views/components/tickets/urgency-analysis.blade.php and resources/views/components/tickets/form.blade.php; test escaping and absence of sensitive logs in tests/Feature/UrgencyAnalysisTest.php.
- [X] T026 [US3] Run tests/Feature/OpenAiUrgencyAnalyzerTest.php and tests/Feature/UrgencyAnalysisTest.php, then measure controlled fallback creation against the ten-second target; record actual results and simulation limits in docs/verification.md without paid calls.

Checkpoint: external success and failures are simulated; local results remain deterministic.
The local classifier unit tests from US1 are also mandatory regression coverage for US3.

## Phase 6: US4, Installer et évaluer (P2)

Goal: deliver a reproducible project that an evaluator can inspect and run without a key.
Independent test: follow the README in an isolated clean copy, initialize
SQLite, run tests/build and demonstrate creation/filtering without a key.

- [ ] T027 [US4] Create repeatable fictional demonstration seeds in database/seeders/TicketSeeder.php and database/seeders/DatabaseSeeder.php covering nine status/priority combinations, tied timestamps and clearly simulated llm/local results; make no provider calls.
- [ ] T028 [US4] Update README.md and docs/livraison.md with Figma as the first presentation link, verified prerequisites/commands, migrations/seeds, local defaults, optional external analysis, privacy disclosure, tests and known limitations.
- [ ] T029 [US4] Execute specs/001-support-ticket-triage/quickstart.md in an isolated clean copy using the lockfiles; record installation, no-key journeys and reproducible command results in docs/verification.md.

Checkpoint: installation and delivery claims have execution evidence, with limits stated.

## Phase 7: Integration, security and final QA

- [ ] T030 Add tests/Feature/TicketSecurityTest.php for escaped ticket/model text, rejected extra fields, invalid input before analysis and configurable rate limiting; verify real-server CSRF rejection separately and record zero writes/outbound calls in docs/verification.md.
- [ ] T031 Implement shared GET fragments and accessible native dialogs in app/Http/Controllers/TicketController.php, resources/views/components/ui/panel.blade.php and resources/js/panels.js, imported by resources/js/app.js; preserve canonical links, ordinary POST navigation, Escape and focus restoration.
- [ ] T032 Run targeted tests, the full Pest suite, Pint and npm build; perform desktop/390px, keyboard, contrast, loading/error, no-JavaScript and recoverable persistence-failure QA from specs/001-support-ticket-triage/quickstart.md; fix approved defects and record evidence in docs/verification.md.
- [ ] T033 Review actual changes against specs/001-support-ticket-triage/spec.md, constitution and docs/livraison.md; check the delivery excludes secrets/local data, report unexecuted checks and request final approval before completion, commit, push or publication.

## Dependencies and parallel work

Setup → Foundations → US1 → US2 → US3 → US4 → Final QA.

US1 supplies local analysis, persistence and detail reused by later stories.
US2 is independently testable with factories.
US3 extends US1 with the external adapter and failure coverage.
US4 validates the integrated product.

Within a story, tests precede implementation; services precede endpoints and integration.
Tasks touching the same files run sequentially. A checkpoint requires relevant tests to pass.

Parallel examples:
- US1: T008 and T009, separate test files.
- US2: after T016/T017, T019 can proceed alongside T018 against the approved contract.
- US3: T021 and T022, separate test files.
- US4: after T027, documentation T028 can proceed alongside adapter validation T026 if those validation results are not yet claimed in the documentation.

The [P] marker denotes work with no unfinished task dependency within its test-writing
wave and no file conflict. Examples involving later tasks require their stated prerequisites.
These examples do not authorize spawning agents.

## Implementation strategy

First working increment: Foundations + US1 with genuine local analysis.
This is an internal MVP, not the complete assessment.
Complete US2, US3 and US4 before delivery, followed by final integration/security QA.
Validate each story before advancing and retain regression coverage.

## Coverage and current test inventory

| Requirements | Tasks |
|---|---|
| FR-001 to FR-005 | T008, T011 to T015 |
| FR-006 to FR-008 | T016 to T020, T013 |
| FR-009 to FR-013 | T009, T010, T021 to T026 |
| FR-014 | T007, T014, T019, T031, T032 |
| FR-015 to FR-018 | T006, T008, T021, T025, T028, T030 |
| FR-019 to FR-021 | T027 to T029, T032, T033 |

Inspection on 2026-11-03 found only tests/Unit/ExampleTest.php and
tests/Feature/ExampleTest.php. Both passed (2 tests, 2 assertions).
These scaffold checks do not cover ticket behavior. T009 creates the missing business
unit tests; T008, T016, T021, T022 and T030 add HTTP/integration/security coverage.
No application implementation task is marked complete by this inspection.

Counts: 33 tasks; US1 8, US2 5, US3 6, US4 3, shared setup/foundation/final QA 11.
