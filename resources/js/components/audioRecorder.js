import {
    AUDIO_MIMES,
    baseType,
    extensionFor,
    livewireUpload,
    pickMime,
    recordingSupported,
    stopStream,
} from './mediaRecorderSupport';

/**
 * Stimme für eine Bilderbuch-Seite aufnehmen (Admin).
 * recState: 0 = bereit, 1 = nimmt auf, 2 = fertig. `uploaded` ist true, sobald Livewire die Datei hat.
 */
export default () => ({
    recState: 0,
    uploaded: false,
    uploading: false,
    secs: 0,
    audioUrl: null,
    problem: null,
    recorder: null,
    stream: null,
    chunks: [],
    timer: null,

    get statusText() {
        if (this.problem === 'denied') {
            return 'Mikrofon nicht erlaubt – bitte in den Browser-Einstellungen freigeben.';
        }
        if (this.problem === 'unsupported') {
            return 'Dieser Browser kann nicht aufnehmen.';
        }
        if (this.problem === 'upload') {
            return 'Fehler beim Hochladen – bitte nochmal aufnehmen.';
        }
        if (this.recState === 1) {
            return `● Nimmt auf … ${this.clock}`;
        }
        if (this.uploading) {
            return 'Wird hochgeladen …';
        }
        if (this.recState === 2) {
            return `Aufnahme fertig – ${this.clock}`;
        }

        return 'Bereit zum Aufnehmen';
    },

    get clock() {
        return `${Math.floor(this.secs / 60)}:${String(this.secs % 60).padStart(2, '0')}`;
    },

    async toggle() {
        if (this.recState === 1) {
            return this.stop();
        }

        return this.start();
    },

    async start() {
        this.problem = null;

        if (!recordingSupported()) {
            this.problem = 'unsupported';
            return;
        }

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        } catch {
            this.problem = 'denied';
            return;
        }

        this.reset();
        const mime = pickMime(AUDIO_MIMES);
        this.chunks = [];
        this.recorder = new MediaRecorder(this.stream, { ...(mime ? { mimeType: mime } : {}), audioBitsPerSecond: 96_000 });
        this.recorder.ondataavailable = (event) => event.data.size > 0 && this.chunks.push(event.data);
        this.recorder.onstop = () => this.finish();
        this.recorder.start(1000);

        this.recState = 1;
        this.secs = 0;
        this.timer = setInterval(() => this.secs++, 1000);
    },

    stop() {
        clearInterval(this.timer);

        if (this.recorder && this.recorder.state !== 'inactive') {
            this.recorder.stop();
        }
        stopStream(this.stream);
    },

    async finish() {
        const type = baseType(this.recorder?.mimeType) || 'audio/webm';
        const blob = new Blob(this.chunks, { type });

        this.audioUrl = URL.createObjectURL(blob);
        this.recState = 2;
        this.uploading = true;

        try {
            const file = new File([blob], `stimme.${extensionFor(type)}`, { type });
            await livewireUpload(this.$wire, 'audio', file);
            this.uploaded = true;
        } catch {
            this.problem = 'upload';
            this.recState = 0;
            this.audioUrl = null;
        } finally {
            this.uploading = false;
        }
    },

    reset() {
        if (this.audioUrl) {
            URL.revokeObjectURL(this.audioUrl);
        }
        this.audioUrl = null;
        this.uploaded = false;
        this.recState = 0;
        this.$wire.set('audio', null);
    },
});
