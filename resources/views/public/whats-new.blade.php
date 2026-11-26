@extends('layouts.public-trust')

@section('title', "What's new")
@section('description', 'Dated engineering and product updates for Zazu EMP.')

@section('content')
    <p class="eyebrow">Project updates</p>
    <h1>What's changing in Zazu.</h1>
    <p class="lede">A plain-language record of meaningful product and engineering work. These notes do not mean every change is already available in an installed release.</p>

    <div class="content-grid">
        <article class="prose">
            <section class="update">
                <time datetime="2026-11-26">26 November 2026 · In progress</time>
                <h2>Navigation reliability and public trust foundation</h2>
                <p>We are correcting a navigation fallback that could show the cached Dashboard when a destination page took too long to load. The service-worker navigation window has been extended and regression coverage added for slow-but-healthy routes and the collapsed desktop navigation rail. This work remains subject to automated acceptance before it is treated as release-ready.</p>
                <p>We are also building public information about product ownership, security practices, privacy, terms and project contact details. Privacy and Terms remain review copies until the deployment and legal publication requirements are confirmed.</p>
            </section>
            <section class="update">
                <time datetime="2026-11-26">26 November 2026 · Engineering update</time>
                <h2>Authentication and device security hardening</h2>
                <p>Recent engineering work strengthened phone pairing, session transitions and device credential handling. The adversarial authentication review remains open; this is an ongoing security programme, not a claim that the review is complete.</p>
            </section>
            <section class="update">
                <time datetime="2026-11-25">25 November 2026 · Product foundation</time>
                <h2>Clearer event-management identity and offline navigation</h2>
                <p>Zazu's product definition was aligned around event management rather than a single service category. Offline workspace navigation and related regression coverage were also refined. Supported offline workflows remain narrower than the complete online application.</p>
            </section>
        </article>
        <aside>
            <section class="side-card">
                <h2>Release posture</h2>
                <p>Zazu is in engineering hardening and acceptance. These are project updates, not release notes for a generally available service.</p>
            </section>
            <section class="side-card">
                <h2>No public status claim</h2>
                <p>A live uptime/status page will be added only when independent monitoring is operating and its reporting boundaries are defined.</p>
            </section>
        </aside>
    </div>
@endsection
