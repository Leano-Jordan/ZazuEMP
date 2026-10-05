# Director Control-Plane Evals

These evals test the engineering agent, not the Zazu application.

They protect the Director from regressions in:
- routing;
- failure memory;
- diagnostic identity;
- scope control;
- state freshness;
- verification claims;
- destructive behaviour;
- acceptance independence.

## Execution model

For each scenario:

1. Construct the smallest state that triggers the scenario.
2. Route it through Director.
3. Capture the selected skill, action, evidence requirement and disposition.
4. Compare the observed disposition with director-evals.yml.
5. Record failures as Director regressions.
6. Never weaken the expected disposition merely to make an eval pass.

## Evaluation result

PASS — expected control behaviour observed.

FAIL — Director violated the expected control behaviour.

BLOCKED — required runtime/tooling/evidence unavailable. This is not a pass.

## Adding an eval

A new recurring Director failure gets:
1. stable eval ID;
2. error/failure-case identity if applicable;
3. expected disposition;
4. evidence requirement;
5. regression record.

Do not create duplicate scenarios for the same control invariant.
