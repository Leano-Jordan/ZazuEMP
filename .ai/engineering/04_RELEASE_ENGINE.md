# ZAZU EMP RELEASE ENGINE

## Mission

Continuously move Zazu toward **commercially usable software** by maintaining release gates and closing the highest-risk remaining gaps.

Release is not a last-minute checklist.

## Activation

Release activates when:
- a change affects a release gate;
- a dependency/configuration/migration/security/recovery decision is involved;
- a readiness review is requested;
- the system reaches release-candidate preparation.

The engine remains aware of the complete readiness register even during ordinary feature work.

## Gate domains

- Correctness
- Architecture
- Data integrity
- Security
- Workflow integrity
- UX/accessibility
- Reliability/recovery
- Operability/observability
- Deployment/configuration
- Documentation/runbooks
- Dependency/licensing/IP
- Release evidence

## Commercial-readiness rule

Commercially usable means the product can be safely operated, maintained, upgraded and recovered by its intended owner/users.

It does not mean every future feature or scale programme is implemented.

## Evidence maturity

0 = not started
1 = designed
2 = implemented
3 = automated evidence
4 = runtime/CI verified
5 = repeatedly proven through realistic/production/recovery evidence

## Release gate

A release candidate requires evidence that:
- critical workflows work;
- critical data invariants hold;
- security boundaries are enforced;
- failure/recovery behaviour is understood;
- migrations are safe for clean and representative existing states;
- backup/restore is demonstrated;
- required browser/runtime paths are verified;
- operational documentation exists;
- known exceptions are explicitly recorded.

## Anti-inflation

Do not raise readiness because:
- files were created;
- tests were counted;
- a report was written;
- a screen exists without complete workflow behaviour.

Readiness rises when meaningful risk is removed and evidence matures.

## Escalation

Record the exact blocker, required evidence/fix and next actionable closure target.

Do not repeatedly rerun a gate whose prerequisite evidence is still unavailable.
