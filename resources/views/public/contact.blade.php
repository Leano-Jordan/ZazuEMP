@extends('layouts.public-trust')

@section('title', 'Contact')
@section('description', 'Contact the independent project owner behind Zazu EMP.')

@section('content')
    <p class="eyebrow">Contact</p>
    <h1>A direct line to the person building Zazu.</h1>
    <p class="lede">Zazu is currently a founder-operated project. Enquiries go to the owner, not a call centre or an unstaffed support desk.</p>

    <div class="content-grid">
        <article class="prose">
            <section class="content-card">
                <h2 style="margin-top:0">General enquiries</h2>
                <p><a href="mailto:rosscorelabs@outlook.com">rosscorelabs@outlook.com</a></p>
                <p>This is the founder's current provisional contact address. It is not a dedicated Zazu support desk or privacy mailbox. A dedicated privacy channel has not yet been established.</p>
            </section>

            <h2>What to include</h2>
            <p>For a product enquiry, briefly describe your business, the event workflow you want to improve and whether you are interested in a future controlled pilot.</p>
            <p>For a security concern, ask for a private reporting conversation. Do not send passwords, access tokens, payment card details or unnecessary personal information.</p>

            <h2>Current availability</h2>
            <p>Zazu is not generally available and is not accepting paid subscriptions or public commercial orders. A private, non-commercial pilot is possible only by explicit arrangement and after the relevant data-handling safeguards are confirmed.</p>
            <p>No response-time, uptime or support-hours commitment is currently offered.</p>
        </article>
        <aside>
            <section class="side-card">
                <h2>Project owner</h2>
                <p>Isaac Junior Lehlogonolo Maluleka</p>
                <p>Independent developer and current Zazu operator, South Africa.</p>
            </section>
            <section class="side-card">
                <h2>Future business identity</h2>
                <p>Rosscore Labs is planned, but is not yet registered and is not currently the contracting provider.</p>
            </section>
        </aside>
    </div>
@endsection
