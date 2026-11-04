import videoRecorder from './components/videoRecorder';
import audioRecorder from './components/audioRecorder';
import mailbox from './components/mailbox';
import bookViewer from './components/bookViewer';
import confirmDialog from './components/confirmDialog';

// Livewire liefert Alpine.js mit; eigene Komponenten werden hier registriert, bevor Alpine startet.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('videoRecorder', videoRecorder);
    window.Alpine.data('audioRecorder', audioRecorder);
    window.Alpine.data('mailbox', mailbox);
    window.Alpine.data('bookViewer', bookViewer);
    window.Alpine.data('confirmDialog', confirmDialog);
});

// PWA: Service Worker (nur im Produktivbetrieb, nicht am Vite-Dev-Server)
if ('serviceWorker' in navigator && !import.meta.env.DEV) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch(() => {});
    });
}
