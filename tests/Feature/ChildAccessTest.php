<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureChildAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_child_area_is_open_when_no_token_is_configured(): void
    {
        config(['handinhand.child_token' => null]);

        $this->get(route('child.dashboard'))->assertOk();
    }

    public function test_child_area_is_locked_without_the_device_cookie(): void
    {
        config(['handinhand.child_token' => 'geheimer-token']);

        $this->get(route('child.dashboard'))->assertForbidden()->assertSee('noch nicht freigeschaltet');
        $this->get(route('child.mailbox'))->assertForbidden();
        $this->get(route('media.profile'))->assertForbidden();
    }

    public function test_opening_the_access_link_unlocks_the_device(): void
    {
        config(['handinhand.child_token' => 'geheimer-token']);

        $this->get(route('child.access', 'geheimer-token'))
            ->assertRedirect(route('child.dashboard'))
            ->assertCookie(EnsureChildAccess::COOKIE);

        $this->withCookie(EnsureChildAccess::COOKIE, EnsureChildAccess::cookieValue('geheimer-token'))
            ->get(route('child.dashboard'))
            ->assertOk();
    }

    public function test_wrong_token_is_a_404_and_sets_no_cookie(): void
    {
        config(['handinhand.child_token' => 'geheimer-token']);

        $this->get(route('child.access', 'falsch'))->assertNotFound()->assertCookieMissing(EnsureChildAccess::COOKIE);
    }

    public function test_forged_cookie_value_does_not_unlock(): void
    {
        config(['handinhand.child_token' => 'geheimer-token']);

        $this->withCookie(EnsureChildAccess::COOKIE, 'irgendwas')
            ->get(route('child.dashboard'))
            ->assertForbidden();
    }

    public function test_logged_in_admin_always_has_access(): void
    {
        config(['handinhand.child_token' => 'geheimer-token']);

        $this->actingAs(User::factory()->create())->get(route('child.dashboard'))->assertOk();
    }
}
