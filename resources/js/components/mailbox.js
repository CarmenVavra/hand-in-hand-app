/**
 * Postkasten: Kachel antippen -> Vollbild-Player (Tippen = Pause/Play, Replay, Home = zurück zu den Kacheln).
 * Der Player lebt auf derselben Seite, damit play() direkt im Tipp-Ereignis läuft (iOS-Autoplay-Regeln).
 */
export default ({ items = [] } = {}) => ({
    items,
    read: {},
    current: null,
    paused: false,
    progress: 0,

    isNew(item) {
        return item.isNew && !this.read[item.id];
    },

    get open() {
        return this.current !== null;
    },

    openItem(index) {
        const item = this.items[index];
        const video = this.$refs.video;

        this.current = item;
        this.progress = 0;
        this.read[item.id] = true;
        this.$wire.markRead(item.id);

        video.src = item.url;
        this.play();
    },

    play() {
        const video = this.$refs.video;

        video.play().then(
            () => (this.paused = false),
            () => (this.paused = true), // Autoplay blockiert: großes Play-Symbol, Antippen startet
        );
    },

    toggle() {
        const video = this.$refs.video;

        if (video.paused || video.ended) {
            this.play();
        } else {
            video.pause();
            this.paused = true;
        }
    },

    replay() {
        this.$refs.video.currentTime = 0;
        this.play();
    },

    close() {
        const video = this.$refs.video;

        video.pause();
        video.removeAttribute('src');
        video.load();
        this.current = null;
        this.paused = false;
        this.progress = 0;
    },

    onTime() {
        const video = this.$refs.video;

        this.progress = video.duration ? Math.min(100, (video.currentTime / video.duration) * 100) : 0;
    },

    onEnded() {
        this.progress = 100;
        this.paused = true;
    },
});
