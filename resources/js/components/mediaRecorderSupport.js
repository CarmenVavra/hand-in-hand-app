/**
 * Gemeinsame Helfer für MediaRecorder (Video und Audio).
 */

export const VIDEO_MIMES = [
    'video/webm;codecs=vp9,opus',
    'video/webm;codecs=vp8,opus',
    'video/webm',
    'video/mp4;codecs=avc1,mp4a.40.2',
    'video/mp4',
];

export const AUDIO_MIMES = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg;codecs=opus'];

/** Erster vom Browser unterstützter Typ (Safari: mp4, Chrome/Firefox: webm). */
export function pickMime(candidates) {
    if (typeof MediaRecorder === 'undefined' || typeof MediaRecorder.isTypeSupported !== 'function') {
        return '';
    }

    return candidates.find((type) => MediaRecorder.isTypeSupported(type)) ?? '';
}

export function recordingSupported() {
    return Boolean(navigator.mediaDevices?.getUserMedia) && typeof MediaRecorder !== 'undefined';
}

/** "video/webm;codecs=vp9,opus" -> "video/webm" */
export function baseType(mime) {
    return (mime || '').split(';')[0];
}

export function extensionFor(mime) {
    const type = baseType(mime);

    if (type.includes('mp4')) {
        return 'mp4';
    }
    if (type.includes('ogg')) {
        return 'ogg';
    }

    return 'webm';
}

export function stopStream(stream) {
    stream?.getTracks().forEach((track) => track.stop());
}

/** Livewire-Upload als Promise (Livewire: $wire.$upload(name, file, finish, error, progress)). */
export function livewireUpload(wire, property, file, onProgress) {
    return new Promise((resolve, reject) => {
        wire.$upload(property, file, resolve, reject, (event) => onProgress?.(event?.detail?.progress ?? 0));
    });
}
