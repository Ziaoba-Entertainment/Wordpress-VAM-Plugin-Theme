/* Ziaoba Auth Modal JS */

(function($) {
    'use strict';

    const AuthModal = {
        init: function() {
            this.cacheDOM();
            this.bindEvents();
        },

        cacheDOM: function() {
            this.$modal = $('#ziaoba-auth-modal');
            this.$closeBtn = $('.ziaoba-modal-close');
            this.$loginForm = $('#ziaoba-login-form');
            this.$registerForm = $('#ziaoba-register-form');
            this.$switchBtns = $('.ziaoba-switch-form');
            this.$watchNowBtns = $('.ziaoba-watch-now-placeholder, .ziaoba-trigger-auth');
        },

        bindEvents: function() {
            const self = this;

            // Open modal on click of restricted content
            $(document).on('click', '.ziaoba-watch-now-placeholder, .ziaoba-trigger-auth', function(e) {
                e.preventDefault();
                if (!ziaobaAuth.isLoggedIn) {
                    self.openModal();
                }
            });

            // Close modal
            this.$closeBtn.on('click', this.closeModal.bind(this));
            $(window).on('click', function(e) {
                if ($(e.target).is(self.$modal)) {
                    self.closeModal();
                }
            });

            // Switch forms
            this.$switchBtns.on('click', function(e) {
                e.preventDefault();
                const target = $(this).data('target');
                self.switchForm(target);
            });

            // Login submission
            this.$loginForm.on('submit', this.handleLogin.bind(this));

            // Register submission
            this.$registerForm.on('submit', this.handleRegister.bind(this));
        },

        openModal: function() {
            this.$modal.fadeIn(300);
            $('body').addClass('ziaoba-modal-open');
        },

        closeModal: function() {
            this.$modal.fadeOut(300);
            $('body').removeClass('ziaoba-modal-open');
        },

        switchForm: function(target) {
            if (target === 'register') {
                this.$loginForm.fadeOut(200, () => {
                    this.$registerForm.fadeIn(200);
                });
            } else {
                this.$registerForm.fadeOut(200, () => {
                    this.$loginForm.fadeIn(200);
                });
            }
        },

        handleLogin: function(e) {
            e.preventDefault();
            const self = this;
            const $form = $(e.target);
            const $btn = $form.find('button[type="submit"]');
            const $error = $('#login-error');

            $error.text('');
            $btn.addClass('loading');

            $.ajax({
                url: ziaobaAuth.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ziaoba_login',
                    nonce: ziaobaAuth.nonce,
                    username: $form.find('input[name="username"]').val(),
                    password: $form.find('input[name="password"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $error.css('color', '#2ecc71').text(ziaobaAuth.messages.login_success);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        $btn.removeClass('loading');
                        $error.css('color', '#e87c03').text(response.data.message || ziaobaAuth.messages.error_generic);
                    }
                },
                error: function() {
                    $btn.removeClass('loading');
                    $error.css('color', '#e87c03').text(ziaobaAuth.messages.error_generic);
                }
            });
        },

        handleRegister: function(e) {
            e.preventDefault();
            const self = this;
            const $form = $(e.target);
            const $btn = $form.find('button[type="submit"]');
            const $error = $('#register-error');

            $error.text('');
            $btn.addClass('loading');

            $.ajax({
                url: ziaobaAuth.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ziaoba_register',
                    nonce: ziaobaAuth.nonce,
                    username: $form.find('input[name="username"]').val(),
                    email: $form.find('input[name="email"]').val(),
                    password: $form.find('input[name="password"]').val(),
                    dob: $form.find('input[name="dob"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $error.css('color', '#2ecc71').text(ziaobaAuth.messages.register_success);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        $btn.removeClass('loading');
                        $error.css('color', '#e87c03').text(response.data.message || ziaobaAuth.messages.error_generic);
                    }
                },
                error: function() {
                    $btn.removeClass('loading');
                    $error.css('color', '#e87c03').text(ziaobaAuth.messages.error_generic);
                }
            });
        }
    };

    $(document).ready(function() {
        AuthModal.init();
    });

})(jQuery);
