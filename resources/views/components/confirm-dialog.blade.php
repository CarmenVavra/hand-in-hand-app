{{--
    Eigener Bestätigungsdialog (statt window.confirm).
    Öffnen:  $dispatch('confirm-dialog', { title, message, confirmLabel, onConfirm: () => $wire.delete(1) })
--}}
<div
    x-data="confirmDialog"
    x-on:confirm-dialog.window="show($event.detail)"
    x-on:keydown.escape.window="open && close()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-ink/70 p-5"
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="confirm-title"
    aria-describedby="confirm-message"
>
    <div class="w-full max-w-sm border-2 border-ink bg-white shadow-[8px_8px_0_#201e1d]" x-on:click.outside="close()">
        <div class="border-b-2 border-ink bg-sun px-5 py-3">
            <h2 id="confirm-title" class="text-lg font-black" x-text="title"></h2>
        </div>
        <p id="confirm-message" class="px-5 py-4 text-base font-semibold" x-text="message"></p>
        <div class="flex gap-2 border-t-2 border-ink p-3">
            <button type="button" x-ref="cancel" x-on:click="close()" class="btn btn-plain flex-1">Abbrechen</button>
            <button type="button" x-on:click="confirm()" class="btn btn-primary flex-1" x-text="confirmLabel"></button>
        </div>
    </div>
</div>
