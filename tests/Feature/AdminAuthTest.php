<?php

namespace Tests\Feature;

use App\Livewire\Admin\Login;
use App\Livewire\Admin\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\MakesFakeMedia;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use MakesFakeMedia, RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_every_admin_page(): void
    {
        foreach (['admin.home', 'admin.messages', 'admin.messages.create', 'admin.settings'] as $name) {
            $this->get(route($name))->assertRedirect(route('admin.login'));
        }

        $this->get('/admin/buch/1')->assertRedirect(route('admin.login'));
        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
    }

    public function test_login_page_has_no_registration(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee('Anmelden');
        $this->get('/register')->assertNotFound();
    }

    public function test_aunt_can_log_in_with_correct_credentials(): void
    {
        User::factory()->create(['email' => 'tante@example.test', 'password' => 'ein-langes-passwort']);

        Livewire::test(Login::class)
            ->set('email', 'tante@example.test')
            ->set('password', 'ein-langes-passwort')
            ->call('login')
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticated();
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'tante@example.test', 'password' => 'ein-langes-passwort']);

        Livewire::test(Login::class)
            ->set('email', 'tante@example.test')
            ->set('password', 'falsch')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        User::factory()->create(['email' => 'tante@example.test', 'password' => 'ein-langes-passwort']);

        $component = Livewire::test(Login::class)->set('email', 'tante@example.test')->set('password', 'falsch');

        foreach (range(1, 5) as $ignored) {
            $component->call('login');
        }

        // Selbst das richtige Passwort wird jetzt gesperrt.
        $component->set('password', 'ein-langes-passwort')->call('login')->assertHasErrors('email');
        $this->assertGuest();
    }

    public function test_logged_in_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.login'))->assertRedirect(route('admin.home'));
    }

    public function test_logout_ends_the_session(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.logout'))->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_aunt_can_change_her_password(): void
    {
        $user = User::factory()->create(['password' => 'altes-passwort-1']);

        Livewire::actingAs($user)->test(Settings::class)
            ->set('current_password', 'altes-passwort-1')
            ->set('password', 'neues-passwort-2')
            ->set('password_confirmation', 'neues-passwort-2')
            ->call('savePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('neues-passwort-2', $user->fresh()->password));
    }

    public function test_password_change_needs_the_current_password_and_min_length(): void
    {
        $user = User::factory()->create(['password' => 'altes-passwort-1']);

        Livewire::actingAs($user)->test(Settings::class)
            ->set('current_password', 'falsch')
            ->set('password', 'kurz')
            ->set('password_confirmation', 'kurz')
            ->call('savePassword')
            ->assertHasErrors(['current_password', 'password']);

        $this->assertTrue(Hash::check('altes-passwort-1', $user->fresh()->password));
    }

    public function test_aunt_can_change_name_and_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['name' => 'Alt']);

        Livewire::actingAs($user)->test(Settings::class)
            ->set('name', 'Tante Carmen')
            ->set('photo', $this->fakeImage('ich.jpg', 2400, 1800))
            ->call('saveProfile')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('Tante Carmen', $user->name);
        Storage::disk('local')->assertExists($user->photo_path);
    }

    public function test_admin_command_creates_and_updates_the_account(): void
    {
        $this->artisan('handinhand:admin', ['--email' => 'tante@example.test', '--password' => 'langes-passwort-1', '--name' => 'Tante'])
            ->assertSuccessful();

        $this->assertTrue(Hash::check('langes-passwort-1', User::query()->where('email', 'tante@example.test')->value('password')));

        $this->artisan('handinhand:admin', ['--email' => 'tante@example.test', '--password' => 'anderes-passwort-2', '--name' => 'Tante'])
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_command_rejects_short_passwords(): void
    {
        $this->artisan('handinhand:admin', ['--email' => 'tante@example.test', '--password' => 'kurz'])->assertFailed();
    }
}
