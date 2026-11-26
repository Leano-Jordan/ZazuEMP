@extends('layouts.public-trust')

@section('title', 'Privacy review')
@section('description', 'Pre-publication privacy information for Zazu EMP. This is a review copy, not an effective privacy notice.')

@section('content')
    <p class="eyebrow">Privacy and personal information</p>
    <h1>Privacy matters before real customer data enters Zazu.</h1>
    <p class="lede">This page explains the intended approach to personal information and the controls still being prepared. It is deliberately labelled as a review copy.</p>

    <div class="notice" role="note">
        <strong>PRE-PUBLICATION REVIEW COPY · 26 NOVEMBER 2026</strong>
        <p>This is not yet an effective privacy notice or a completed legal statement for a live production service. Deployment providers, privacy contact arrangements, Information Officer registration and final legal review are still outstanding. Do not rely on this page as confirmation that Zazu is ready to process real customer personal information.</p>
    </div>

    <div class="content-grid">
        <article class="prose">
            <h2>Who currently operates Zazu</h2>
            <p>Zazu EMP is currently developed and operated by Isaac Junior Lehlogonolo Maluleka as an individual. Rosscore Labs is a future business identity; it is not yet registered and is not the current contracting provider.</p>

            <h2>Information the product may handle</h2>
            <p>Depending on the enabled features and the customer's use, Zazu may handle account and workspace details; customer names and contact information; event dates, addresses and operational notes; quotes, invoices and payment records; business documents, receipts, photographs and attachments; and technical, audit and security records.</p>
            <p>Zazu is not designed as a medical-record service. Users should not enter identity-document numbers, bank-account details, children's information, diagnoses or unrelated sensitive information into general event notes. If a minimum dietary or allergy instruction is necessary for an event, access should be limited to people who need it for that event.</p>

            <h2>Why information may be used</h2>
            <p>The intended purposes include providing event and business-management workflows, maintaining accounts and permissions, linking operational records, generating business documents and reports, protecting the application, investigating incidents, supporting recovery and meeting applicable legal obligations.</p>

            <h2>Customer and provider responsibilities</h2>
            <p>A business using Zazu will ordinarily decide why it records personal information about its own customers and staff. For a future hosted deployment, the provider may act as an operator for customer-managed information and an appropriate written operator agreement will be required where applicable. For local/offline installations, the customer ordinarily controls the device, local environment and backups. The actual role must be assessed against the deployment and processing activity.</p>

            <h2>Storage, providers and retention</h2>
            <p>No production hosting, cloud storage, analytics, payment, messaging, monitoring or external support provider is currently provisioned for Zazu. The final privacy information must identify actual providers and data locations before hosted personal information is processed.</p>
            <p>A retention and disposal schedule exists as a provisional recommendation. It has not yet been approved as final policy or fully enforced by the application. Zazu must not promise automatic deletion until the relevant controls are implemented and tested.</p>

            <h2>Requests and questions</h2>
            <p>Questions or privacy requests may be directed to the provisional owner contact at <a href="mailto:rosscorelabs@outlook.com">rosscorelabs@outlook.com</a>. This is not yet a dedicated privacy mailbox. The founder has not completed Information Officer registration; no registration is claimed on this page.</p>

            <h2>What must happen before publication</h2>
            <ul>
                <li>Confirm the actual first deployment and every provider that processes personal information.</li>
                <li>Establish and test a dedicated privacy contact channel.</li>
                <li>Confirm the Information Officer registration route and complete the required formalities.</li>
                <li>Complete legal review and reconcile the notice with implemented data handling, retention and customer agreements.</li>
            </ul>
            <p><a href="{{ route('public.contact') }}">Contact the project owner</a> · <a href="{{ route('public.terms') }}">Read the Terms review copy</a></p>
        </article>
        <aside>
            <section class="side-card">
                <h2>Current privacy status</h2>
                <p>Preparation in progress. No completed Information Officer registration, dedicated privacy mailbox or production processor is claimed.</p>
            </section>
            <section class="side-card">
                <h2>Source document</h2>
                <p>This summary is based on the internal pre-launch privacy notice draft and current Founder decisions. It will be revised against the actual deployment before becoming effective.</p>
            </section>
        </aside>
    </div>
@endsection
