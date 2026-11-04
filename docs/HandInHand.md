# HandInHand – Projektnotizen

Private PWA (Laravel 12 · Livewire 4 · Alpine.js · Tailwind 4) für ein zweijähriges Kind und ihre Tante.
Grundlage: `Konzept.pdf` und `UI_Mockups/design_handoff_handinhand/`.

## Starten (lokal)

```bash
start.bat        # PHP-Server (8600) + Vite (5173), öffnet http://localhost:8600/home
stop.bat         # beendet beides
start.bat lan    # auch im WLAN erreichbar
```

| Adresse | Zweck |
|---|---|
| `/home` | Kinder-Hauptmenü (3 Kacheln) |
| `/home/nachricht` | Selfie-Video aufnehmen → an Tante |
| `/home/postkasten` | Videos von Tante (Vollbild-Player) |
| `/home/bilderbuch/{id}` | Bilderbuch (Foto + Stimme) |
| `/admin` | Bücher · Postkasten · Einstellungen (Login nötig) |

Kamera/Mikrofon funktionieren nur über **HTTPS** oder `localhost`. Ein Handy im WLAN (`start.bat lan`, http) darf die Kamera daher
nicht benutzen – dafür die gehostete Version oder einen HTTPS-Tunnel verwenden.

Demo-Daten (nur lokal): `php artisan db:seed --class=DemoSeeder`.

## Admin-Zugang

Es gibt keine Registrierung. Der Zugang wird per Seeder/Befehl angelegt (Werte aus `.env`: `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_NAME`):

```bash
php artisan handinhand:admin                 # legt an bzw. setzt das Passwort neu (fragt bei fehlenden Werten nach)
php artisan handinhand:admin --email=… --password=…
```

Passwort ändern: Admin → Einstellungen. „Passwort vergessen“: derselbe Befehl auf dem Server.

## Konfiguration (`.env`)

| Variable | Bedeutung |
|---|---|
| `HIH_CHILD_TOKEN` | Wenn gesetzt, muss das Kinder-Handy einmalig `/zugang/<token>` öffnen (Link steht in Admin → Einstellungen). **Vor dem Livegang setzen.** |
| `HIH_MAX_VIDEO_MB` / `_AUDIO_MB` / `_IMAGE_MB` | Upload-Limits (30 / 5 / 12). Fotos werden auf max. 1600 px verkleinert. |
| `HIH_MAX_RECORD_SECONDS` | Auto-Stopp der Kind-Aufnahme (90 s, hält Videos unter dem Limit). |
| `HIH_POLL_SECONDS` | Wie oft der Kinder-Bildschirm / Admin nach Neuem fragt (20 s). |
| `LEGAL_*` | Angaben für Impressum und Datenschutzerklärung (`/impressum`, `/datenschutz`). |

PHP-Upload-Limits stehen in `public/.user.ini` (64 MB) – bei netcup ggf. zusätzlich in Plesk setzen.

## Abweichungen vom Konzept (bewusst)

- **Kein Reverb/WebSocket:** Der „hüpfende Postkasten“ wird per Livewire-Polling (20 s) aktualisiert. Reverb bräuchte einen dauerhaft laufenden
  Prozess, der auf Webhosting meist nicht möglich ist. Nachrüstbar, falls ein vServer genutzt wird.
- **Medien privat:** Alles liegt in `storage/app/private` und wird über `MediaController` ausgeliefert (mit Range-Support). Videos des Kindes
  sieht nur die eingeloggte Tante. Es gibt keinen `/storage`-Link.
- **Zusätzlich zum Mockup:** Admin-Bereiche für Postkasten (Videos des Kindes ansehen, Nachrichten an das Kind schicken), Einstellungen
  (Name, Foto, Passwort), Buch anlegen/umbenennen/löschen, optionaler Seitenname.
- `manifest.webmanifest`: Tippfehler `"none"` aus dem Konzept ist zu `"name"` korrigiert; Icons liegen in `public/app-icons/` (nicht `icons`, wegen netcup/Apache-Alias).
- Aufnahme-Format: webm (Chrome/Firefox) bzw. mp4 (Safari) – je nachdem, was der Browser kann; Bitrate begrenzt (~1,2 Mbit/s).

## Qualitätssicherung

```bash
php artisan test                 # 61 Feature-Tests
./vendor/bin/pint --test         # Code-Stil
./vendor/bin/phpstan analyse     # Larastan Level 6
npm run build                    # Frontend
```

## Deployment

`deploy.sh` (Entwurf, wird noch abgestimmt): prüft, baut lokal (Composer `--no-dev`, Vite), lädt per SSH hoch, migriert und wärmt Caches auf.
Konfiguration in `.deploy.env` (Vorlage `.deploy.env.example`). `./deploy.sh --build-only` erzeugt nur das Paket für manuellen Upload.
