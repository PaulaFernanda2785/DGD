var pendingSubmitter = null;
var pendingHistorySubmitter = null;

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

    summaryBox.innerHTML = '';
    buildHistorySummary(form).forEach(function (line) {
        var item = document.createElement('p');
        item.textContent = line;
        summaryBox.appendChild(item);
    });

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
                pgeDateInput.setCustomValidity('Informe a data de envio à PGE.');
                pgeDateInput.reportValidity();
                return;
            }

            pgeDateInput.setCustomValidity('');

            var pgeTarget = form.querySelector('[data-pge-date-target]');

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

        if (campo.value === 'status_envio_pge_id' && selectedOptionCode(valor) === 'ENVIADO_PGE') {
            lines.push('Informe a data de envio à PGE antes de confirmar.');
        }

        if (campo.value === 'homologacao_status_id' && selectedOptionCode(valor) === 'HOMOLOGADO') {
            lines.push('O envio à PGE será marcado como Concluído e a contagem será encerrada.');
        }

        if (campo.value === 'homologacao_status_id' && selectedOptionCode(valor) !== 'HOMOLOGADO') {
            lines.push('Se o decreto estava homologado, o estado anterior do envio à PGE será restaurado.');
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

function configureHistoryPgeDate(form, field, input) {
    if (!(field instanceof HTMLElement) || !(input instanceof HTMLInputElement)) {
        return;
    }

    var statusField = form.querySelector('input[name="campo"]');
    var valueSelect = form.querySelector('select[name="valor"]');
    var target = form.querySelector('[data-pge-date-target]');
    var shouldAskDate = false;

    if (statusField instanceof HTMLInputElement && statusField.value === 'status_envio_pge_id' && valueSelect instanceof HTMLSelectElement) {
        shouldAskDate = selectedOptionCode(valueSelect) === 'ENVIADO_PGE';
    }

    field.hidden = !shouldAskDate;
    input.required = shouldAskDate;
    input.value = shouldAskDate && target instanceof HTMLInputElement ? target.value : '';
    input.setCustomValidity('');
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
    var dateInput = document.querySelector('input[name="data_envio_pge"]');
    var statusSelect = document.querySelector('select[name="status_envio_pge_id"]');
    var homologacaoSelect = document.querySelector('select[name="homologacao_status_id"]');
    var statusBeforeHomologacao = null;

    if (!(dateInput instanceof HTMLInputElement) || !(statusSelect instanceof HTMLSelectElement)) {
        return;
    }

    var isHomologado = function () {
        return homologacaoSelect instanceof HTMLSelectElement && selectedOptionCode(homologacaoSelect) === 'HOMOLOGADO';
    };

    var syncStatusFromDate = function () {
        if (!dateInput.value || isHomologado() || selectedOptionCode(statusSelect) === 'CONCLUIDO') {
            return;
        }

        selectOptionByCode(statusSelect, 'ENVIADO_PGE');
    };

    var syncStatusFromHomologacao = function () {
        if (!(homologacaoSelect instanceof HTMLSelectElement)) {
            return;
        }

        if (isHomologado()) {
            if (selectedOptionCode(statusSelect) !== 'CONCLUIDO') {
                statusBeforeHomologacao = statusSelect.value;
            }

            selectOptionByCode(statusSelect, 'CONCLUIDO');
            return;
        }

        if (statusBeforeHomologacao !== null) {
            statusSelect.value = statusBeforeHomologacao;
            statusBeforeHomologacao = null;
        }

        syncStatusFromDate();
    };

    dateInput.addEventListener('change', syncStatusFromDate);
    dateInput.addEventListener('input', syncStatusFromDate);
    if (homologacaoSelect instanceof HTMLSelectElement) {
        homologacaoSelect.addEventListener('change', syncStatusFromHomologacao);
    }

    syncStatusFromHomologacao();
    syncStatusFromDate();
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
    fillSelect(select, [], message || 'Nao foi possivel carregar');
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
