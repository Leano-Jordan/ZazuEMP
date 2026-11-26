@extends('layouts.public-trust')

@section('title', 'About Zazu')
@section('description', 'Who builds Zazu EMP and what the South African event management platform is designed to do.')

@section('content')
    <p class="eyebrow">About the product</p>
    <h1>Run every job. Control every cost. Keep working.</h1>
    <p class="lede">Zazu EMP is an event management platform designed to connect the work behind an event—from customer and job records through planning, resources, quotes and financial tracking.</p>

    <div class="content-grid">
        <article class="prose">
            <h2>Built around the event</h2>
            <p>Zazu is built around the event, not around one supplier category. Businesses can configure capabilities for services such as catering, equipment hire, sound and AV, décor, rentals, baking and photography. Those are supported business use cases, not limits on the kinds of events the platform is intended to manage.</p>
            <p>The product brings connected operational records into one workspace: customers, events and jobs, requirements, service options, quotes, purchasing, preparation, resources, attachments, costs, invoices, payments and reporting. The exact workflows available depend on the release that has passed acceptance.</p>

            <h2>Designed for South African operating conditions</h2>
            <p>Zazu is being developed with South African business workflows and unreliable connectivity in mind. The product direction includes local currency and tax handling, and offline-first operation for supported workflows. Offline capability is not a promise that every screen or every multi-device workflow works without a connection; those boundaries are documented and tested as the product develops.</p>

            <h2>Who builds Zazu</h2>
            <p>Zazu is currently developed and operated independently by its founder, Isaac Junior Lehlogonolo Maluleka. It is a solo project: there is no larger team, outsourced support desk or hidden operating company behind the product.</p>
            <p>Rosscore Labs is the intended future business identity, but it is not yet registered and is not the current owner or contracting provider. The founder personally owns the current Zazu software and intellectual property.</p>

            <h2>Where the product stands</h2>
            <p>Zazu is in active engineering and release-readiness work. It is not being offered as a generally available commercial service. A controlled, non-commercial private pilot may take place only when the founder authorises it and the relevant privacy and data-handling arrangements are ready.</p>
        </article>
        <aside>
            <section class="side-card">
                <h2>Product identity</h2>
                <p><strong>Zazu EMP</strong><br>Event management platform</p>
                <p><strong>Current operator</strong><br>Independent, founder-operated project</p>
                <p><strong>Based in</strong><br>South Africa</p>
            </section>
            <section class="side-card">
                <h2>Current stage</h2>
                <p>Engineering hardening and release acceptance. No public beta, paid subscriptions or uptime commitment.</p>
                <a href="{{ route('public.whats-new') }}">Read project updates →</a>
            </section>
        </aside>
    </div>
@endsection
