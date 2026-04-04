document.addEventListener('alpine:init', () => {
    const { startAuthentication, browserSupportsWebAuthn } = window.SimpleWebAuthnBrowser;

    Alpine.data('passkeyAssertion', (config) => ({
        optionsUrl: config.optionsUrl,
        authenticateUrl: config.authenticateUrl,
        messages: config.messages || {},
        useBrowserAutofill: config.useBrowserAutofill || false,
        showPasswordField: !browserSupportsWebAuthn(),
        error: '',
        username: '',

        async init() {
            this.$el.addEventListener('submit', (e) => {
                e.preventDefault();
                this.authenticate(this.$el, true);
            });

            this.authenticate(this.$el);
        },

        async authenticate(form, manualSubmission = false) {
            if (this.showPasswordField) {
                return form.submit();
            }

            // TODO: it doesn't clean error bag after wrong password
            this.error = '';

            try {
                const options = await this.getAuthOptions(this.username?.trim() || null);
                const answer = await startAuthentication({
                    optionsJSON: options,
                    useBrowserAutofill: this.useBrowserAutofill,
                });
                this.action(form, answer);
            } catch (e) {
                if (manualSubmission) this.showPasswordField = true;
            } finally {
                if (!manualSubmission) this.username = '';
            }
        },

        async getAuthOptions(email) {
            try {
                const response = await window.axios.post(
                    this.optionsUrl,
                    email ? { email } : {},
                );

                return response.data;
            } catch {
                throw new Error(this.messages.failed);
            }
        },

        action(form, answer) {
            form.action = this.authenticateUrl;
            form.addEventListener('formdata', ({formData}) => {
                formData.set('answer', JSON.stringify(answer));
            });
            form.submit();
        },

        onChangeField(event) {},
    }));
});
