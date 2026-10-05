# ZAZU EMP DISCOVERY & DESIGN ENGINE

## Mission

Determine what Zazu should do, why, where the behaviour lives, what can safely change and what must remain invariant.

This engine prevents implementation driven by guesses.

## Modes

### RECON
Inspect the real repository surface:
routes, controllers/services, models, migrations, views/components, configuration, policies/permissions, tests/e2e, shared UI and relevant documentation.

### IMPACT
Trace user journey, business rules, state transitions, parent/child relationships, authorization boundaries, shared dependencies and regression blast radius.

### WORKFLOW
Trace the operation from entry to persisted outcome and user feedback:
start → validation → write → state transition → downstream effects → cancellation → retry → recovery → completion.

### ARCHITECTURE
Assess responsibility boundaries, coupling, duplication, domain ownership, service placement, component reuse, dependency direction and migration/data-model implications.

### PRODUCT
Check that the change advances Zazu's actual product direction without inventing speculative infrastructure.

### USER
Review comprehension, terminology, discoverability, feedback, error recovery and operational friction.

### COMPETITIVE
Use external research only when relevant. Record it as evidence, not implementation authority.

### FRESH EYES
Challenge:
- hidden assumptions;
- missing states;
- unnecessary steps;
- failure paths;
- inconsistent behaviour;
- root-cause quality.

## Required decision

Before implementation, establish:
- target;
- observed current behaviour;
- desired behaviour;
- affected surfaces;
- invariants;
- risk;
- chosen approach;
- acceptance criteria;
- verification strategy;
- scope in/out.

## Conflict handling

When Zazu documents disagree:
1. compare their dates and current repository state;
2. prefer current repository reality;
3. prefer explicit owner decisions;
4. mark unresolved conflict;
5. never silently merge contradictory rules.

Never fill a Zazu gap from another project's context.
