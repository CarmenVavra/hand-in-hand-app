<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithToast;
use App\Services\ImageOptimizer;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts::admin')]
#[Title('Einstellungen')]
class Settings extends Component
{
    use WithFileUploads, WithToast;

    public string $name = '';

    /** @var TemporaryUploadedFile|null */
    public $photo;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
    }

    public function saveProfile(ImageOptimizer $images): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:60'],
            'photo' => Media::imageRules(required: false),
        ]);

        $user = Auth::user();
        $user->name = $this->name;

        if ($this->photo) {
            Media::deleteFiles($user->photo_path);
            $user->photo_path = $images->store($this->photo, 'profile');
        }

        $user->save();
        $this->reset('photo');
        $this->toast('Gespeichert.');
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:10', 'confirmed', 'different:current_password'],
        ]);

        Auth::user()->update(['password' => $this->password]);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->toast('Das Passwort wurde geändert.');
    }

    public function render(): View
    {
        $token = config('handinhand.child_token');

        return view('livewire.admin.settings', [
            'user' => Auth::user(),
            'childLink' => $token ? route('child.access', ['token' => $token]) : null,
        ]);
    }
}
