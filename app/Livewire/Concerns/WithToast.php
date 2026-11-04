<?php

namespace App\Livewire\Concerns;

/**
 * Grüne Hinweisleiste (Toast) wie im Mockup. Übernimmt auch Hinweise,
 * die per session()->flash('toast') über eine Weiterleitung kommen.
 */
trait WithToast
{
    public ?string $toast = null;

    public function mountWithToast(): void
    {
        $this->toast = session('toast');
    }

    protected function toast(string $message): void
    {
        $this->toast = $message;
    }
}
