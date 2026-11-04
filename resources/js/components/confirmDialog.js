/**
 * Eigener Bestätigungsdialog statt window.confirm().
 * Auslösen: $dispatch('confirm-dialog', { title, message, confirmLabel, onConfirm: () => $wire.delete(1) })
 */
export default () => ({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Ja',
    onConfirm: null,

    show(detail) {
        this.title = detail.title ?? 'Bist du sicher?';
        this.message = detail.message ?? '';
        this.confirmLabel = detail.confirmLabel ?? 'Ja';
        this.onConfirm = detail.onConfirm ?? null;
        this.open = true;
        this.$nextTick(() => this.$refs.cancel?.focus());
    },

    confirm() {
        const action = this.onConfirm;

        this.close();
        action?.();
    },

    close() {
        this.open = false;
        this.onConfirm = null;
    },
});
