<x-page title="Datenschutzerklärung">
    <h1 class="text-[26px] leading-tight font-black">Datenschutzerklärung</h1>

    @php($legal = config('legal'))

    <div class="mt-6 space-y-6 text-sm leading-relaxed">
        <p class="border-2 border-ink bg-sun p-3 text-xs font-bold">
            Entwurf – vor dem Livegang prüfen und an die tatsächliche Nutzung anpassen.
        </p>

        <section>
            <h2 class="text-base font-extrabold">1. Verantwortliche Stelle</h2>
            <p class="mt-2">
                {{ $legal['name'] ?: '[LEGAL_NAME]' }}, {{ $legal['street'] ?: '[LEGAL_STREET]' }},
                {{ $legal['city'] ?: '[LEGAL_CITY]' }}, {{ $legal['country'] }}<br>
                E-Mail: {{ $legal['email'] ?: '[LEGAL_EMAIL]' }}
            </p>
        </section>

        <section>
            <h2 class="text-base font-extrabold">2. Zweck der App</h2>
            <p class="mt-2">
                HandInHand dient ausschließlich dem privaten Austausch zwischen einem Kind, seinen Eltern und seiner Tante:
                Videonachrichten, Sprachnachrichten sowie ein Bilderbuch aus Fotos mit eingesprochener Stimme.
                Die App ist nicht öffentlich und wird nur von einem kleinen Familienkreis genutzt.
            </p>
        </section>

        <section>
            <h2 class="text-base font-extrabold">3. Welche Daten verarbeitet werden</h2>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>Videos, die das Kind bzw. die Eltern über die Kamera der App aufnehmen und an die Tante senden.</li>
                <li>Videos, Sprachaufnahmen und Fotos, die die Tante hochlädt oder aufnimmt.</li>
                <li>Für den Admin-Zugang: Name, E-Mail-Adresse und ein verschlüsselt gespeichertes Passwort.</li>
                <li>Technisch notwendige Server-Protokolle (IP-Adresse, Zeitpunkt, aufgerufene Seite) beim Hosting-Anbieter.</li>
            </ul>
            <p class="mt-2">
                Kamera und Mikrofon werden nur nach ausdrücklicher Freigabe im Browser genutzt und nur während einer Aufnahme.
                Es erfolgt keine Analyse, kein Tracking und keine Weitergabe an Werbe- oder Analysedienste.
            </p>
        </section>

        <section>
            <h2 class="text-base font-extrabold">4. Cookies</h2>
            <p class="mt-2">
                Es werden ausschließlich technisch notwendige Cookies gesetzt: ein Sitzungs-Cookie für die Anmeldung der Tante,
                ein Sicherheits-Cookie (CSRF-Schutz) und – falls aktiviert – ein Cookie, das ein Gerät für den Kinderbereich freischaltet.
                Eine Einwilligung ist dafür nicht erforderlich.
            </p>
        </section>

        <section>
            <h2 class="text-base font-extrabold">5. Schriftarten</h2>
            <p class="mt-2">
                Die verwendete Schrift (Archivo) wird direkt von diesem Server ausgeliefert.
                Es findet keine Verbindung zu Google Fonts oder anderen Drittanbietern statt.
            </p>
        </section>

        <section>
            <h2 class="text-base font-extrabold">6. Hosting und Speicherung</h2>
            <p class="mt-2">
                Die App wird bei netcup GmbH (Deutschland) gehostet. Medien werden nicht öffentlich, sondern nur über geschützte Adressen ausgeliefert.
                Videos und Bilder bleiben so lange gespeichert, bis sie im Admin-Bereich gelöscht werden.
            </p>
        </section>

        <section>
            <h2 class="text-base font-extrabold">7. Ihre Rechte</h2>
            <p class="mt-2">
                Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung und Datenübertragbarkeit
                sowie das Recht, sich bei der zuständigen Datenschutzbehörde zu beschweren (in Österreich: Datenschutzbehörde, dsb.gv.at).
                Wenden Sie sich dazu an die oben genannte E-Mail-Adresse.
            </p>
        </section>
    </div>
</x-page>
