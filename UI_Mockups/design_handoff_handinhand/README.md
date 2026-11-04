# Handoff: HandInHand (Kinder-App für Videos & Bilderbuch)

## Overview
HandInHand ist eine private PWA für eine zweijährige Nichte und ihre Tante Carmen. Das Kind sieht nur drei riesige, bunte Kacheln: Videonachricht aufnehmen, Postkasten (Videos von Tante Carmen), Bilderbuch (Foto + Stimme). Tante Carmen verwaltet Inhalte in einer Admin-Ansicht. Quelle der Anforderungen: `uploads/Konzept.pdf` (Laravel + Livewire + Alpine.js, MediaRecorder API, Reverb).

## About the Design Files
`HandInHand.dc.html` ist eine **HTML-Design-Referenz** (klickbarer Prototyp), kein Produktionscode. Aufgabe: Die Screens im Ziel-Stack nachbauen – laut Konzept Laravel + Livewire + Alpine.js + Tailwind als PWA – mit dessen Mustern. Die .dc.html nutzt ein internes Komponentenformat; nur Look und Verhalten übernehmen. Platzhalter (Fotos/Videos) durch echte Medien ersetzen.

## Fidelity
**High-fidelity**: finale Farben, Typografie, Abstände, Animationen. Pixelnah nachbauen.

## Screens (Phone-Frame 390×800, 2px Ink-Rahmen; Screenshots in `screenshots/`)
Alle Ecken eckig (radius 0) außer den runden Aufnahme-Buttons. Trennlinien 2px #201e1d. Schrift **Archivo** (Gewichte 700–900 für Titel). Tap-Ziele ≥ 64px.

1. **Hauptmenü (Kind)** – `01-hauptmenue.png`. Hintergrund #FFD23F. Kopf: Foto-Quadrat 76×76 (Platzhalter, Ink-Fläche) + „Tante Carmen“ (900/30px, zweizeilig), Unterlinie 2px. Darunter 3 gleich hohe, volle Kacheln (flex:1), je Icon 84px (weiß, Lucide-Stil) + Label links (900/32px, weiß):
   - „Nachricht für dich“ Rot #EC3013 (pressed #B8250E) → Aufnahme
   - „Postkasten“ Blau #2D6BFF (pressed #1E4FCC) → Postkasten; Icon hüpft (`hop 1.4s infinite`) und trägt gelbes „!“-Badge 34×34 (blinkt), solange ungelesene Videos existieren
   - „Unser Bilderbuch“ Grün #0E8F55 (pressed #0A6E41) → Bilderbuch
2. **Nachricht aufnehmen** – `02-…png`. Oben Kamera-Vorschau (rot, Platzhalter „Selfie-Kamera“), unten gelber Balken 240px: Home-Button 84×84 links, runder Aufnahme-Button 130px (rot, 6px Ink-Rand, weißer Punkt → weißes Quadrat beim Aufnehmen, `pulse 1s`). Timer-Badge oben links (weiß, rotes blinkendes Quadrat, „0:03“). Tap 1 startet, Tap 2 stoppt und sendet → Vollfläche „Gesendet!“ (56px/900) + „Tante Carmen bekommt dein Video.“, nach 2,2 s automatisch zurück zum Hauptmenü.
3. **Postkasten** – `03-…png`. Blau #2D6BFF. Kopf gelb: Home-Button 64×64 + „Postkasten“ (900/30). 2×2-Grid (2px Ink-Gap) mit Video-Kacheln (Farben #EC3013, #0E8F55, #7A3BFF, #E8590C), großes Play-Icon, Titel unten links (800/18). Ungelesen: gelbes Badge „NEU“ (blinkt). Unten roter Button „Video für Tante“ (96px hoch) → Aufnahme.
4. **Video ansehen** – `04-…png`. Vollbild in Kachelfarbe, Titel oben links (900/24), Video-Fläche mittig, Tap = Pause/Play (großes Play-Icon bei Pause), Fortschrittsbalken 14px gelb, Fußleiste 120px Ink: Home links, Replay (gelb) rechts. Öffnen markiert das Video als gelesen.
5. **Bilderbuch** – `05-…png`. Vollbild-Foto pro Seite (Hintergrundfarbe je Seite). Tap rechte 75 % = weiter, linke 25 % = zurück (Chevrons weiß 48/64px nur wenn möglich). Fußleiste 120px Ink mit Lautsprecher-Icon, animierten Audio-Balken (`wave 1s`, 14 Stück, 7px breit) und „2 / 4“. Home-Button oben links (64×64 gelb). Letzte Seite: großer gelber Home-Button 150×150 (pulsiert). Audio startet automatisch pro Seite.
6. **Admin: Buch-Übersicht** – `06-…png`. Hintergrund #F3F2F2. Kopf: „ADMIN · Tante Carmen“ (rot, 11px, Versalien), Buchtitel (900/26), „n Seiten“, Button „+ Neu“ (rot). Zeilen (weiß, 2px Linie): Thumbnail 60×60, „Seite n · Label“, Mini-Audio-Player, rechts ▲ / ✕ (rot) / ▼ (34×30; erstes/letztes ausgegraut). Grüne Toast-Leiste (#0E8F55) bei Löschen/Hinzufügen. Footer-Link „◀ Zurück zum Hauptmenü“.
7. **Admin: Neue Seite** – `07-…png`. 1 · Foto (Button 170px hoch, nach Auswahl gelb mit Haken), 2 · Deine Stimme (runder Rec-Button 72px, Status: „Bereit zum Aufnehmen“ / „● Nimmt auf …“ / „Aufnahme fertig“). Footer: „Abbrechen“ + „Seite ins Buch zaubern“ (grün, erst aktiv wenn Foto + Audio vorhanden) → zurück zur Übersicht.

## Interactions & Behavior
- Immer Weg zurück: Home-Button auf jedem Kind-Screen (keine Sackgassen).
- Animationen: hop (translateY 0→-14→0, 1.4s), pulse (scale 1→1.12, 1s), blink (opacity 1→.25, 1s), wave (Höhe 10↔46px, 1s, versetzt).
- Hover/Active: Kacheln dunkeln im Pressed-Zustand ab (Werte oben); Fokus: 2px Outline in Akzent #EC3013, offset 2px.
- Fehlerzustände (im Mockup nicht dargestellt): Kamera/Mikrofon verweigert → Statustext; Upload-Fehler → „Fehler beim Senden!“.

## State Management
Kind: `screen`, `recording`, `sent`, `secs`, aktuelles Video + `paused` + `progress`, `read{}`, Buchseite `pageIndex`. Admin: Seitenreihenfolge, `photo`, `audioRecState (0/1/2)`, `toast`.
Backend laut Konzept: Tabellen `media_messages` (sender_id, type video|audio|picture_book, file_path, thumbnail_path, caption, is_read), `picture_books`, `picture_book_pages` (image_path, audio_path, order_column). Routes: /home, /home/postkasten, /home/bilderbuch/{id}, /admin/buch/{id}, /admin/buch/{id}/neu. Neues Video → Reverb-Event, damit der Postkasten hüpft. Recorder: MediaRecorder API; Videos max. 20–30 MB, Audio max. 5 MB.
Hinweis: manifest.json im Konzept hat Tippfehler (`"none"` statt `"name"`).

## Design Tokens
- Farben: Ink #201E1D · Ground #F3F2F2 · Gelb #FFD23F · Rot #EC3013 (hover/pressed #B8250E) · Blau #2D6BFF · Grün #0E8F55 · Violett #7A3BFF · Orange #E8590C · Weiß #FFF
- Typo: Archivo; Titel 900/24–56px, Labels 700–800/11–18px, Versalien mit 1–2px Letter-Spacing
- Radius: 0 (nur runde Aufnahme-Buttons 50 %); Linien 2px; Phone-Schatten 12px 12px 0 #201E1D
- Basis: Modernist-Designsystem (flach, eckig, Raster), Farbpalette für Kinder bunt erweitert.

## Assets
Keine Bilder. Foto/Video-Flächen sind Platzhalter (farbige Blöcke mit Label). Icons: Lucide-Stil inline-SVG (video, mail, book-open, home, rotate-ccw, mic, volume).

## Files
- `HandInHand.dc.html` – kompletter klickbarer Prototyp (alle Screens, Logik im Skript)
- `screenshots/*.png` – 7 Screens
- Anforderungen: `Konzept.pdf`
