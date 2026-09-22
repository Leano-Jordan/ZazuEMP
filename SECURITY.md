# Security Policy

## Scope

This policy applies to the ZazuEMP repository and its project-controlled source, configuration and documentation.

## Never commit

- passwords
- API keys
- access tokens
- private keys
- certificates containing private material
- production database dumps
- customer personal information
- authentication/session secrets
- confidential contracts
- unpublished security research that would materially increase exploitation risk

## Reporting a vulnerability

Do not publish sensitive vulnerability details in a public issue.

Report security concerns privately to the project owner through a private communication channel established outside this public repository.

Include, where safe:

1. affected component or file
2. reproducible conditions
3. security impact
4. evidence
5. suggested mitigation

Do not include live credentials or unnecessary personal information.

## Security standard

Security work should follow the project's engineering rules: establish an evidence-backed attack path, distinguish confirmed findings from hypotheses, make the smallest justified change, and verify the result.

## Disclosure

The project owner decides disclosure timing and remediation coordination based on the severity, exploitability and affected users.

## Personal information

Where personal information is processed, applicable privacy law and project requirements must be assessed before implementation. Security controls should be appropriate to the actual risks.

This policy does not grant permission to perform intrusive testing against systems you do not own or have explicit authorization to test.
