document.addEventListener("alpine:init", () => {
    const { WebAuthnError, startRegistration } = window.SimpleWebAuthnBrowser;

    Alpine.data("passkeyAttestation", (config) => ({
        optionsUrl: config.optionsUrl || '',
        storeUrl: config.storeUrl || '',
        container: config.container || '',
        messages: config.messages || {},
        newPasskey: '',
        error: '',
        loading: false,

        showError(message) {
            this.error = message;
        },

        async createPasskey(name) {
            name = name.trim();
            if (!name || this.loading) return;

            this.loading = true;
            this.error = '';

            try {
                const options = await this.getRegistrationOptions(name);
                const attestationResponse = await startRegistration({ optionsJSON: options });
                await this.storeAttestation(attestationResponse, name);
                this.newPasskey = '';
            } catch (error) {
                const defaultMessage = error.message ?? this.messages.registrationFailed;
                const message = error instanceof WebAuthnError
                    ? this.getErrorMessages(error.name, defaultMessage)
                    : defaultMessage;

                this.showError(message);
                MoonShine.ui.toast(message, 'error');
            } finally {
                this.loading = false;
            }
        },

        async getRegistrationOptions(name) {
            try {
                const response = await window.axios.post(this.optionsUrl, { name });

                return response.data;
            } catch (axiosError) {
                throw new Error(axiosError.response?.data?.message ?? this.messages.registrationFailed);
            }
        },

        async storeAttestation(attestationResponse, name) {
            try {
                const response = await window.axios.post(this.storeUrl, {
                    passkey: JSON.stringify(attestationResponse),
                    name: name,
                });

                const data = response.data;

                MoonShine.ui.toast(data.message, data.messageType ?? 'success', data.messageDuration ?? null);
                this.$dispatch('fragment_updated:' + this.container);
            } catch (axiosError) {
                throw new Error(axiosError.response?.data?.message ?? this.messages.registrationFailed);
            }
        },

        getErrorMessages(name, defaultMessage) {
            const map = {
                NotAllowedError: this.messages.notAllowed,
                InvalidStateError: this.messages.invalidState,
                NotSupportedError: this.messages.notSupported,
                AbortError: this.messages.aborted,
            };

            return map[name] ?? defaultMessage;
        },
    }))

    Alpine.data("passkeyRow", (config) => ({
        editing: false,
        editName: config.name || '',
        originalName: config.name || '',
        updateUrl: config.updateUrl || '',
        container: config.container || '',
        failedMessage: config.failedMessage || '',

        startEdit() {
            this.editing = true;
            this.$nextTick(() => this.$refs.editInput?.focus());
        },

        cancelEdit() {
            this.editing = false;
            this.editName = this.originalName;
        },

        async saveEdit() {
            const name = this.editName.trim();

            if (!name || name === this.originalName) {
                this.editing = false;
                this.$dispatch('fragment_updated:' + this.container);
                return;
            }

            try {
                const response = await window.axios.patch(this.updateUrl, { name });
                const data = response.data;

                this.originalName = name;
                this.editing = false;

                MoonShine.ui.toast(data.message, data.messageType ?? 'success', data.messageDuration ?? null);
                this.$dispatch('fragment_updated:' + this.container);
            } catch (axiosError) {
                this.editName = this.originalName;
                this.editing = false;

                const message = axiosError.response?.data?.message ?? this.failedMessage;
                MoonShine.ui.toast(message, 'error');
            }
        },
    }))
})
