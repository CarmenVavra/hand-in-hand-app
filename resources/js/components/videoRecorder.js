import {
    VIDEO_MIMES,
    baseType,
    extensionFor,
    livewireUpload,
    pickMime,
    recordingSupported,
    stopStream,
} from './mediaRecorderSupport';

/**
 * Selfie-Video aufnehmen (Kind): Tippen 1 = Start, Tippen 2 = Stopp + Senden.
 * Zustände: init -> ready -> recording -> sending -> sent | error
 */
export default ({ maxSeconds = 90, homeUrl = '/home' } = {}) => ({
    state: 'init',
    secs: 0,
    problem: null, // unsupported | denied | camera | send
    stream: null,
    recorder: null,
    chunks: [],
    blob: null,
    mime: '',
    timer: null,

    get recording() {
        return this.state === 'recording';
    },

    get clock() {
        return `${Math.floor(this.secs / 60)}:${String(this.secs % 60).padStart(2, '0')}`;
    },

    get problemText() {
        return {
            unsupported: 'Dieser Browser kann keine Videos aufnehmen.',
            denied: 'Kamera und Mikrofon sind nicht erlaubt. Bitte in den Einstellungen freigeben.',
            camera: 'Die Kamera konnte nicht gestartet werden.',
            send: 'Fehler beim Senden! Tippe auf den Knopf, um es nochmal zu versuchen.',
        }[this.problem];
    },

    async init() {
        window.addEventListener('pagehide', () => this.release());
        await this.openCamera();
    },

    async openCamera() {
        this.problem = null;

        if (!recordingSupported()) {
            this.state = 'error';
            this.problem = 'unsupported';
            return;
        }

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 960 }, height: { ideal: 720 } },
                audio: true,
            });
            this.$refs.preview.srcObject = this.stream;
            this.state = 'ready';
        } catch (error) {
            this.state = 'error';
            this.problem = ['NotAllowedError', 'SecurityError'].includes(error?.name) ? 'denied' : 'camera';
        }
    },

    toggle() {
        if (this.state === 'recording') {
            return this.stop();
        }

        if (this.state === 'ready') {
            return this.start();
        }

        if (this.state === 'error') {
            // Senden war fehlgeschlagen: erneut versuchen. Sonst Kamera neu öffnen.
            return this.blob ? this.send() : this.openCamera();
        }
    },

    start() {
        this.chunks = [];
        this.blob = null;
        this.secs = 0;
        this.mime = pickMime(VIDEO_MIMES);

        this.recorder = new MediaRecorder(this.stream, {
            ...(this.mime ? { mimeType: this.mime } : {}),
            videoBitsPerSecond: 1_200_000, // hält ~90 s unter 20 MB
            audioBitsPerSecond: 64_000,
        });
        this.recorder.ondataavailable = (event) => event.data.size > 0 && this.chunks.push(event.data);
        this.recorder.onstop = () => this.finish();
        this.recorder.start(1000);

        this.state = 'recording';
        this.timer = setInterval(() => {
            this.secs++;
            if (this.secs >= maxSeconds) {
                this.stop();
            }
        }, 1000);
    },

    stop() {
        clearInterval(this.timer);
        this.state = 'sending';

        if (this.recorder && this.recorder.state !== 'inactive') {
            this.recorder.stop();
        }
    },

    finish() {
        this.mime = baseType(this.recorder?.mimeType || this.mime) || 'video/webm';
        this.blob = new Blob(this.chunks, { type: this.mime });
        this.send();
    },

    async send() {
        if (!this.blob || this.blob.size === 0) {
            this.state = 'error';
            this.problem = 'send';
            return;
        }

        this.state = 'sending';
        this.problem = null;

        try {
            const file = new File([this.blob], `nachricht.${extensionFor(this.mime)}`, { type: this.mime });
            await livewireUpload(this.$wire, 'video', file);

            if ((await this.$wire.save()) !== true) {
                throw new Error('save failed');
            }

            this.state = 'sent';
            this.blob = null;
            this.release();
            setTimeout(() => (window.location.href = homeUrl), 2200);
        } catch {
            this.state = 'error';
            this.problem = 'send';
        }
    },

    /** Kamera aus. Wird die Seite mitten in der Aufnahme verlassen (Home-Knopf), wird sie verworfen statt halb gesendet. */
    release() {
        clearInterval(this.timer);

        if (this.recorder && this.recorder.state !== 'inactive' && this.state === 'recording') {
            this.recorder.onstop = null;
        }

        stopStream(this.stream);
        this.stream = null;
    },
});
