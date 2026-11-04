/**
 * Bilderbuch: rechte 75 % = weiter, linke 25 % = zurück, Stimme startet pro Seite automatisch.
 * Wird Autoplay blockiert (z. B. iOS nach Seitenwechsel), startet der erste Tipp die Stimme.
 */
export default ({ pages = [] } = {}) => ({
    pages,
    index: 0,
    playing: false,
    blocked: false,
    preloaded: [],

    init() {
        const audio = this.$refs.audio;

        audio.addEventListener('playing', () => (this.playing = true));
        audio.addEventListener('pause', () => (this.playing = false));
        audio.addEventListener('ended', () => (this.playing = false));

        if (this.pages.length > 0) {
            this.playCurrent();
        }
    },

    get page() {
        return this.pages[this.index];
    },

    get isFirst() {
        return this.index === 0;
    },

    get isLast() {
        return this.index === this.pages.length - 1;
    },

    get pageNumber() {
        return `${this.index + 1} / ${this.pages.length}`;
    },

    playCurrent() {
        const audio = this.$refs.audio;

        audio.src = this.page.audio;
        audio.currentTime = 0;
        audio.play().then(
            () => (this.blocked = false),
            () => ((this.blocked = true), (this.playing = false)),
        );
        this.preloadNext();
    },

    preloadNext() {
        const next = this.pages[this.index + 1];

        if (next) {
            const image = new Image();
            image.src = next.image;
            const sound = new Audio();
            sound.preload = 'auto';
            sound.src = next.audio;
            this.preloaded = [image, sound]; // Referenzen halten, damit der Browser sie lädt
        }
    },

    unblockOrRun(action) {
        if (this.blocked) {
            this.playCurrent();
            return;
        }

        action();
    },

    next() {
        this.unblockOrRun(() => {
            if (!this.isLast) {
                this.index++;
                this.playCurrent();
            }
        });
    },

    prev() {
        this.unblockOrRun(() => {
            if (!this.isFirst) {
                this.index--;
                this.playCurrent();
            }
        });
    },

    replay() {
        this.playCurrent();
    },
});
