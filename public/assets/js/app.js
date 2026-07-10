var pendingSubmitter = null;
var pendingHistorySubmitter = null;

document.addEventListener('click', function (event) {
    var target = event.target;

    if (!(target instanceof HTMLElement)) {
        return;
    }

    var menuButton = target.closest('[data-menu-toggle]');

    if (menuButton) {
        toggleSidebar();
    }

    var collapseButton = target.closest('[data-sidebar-collapse]');

    if (collapseButton) {
        var collapsed = document.body.classList.toggle('sidebar-collapsed');
        collapseButton.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
        collapseButton.setAttribute('aria-label', collapsed ? 'Expandir menu' : 'Recolher menu');
        try {
            window.localStorage.setItem('dgd.sidebar.collapsed', collapsed ? '1' : '0');
        } catch (error) {
            // LocalStorage pode estar indisponível em alguns navegadores restritos.
        }
    }

    var sidebarBackdrop = target.closest('[data-sidebar-backdrop]');

    if (sidebarBackdrop) {
        closeSidebar();
    }

    var backToTopButton = target.closest('[data-back-to-top]');

    if (backToTopButton) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
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

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
        closeSidebar();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var collapseButton = document.querySelector('[data-sidebar-collapse]');

    try {
        if (window.localStorage.getItem('dgd.sidebar.collapsed') === '1') {
            document.body.classList.add('sidebar-collapsed');
            if (collapseButton instanceof HTMLButtonElement) {
                collapseButton.setAttribute('aria-pressed', 'true');
                collapseButton.setAttribute('aria-label', 'Expandir menu');
            }
        }
    } catch (error) {
        // Preferência visual opcional.
    }

    syncSidebarState();
});

document.addEventListener('DOMContentLoaded', initBackToTop);

window.addEventListener('resize', syncSidebarState);

function isMobileSidebar() {
    return window.matchMedia('(max-width: 800px)').matches;
}

function toggleSidebar() {
    if (!isMobileSidebar()) {
        var collapseButton = document.querySelector('[data-sidebar-collapse]');
        var collapsed = document.body.classList.toggle('sidebar-collapsed');

        if (collapseButton instanceof HTMLButtonElement) {
            collapseButton.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            collapseButton.setAttribute('aria-label', collapsed ? 'Expandir menu' : 'Recolher menu');
        }

        try {
            window.localStorage.setItem('dgd.sidebar.collapsed', collapsed ? '1' : '0');
        } catch (error) {
            // Preferência visual opcional.
        }

        return;
    }

    var open = document.body.classList.toggle('sidebar-open');
    setSidebarOpen(open);
}

function closeSidebar() {
    setSidebarOpen(false);
}

function setSidebarOpen(open) {
    var sidebar = document.getElementById('app-sidebar');
    var menuButton = document.querySelector('[data-menu-toggle]');
    var backdrop = document.querySelector('[data-sidebar-backdrop]');

    document.body.classList.toggle('sidebar-open', open);

    if (menuButton instanceof HTMLElement) {
        menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        menuButton.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
    }

    if (sidebar) {
        sidebar.setAttribute('aria-hidden', isMobileSidebar() && !open ? 'true' : 'false');
    }

    if (backdrop instanceof HTMLElement) {
        backdrop.hidden = !open;
    }
}

function syncSidebarState() {
    if (!isMobileSidebar()) {
        setSidebarOpen(false);
        return;
    }

    var sidebar = document.getElementById('app-sidebar');

    if (sidebar) {
        sidebar.setAttribute('aria-hidden', document.body.classList.contains('sidebar-open') ? 'false' : 'true');
    }
}

function initBackToTop() {
    var button = document.querySelector('[data-back-to-top]');

    if (!(button instanceof HTMLButtonElement)) {
        return;
    }

    var syncVisibility = function () {
        var shouldShow = window.scrollY > 320;
        button.hidden = !shouldShow;
        button.classList.toggle('is-visible', shouldShow);
    };

    syncVisibility();
    window.addEventListener('scroll', syncVisibility, { passive: true });
}

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

    if (form.hasAttribute('data-history-modal') && form.dataset.historyConfirmed !== '1') {
        event.preventDefault();
        openHistoryModal(form, submitter instanceof HTMLElement ? submitter : null);
        return;
    }

    if (form.dataset.historyConfirmed === '1') {
        delete form.dataset.historyConfirmed;
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

function openHistoryModal(form, submitter) {
    var backdrop = document.querySelector('[data-history-backdrop]');
    var summaryBox = document.querySelector('[data-history-modal-summary]');
    var textarea = document.querySelector('[data-history-textarea]');
    var confirmButton = document.querySelector('[data-history-confirm]');
    var cancelButton = document.querySelector('[data-history-cancel]');
    var pgeDateField = document.querySelector('[data-history-pge-date-field]');
    var pgeDateInput = document.querySelector('[data-history-pge-date]');
    var observationInput = form.querySelector('[data-history-observation]');

    if (!backdrop || !summaryBox || !textarea || !confirmButton || !cancelButton || !(observationInput instanceof HTMLInputElement)) {
        form.dataset.historyConfirmed = '1';
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit(submitter instanceof HTMLElement ? submitter : undefined);
            return;
        }
        form.submit();
        return;
    }

    renderHistorySummary(summaryBox, buildHistorySummary(form), form);

    textarea.value = '';
    configureHistoryPgeDate(form, pgeDateField, pgeDateInput);
    backdrop.hidden = false;
    if (pgeDateInput instanceof HTMLInputElement && pgeDateField instanceof HTMLElement && !pgeDateField.hidden) {
        pgeDateInput.focus();
    } else {
        textarea.focus();
    }

    var close = function () {
        backdrop.hidden = true;
        confirmButton.removeEventListener('click', confirm);
        cancelButton.removeEventListener('click', close);
        document.removeEventListener('keydown', onKeydown);
    };

    var confirm = function () {
        if (pgeDateInput instanceof HTMLInputElement && pgeDateField instanceof HTMLElement && !pgeDateField.hidden) {
            if (!pgeDateInput.value) {
                pgeDateInput.setCustomValidity(pgeDateInput.dataset.validationMessage || 'Informe a data obrigatória.');
                pgeDateInput.reportValidity();
                return;
            }

            pgeDateInput.setCustomValidity('');

            var dateConfig = historyStatusDateConfig(form);
            var pgeTarget = dateConfig ? dateConfig.target : null;

            if (pgeTarget instanceof HTMLInputElement) {
                pgeTarget.value = pgeDateInput.value;
            }
        }

        observationInput.value = textarea.value.trim();
        close();
        form.dataset.historyConfirmed = '1';
        pendingHistorySubmitter = submitter;

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit(submitter instanceof HTMLElement ? submitter : undefined);
        } else if (submitter instanceof HTMLElement) {
            submitter.click();
        } else {
            form.submit();
        }

        pendingHistorySubmitter = null;
    };

    var onKeydown = function (event) {
        if (event.key === 'Escape') {
            close();
        }
    };

    confirmButton.addEventListener('click', confirm);
    cancelButton.addEventListener('click', close);
    document.addEventListener('keydown', onKeydown);
}

function buildHistorySummary(form) {
    var lines = [];
    var baseSummary = form.getAttribute('data-history-summary') || 'Alteração no decreto';
    var campo = form.querySelector('input[name="campo"]');
    var valor = form.querySelector('select[name="valor"]');
    var files = [];
    var changedFields = [];

    lines.push(baseSummary);

    if (campo instanceof HTMLInputElement && valor instanceof HTMLSelectElement) {
        var selectedOption = valor.options[valor.selectedIndex];
        lines.push('Novo valor: ' + (selectedOption ? selectedOption.textContent.trim() : valor.value));

        if (campo.value === 'homologacao_status_id' && selectedOptionCode(valor) === 'ENVIADO_PGE') {
            lines.push('Informe a data de envio à PGE antes de confirmar.');
        }

        if (campo.value === 'homologacao_status_id' && selectedOptionCode(valor) === 'HOMOLOGADO') {
            lines.push('Informe a data de homologação antes de confirmar.');
            lines.push('O status da PGE será marcado como Aprovado e a contagem será encerrada.');
        }

        if (campo.value === 'homologacao_status_id' && selectedOptionCode(valor) === 'NAO_HOMOLOGADO') {
            lines.push('Informe a data da não homologação antes de confirmar.');
            lines.push('O status da PGE será marcado como Reprovado.');
        }
    } else {
        changedFields = collectChangedFields(form);

        changedFields.slice(0, 8).forEach(function (item) {
            lines.push(item.label + ': ' + item.value);
        });

        if (changedFields.length > 8) {
            lines.push('Outros campos alterados: ' + (changedFields.length - 8));
        }
    }

    form.querySelectorAll('input[type="file"]').forEach(function (input) {
        Array.prototype.forEach.call(input.files || [], function (file) {
            files.push(file.name);
        });
    });

    if (files.length > 0) {
        lines.push('Anexo(s): ' + files.join(', '));
    }

    return lines;
}

function renderHistorySummary(container, lines, form) {
    var title = Array.isArray(lines) && lines.length > 0 ? lines[0] : 'Alteração no decreto';
    var changes = historyChangesForForm(form);
    var notes = Array.isArray(lines) ? lines.slice(1).filter(function (line) {
        if (line.indexOf('Novo valor:') === 0 || line.indexOf('Anexo(s):') === 0) {
            return false;
        }

        return !changes.some(function (change) {
            return line === change.label + ': ' + change.after;
        });
    }) : [];
    var files = historyFilesForForm(form);

    container.innerHTML = '';

    var header = document.createElement('div');
    var eyebrow = document.createElement('span');
    var heading = document.createElement('strong');

    header.className = 'history-summary-head';
    eyebrow.textContent = 'Registro de alteração';
    heading.textContent = title;
    header.appendChild(eyebrow);
    header.appendChild(heading);
    container.appendChild(header);

    if (changes.length > 0) {
        var list = document.createElement('div');
        list.className = 'history-change-list';

        changes.forEach(function (change) {
            var item = document.createElement('div');
            var field = document.createElement('div');
            var fieldLabel = document.createElement('span');
            var fieldName = document.createElement('strong');
            var values = document.createElement('div');

            item.className = 'history-change-item';
            field.className = 'history-change-field';
            fieldLabel.textContent = 'Campo editado';
            fieldName.textContent = change.label || 'Campo não identificado';
            field.appendChild(fieldLabel);
            field.appendChild(fieldName);

            values.className = 'history-change-values';
            values.appendChild(historyValueBlock('Valor atual', change.before || 'Não informado'));
            values.appendChild(historyValueBlock('Novo valor', change.after || 'Não informado', true));

            item.appendChild(field);
            item.appendChild(values);
            list.appendChild(item);
        });

        container.appendChild(list);
    } else if (files.length === 0) {
        var empty = document.createElement('p');
        empty.className = 'history-summary-note';
        empty.textContent = 'Nenhum campo alterado foi identificado automaticamente.';
        container.appendChild(empty);
    }

    if (files.length > 0) {
        var fileBlock = document.createElement('div');
        var fileLabel = document.createElement('span');
        var fileText = document.createElement('strong');

        fileBlock.className = 'history-file-list';
        fileLabel.textContent = 'Anexo(s)';
        fileText.textContent = files.join(', ');
        fileBlock.appendChild(fileLabel);
        fileBlock.appendChild(fileText);
        container.appendChild(fileBlock);
    }

    notes.forEach(function (note) {
        var item = document.createElement('p');
        item.className = 'history-summary-note';
        item.textContent = note;
        container.appendChild(item);
    });
}

function historyChangesForForm(form) {
    var campo = form.querySelector('input[name="campo"]');
    var valor = form.querySelector('select[name="valor"]');

    if (campo instanceof HTMLInputElement && valor instanceof HTMLSelectElement) {
        var selectedOption = valor.options[valor.selectedIndex];
        var currentOption = Array.prototype.find.call(valor.options, function (optionItem) {
            return optionItem.defaultSelected;
        });

        return [{
            label: historyFieldLabel(campo.value),
            before: currentOption ? currentOption.textContent.trim() : 'Não informado',
            after: selectedOption ? selectedOption.textContent.trim() : valor.value || 'Não informado'
        }];
    }

    return collectHistoryChangedFields(form).slice(0, 8).map(function (item) {
        return {
            label: item.label,
            before: item.before || 'Não informado',
            after: item.value || 'Não informado'
        };
    });
}

function historyFilesForForm(form) {
    var files = [];

    form.querySelectorAll('input[type="file"]').forEach(function (input) {
        Array.prototype.forEach.call(input.files || [], function (file) {
            files.push(file.name);
        });
    });

    return files;
}

function historyValueBlock(label, value, isNew) {
    var block = document.createElement('div');
    var caption = document.createElement('span');
    var text = document.createElement('strong');

    block.className = 'history-change-value' + (isNew ? ' is-new' : '');
    caption.textContent = label;
    text.textContent = value;
    block.appendChild(caption);
    block.appendChild(text);

    return block;
}

function historyFieldLabel(field) {
    var labels = {
        homologacao_status_id: 'Homologação',
        reconhecimento_status_id: 'Reconhecimento',
        status_envio_pge_id: 'Envio à PGE',
        analista_id: 'Analista'
    };

    return labels[field] || String(field || 'Campo').replace(/_/g, ' ');
}

function configureHistoryPgeDate(form, field, input) {
    if (!(field instanceof HTMLElement) || !(input instanceof HTMLInputElement)) {
        return;
    }

    var config = historyStatusDateConfig(form);
    var label = field.querySelector('label');

    field.hidden = !config;
    input.required = !!config;
    input.value = config && config.target instanceof HTMLInputElement ? config.target.value : '';
    input.dataset.validationMessage = config ? config.message : '';
    if (label instanceof HTMLLabelElement && config) {
        label.textContent = config.label;
    }
    input.setCustomValidity('');
}

function historyStatusDateConfig(form) {
    var statusField = form.querySelector('input[name="campo"]');
    var valueSelect = form.querySelector('select[name="valor"]');

    if (!(statusField instanceof HTMLInputElement) || statusField.value !== 'homologacao_status_id' || !(valueSelect instanceof HTMLSelectElement)) {
        return null;
    }

    var code = selectedOptionCode(valueSelect);

    if (code === 'ENVIADO_PGE') {
        return {
            label: 'Data de envio à PGE',
            message: 'Informe a data de envio à PGE.',
            target: historyPgeDateTarget(form)
        };
    }

    if (code === 'HOMOLOGADO') {
        return {
            label: 'Data de homologação',
            message: 'Informe a data de homologação.',
            target: historyHomologacaoDateTarget(form)
        };
    }

    if (code === 'NAO_HOMOLOGADO') {
        return {
            label: 'Data da não homologação',
            message: 'Informe a data da não homologação.',
            target: historyHomologacaoDateTarget(form)
        };
    }

    return null;
}

function historyPgeDateTarget(form) {
    var target = form.querySelector('[data-pge-date-target]');

    if (target instanceof HTMLInputElement) {
        return target;
    }

    target = form.querySelector('[data-pge-date-input]');

    if (target instanceof HTMLInputElement) {
        return target;
    }

    target = form.querySelector('input[name="data_envio_pge"]');

    return target instanceof HTMLInputElement ? target : null;
}

function historyHomologacaoDateTarget(form) {
    var target = form.querySelector('[data-homologacao-date-target]');

    if (target instanceof HTMLInputElement) {
        return target;
    }

    target = form.querySelector('[data-homologacao-date-input]');

    if (target instanceof HTMLInputElement) {
        return target;
    }

    target = form.querySelector('input[name="data_decreto_homologacao"]');

    return target instanceof HTMLInputElement ? target : null;
}

function selectedOptionCode(select) {
    if (!(select instanceof HTMLSelectElement)) {
        return '';
    }

    var option = select.options[select.selectedIndex];

    return option ? option.getAttribute('data-codigo') || '' : '';
}

function selectOptionByCode(select, code) {
    if (!(select instanceof HTMLSelectElement)) {
        return false;
    }

    var found = Array.prototype.find.call(select.options, function (option) {
        return option.getAttribute('data-codigo') === code;
    });

    if (!found) {
        return false;
    }

    select.value = found.value;
    return true;
}

function initPgeStatusSync() {
    document.querySelectorAll('.decree-form').forEach(function (form) {
        initHomologacaoDateSync(form);
    });
}

function initHomologacaoDateSync(form) {
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    var dateInput = form.querySelector('[data-pge-date-input]');
    var pgeProtocolInput = form.querySelector('[data-pge-protocol-input]');
    var homologacaoDateInput = form.querySelector('[data-homologacao-date-input]');
    var homologacaoSelect = form.querySelector('select[name="homologacao_status_id"]');

    if (!(homologacaoSelect instanceof HTMLSelectElement)) {
        return;
    }

    var dateField = dateInput instanceof HTMLInputElement ? dateInput.closest('[data-pge-date-form-field]') || dateInput.closest('.field') : null;
    var pgeProtocolField = pgeProtocolInput instanceof HTMLInputElement ? pgeProtocolInput.closest('[data-pge-protocol-form-field]') || pgeProtocolInput.closest('.field') : null;
    var homologacaoDateField = homologacaoDateInput instanceof HTMLInputElement ? homologacaoDateInput.closest('[data-homologacao-date-form-field]') || homologacaoDateInput.closest('.field') : null;
    var homologacaoDateLabel = form.querySelector('[data-homologacao-date-label]');
    var homologacaoDateHelp = form.querySelector('[data-homologacao-date-help]');
    var pgeStatusPreview = form.querySelector('[data-pge-status-preview]');

    var syncHomologacaoDateFields = function () {
        var homologacaoCode = selectedOptionCode(homologacaoSelect);
        var shouldShowPgeDate = homologacaoCode === 'ENVIADO_PGE';
        var shouldShowHomologacaoDate = ['HOMOLOGADO', 'NAO_HOMOLOGADO'].indexOf(homologacaoCode) >= 0;

        if (dateField instanceof HTMLElement) {
            dateField.hidden = !shouldShowPgeDate;
        }

        if (pgeProtocolField instanceof HTMLElement) {
            pgeProtocolField.hidden = !shouldShowPgeDate;
        }

        if (homologacaoDateField instanceof HTMLElement) {
            homologacaoDateField.hidden = !shouldShowHomologacaoDate;
        }

        if (dateInput instanceof HTMLInputElement) {
            dateInput.required = shouldShowPgeDate;
        }

        if (homologacaoDateInput instanceof HTMLInputElement) {
            homologacaoDateInput.required = shouldShowHomologacaoDate;
        }

        if (homologacaoDateLabel instanceof HTMLElement) {
            homologacaoDateLabel.textContent = homologacaoCode === 'NAO_HOMOLOGADO' ? 'Data da n\u00e3o homologa\u00e7\u00e3o' : 'Data de homologa\u00e7\u00e3o';
        }

        if (homologacaoDateHelp instanceof HTMLElement) {
            homologacaoDateHelp.textContent = homologacaoCode === 'NAO_HOMOLOGADO'
                ? 'Informe a data oficial da n\u00e3o homologa\u00e7\u00e3o.'
                : 'Informe a data oficial da homologa\u00e7\u00e3o.';
        }

        if (!shouldShowPgeDate && dateInput instanceof HTMLInputElement) {
            dateInput.value = '';
        }

        if (!shouldShowPgeDate && pgeProtocolInput instanceof HTMLInputElement) {
            pgeProtocolInput.value = '';
        }

        if (!shouldShowHomologacaoDate && homologacaoDateInput instanceof HTMLInputElement) {
            homologacaoDateInput.value = '';
        }

        updatePgeStatusPreview(pgeStatusPreview, homologacaoCode, dateInput instanceof HTMLInputElement ? dateInput.value : '');
    };

    homologacaoSelect.addEventListener('change', syncHomologacaoDateFields);
    if (dateInput instanceof HTMLInputElement) {
        dateInput.addEventListener('input', syncHomologacaoDateFields);
        dateInput.addEventListener('change', syncHomologacaoDateFields);
    }
    syncHomologacaoDateFields();
}

function updatePgeStatusPreview(container, homologacaoCode, dataEnvioPge) {
    if (!(container instanceof HTMLElement)) {
        return;
    }

    var status = previewPgeStatus(homologacaoCode, dataEnvioPge);
    var badge = document.createElement('span');

    badge.className = 'status-badge ' + status.className;
    badge.textContent = status.label;

    container.replaceChildren(badge);
}

function previewPgeStatus(homologacaoCode, dataEnvioPge) {
    if (homologacaoCode === 'HOMOLOGADO') {
        return { label: 'Aprovado', className: 'badge-success' };
    }

    if (homologacaoCode === 'NAO_HOMOLOGADO') {
        return { label: 'Reprovado', className: 'badge-danger' };
    }

    if (homologacaoCode === 'ENVIADO_PGE' && dataEnvioPge) {
        var sentDate = new Date(dataEnvioPge + 'T00:00:00');
        var today = new Date();
        var todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        var diffDays = Math.floor((todayDate.getTime() - sentDate.getTime()) / 86400000);

        if (!Number.isNaN(diffDays) && diffDays > 7) {
            return { label: 'Pendente', className: 'badge-warning' };
        }

        return { label: 'No prazo', className: 'badge-warning' };
    }

    return { label: 'Não registrado', className: 'badge-muted' };
}

function collectHistoryChangedFields(form) {
    var fields = [];
    var radioNames = {};

    form.querySelectorAll('input, select, textarea').forEach(function (control) {
        if (!(control instanceof HTMLInputElement) && !(control instanceof HTMLSelectElement) && !(control instanceof HTMLTextAreaElement)) {
            return;
        }

        if (!control.name || control.type === 'hidden' || control.type === 'file' || control.name === '_csrf' || control.name === 'historico_observacao') {
            return;
        }

        if (control instanceof HTMLInputElement && control.type === 'radio') {
            if (radioNames[control.name]) {
                return;
            }

            radioNames[control.name] = true;

            var checked = form.querySelector('input[name="' + cssEscape(control.name) + '"]:checked');
            var defaultChecked = Array.prototype.find.call(form.querySelectorAll('input[name="' + cssEscape(control.name) + '"]'), function (radio) {
                return radio.defaultChecked;
            });

            if ((checked ? checked.value : '') === (defaultChecked ? defaultChecked.value : '')) {
                return;
            }

            fields.push({
                label: fieldLabel(control),
                before: defaultChecked ? radioText(defaultChecked) : 'Não informado',
                value: checked ? radioText(checked) : 'Não informado'
            });
            return;
        }

        if (control instanceof HTMLSelectElement) {
            var defaultOption = Array.prototype.find.call(control.options, function (optionItem) {
                return optionItem.defaultSelected;
            });
            var currentOption = control.options[control.selectedIndex] || null;

            if (control.value === (defaultOption ? defaultOption.value : '')) {
                return;
            }

            fields.push({
                label: fieldLabel(control),
                before: defaultOption ? defaultOption.textContent.trim() : 'Não informado',
                value: currentOption ? currentOption.textContent.trim() : control.value || 'Não informado'
            });
            return;
        }

        if (control.value === control.defaultValue) {
            return;
        }

        fields.push({
            label: fieldLabel(control),
            before: control.defaultValue || 'Não informado',
            value: control.value || 'Não informado'
        });
    });

    return fields;
}

function collectChangedFields(form) {
    var fields = [];
    var radioNames = {};

    form.querySelectorAll('input, select, textarea').forEach(function (control) {
        if (!(control instanceof HTMLInputElement) && !(control instanceof HTMLSelectElement) && !(control instanceof HTMLTextAreaElement)) {
            return;
        }

        if (!control.name || control.type === 'hidden' || control.type === 'file' || control.name === '_csrf' || control.name === 'historico_observacao') {
            return;
        }

        if (control instanceof HTMLInputElement && control.type === 'radio') {
            if (radioNames[control.name]) {
                return;
            }

            radioNames[control.name] = true;

            var checked = form.querySelector('input[name="' + cssEscape(control.name) + '"]:checked');
            var defaultChecked = Array.prototype.find.call(form.querySelectorAll('input[name="' + cssEscape(control.name) + '"]'), function (radio) {
                return radio.defaultChecked;
            });

            if ((checked ? checked.value : '') === (defaultChecked ? defaultChecked.value : '')) {
                return;
            }

            fields.push({
                label: fieldLabel(control),
                before: defaultChecked ? radioText(defaultChecked) : 'Não informado',
                value: checked ? radioText(checked) : 'Não informado'
            });
            return;
        }

        if (control instanceof HTMLSelectElement) {
            var defaultValue = Array.prototype.find.call(control.options, function (option) {
                return option.defaultSelected;
            });
            var currentText = control.options[control.selectedIndex] ? control.options[control.selectedIndex].textContent.trim() : control.value;

            if (control.value === (defaultValue ? defaultValue.value : '')) {
                return;
            }

            fields.push({ label: fieldLabel(control), value: currentText || 'Não informado' });
            return;
        }

        if (control.value === control.defaultValue) {
            return;
        }

        fields.push({ label: fieldLabel(control), value: control.value || 'Não informado' });
    });

    return fields;
}

function fieldLabel(control) {
    var field = control.closest('.field');
    var label = field ? field.querySelector('label, .field-label') : null;

    if (label) {
        return label.textContent.replace(/\s+/g, ' ').trim();
    }

    return control.name.replace(/_/g, ' ');
}

function radioText(radio) {
    var label = radio.closest('label');
    var text = label ? label.textContent.replace(/\s+/g, ' ').trim() : radio.value;

    return text || radio.value;
}

function cssEscape(value) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
        return window.CSS.escape(value);
    }

    return String(value).replace(/"/g, '\\"');
}

function option(value, label) {
    var item = document.createElement('option');
    item.value = value;
    item.textContent = label;
    return item;
}

function cobradeImageUrl(data) {
    var path = data && data.simbologia ? String(data.simbologia).trim() : '';
    var codigo = data && data.codigo ? String(data.codigo).trim() : '';

    if (path) {
        path = path.replace(/^\/+/, '');
        path = path.replace(/^cobrade_simbologia\//, 'assets/images/cobrade_simbologia/');
        return appBaseUrl() + '/' + path;
    }

    if (!codigo) {
        return '';
    }

    return appBaseUrl() + '/assets/images/cobrade_simbologia/simbologia_cobrade_' + codigo.replace(/\./g, '_') + '.png';
}

function renderCobradePreview(data) {
    var preview = document.querySelector('[data-cobrade-preview]');
    var previewImg = document.querySelector('[data-cobrade-preview-img]');
    var previewSymbol = document.querySelector('[data-cobrade-preview-symbol]');
    var previewTitle = document.querySelector('[data-cobrade-preview-title]');
    var previewMeta = document.querySelector('[data-cobrade-preview-meta]');
    var previewDescricao = document.querySelector('[data-cobrade-preview-descricao]');

    if (!(preview instanceof HTMLElement)) {
        return;
    }

    if (!data || !data.id) {
        preview.hidden = true;
        return;
    }

    var codigo = data.codigo || '';
    var nome = data.nome || '';
    var descricao = data.descricao || 'Sem descricao cadastrada.';
    var imageUrl = cobradeImageUrl(data);

    if (previewTitle instanceof HTMLElement) {
        previewTitle.textContent = (codigo ? codigo + ' - ' : '') + nome;
    }

    if (previewMeta instanceof HTMLElement) {
        previewMeta.textContent = [data.grupo_nome, data.subgrupo_nome, data.tipo_nome].filter(Boolean).join(' / ');
    }

    if (previewDescricao instanceof HTMLElement) {
        previewDescricao.textContent = descricao;
    }

    if (previewSymbol instanceof HTMLElement) {
        previewSymbol.textContent = codigo || 'COBRADE';
    }

    if (previewImg instanceof HTMLImageElement) {
        if (imageUrl) {
            previewImg.src = imageUrl;
            previewImg.alt = codigo ? 'Simbologia COBRADE ' + codigo : 'Simbologia COBRADE';
            previewImg.hidden = false;
        } else {
            previewImg.removeAttribute('src');
            previewImg.alt = '';
            previewImg.hidden = true;
        }
    }

    preview.hidden = false;
}

function fillSelect(select, items, placeholder) {
    if (!select) {
        return;
    }

    select.innerHTML = '';
    select.appendChild(option('', placeholder));

    items.forEach(function (item) {
        var element = option(item.id, item.codigo ? item.codigo + ' - ' + item.nome : item.nome);

        ['grupo_id', 'subgrupo_id', 'tipo_id', 'codigo', 'nome', 'descricao', 'simbologia', 'grupo_nome', 'subgrupo_nome', 'tipo_nome'].forEach(function (key) {
            if (item[key] !== undefined && item[key] !== null) {
                element.dataset[key.replace(/_/g, '-')] = item[key];
            }
        });

        select.appendChild(element);
    });
}

function fetchJson(path) {
    return fetch(path, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function (response) {
        var contentType = response.headers.get('content-type') || '';

        if (contentType.indexOf('application/json') === -1) {
            return response.text().then(function (text) {
                throw {
                    status: response.status,
                    message: text ? text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 120) : 'Resposta invalida do servidor'
                };
            });
        }

        return response.json().then(function (payload) {
            if (!response.ok) {
                payload.status = response.status;
                throw payload;
            }

            return payload;
        });
    });
}

function appBaseUrl() {
    var configuredBaseUrl = document.documentElement.getAttribute('data-app-base-url');

    if (configuredBaseUrl) {
        return configuredBaseUrl.replace(/\/$/, '');
    }

    var path = window.location.pathname || '';
    var routeMarkers = ['/decretos', '/compdecs', '/usuarios', '/painel', '/alterar-senha', '/cobrade', '/anexos'];
    var basePath = '';

    routeMarkers.some(function (marker) {
        var index = path.indexOf(marker);

        if (index >= 0) {
            basePath = path.substring(0, index);
            return true;
        }

        return false;
    });

    return window.location.origin + basePath.replace(/\/$/, '');
}

function cobradeLoadError(select, message) {
    fillSelect(select, [], message || 'Não foi possível carregar');
}

function payloadItems(payload) {
    return payload && Array.isArray(payload.data) ? payload.data : [];
}

function errorMessage(error, fallback) {
    var status = error && error.status ? 'HTTP ' + error.status + ': ' : '';
    var message = error && error.message ? error.message : fallback;

    return status + message;
}

function appRoute(path) {
    return appBaseUrl() + '/' + String(path || '').replace(/^\/+/, '');
}

function initCobradeCascade() {
    var grupoSelect = document.querySelector('[data-cobrade="grupo"]');
    var subgrupoSelect = document.querySelector('[data-cobrade="subgrupo"]');
    var tipoSelect = document.querySelector('[data-cobrade="tipo"]');
    var subtipoSelect = document.querySelector('[data-cobrade="subtipo"]');

    if (!(grupoSelect instanceof HTMLSelectElement)
        || !(subgrupoSelect instanceof HTMLSelectElement)
        || !(tipoSelect instanceof HTMLSelectElement)
        || !(subtipoSelect instanceof HTMLSelectElement)) {
        return;
    }

    var subgrupoOptions = Array.prototype.slice.call(subgrupoSelect.querySelectorAll('option[data-grupo-id]'));
    var tipoOptions = Array.prototype.slice.call(tipoSelect.querySelectorAll('option[data-subgrupo-id]'));
    var subtipoOptions = Array.prototype.slice.call(subtipoSelect.querySelectorAll('option[data-tipo-id]'));
    var selectedSubgrupo = subgrupoSelect.dataset.current || '';
    var selectedTipo = tipoSelect.dataset.current || '';
    var selectedSubtipo = subtipoSelect.dataset.current || '';

    if (!grupoSelect.value && selectedSubtipo) {
        var currentSubtipoOption = subtipoOptions.find(function (sourceOption) {
            return sourceOption.value === selectedSubtipo;
        });

        if (currentSubtipoOption) {
            grupoSelect.value = currentSubtipoOption.dataset.grupoId || '';
            selectedSubgrupo = selectedSubgrupo || currentSubtipoOption.dataset.subgrupoId || '';
            selectedTipo = selectedTipo || currentSubtipoOption.dataset.tipoId || '';
        }
    }

    function placeholder(text) {
        return option('', text);
    }

    function filtrarSubgrupos() {
        var grupoId = grupoSelect.value;

        subgrupoSelect.replaceChildren(placeholder(grupoId ? 'Selecione um subgrupo' : 'Selecione um grupo primeiro'));
        subgrupoSelect.disabled = !grupoId;

        if (!grupoId) {
            filtrarTipos();
            renderCobradePreview(null);
            return;
        }

        subgrupoOptions.forEach(function (sourceOption) {
            if (sourceOption.dataset.grupoId !== grupoId) {
                return;
            }

            var clone = sourceOption.cloneNode(true);

            if (clone.value === selectedSubgrupo) {
                clone.selected = true;
            }

            subgrupoSelect.appendChild(clone);
        });

        selectedSubgrupo = '';
        filtrarTipos();
    }

    function filtrarTipos() {
        var subgrupoId = subgrupoSelect.value;

        tipoSelect.replaceChildren(placeholder(subgrupoId ? 'Selecione um tipo' : 'Selecione um subgrupo primeiro'));
        tipoSelect.disabled = !subgrupoId;

        if (!subgrupoId) {
            filtrarSubtipos();
            renderCobradePreview(null);
            return;
        }

        tipoOptions.forEach(function (sourceOption) {
            if (sourceOption.dataset.subgrupoId !== subgrupoId) {
                return;
            }

            var clone = sourceOption.cloneNode(true);

            if (clone.value === selectedTipo) {
                clone.selected = true;
            }

            tipoSelect.appendChild(clone);
        });

        selectedTipo = '';
        filtrarSubtipos();
    }

    function filtrarSubtipos() {
        var tipoId = tipoSelect.value;

        subtipoSelect.replaceChildren(placeholder(tipoId ? 'Selecione um subtipo' : 'Selecione um tipo primeiro'));
        subtipoSelect.disabled = !tipoId;

        if (!tipoId) {
            renderCobradePreview(null);
            return;
        }

        subtipoOptions.forEach(function (sourceOption) {
            if (sourceOption.dataset.tipoId !== tipoId) {
                return;
            }

            var clone = sourceOption.cloneNode(true);

            if (clone.value === selectedSubtipo) {
                clone.selected = true;
            }

            subtipoSelect.appendChild(clone);
        });

        selectedSubtipo = '';
        atualizarPreview();
    }

    function atualizarPreview() {
        var selectedOption = subtipoSelect.selectedOptions[0];

        if (!(selectedOption instanceof HTMLOptionElement) || !selectedOption.value) {
            renderCobradePreview(null);
            return;
        }

        renderCobradePreview({
            id: selectedOption.value,
            codigo: selectedOption.dataset.codigo || '',
            nome: selectedOption.dataset.nome || selectedOption.textContent || '',
            descricao: selectedOption.dataset.descricao || '',
            simbologia: selectedOption.dataset.simbologia || '',
            grupo_nome: selectedOption.dataset.grupoNome || '',
            subgrupo_nome: selectedOption.dataset.subgrupoNome || '',
            tipo_nome: selectedOption.dataset.tipoNome || ''
        });
    }

    grupoSelect.addEventListener('change', function () {
        selectedSubgrupo = '';
        selectedTipo = '';
        selectedSubtipo = '';
        filtrarSubgrupos();
    });

    subgrupoSelect.addEventListener('change', function () {
        selectedTipo = '';
        selectedSubtipo = '';
        filtrarTipos();
    });

    tipoSelect.addEventListener('change', function () {
        selectedSubtipo = '';
        filtrarSubtipos();
    });

    subtipoSelect.addEventListener('change', atualizarPreview);
    filtrarSubgrupos();
}

document.addEventListener('DOMContentLoaded', initCobradeCascade);
document.addEventListener('DOMContentLoaded', initPgeStatusSync);

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
    var municipioSelect = document.querySelector('[data-ubm-municipio]');
    var compdecFields = document.querySelectorAll('[data-compdec-field]');

    if (!(municipioSelect instanceof HTMLSelectElement)) {
        return;
    }

    var notRegisteredText = 'Não foi registrado';

    function fieldValue(data, key) {
        if (!data || data[key] === null || data[key] === undefined || String(data[key]).trim() === '') {
            return notRegisteredText;
        }

        return String(data[key]);
    }

    function fillCompdecFields(data) {
        compdecFields.forEach(function (field) {
            if (!(field instanceof HTMLInputElement)) {
                return;
            }

            var key = field.getAttribute('data-compdec-field') || '';
            field.value = fieldValue(data, key);
        });
    }

    function loadCompdec() {
        var municipioId = municipioSelect.value || '';

        if (municipioId === '') {
            fillCompdecFields(null);
            return;
        }

        fetchJson(appBaseUrl() + '/compdecs/municipio/' + encodeURIComponent(municipioId))
            .then(function (payload) {
                var data = payload.data || null;
                fillCompdecFields(data);
            })
            .catch(function () {
                fillCompdecFields({
                    situacao_compdec: notRegisteredText,
                    ubm_nome: notRegisteredText,
                    regiao_integracao: notRegisteredText,
                    prefeito: notRegisteredText,
                    coordenador: notRegisteredText,
                    telefone: notRegisteredText,
                    email: notRegisteredText
                });
            });
    }

    municipioSelect.addEventListener('change', loadCompdec);

    if (municipioSelect.value) {
        loadCompdec();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var zones = document.querySelectorAll('[data-attachment-zone]');
    var allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

    function extension(file) {
        var parts = String(file.name || '').split('.');

        return parts.length > 1 ? parts.pop().toLowerCase() : '';
    }

    function validFiles(files) {
        return Array.prototype.slice.call(files || []).filter(function (file) {
            return allowedExtensions.indexOf(extension(file)) >= 0;
        });
    }

    function setFiles(input, files) {
        var dataTransfer = new DataTransfer();

        files.forEach(function (file) {
            dataTransfer.items.add(file);
        });

        input.files = dataTransfer.files;
    }

    function currentFiles(input) {
        return Array.prototype.slice.call(input.files || []);
    }

    function addFiles(input, files) {
        var selectedFiles = validFiles(files);

        if (!input.multiple) {
            setFiles(input, selectedFiles.slice(-1));
            return;
        }

        setFiles(input, currentFiles(input).concat(selectedFiles));
    }

    function formatSize(size) {
        if (!size) {
            return '0 KB';
        }

        if (size >= 1024 * 1024) {
            return (size / 1024 / 1024).toFixed(1).replace('.', ',') + ' MB';
        }

        return (size / 1024).toFixed(1).replace('.', ',') + ' KB';
    }

    function renderList(zone) {
        var input = zone.querySelector('[data-attachment-input]');
        var list = zone.querySelector('[data-attachment-list]');

        if (!(input instanceof HTMLInputElement) || !(list instanceof HTMLElement)) {
            return;
        }

        var files = currentFiles(input);
        list.innerHTML = '';

        if (files.length === 0) {
            var empty = document.createElement('li');
            empty.textContent = 'Nenhum arquivo selecionado.';
            list.appendChild(empty);
            return;
        }

        files.forEach(function (file, index) {
            var item = document.createElement('li');
            var name = document.createElement('span');
            var remove = document.createElement('button');

            name.textContent = file.name + ' (' + formatSize(file.size) + ')';
            remove.type = 'button';
            remove.textContent = 'Remover';
            remove.addEventListener('click', function () {
                var updatedFiles = currentFiles(input).filter(function (_, fileIndex) {
                    return fileIndex !== index;
                });
                setFiles(input, updatedFiles);
                renderList(zone);
            });

            item.appendChild(name);
            item.appendChild(remove);
            list.appendChild(item);
        });
    }

    zones.forEach(function (zone) {
        var input = zone.querySelector('[data-attachment-input]');

        if (!(zone instanceof HTMLElement) || !(input instanceof HTMLInputElement)) {
            return;
        }

        input.addEventListener('change', function () {
            renderList(zone);
        });

        if (typeof DataTransfer === 'undefined') {
            renderList(zone);
            return;
        }

        zone.addEventListener('dragover', function (event) {
            event.preventDefault();
            zone.classList.add('is-dragover');
        });

        zone.addEventListener('dragleave', function () {
            zone.classList.remove('is-dragover');
        });

        zone.addEventListener('drop', function (event) {
            event.preventDefault();
            zone.classList.remove('is-dragover');
            addFiles(input, event.dataTransfer ? event.dataTransfer.files : []);
            renderList(zone);
        });

        zone.addEventListener('paste', function (event) {
            if (!event.clipboardData || !event.clipboardData.files || event.clipboardData.files.length === 0) {
                return;
            }

            event.preventDefault();
            addFiles(input, event.clipboardData.files);
            renderList(zone);
        });

        renderList(zone);
    });
});

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
            target.textContent = 'Não foi possível gerar o QR Code.';
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

            target.textContent = 'Não foi possível carregar o QR Code. Use a chave manual.';
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
