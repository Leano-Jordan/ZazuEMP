<x-app-layout>
    <x-slot:title>Founder Control Centre</x-slot:title>
    <x-slot:heading>Founder Control Centre</x-slot:heading>
    <x-slot:headerAction>
        @if(app(\App\Support\CurrentBusiness::class)->resolve(auth()->user()))
            <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary">Business workspace</a>
        @endif
    </x-slot:headerAction>

    <style nonce="{{ request()->attributes->get('cspNonce') }}">
        .sentinel-wrap{display:grid;gap:1rem;max-width:1440px;margin-inline:auto;padding-bottom:1.5rem}
        .sentinel-intro{display:flex;justify-content:space-between;gap:1.25rem;align-items:center;padding:1.35rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:18px;background:var(--zazu-surface,#fff)}
        .sentinel-kicker{font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:var(--zazu-muted,#687386)}
        .sentinel-title{font-size:1.45rem;font-weight:750;margin:.3rem 0}
        .sentinel-copy{color:var(--zazu-muted,#687386);max-width:58rem}
        .sentinel-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.8rem}
        .sentinel-health-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem}
        .sentinel-health-card{display:grid;gap:.35rem;padding:.85rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px;min-width:0}
        .sentinel-health-card strong{font-size:.9rem}
        .sentinel-health-card p{margin:0;color:var(--zazu-muted,#687386);font-size:.82rem}
        .sentinel-health-card small{color:var(--zazu-muted,#687386);font-size:.72rem}
        .sentinel-health-healthy{border-left:4px solid #23865b}
        .sentinel-health-attention{border-left:4px solid #b7791f}
        .sentinel-health-failed{border-left:4px solid #c04444}
        .sentinel-health-unknown,.sentinel-health-not_checked{border-left:4px solid var(--zazu-border,#d9dee7)}
        .sentinel-health-overall{display:flex;justify-content:space-between;gap:1rem;align-items:center;margin-bottom:.9rem}
        .sentinel-health-overall strong{font-size:1rem}
        .sentinel-health-facts{display:flex;gap:.8rem;flex-wrap:wrap;font-size:.75rem;color:var(--zazu-muted,#687386)}
        .sentinel-stat{padding:1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:14px;background:var(--zazu-surface,#fff)}
        .sentinel-stat strong{display:block;font-size:1.55rem;font-variant-numeric:tabular-nums}
        .sentinel-stat span{font-size:.8rem;color:var(--zazu-muted,#687386)}
        .sentinel-panel{padding:1.2rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:16px;background:var(--zazu-surface,#fff);min-width:0}
        .sentinel-panel h3{font-size:1rem;font-weight:700;margin:0 0 .8rem}
        .sentinel-search{display:flex;gap:.6rem;flex-wrap:wrap}
        .sentinel-search input{flex:1 1 16rem;min-width:0;border:1px solid var(--zazu-border,#d9dee7);border-radius:10px;padding:.7rem .85rem;background:var(--zazu-background,#fff);color:inherit}
        .sentinel-button{border:0;border-radius:10px;padding:.7rem 1rem;background:var(--zazu-accent,#263d58);color:#fff;font-weight:650;text-decoration:none;cursor:pointer}
        .sentinel-clear{align-self:center;color:inherit;text-decoration:underline;font-size:.88rem}
        .sentinel-table-wrap{overflow-x:auto}
        .sentinel-table{width:100%;border-collapse:collapse;font-size:.88rem}
        .sentinel-table th,.sentinel-table td{text-align:left;padding:.75rem .65rem;border-bottom:1px solid var(--zazu-border,#e3e7ed);vertical-align:top}
        .sentinel-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--zazu-muted,#687386)}
        .sentinel-table code,.sentinel-code{font-size:.8rem;overflow-wrap:anywhere}
        .sentinel-table a{font-weight:700;color:inherit}
        .sentinel-download{display:inline-block;margin-top:.35rem;font-size:.78rem;font-weight:650;color:var(--zazu-accent,#263d58)!important;text-decoration:underline}
        .sentinel-muted{color:var(--zazu-muted,#687386)}
        .sentinel-pill{display:inline-block;border:1px solid var(--zazu-border,#d9dee7);border-radius:999px;padding:.18rem .55rem;font-size:.75rem}
        .sentinel-timeline{display:grid;gap:.65rem}
        .sentinel-event{display:grid;grid-template-columns:minmax(10rem, .8fr) minmax(0,2fr);gap:1rem;padding:.8rem;border:1px solid var(--zazu-border,#e3e7ed);border-radius:12px}
        .sentinel-event time{font-size:.82rem;font-variant-numeric:tabular-nums}
        .sentinel-event dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem 1rem;margin:0}
        .sentinel-event dt{font-size:.7rem;text-transform:uppercase;color:var(--zazu-muted,#687386)}
        .sentinel-event dd{margin:0;font-size:.85rem;overflow-wrap:anywhere}
        .sentinel-empty{padding:1.2rem;text-align:center;color:var(--zazu-muted,#687386)}
        .sentinel-retention-note{font-size:.78rem}
        .sentinel-license-grid{display:grid;gap:.8rem}
        .sentinel-license-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(18rem,1.2fr);gap:1rem;padding:1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px}
        .sentinel-license-actions{display:grid;gap:.55rem}
        .sentinel-license-form{display:flex;gap:.5rem;flex-wrap:wrap;align-items:end}
        .sentinel-license-form label{display:grid;gap:.25rem;font-size:.76rem;color:var(--zazu-muted,#687386)}
        .sentinel-license-form input,.sentinel-license-form select{min-width:9rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:8px;padding:.55rem;background:var(--zazu-background,#fff);color:inherit}
        .sentinel-license-form .sentinel-button{font-size:.82rem}
        .sentinel-license-form input[type=checkbox]{min-width:auto;padding:0}
        .sentinel-token{width:100%;min-height:6rem;overflow-wrap:anywhere;border:1px solid var(--zazu-border,#d9dee7);border-radius:10px;padding:.7rem;background:var(--zazu-background,#fff);color:inherit}
        .sentinel-alert{padding:.85rem 1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px}
        .sentinel-alert-error{border-color:#c04444}
        .sentinel-license-search{margin:.8rem 0}
        .sentinel-license-register{display:grid;gap:.55rem;padding:1rem;margin:.8rem 0;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px}
        .sentinel-license-register h4{font-weight:700}
        .sentinel-license-register-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}
        .sentinel-license-register-form label{display:grid;gap:.25rem;font-size:.76rem;color:var(--zazu-muted,#687386)}
        .sentinel-license-register-form input,.sentinel-license-register-form textarea{min-width:0;border:1px solid var(--zazu-border,#d9dee7);border-radius:8px;padding:.55rem;background:var(--zazu-background,#fff);color:inherit}
        .sentinel-license-register-form button{justify-self:start}
        @media(max-width:800px){.sentinel-license-register-form{grid-template-columns:1fr}}
        .sentinel-license-facts{margin-top:.45rem}
        .sentinel-license-meta{font-size:.75rem}
        .sentinel-license-pagination{margin-top:1rem}
        .sentinel-section-nav{display:flex;gap:.5rem;flex-wrap:wrap;padding:.25rem 0 .45rem;border-bottom:1px solid var(--zazu-border,#d9dee7)}
        .sentinel-section-nav a{display:inline-flex;align-items:center;min-height:40px;padding:.5rem .8rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:999px;text-decoration:none;font-size:.84rem;font-weight:650;color:inherit;background:var(--zazu-surface,#fff)}
        .sentinel-account-list{display:grid;gap:.65rem;margin-top:1rem}
        .sentinel-account{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(13rem,1fr) auto;gap:1.1rem;align-items:center;padding:1rem;border:1px solid var(--zazu-border,#d9dee7);border-radius:12px}
        .sentinel-account-meta{display:flex;gap:.45rem;flex-wrap:wrap;font-size:.78rem;color:var(--zazu-muted,#687386)}
        .sentinel-account-users{font-size:.82rem;color:var(--zazu-muted,#687386);line-height:1.55;overflow-wrap:anywhere}
        .sentinel-business-email{font-size:.82rem;overflow-wrap:anywhere}
        .sentinel-account-users strong{color:var(--zazu-text,#263445)}
        .sentinel-account-created{font-size:.76rem;color:var(--zazu-muted,#687386);margin-top:.45rem}
        .sentinel-account .sentinel-button{white-space:nowrap;text-align:center;min-width:9.5rem}
        .sentinel-search{align-items:center;margin-top:.9rem}
        .sentinel-search .sentinel-button{min-height:42px}
        .sentinel-license-form .sentinel-button{min-height:40px}
        .sentinel-stats{gap:.7rem}
        .sentinel-stat{padding:1rem 1.1rem}
        @media(max-width:900px){.sentinel-account{grid-template-columns:minmax(0,1fr) minmax(0,1fr)}.sentinel-account .sentinel-button{grid-column:1/-1;justify-self:start}}
        @media(max-width:560px){.sentinel-panel{padding:.9rem}.sentinel-account{grid-template-columns:minmax(0,1fr);gap:.7rem}.sentinel-account .sentinel-button{grid-column:auto;width:100%}.sentinel-intro{padding:1rem}.sentinel-search input{flex-basis:100%}.sentinel-search .sentinel-button{flex:1}.sentinel-section-nav{gap:.4rem}.sentinel-section-nav a{flex:1;justify-content:center}.sentinel-license-row{padding:.85rem}.sentinel-license-form{align-items:stretch}.sentinel-license-form label{flex:1 1 9rem}.sentinel-license-form .sentinel-button{width:100%}}
        @media(max-width:800px){.sentinel-license-row{grid-template-columns:1fr}}
        @media(max-width:700px){.sentinel-stats{grid-template-columns:1fr}.sentinel-health-grid{grid-template-columns:1fr}.sentinel-intro{display:block}.sentinel-event{grid-template-columns:1fr}.sentinel-event dl{grid-template-columns:1fr 1fr}}
    
        /* Founder directory: use the available desktop content width, not viewport width. */
        .sentinel-account > *{min-width:0}
        .sentinel-account{grid-template-columns:minmax(0,1.2fr) minmax(220px,1fr) auto;gap:1.25rem}
        .sentinel-account-users{overflow-wrap:anywhere;word-break:normal}
        .sentinel-account .sentinel-button{min-width:8.5rem;white-space:normal}
        @media(min-width:901px){
            .sentinel-wrap{width:100%;max-width:1600px}
            .sentinel-account{grid-template-columns:minmax(0,1.15fr) minmax(240px,.95fr) auto}
            .sentinel-account .sentinel-button{justify-self:end}
        }
        @media(max-width:900px){
            .sentinel-account{grid-template-columns:minmax(0,1fr) minmax(0,1fr)}
            .sentinel-account .sentinel-button{grid-column:1/-1;justify-self:start}
        }
        @media(max-width:560px){
            .sentinel-account{grid-template-columns:minmax(0,1fr);gap:.7rem}
            .sentinel-account .sentinel-button{grid-column:auto;width:100%}
        }
    </style>

    <div class="sentinel-wrap">
        <section class="sentinel-intro">
            <div>
                <div class="sentinel-kicker">Rosscore Labs · Founder control</div>
                <h2 class="sentinel-title">Founder operations and account oversight.</h2>
                <p class="sentinel-copy">Manage customer business accounts, inspect ownership and membership, administer licences, and review this installation's platform diagnostics. Cross-business visibility does not grant access to customer workspaces.</p>
            </div>
            <span class="sentinel-pill">Founder only</span>
        </section>

        <nav class="sentinel-section-nav" aria-label="Founder Control Centre sections">
            <a href="#platform-accounts">Business accounts</a>
            <a href="#licensing">Licensing</a>
            <a href="#platform-health">Platform health</a>
            <a href="#incident-patterns">Failure patterns</a>
        </nav>

        <section class="sentinel-panel" id="platform-accounts">
            <div class="sentinel-health-overall">
                <div>
                    <h3>Business account directory</h3>
                    <p class="sentinel-muted">Live business records, ordered by newest registration. Check the creation date and linked owner before treating an unfamiliar account as a real customer.</p>
                </div>
                <span class="sentinel-pill">{{ number_format($platformBusinesses->total()) }} matching accounts</span>
            </div>
            <form method="GET" action="{{ route('admin.sentinel') }}" class="sentinel-search">
                <input type="search" name="account_q" value="{{ $accountQuery }}" maxlength="120" placeholder="Search business, owner or member name/email" aria-label="Search platform business accounts">
                <button class="sentinel-button" type="submit">Find account</button>
                <a class="sentinel-clear" href="{{ route('admin.sentinel', ['section' => 'accounts']) }}">Clear</a>
            </form>
            <div class="sentinel-account-list">
                @forelse($platformBusinesses as $platformBusiness)
                    <article class="sentinel-account">
                        <div>
                            <strong>{{ $platformBusiness->name }}</strong>
                            @if($platformBusiness->email)
                                <div class="sentinel-muted sentinel-business-email">{{ $platformBusiness->email }}</div>
                            @endif
                            <div class="sentinel-account-created">Created {{ $platformBusiness->created_at?->format('d M Y · H:i') ?? 'Date unavailable' }}</div>
                            <div class="sentinel-account-meta">
                                <span class="sentinel-pill">{{ strtoupper($platformBusiness->status ?: 'unknown') }}</span>
                                <span>{{ $platformBusiness->users_count }} user(s)</span>
                                <span>{{ $platformBusiness->customers_count }} customer(s)</span>
                                <span>{{ $platformBusiness->events_count }} event(s)</span>
                            </div>
                        </div>
                        <div class="sentinel-account-users">
                            @php
                                $accountOwner = $platformBusiness->users->first(
                                    fn ($member) => ($member->pivot->role ?? null) === 'owner'
                                );
                            @endphp
                            @if($accountOwner)
                                <div><strong>Owner:</strong> {{ $accountOwner->name }}</div>
                                <div>{{ $accountOwner->email }}</div>
                            @else
                                <div><strong>Owner:</strong> Not identified</div>
                            @endif
                            @forelse($platformBusiness->users->where('id', '!=', $accountOwner?->id)->take(2) as $member)
                                <div>{{ $member->name }} · {{ ucfirst($member->pivot->role ?? 'member') }} · {{ $member->email }}</div>
                            @empty
                            @endforelse
                            @if($platformBusiness->users_count > 3)
                                <div>+ {{ $platformBusiness->users_count - 3 }} more</div>
                            @endif
                        </div>
                        <a class="sentinel-button" href="{{ route('admin.sentinel.businesses.show', $platformBusiness) }}">Inspect account</a>
                    </article>
                @empty
                    <div class="sentinel-empty">No platform business accounts match this search.</div>
                @endforelse
            </div>
            <div class="sentinel-license-pagination">{{ $platformBusinesses->links() }}</div>
            <p class="sentinel-muted sentinel-retention-note">This directory is read-only in this release. It does not expose customer records, financial data, passwords, authentication tokens or offline device secrets.</p>
        </section>

                <section class="sentinel-panel" id="licensing">
            <div class="sentinel-health-overall">
                <div>
                    <h3>Registered installations · licensing &amp; entitlements</h3>
                    <p class="sentinel-muted">Founder registry for customer installations. Add the business name and unique licensing reference supplied from the customer's Business Settings, then issue, renew or revoke offline tokens.</p>
                </div>
                <span class="sentinel-pill">{{ $licenceSigningConfigured ? 'Signing configured' : 'Signing not configured' }}</span>
            </div>

            @if(session('error'))
                <div class="sentinel-alert sentinel-alert-error" role="alert">{{ session('error') }}</div>
            @endif

            @if($issuedLicenseToken)
                <div class="sentinel-alert" role="status">
                    <strong>Signed licence {{ $issuedLicenseAction }} for {{ $issuedLicenseBusiness }}.</strong>
                    <p class="sentinel-muted">Copy this token and deliver it only to the matching business owner. It is shown once in this founder session; it is not written to the audit log.</p>
                    <textarea class="sentinel-token" readonly rows="4" aria-label="Signed licence token">{{ $issuedLicenseToken }}</textarea>
                </div>
            @endif

            @if(!$licenceSigningConfigured)
                <p class="sentinel-muted">To enable issuance, set <code>ZAZU_LICENSE_PRIVATE_KEY_FILE</code> to a base64 Ed25519 secret-key file outside the Zazu project. The browser never receives the key. The configured public key must match it.</p>
            @endif

            <div class="sentinel-license-register">
                <h4>Register a customer business</h4>
                <p class="sentinel-muted">The customer finds this UUID under Settings → Zazu licence. Do not use their numeric local Business ID; it is not globally unique.</p>
                <form method="POST" action="{{ route('admin.sentinel.licence-customers.store') }}" class="sentinel-license-register-form">
                    @csrf
                    <label>Business name
                        <input name="business_name" value="{{ old('business_name') }}" maxlength="255" required>
                    </label>
                    <label>Business licensing reference (UUID)
                        <input name="business_uuid" value="{{ old('business_uuid') }}" maxlength="36" required>
                    </label>
                    <label>Contact email (optional)
                        <input type="email" name="contact_email" value="{{ old('contact_email') }}" maxlength="255">
                    </label>
                    <button class="sentinel-button" type="submit">Register business</button>
                </form>
                @if($errors->any())
                    <p class="sentinel-muted" role="alert">{{ $errors->first() }}</p>
                @endif
            </div>

            <form method="GET" action="{{ route('admin.sentinel') }}" class="sentinel-search sentinel-license-search">
                <input type="search" name="business_q" value="{{ $businessQuery }}" maxlength="120" placeholder="Find customer by name, email or UUID" aria-label="Find licensing customer">
                <button class="sentinel-button" type="submit">Find customer</button>
                <a class="sentinel-clear" href="{{ route('admin.sentinel', ['section' => 'licensing']) }}">Clear</a>
            </form>

            <div class="sentinel-license-grid">
                @forelse($customers as $customer)
                    <article class="sentinel-license-row">
                        <div>
                            <strong>{{ $customer->business_name }}</strong>
                            @if($customer->contact_email)
                                <div class="sentinel-muted">{{ $customer->contact_email }}</div>
                            @endif
                            <div class="sentinel-license-meta">Business UUID: <code>{{ $customer->business_uuid }}</code></div>
                            @if($customer->last_license_id)
                                @php
                                    $licenceStatus = $customer->last_status === 'active' ? 'issued active' : $customer->last_status;
                                    if ($licenceStatus === 'active' && $customer->last_starts_at?->isFuture()) {
                                        $licenceStatus = 'not yet active';
                                    } elseif ($licenceStatus === 'active' && $customer->last_expires_at?->isPast()) {
                                        $licenceStatus = 'expired';
                                    }
                                @endphp
                                <div class="sentinel-health-facts sentinel-license-facts">
                                    <span class="sentinel-pill">{{ strtoupper($customer->last_plan) }}</span>
                                    <span class="sentinel-pill">{{ strtoupper($licenceStatus) }}</span>
                                    <span>Expires {{ $customer->last_expires_at?->format('d M Y H:i') ?? 'Never' }}</span>
                                </div>
                                <div class="sentinel-muted sentinel-license-meta">Last issued licence {{ $customer->last_license_id }}</div>
                            @else
                                <div class="sentinel-muted">No licence issued yet</div>
                            @endif
                        </div>
                        <div class="sentinel-license-actions">
                            <form method="POST" action="{{ route('admin.sentinel.licences.issue') }}" class="sentinel-license-form">
                                @csrf
                                <input type="hidden" name="platform_license_customer_id" value="{{ $customer->id }}">
                                <label>Plan
                                    <select name="plan" required>
                                        <option value="basic" @selected(($customer->last_plan ?? 'basic') === 'basic')>Basic</option>
                                        <option value="intermediate" @selected(($customer->last_plan ?? '') === 'intermediate')>Intermediate</option>
                                        <option value="advanced" @selected(($customer->last_plan ?? '') === 'advanced')>Advanced</option>
                                    </select>
                                </label>
                                <label>Expires at
                                    <input type="datetime-local" name="expires_at" value="{{ $customer->last_expires_at?->format('Y-m-d\TH:i') }}">
                                </label>
                                <label><span> </span><span><input type="checkbox" name="never_expires" value="1" @checked($customer->last_license_id && !$customer->last_expires_at)> Never expires</span></label>
                                <button class="sentinel-button" type="submit" {{ !$licenceSigningConfigured ? 'disabled' : '' }}>{{ $customer->last_license_id ? 'Issue renewal' : 'Issue licence' }}</button>
                            </form>
                            @if($customer->last_license_id && $customer->last_status === 'active')
                                <form method="POST" action="{{ route('admin.sentinel.licences.revoke', $customer) }}" class="sentinel-license-form">
                                    @csrf
                                    <span class="sentinel-muted">Revoke latest issued licence</span>
                                    <button class="sentinel-button" type="submit" {{ !$licenceSigningConfigured ? 'disabled' : '' }}>Revoke</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="sentinel-empty">No customer businesses registered yet.</div>
                @endforelse
            </div>
            <div class="sentinel-license-pagination">{{ $customers->links() }}</div>
            <p class="sentinel-muted sentinel-retention-note">The registry stores customer identity and current licence metadata, never signed tokens or private-key material. Issuance and revocation are logged without token contents. Revocation takes effect when the owner imports the signed revocation token into their offline installation.</p>
        </section>

        <section class="sentinel-stats" aria-label="Sentinel overview">
            <div class="sentinel-stat"><strong>{{ number_format($totalFingerprints) }}</strong><span>Failure fingerprints indexed</span></div>
            <div class="sentinel-stat"><strong>{{ number_format($totalOccurrences) }}</strong><span>Recorded occurrences in retained ledger</span></div>
            <div class="sentinel-stat"><strong>{{ $latestIncident ?? '—' }}</strong><span>Most recent recorded incident</span></div>
        </section>

        <section class="sentinel-panel" id="platform-health" aria-label="Sentinel diagnostic health">
            <div class="sentinel-health-overall">
                <div>
                    <h3>This installation's Sentinel health</h3>
                    <p class="sentinel-muted">Checks the local incident evidence store only. It does not report the health of remote customer installations.</p>
                </div>
                <span class="sentinel-pill">{{ strtoupper(str_replace('_', ' ', $health['status'])) }}</span>
            </div>
            <div class="sentinel-muted sentinel-retention-note">Checked {{ !empty($health['checked_at']) ? \Illuminate\Support\Carbon::parse($health['checked_at'])->timezone(config('app.timezone'))->format('j M Y, H:i') : 'Unknown' }}. This check does not query or modify business records.</div>
            <div class="sentinel-health-grid">
                @foreach($health['checks'] as $check)
                    <article class="sentinel-health-card sentinel-health-{{ $check['status'] }}">
                        <strong>{{ $check['label'] }}</strong>
                        <span class="sentinel-pill">{{ strtoupper(str_replace('_', ' ', $check['status'])) }}</span>
                        <p>{{ $check['message'] }}</p>
                        @if(isset($check['records']))
                            <div class="sentinel-health-facts">
                                <span>{{ number_format($check['records']) }} valid records</span>
                                <span>{{ number_format($check['malformed_lines']) }} unreadable lines</span>
                                <span>{{ number_format($check['size_bytes']) }} bytes</span>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section class="sentinel-panel" id="installation-backup" aria-label="Installation backup status">
            <div class="sentinel-health-overall">
                <div>
                    <h3>Installation backup</h3>
                    <p class="sentinel-muted">Latest full-installation archive, including every business and private file.</p>
                </div>
                <span class="sentinel-pill">{{ strtoupper($backupStatus['status']) }}</span>
            </div>
            @if($backupStatus['latest_at'])
                <p><strong>Last backup:</strong> {{ \Illuminate\Support\Carbon::parse($backupStatus['latest_at'])->timezone(config('app.timezone'))->format('j M Y, H:i') }} ({{ $backupStatus['age_hours'] }} hours ago)</p>
                @if($backupStatus['status'] === 'attention')
                    @if(($backupStatus['age_hours'] ?? null) !== null && $backupStatus['age_hours'] > 26)
                        <p class="sentinel-health-attention">Backup overdue. Check the Windows scheduled task and verify a fresh archive is created.</p>
                    @endif
                    <p class="sentinel-health-attention">{{ $backupStatus['message'] }}</p>
                @endif
            @else
                <p class="sentinel-health-failed">No scheduled backup has been recorded on this installation.</p>
                <p class="sentinel-health-attention">{{ $backupStatus['secondary_message'] }}</p>
            @endif
            <p class="sentinel-muted">Independent copy: {{ $backupStatus['secondary_message'] }}</p>
            <p class="sentinel-muted sentinel-retention-note">The daily task retains 14 local archives. Copy the latest archive to BitLocker-protected USB or an encrypted, access-controlled cloud vault; the runner verifies copy integrity but does not encrypt or verify destination encryption. A recent local archive alone does not protect against disk failure or replace the controlled restore drill.</p>
        </section>

        <section class="sentinel-panel" id="incident-patterns">
            <h3>Search failure patterns</h3>
            <form method="GET" action="{{ route('admin.sentinel') }}" class="sentinel-search">
                <input type="search" name="q" value="{{ $searchQuery }}" maxlength="120" placeholder="Fingerprint, error code, exception, failure class or source file" aria-label="Search incidents">
                <button class="sentinel-button" type="submit">Search</button>
                @if($searchQuery !== '' || $selectedFingerprint)
                    <a class="sentinel-clear" href="{{ route('admin.sentinel') }}">Clear filters</a>
                @endif
            </form>
        </section>

        <section class="sentinel-panel">
            <h3>Recurring failure patterns <span class="sentinel-muted">({{ count($summaries) }})</span></h3>
            @if(count($summaries))
                <div class="sentinel-table-wrap">
                    <table class="sentinel-table">
                        <thead><tr><th>Fingerprint</th><th>Error</th><th>Occurrences</th><th>Last seen</th><th>Failure / exception</th><th>Source</th></tr></thead>
                        <tbody>
                        @foreach($summaries as $item)
                            <tr>
                                <td><a href="{{ route('admin.sentinel', array_filter(['q' => $searchQuery, 'fingerprint' => $item['fingerprint']])) }}"><code>{{ $item['fingerprint'] }}</code></a></td>
                                <td><span class="sentinel-pill">{{ $item['error_code'] }}</span></td>
                                <td><strong>{{ number_format($item['occurrences']) }}</strong></td>
                                <td>{{ $item['last_seen'] }}</td>
                                <td>{{ $item['failure_class'] !== 'unknown' ? $item['failure_class'] : $item['exception'] }}</td>
                                <td>{{ $item['source_file'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="sentinel-empty">No incidents match this search. The console will populate as the ledger records failures.</div>
            @endif
        </section>

        @if($selectedFingerprint)
            <section class="sentinel-panel" id="incident-timeline">
                <h3>Occurrence timeline <span class="sentinel-muted">· {{ $selectedFingerprint }}</span></h3>
                @if(count($timeline))
                    <div class="sentinel-timeline">
                        @foreach($timeline as $event)
                            <article class="sentinel-event">
                                <div>
                                    <time>{{ $event['timestamp'] }}</time>
                                    <div><code class="sentinel-code">{{ $event['request_id'] }}</code></div>
                                    @if($event['error_code'] === 'DB-001')
                                        <a class="sentinel-download" href="{{ URL::temporarySignedRoute('support.incidents.show', now()->addMinutes(15), ['requestId' => $event['request_id']]) }}">Download safe DB report</a>
                                    @endif
                                </div>
                                <dl>
                                    <div><dt>Error</dt><dd>{{ $event['error_code'] }}</dd></div>
                                    <div><dt>Source</dt><dd>{{ $event['source_file'] }}</dd></div>
                                    <div><dt>Component</dt><dd>{{ $event['component'] }}</dd></div>
                                    <div><dt>Exception</dt><dd>{{ $event['exception'] }}</dd></div>
                                    <div><dt>Database driver / state</dt><dd>{{ $event['database_driver'] }} / {{ $event['database_sqlstate'] }}</dd></div>
                                    <div><dt>DB operation / class</dt><dd>{{ $event['database_operation'] }} / {{ $event['database_failure_class'] }}</dd></div>
                                </dl>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="sentinel-empty">No retained occurrences were found for this fingerprint.</div>
                @endif
            </section>
        @endif

        <p class="sentinel-muted sentinel-retention-note">Retention is limited to the active and previous ledger segments. Incident messages, SQL statements, bindings, customer data and stack traces are intentionally excluded.</p>
    </div>
</x-app-layout>
