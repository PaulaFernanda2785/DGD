var pendingSubmitter = null;

document.addEventListener('click', function (event) {
    var target = event.target;

    if (!(target instanceof HTMLElement)) {
        return;
    }

    var menuButton = target.closest('[data-menu-toggle]');

    if (menuButton) {
        var sidebar = document.getElementById('app-sidebar');
        var open = document.body.classList.toggle('sidebar-open');
        menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');

        if (sidebar) {
            sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
    }

    var passwordButton = target.closest('[data-password-toggle]');

    if (passwordButton) {
        var field = passwordButton.closest('.password-field');
        var shell = passwordButton.closest('[data-password-shell]');
        var input = field ? field.querySelector('input') : null;

        if (!input && shell) {
            input = shell.querySelector('[data-password-reveal]');
        }

        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
            passwordButton.setAttribute('aria-label', input.type === 'password' ? 'Mostrar senha' : 'Ocultar senha');
            passwordButton.setAttribute('aria-pressed', input.type === 'password' ? 'false' : 'true');

            if (shell) {
                shell.classList.toggle('is-password-visible', input.type !== 'password');
            }
        }
    }
});

document.addEventListener('submit', function (event) {
    var form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    var submitter = event.submitter;

    if (submitter && submitter.hasAttribute('data-confirm') && pendingSubmitter !== submitter) {
        event.preventDefault();
        openConfirm(submitter.getAttribute('data-confirm') || 'Deseja continuar?', function () {
            pendingSubmitter = submitter;
            submitter.click();
            pendingSubmitter = null;
        });
        return;
    }

    var buttons = form.querySelectorAll('button[type="submit"]');

    buttons.forEach(function (button) {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    });
});

function openConfirm(message, onConfirm) {
    var backdrop = document.querySelector('[data-confirm-backdrop]');
    var messageBox = document.querySelector('[data-confirm-message]');
    var okButton = document.querySelector('[data-confirm-ok]');
    var cancelButton = document.querySelector('[data-confirm-cancel]');

    if (!backdrop || !messageBox || !okButton || !cancelButton) {
        if (window.confirm(message)) {
            onConfirm();
        }
        return;
    }

    messageBox.textContent = message;
    backdrop.hidden = false;
    okButton.focus();

    var close = function () {
        backdrop.hidden = true;
        okButton.removeEventListener('click', confirm);
        cancelButton.removeEventListener('click', close);
    };

    var confirm = function () {
        close();
        onConfirm();
    };

    okButton.addEventListener('click', confirm);
    cancelButton.addEventListener('click', close);
}

function option(value, label) {
    var item = document.createElement('option');
    item.value = value;
    item.textContent = label;
    return item;
}

function fillSelect(select, items, placeholder) {
    if (!select) {
        return;
    }

    select.innerHTML = '';
    select.appendChild(option('', placeholder));

    items.forEach(function (item) {
        select.appendChild(option(item.id, item.codigo ? item.codigo + ' - ' + item.nome : item.nome));
    });
}

function fetchJson(path) {
    return fetch(path, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function (response) {
        return response.json();
    });
}

document.addEventListener('change', function (event) {
    var target = event.target;

    if (!(target instanceof HTMLSelectElement)) {
        return;
    }

    var role = target.getAttribute('data-cobrade');
    var baseUrl = document.querySelector('link[rel="stylesheet"]').href.replace('/assets/css/app.css', '');

    if (role === 'grupo') {
        fetchJson(baseUrl + '/cobrade/subgrupos?grupo_id=' + encodeURIComponent(target.value))
            .then(function (payload) {
                fillSelect(document.querySelector('[data-cobrade="subgrupo"]'), payload.data || [], 'Selecione');
                fillSelect(document.querySelector('[data-cobrade="tipo"]'), [], 'Selecione');
                fillSelect(document.querySelector('[data-cobrade="subtipo"]'), [], 'Selecione');
            });
    }

    if (role === 'subgrupo') {
        fetchJson(baseUrl + '/cobrade/tipos?subgrupo_id=' + encodeURIComponent(target.value))
            .then(function (payload) {
                fillSelect(document.querySelector('[data-cobrade="tipo"]'), payload.data || [], 'Selecione');
                fillSelect(document.querySelector('[data-cobrade="subtipo"]'), [], 'Selecione');
            });
    }

    if (role === 'tipo') {
        fetchJson(baseUrl + '/cobrade/subtipos?tipo_id=' + encodeURIComponent(target.value))
            .then(function (payload) {
                fillSelect(document.querySelector('[data-cobrade="subtipo"]'), payload.data || [], 'Selecione');
            });
    }

    if (role === 'subtipo' && target.value) {
        fetchJson(baseUrl + '/cobrade/' + encodeURIComponent(target.value) + '/detalhe')
            .then(function (payload) {
                var box = document.getElementById('cobrade-descricao');
                if (box && payload.data) {
                    box.textContent = payload.data.descricao || payload.data.nome || 'Sem descricao cadastrada.';
                }
            });
    }
});

function updateAffectedTotal() {
    var total = 0;

    document.querySelectorAll('.affected-input').forEach(function (input) {
        total += parseInt(input.value || '0', 10) || 0;
    });

    var preview = document.getElementById('total-afetados-preview');

    if (preview) {
        preview.value = total;
    }
}

document.addEventListener('input', function (event) {
    if (event.target.classList && event.target.classList.contains('affected-input')) {
        updateAffectedTotal();
    }
});

document.addEventListener('DOMContentLoaded', updateAffectedTotal);

document.addEventListener('DOMContentLoaded', function () {
    function renderQr(target, options) {
        var text = target.getAttribute('data-qr-text') || '';

        if (!text || typeof qrcode !== 'function') {
            return false;
        }

        var settings = Object.assign({
            correctionLevel: 'L',
            cellSize: 6,
            margin: 4
        }, options || {});

        try {
            var qr = qrcode(0, settings.correctionLevel);
            qr.addData(text);
            qr.make();
            target.innerHTML = qr.createSvgTag(settings.cellSize, settings.margin);
            target.classList.add('is-ready');
            return true;
        } catch (error) {
            target.textContent = 'Nao foi possivel gerar o QR Code.';
            return false;
        }
    }

    function renderTwoFactorQr(target, attempt) {
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (typeof qrcode !== 'function') {
            if ((attempt || 0) < 8) {
                window.setTimeout(function () {
                    renderTwoFactorQr(target, (attempt || 0) + 1);
                }, 120);
                return;
            }

            target.textContent = 'Nao foi possivel carregar o QR Code. Use a chave manual.';
            return;
        }

        renderQr(target);
    }

    function setupTwoFactorCopy(button) {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        var secret = button.getAttribute('data-copy-secret') || '';

        button.addEventListener('click', function () {
            if (!secret || !navigator.clipboard) {
                button.textContent = 'Copie manualmente';
                return;
            }

            navigator.clipboard.writeText(secret).then(function () {
                var original = button.textContent;
                button.textContent = 'Chave copiada';
                button.disabled = true;

                window.setTimeout(function () {
                    button.textContent = original;
                    button.disabled = false;
                }, 1600);
            }).catch(function () {
                button.textContent = 'Copie manualmente';
            });
        });
    }

    function setupTwoFactorModal(modalSelector, closeSelector, openCallback) {
        var modal = document.querySelector(modalSelector);
        var lastFocused = null;

        if (!(modal instanceof HTMLElement)) {
            return { open: function () {} };
        }

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('two-factor-modal-open');

            if (lastFocused instanceof HTMLElement) {
                lastFocused.focus();
            }
        }

        function openModal(trigger) {
            lastFocused = trigger instanceof HTMLElement ? trigger : null;

            if (typeof openCallback === 'function') {
                openCallback(modal, trigger);
            }

            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('two-factor-modal-open');
        }

        modal.querySelectorAll(closeSelector).forEach(function (control) {
            control.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        return { open: openModal };
    }

    document.querySelectorAll('[data-twofactor-qr]').forEach(renderTwoFactorQr);
    document.querySelectorAll('[data-copy-secret]').forEach(setupTwoFactorCopy);

    var appDownloadModal = setupTwoFactorModal('[data-app-download-modal]', '[data-app-download-close]', function (modal, trigger) {
        if (!(trigger instanceof HTMLAnchorElement)) {
            return;
        }

        var title = modal.querySelector('[data-app-download-title]');
        var label = modal.querySelector('[data-app-download-label]');
        var directLink = modal.querySelector('[data-app-download-link]');
        var qrTarget = modal.querySelector('[data-app-download-qr]');

        if (title instanceof HTMLElement) {
            title.textContent = trigger.getAttribute('data-download-title') || 'Baixar Google Authenticator';
        }

        if (label instanceof HTMLElement) {
            label.textContent = trigger.getAttribute('data-download-label') || 'Aplicativo autenticador';
        }

        if (directLink instanceof HTMLAnchorElement) {
            directLink.href = trigger.href;
        }

        if (qrTarget instanceof HTMLElement) {
            qrTarget.setAttribute('data-qr-text', trigger.href);
            qrTarget.textContent = 'Gerando QR Code...';
            renderQr(qrTarget, { correctionLevel: 'M', cellSize: 7, margin: 4 });
        }
    });

    document.querySelectorAll('[data-app-download]').forEach(function (trigger) {
        trigger.addEventListener('click', function (event) {
            if (typeof qrcode !== 'function') {
                return;
            }

            event.preventDefault();
            appDownloadModal.open(trigger);
        });
    });

    var activationModal = setupTwoFactorModal('[data-activation-qr-modal]', '[data-activation-qr-close]', function (modal) {
        var target = modal.querySelector('[data-activation-qr-target]');

        if (target instanceof HTMLElement) {
            target.textContent = 'Gerando QR Code...';
            renderQr(target, { correctionLevel: 'L', cellSize: 10, margin: 6 });
        }
    });

    var activationOpen = document.querySelector('[data-activation-qr-open]');

    if (activationOpen instanceof HTMLButtonElement) {
        activationOpen.addEventListener('click', function () {
            activationModal.open(activationOpen);
        });
    }
});
