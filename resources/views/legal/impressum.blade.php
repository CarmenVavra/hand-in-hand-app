<x-page title="Impressum">
    <h1 class="text-[26px] leading-tight font-black">Impressum</h1>

    @php($legal = config('legal'))

    <div class="mt-6 space-y-6 text-sm leading-relaxed">
        <section>
            <h2 class="text-base font-extrabold">Angaben gemäß § 5 ECG / § 5 TMG</h2>
            <p class="mt-2">
                {{ $legal['name'] ?: '[LEGAL_NAME – Name / Firma]' }}<br>
                {{ $legal['city'] ?: '[LEGAL_CITY – Ort]' }}<br>
                {{ $legal['country'] }}
            </p>
        </section>

        <section>
            <h2 class="text-base font-extrabold">Kontakt</h2>
            <p class="mt-2">
                E-Mail: {{ $legal['email'] ?: '[LEGAL_EMAIL]' }}
            </p>
        </section>

        @if ($legal['vat_id'])
            <section>
                <h2 class="text-base font-extrabold">Umsatzsteuer-Identifikationsnummer</h2>
                <p class="mt-2">{{ $legal['vat_id'] }}</p>
            </section>
        @endif

        <p class="text-xs text-mute">
            HandInHand ist eine private, nicht öffentliche App für den Austausch von Videos und Bilderbüchern innerhalb der Familie.
        </p>
    </div>
</x-page>
