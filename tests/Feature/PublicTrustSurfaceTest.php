<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicTrustSurfaceTest extends TestCase
{
    public function test_public_trust_pages_are_available_without_authentication(): void
    {
        $pages = [
            'public.about' => 'Who builds Zazu',
            'public.security' => 'Security is part of the product',
            'public.contact' => 'A direct line to the person building Zazu',
            'public.whats-new' => "What's changing in Zazu",
            'public.privacy' => 'Privacy matters before real customer data enters Zazu',
            'public.terms' => 'Clear boundaries. No surprise commitments.',
        ];

        foreach ($pages as $route => $heading) {
            $response = $this->get(route($route));

            $response->assertOk();
            $response->assertSee($heading, false);
        }
    }

    public function test_privacy_and_terms_pages_are_explicitly_review_copies_not_effective_contracts(): void
    {
        $this->get(route('public.privacy'))
            ->assertOk()
            ->assertSee('PRE-PUBLICATION REVIEW COPY')
            ->assertSee('not yet an effective privacy notice');

        $this->get(route('public.terms'))
            ->assertOk()
            ->assertSee('PRE-PUBLICATION REVIEW COPY')
            ->assertSee('not a binding contract');
    }

    public function test_public_contact_and_ownership_are_transparent_about_current_status(): void
    {
        $this->get(route('public.contact'))
            ->assertOk()
            ->assertSee('rosscorelabs@outlook.com')
            ->assertSee('provisional contact address')
            ->assertSee('not a dedicated Zazu support desk');

        $this->get(route('public.about'))
            ->assertOk()
            ->assertSee('Isaac Junior Lehlogonolo Maluleka')
            ->assertSee('Rosscore Labs is the intended future business identity')
            ->assertSee('not yet registered')
            ->assertSee('Name clearance and any trade-mark filing remain unresolved');
    }

    public function test_landing_page_links_to_the_public_trust_surface(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee(route('public.about'), false)
            ->assertSee(route('public.security'), false)
            ->assertSee(route('public.contact'), false)
            ->assertSee(route('public.privacy'), false)
            ->assertSee(route('public.terms'), false);
    }
}
