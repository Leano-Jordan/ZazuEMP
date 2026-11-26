@extends('layouts.public-trust')

@section('title', 'Terms review')
@section('description', 'Pre-publication terms information for Zazu EMP. This is a review copy, not a binding customer agreement.')

@section('content')
    <p class="eyebrow">Service terms</p>
    <h1>Clear boundaries. No surprise commitments.</h1>
    <p class="lede">This page records the intended service boundaries while Zazu is still being prepared for release.</p>

    <div class="notice" role="note">
        <strong>PRE-PUBLICATION REVIEW COPY · 26 NOVEMBER 2026</strong>
        <p>This page is not a binding contract, commercial offer or effective customer agreement. Zazu is not currently launching commercially. Final terms require a confirmed release scope, deployment model, data-processing arrangements and legal review.</p>
    </div>

    <div class="content-grid">
        <article class="prose">
            <h2>What Zazu is</h2>
            <p>Zazu EMP is an event management platform intended to connect event and customer records with planning, service options, quotes, purchasing, preparation, resources, costs and financial records. Available functionality is limited to the workflows included in the accepted release.</p>

            <h2>Current availability</h2>
            <p>Zazu is in engineering and release-readiness work. It is not generally available, no paid subscriptions are being accepted, and no public pricing or uptime commitment is offered. A controlled, non-commercial private pilot may occur only by explicit arrangement and authorisation.</p>

            <h2>Customer information</h2>
            <p>Customers retain ownership of the business records and content they create or upload. A future agreement must describe the limited permissions needed to operate, store, secure, back up and support that content. The applicable privacy and operator arrangements must be completed before real customer personal information is processed in a pilot or hosted deployment.</p>

            <h2>Offline operation and availability</h2>
            <p>Some workflows are designed to continue offline on a trusted device. Offline support does not mean every feature, every route or continuous multi-device synchronisation is available without a connection. The actual offline capability contract and installation requirements govern each release.</p>
            <p>No uninterrupted service, 24/7 support, guaranteed response time or particular uptime level is promised.</p>

            <h2>Accounts and security</h2>
            <p>Users will be expected to protect their credentials and devices, grant access only to authorised people, use roles appropriately and report suspected compromise promptly. The final terms will describe the provider's security and suspension responsibilities against the actual deployed service.</p>

            <h2>Ownership and provider identity</h2>
            <p>The current software and intellectual property are personally owned by the founder, who operates the project independently. Rosscore Labs is a planned future business identity and is not yet registered or the current contracting provider.</p>

            <h2>Before these terms can take effect</h2>
            <ul>
                <li>Complete the V1 release and deployment acceptance gates.</li>
                <li>Confirm the final service scope, commercial model and any checkout or payment process.</li>
                <li>Complete the privacy notice and any required operator/data-processing agreement.</li>
                <li>Obtain legal review and publish a dated effective version for explicit customer acceptance.</li>
            </ul>
            <p><a href="{{ route('public.privacy') }}">Read the Privacy review copy</a> · <a href="{{ route('public.contact') }}">Contact the project owner</a></p>
        </article>
        <aside>
            <section class="side-card">
                <h2>Commercial status</h2>
                <p>No public pricing, subscriptions, ads or paid pilot are being offered.</p>
            </section>
            <section class="side-card">
                <h2>Document status</h2>
                <p>Review copy only. No effective date or customer acceptance is implied.</p>
            </section>
        </aside>
    </div>
@endsection
