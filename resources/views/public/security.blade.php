@extends('layouts.public-trust')

@section('title', 'Security')
@section('description', 'Zazu EMP security practices, limitations and vulnerability reporting guidance.')

@section('content')
    <p class="eyebrow">Security and responsible disclosure</p>
    <h1>Security is part of the product, not a launch badge.</h1>
    <p class="lede">Zazu is being hardened before commercial release. This page describes the current approach and its limits; it is not a certification or a guarantee that vulnerabilities do not exist.</p>

    <div class="content-grid">
        <article class="prose">
            <h2>Current security approach</h2>
            <ul>
                <li>Authentication and session protections are part of the application security baseline.</li>
                <li>Workspace access is designed around business context, roles and permission checks.</li>
                <li>Offline device access and synchronisation are treated as security boundaries, not simply convenience features.</li>
                <li>Security-relevant activity and incidents have application-level recording and investigation paths.</li>
                <li>Changes are expected to be supported by regression tests and evidence, not by claims alone.</li>
            </ul>

            <h2>Known limits and honest status</h2>
            <p>Security hardening and adversarial authentication review are ongoing. The project does not currently claim an independent penetration test, external security certification, formal compliance certification or a guaranteed response time.</p>
            <p>Security controls must be assessed against the actual installed release and deployment. A local installation, a future hosted deployment and a customer's own device/network have different responsibilities and risks.</p>

            <h2>Report a vulnerability</h2>
            <p>Please do not publish sensitive vulnerability details in a public issue or social post. Contact the project owner privately first and include the affected component, reproducible conditions, potential impact and supporting evidence where safe. Do not include passwords, live tokens or unnecessary personal information.</p>
            <p>The project owner coordinates disclosure and remediation based on severity, exploitability and affected users. Do not perform intrusive testing against systems you do not own or have explicit authorisation to test.</p>

            <h2>Privacy and security incidents</h2>
            <p>If you believe personal information has been exposed, use the contact channel on the <a href="{{ route('public.contact') }}">Contact page</a> and avoid sending sensitive information in the first message. The current contact route is provisional and is not a dedicated security operations centre.</p>
        </article>
        <aside>
            <section class="side-card">
                <h2>Disclosure route</h2>
                <p>Private communication with the project owner. Do not post exploit details publicly.</p>
                <a href="{{ route('public.contact') }}">Contact the project owner →</a>
            </section>
            <section class="side-card">
                <h2>Security status</h2>
                <span class="status-line"><span class="status-dot" aria-hidden="true"></span> Active hardening</span>
                <p>Independent testing and certification are not claimed.</p>
            </section>
        </aside>
    </div>
@endsection
