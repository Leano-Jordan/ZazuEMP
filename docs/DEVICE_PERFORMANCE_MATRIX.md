# Zazu EMP — Device performance and resource acceptance

**Status:** local Windows sampler and repeatable acceptance protocol are in place; real-device baselines are not yet established.

## Objective

Zazu must be proven on more than the developer's machine. Every release candidate is assessed against a repeatable workload on low-resource, mainstream and high-performance hardware. The result must identify the exact application commit, installation package hash, device specification, dataset, run conditions and measured resource profile.

## Measurement model

| Resource | Host Windows measurement | Client/device measurement | Interpretation |
|---|---|---|---|
| CPU | Whole-host processor utilisation and Zazu PHP/database/proxy process CPU, normalised to total logical processors | Browser interaction/request timings; Android system process CPU requires a physical-device ADB capture | Never treat browser JavaScript CPU hints as a true device CPU meter |
| Memory | Installed RAM, available RAM, host usage, Zazu process working set and private bytes | Browser JS heap where supported; Android app/browser process PSS via ADB | Browser APIs cannot reliably report total phone RAM or the complete browser process footprint |
| Disk | Host disk read/write throughput, target-volume capacity/free space, and Zazu database/private-data footprint | Browser storage quota/usage where supported; Android device storage and browser cache measured separately | Host disk counters are system-wide; process I/O counters are not disk-only |
| Network | Host network-adapter receive/send throughput | Browser resource request duration, transfer size and failed-request counts; ADB/system capture for physical Android | Adapter throughput is not Zazu-only. Do not label it application bandwidth |

The Windows sampler is local-only. It writes CSV samples and a JSON hardware/run manifest to the user's Documents\Zazu\Performance folder. It does not upload telemetry or capture usernames, IP addresses, URLs, filenames, credentials, database contents or customer records.

## Supported Windows hardware cohorts

Do not assume one developer PC represents the market. The sampler assigns a provisional cohort from installed RAM and logical processor count: Entry (8 GB RAM or less, or 4 logical processors or fewer), High-performance (at least 16 GB RAM and 8 logical processors), otherwise Mainstream. This is a comparison aid, not a supported-device promise or a minimum requirement. Record actual CPU model, core counts, RAM, storage model/interface/type and free space alongside the assigned cohort.

- **Entry / constrained:** older dual- or quad-core CPU, 4–8 GB RAM, HDD or low-end SATA/eMMC storage, integrated graphics.
- **Mainstream:** modern 4–8 core CPU, 8–16 GB RAM, SATA SSD or NVMe.
- **High-performance:** 8+ cores, 16+ GB RAM, NVMe storage.

For every device, record CPU model and logical/physical cores, installed RAM, storage model/interface/type and free space, Windows version, PHP version, database engine, Zazu package/source SHA and whether antivirus/backup/indexing tasks were active. Do not publish device serial numbers, account names, network addresses or customer data.

## Repeatable workload

Run each cohort against the same anonymised, seeded dataset and the same exact release candidate:

1. Cold start after a full Zazu process shutdown; capture startup and first dashboard render.
2. Warm start and 10-minute normal workflow: dashboard, calendar, customer search, quote editing, event/work record, invoice/payment view and inventory.
3. Data-heavy workflow with a fixed representative record count and attachment set.
4. Save and search while disconnected where the workflow is declared offline-capable; reconnect and measure sync completion separately.
5. Idle for five minutes to establish background CPU, memory and network activity.
6. Repeat each scenario at least three times. Report median and p95, not the best run.

Capture host CPU, host memory, Zazu process CPU and private/working-set memory, host disk throughput, Zazu process I/O, volume free space, network adapter throughput, browser request timings, page interaction timings, errors and sync outcomes.

## Acceptance and reporting rules

- Every release acceptance report must include the exact source commit SHA and package SHA-256. The sampler discovers the Git commit automatically in a source checkout; pass -BuildCommitSha when testing an installed build. Pass -PackagePath to hash the exact installer/package. A run without both identifiers is diagnostic-only, not release evidence.
- Record p50 and p95 for cold start, warm navigation, search, save, and sync; report CPU/memory/disk/network alongside them.
- Compare entry, mainstream and high-performance cohorts. Do not average away a failure on the entry cohort.
- No arbitrary resource thresholds are release blockers until a repeatable baseline exists. First collect three runs per device; then set budgets from observed headroom and product requirements.
- A run is invalid if the process sampler did not match the Zazu server process, the workload/dataset differed, the machine was doing unrecorded heavy background work, or the capture was interrupted.
- CI browser emulation is useful for regression detection, but it does not certify real hardware performance.
- A phone connected to a PC is a separate client and host. Record both sides; a good PC result does not prove good phone performance.
- Do not collect continuous customer telemetry by default. Any future opt-in diagnostics must disclose fields, purpose, retention, export/delete behaviour and network destination before collection is enabled.

## Running the Windows sampler

From the Zazu repository root in Windows PowerShell 5.1:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\ops\windows\Measure-ZazuResources.ps1 -DurationSeconds 600 -IntervalSeconds 5
```

A short smoke capture can be used to validate the sampler:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\ops\windows\Measure-ZazuResources.ps1 -DurationSeconds 5 -IntervalSeconds 1
```

After capture, generate a local p50/p95 report from the matching CSV and JSON manifest:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\ops\windows\Summarize-ZazuResources.ps1 -CsvPath '<sample-csv-path>' -ManifestPath '<manifest-json-path>'
```

The report includes sample counts, p50, p95, minimum and maximum for host and Zazu-scoped measurements. It deliberately omits the Windows computer name. Missing process matches and unsupported counters remain marked as unavailable, never as zero.

The sampler looks for the PHP process listening on the configured Zazu ports and its child processes, plus matching local database/proxy processes. It records the provisional hardware cohort, source commit and optional installer SHA-256. The first Zazu CPU sample is intentionally blank because a real CPU delta requires two observations. If it cannot identify the Zazu process, it marks `ZazuProcessMatched=false` and leaves process CPU/memory/I/O values blank; missing data is never reported as zero usage. Network adapter and host disk metrics remain system-wide. PHP process I/O is labelled process I/O because Windows process I/O counters include more than disk activity. This is a local test instrument, not an always-on product telemetry service.

## Evidence still required

- [ ] Run the sampler on the actual owner PC during a repeatable Zazu workload, with the exact release commit and package hash.
- [ ] Run the same release and dataset on at least one entry-level and one mainstream PC.
- [ ] Capture physical Android client/browser performance separately; browser-only CPU/memory estimates are not sufficient.
- [ ] Establish p50/p95 and resource budgets from at least three runs per cohort.
- [ ] Add the resulting measured support floor and known limitations to the V1 release notes.
