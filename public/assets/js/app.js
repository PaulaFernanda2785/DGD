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

    var panelPrintOpen = target.closest('[data-panel-print-open]');

    if (panelPrintOpen) {
        event.preventDefault();
        openPanelPrintModal(panelPrintOpen);
        return;
    }

    var decreePrintOpen = target.closest('[data-decree-print-open]');

    if (decreePrintOpen) {
        event.preventDefault();
        openDecreePrintModal(decreePrintOpen);
    }

    if (target.matches('[data-decree-print-backdrop]')) {
        closeDecreePrintModal();
        return;
    }

    var decreePrintClose = target.closest('[data-decree-print-close]');

    if (decreePrintClose) {
        closeDecreePrintModal();
    }

    var decreePrintConfirm = target.closest('[data-decree-print-confirm]');

    if (decreePrintConfirm) {
        printDecreeReport();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
        closeSidebar();
    }

    if (event.key === 'Escape') {
        closeDecreePrintModal();
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

function decreePrintElements() {
    return {
        backdrop: document.querySelector('[data-decree-print-backdrop]'),
        body: document.querySelector('[data-decree-print-body]'),
        title: document.querySelector('[data-decree-print-title]'),
        printButton: document.querySelector('[data-decree-print-confirm]')
    };
}

function openDecreePrintModal(trigger) {
    var elements = decreePrintElements();
    var url = trigger instanceof HTMLElement ? trigger.getAttribute('data-report-url') : '';

    if (!(elements.backdrop instanceof HTMLElement) || !(elements.body instanceof HTMLElement) || !url) {
        return;
    }

    elements.backdrop.hidden = false;
    elements.backdrop.dataset.decreePrintFilename = 'relatorio-decreto.pdf';
    elements.body.innerHTML = '<div class="panel-empty">Carregando relatório do decreto...</div>';

    if (elements.title instanceof HTMLElement) {
        elements.title.textContent = 'Relatório para impressão';
    }

    if (elements.printButton instanceof HTMLButtonElement) {
        elements.printButton.disabled = true;
        elements.printButton.setAttribute('aria-busy', 'true');
    }

    fetch(url, {
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Não foi possível carregar o relatório.');
            }

            return response.json();
        })
        .then(function (data) {
            if (!data || data.success !== true || !data.html) {
                throw new Error(data && data.message ? data.message : 'Relatório indisponível.');
            }

            elements.body.innerHTML = data.html;
            elements.backdrop.dataset.decreePrintFilename = data.filename || 'relatorio-decreto.pdf';

            if (elements.title instanceof HTMLElement) {
                elements.title.textContent = data.title || 'Relatório para impressão';
            }

            if (elements.printButton instanceof HTMLButtonElement) {
                elements.printButton.disabled = false;
                elements.printButton.removeAttribute('aria-busy');
                elements.printButton.focus();
            }
        })
        .catch(function (error) {
            elements.body.innerHTML = '<div class="panel-empty">' + escapeText(error.message || 'Erro ao carregar relatório.') + '</div>';

            if (elements.printButton instanceof HTMLButtonElement) {
                elements.printButton.disabled = true;
                elements.printButton.removeAttribute('aria-busy');
            }
        });
}

function openPanelPrintModal(trigger) {
    if (!(trigger instanceof HTMLElement)) {
        return;
    }

    var baseUrl = trigger.getAttribute('data-report-base-url') || trigger.getAttribute('data-report-url') || '';

    if (!baseUrl) {
        return;
    }

    var form = document.querySelector('.panel-filter-form');
    var query = new URLSearchParams();

    if (form instanceof HTMLFormElement) {
        new FormData(form).forEach(function (value, key) {
            value = String(value || '').trim();

            if (value !== '') {
                query.set(key, value);
            }
        });
    }

    trigger.setAttribute('data-report-url', baseUrl + (query.toString() !== '' ? '?' + query.toString() : ''));
    openDecreePrintModal(trigger);
}

function closeDecreePrintModal() {
    var elements = decreePrintElements();

    if (!(elements.backdrop instanceof HTMLElement) || elements.backdrop.hidden) {
        return;
    }

    elements.backdrop.hidden = true;
    delete elements.backdrop.dataset.decreePrintFilename;

    if (elements.body instanceof HTMLElement) {
        elements.body.innerHTML = '<div class="panel-empty">Selecione um decreto para carregar o relatório.</div>';
    }

    cleanupDecreePrintPages();

    if (elements.printButton instanceof HTMLButtonElement) {
        elements.printButton.disabled = false;
        elements.printButton.removeAttribute('aria-busy');
    }
}

function printDecreeReport() {
    var elements = decreePrintElements();
    var report = elements.body instanceof HTMLElement ? elements.body.querySelector('[data-decree-print-content]') : null;

    if (!(elements.backdrop instanceof HTMLElement) || !(report instanceof HTMLElement)) {
        return;
    }

    if (elements.printButton instanceof HTMLButtonElement) {
        elements.printButton.disabled = true;
        elements.printButton.setAttribute('aria-busy', 'true');
    }

    waitForDecreePrintAssets(report).then(function () {
        var pagedReport = prepareDecreePrintPages(report);

        if (!(pagedReport instanceof HTMLElement)) {
            return;
        }

        var previousTitle = document.title;
        var restored = false;

        var restore = function () {
            if (restored) {
                return;
            }

            restored = true;
            document.body.classList.remove('is-printing-decree-report');
            document.title = previousTitle;
            cleanupDecreePrintPages();
            window.removeEventListener('afterprint', restore);

            if (elements.printButton instanceof HTMLButtonElement) {
                elements.printButton.disabled = false;
                elements.printButton.removeAttribute('aria-busy');
            }
        };

        document.title = elements.backdrop.dataset.decreePrintFilename || previousTitle;
        document.body.classList.add('is-printing-decree-report');
        window.addEventListener('afterprint', restore);
        window.print();

        window.setTimeout(restore, 1000);
    }).catch(function () {
        if (elements.printButton instanceof HTMLButtonElement) {
            elements.printButton.disabled = false;
            elements.printButton.removeAttribute('aria-busy');
        }
    });
}

function waitForDecreePrintAssets(report) {
    var images = Array.prototype.filter.call(report.querySelectorAll('img'), function (image) {
        return image instanceof HTMLImageElement && !image.complete;
    });

    if (images.length === 0) {
        return Promise.resolve();
    }

    return Promise.race([
        Promise.all(images.map(function (image) {
            return new Promise(function (resolve) {
                image.addEventListener('load', resolve, { once: true });
                image.addEventListener('error', resolve, { once: true });
            });
        })),
        new Promise(function (resolve) {
            window.setTimeout(resolve, 1500);
        })
    ]);
}

function prepareDecreePrintPages(report) {
    cleanupDecreePrintPages();

    var footer = report.querySelector('.decree-print-footer');
    var contentNodes = Array.prototype.filter.call(report.children, function (node) {
        return node !== footer;
    });

    if (contentNodes.length === 0) {
        return null;
    }

    var container = document.createElement('div');
    container.className = 'decree-print-paged-report is-measuring';
    container.setAttribute('data-decree-print-paged', '');
    document.body.appendChild(container);

    var current = createDecreePrintPage(footer, false);
    container.appendChild(current.page);

    contentNodes.forEach(function (node) {
        current = appendDecreePrintNode(node, footer, container, current);
    });

    var pages = Array.prototype.slice.call(container.querySelectorAll('[data-decree-print-page]'));
    var totalPages = Math.max(pages.length, 1);

    pages.forEach(function (page, index) {
        var number = page.querySelector('[data-decree-print-page-number]');

        if (number instanceof HTMLElement) {
            number.textContent = 'P\u00e1gina ' + (index + 1) + ' de ' + totalPages;
        }
    });

    container.classList.remove('is-measuring');

    return container;
}

function appendDecreePrintNode(node, footer, container, current) {
    var clone = node.cloneNode(true);
    current.body.appendChild(clone);

    if (current.body.children.length > 1 && decreePrintPageOverflows(current)) {
        current.body.removeChild(clone);
        current = createDecreePrintPage(footer, true);
        container.appendChild(current.page);
        current.body.appendChild(clone);
    }

    if (decreePrintPageOverflows(current)) {
        current.body.removeChild(clone);
        return splitDecreePrintNode(node, footer, container, current);
    }

    return current;
}

function splitDecreePrintNode(node, footer, container, current) {
    if (!(node instanceof HTMLElement) || !node.classList.contains('decree-print-section')) {
        var clone = node.cloneNode(true);
        current.body.appendChild(clone);
        current.page.classList.add('decree-print-page-overflow-review');
        return current;
    }

    return splitDecreePrintSection(node, footer, container, current);
}

function splitDecreePrintSection(section, footer, container, current) {
    var title = Array.prototype.find.call(section.children, function (child) {
        return child instanceof HTMLElement && child.tagName.toLowerCase() === 'h3';
    });
    var content = Array.prototype.find.call(section.children, function (child) {
        return child instanceof HTMLElement && child !== title;
    });

    if (!(title instanceof HTMLElement) || !(content instanceof HTMLElement)) {
        current.body.appendChild(section.cloneNode(true));
        return current;
    }

    var plan = createDecreePrintSectionPlan(content);
    var fragment = createDecreePrintSectionFragment(section, title, plan.template, false);
    current.body.appendChild(fragment.section);

    plan.items.forEach(function (item) {
        var clone = item.cloneNode(true);
        fragment.container.appendChild(clone);

        if (decreePrintPageOverflows(current)) {
            fragment.container.removeChild(clone);

            if (fragment.container.children.length <= fragment.fixedChildren) {
                fragment.container.appendChild(clone);
                fragment.section.classList.add('decree-print-section-overflow-review');
                return;
            }

            current = createDecreePrintPage(footer, true);
            container.appendChild(current.page);
            fragment = createDecreePrintSectionFragment(section, title, plan.template, true);
            current.body.appendChild(fragment.section);
            fragment.container.appendChild(clone);
        }
    });

    return current;
}

function createDecreePrintSectionPlan(content) {
    var template = content.cloneNode(false);
    var fixedChildren = 0;
    var items = Array.prototype.slice.call(content.children);

    if (content.classList.contains('decree-print-table')) {
        var header = items.find(function (child) {
            return child instanceof HTMLElement && child.classList.contains('decree-print-row-head');
        });

        if (header instanceof HTMLElement) {
            template.appendChild(header.cloneNode(true));
            fixedChildren = 1;
            items = items.filter(function (child) {
                return child !== header;
            });
        }
    }

    if (items.length === 0) {
        items = [content.cloneNode(true)];
        template = document.createElement('div');
    }

    return {
        fixedChildren: fixedChildren,
        items: items,
        template: template
    };
}

function createDecreePrintSectionFragment(section, title, contentTemplate, continued) {
    var fragment = section.cloneNode(false);
    var heading = title.cloneNode(true);
    var container = contentTemplate.cloneNode(true);

    if (continued) {
        heading.appendChild(document.createTextNode(' (continua\u00e7\u00e3o)'));
    }

    fragment.appendChild(heading);
    fragment.appendChild(container);

    return {
        container: container,
        fixedChildren: container.children.length,
        section: fragment
    };
}

function decreePrintPageOverflows(current) {
    var last = current.body.lastElementChild;

    if (!(last instanceof HTMLElement)) {
        return false;
    }

    var bodyRect = current.body.getBoundingClientRect();
    var lastRect = last.getBoundingClientRect();
    var safeGap = current.page.classList.contains('decree-print-page-first') ? 96 : 48;

    return lastRect.bottom > bodyRect.bottom - safeGap;
}

function createDecreePrintPage(footer, continued) {
    var page = document.createElement('article');
    page.className = 'decree-print-report decree-print-page';
    page.setAttribute('data-decree-print-page', '');

    if (continued) {
        page.classList.add('decree-print-page-continued');
    } else {
        page.classList.add('decree-print-page-first');
    }

    var body = document.createElement('div');
    body.className = 'decree-print-page-body';
    page.appendChild(body);

    if (footer instanceof HTMLElement) {
        page.appendChild(footer.cloneNode(true));
    }

    return {
        page: page,
        body: body
    };
}

function cleanupDecreePrintPages() {
    document.querySelectorAll('[data-decree-print-paged]').forEach(function (node) {
        node.remove();
    });
}

function escapeText(value) {
    return String(value || '').replace(/[&<>"']/g, function (char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char];
    });
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

        var isPgeLifecycleStatus = shouldShowPgeDate || shouldShowHomologacaoDate;

        if (!isPgeLifecycleStatus && dateInput instanceof HTMLInputElement) {
            dateInput.value = '';
        }

        if (!isPgeLifecycleStatus && pgeProtocolInput instanceof HTMLInputElement) {
            pgeProtocolInput.value = '';
        }

        if (!shouldShowHomologacaoDate && homologacaoDateInput instanceof HTMLInputElement) {
            homologacaoDateInput.value = '';
        }

        updatePgeIndicators(
            form,
            pgeStatusPreview,
            homologacaoCode,
            dateInput instanceof HTMLInputElement ? dateInput.value : '',
            homologacaoDateInput instanceof HTMLInputElement ? homologacaoDateInput.value : ''
        );
    };

    homologacaoSelect.addEventListener('change', syncHomologacaoDateFields);
    if (dateInput instanceof HTMLInputElement) {
        dateInput.addEventListener('input', syncHomologacaoDateFields);
        dateInput.addEventListener('change', syncHomologacaoDateFields);
    }
    if (homologacaoDateInput instanceof HTMLInputElement) {
        homologacaoDateInput.addEventListener('input', syncHomologacaoDateFields);
        homologacaoDateInput.addEventListener('change', syncHomologacaoDateFields);
    }
    syncHomologacaoDateFields();
}

function renderPgeStatusPreview(container, status) {
    if (!(container instanceof HTMLElement)) {
        return;
    }

    var badge = document.createElement('span');

    badge.className = 'status-badge ' + status.className;
    badge.textContent = status.label;

    container.replaceChildren(badge);
}

function updatePgeIndicators(form, primaryContainer, homologacaoCode, dataEnvioPge, dataHomologacao) {
    var preview = previewPgeIndicators(homologacaoCode, dataEnvioPge, dataHomologacao);

    renderPgeStatusPreview(primaryContainer, preview.status);
    renderPgeStatusPreview(form.querySelector('[data-pge-send-status-preview]'), preview.status);
    renderPgeStatusPreview(form.querySelector('[data-pge-deadline-status-preview]'), preview.status);

    var daysPreview = form.querySelector('[data-pge-days-preview]');
    if (daysPreview instanceof HTMLElement) {
        daysPreview.textContent = preview.days === null ? '-' : String(preview.days);
    }
}

function previewPgeIndicators(homologacaoCode, dataEnvioPge, dataHomologacao) {
    var days = previewPgeDuration(homologacaoCode, dataEnvioPge, dataHomologacao);

    if (homologacaoCode === 'HOMOLOGADO') {
        return { status: { label: 'Aprovado', className: 'badge-success' }, days: days };
    }

    if (homologacaoCode === 'NAO_HOMOLOGADO') {
        return { status: { label: 'Reprovado', className: 'badge-danger' }, days: days };
    }

    if (homologacaoCode === 'ENVIADO_PGE' && days !== null && days >= 0) {
        if (days > 7) {
            return { status: { label: 'Pendente', className: 'badge-warning' }, days: days };
        }

        return { status: { label: 'No prazo', className: 'badge-warning' }, days: days };
    }

    return { status: { label: 'Não registrado', className: 'badge-muted' }, days: days };
}

function previewPgeDuration(homologacaoCode, dataEnvioPge, dataHomologacao) {
    var sentDate = parseIsoDateUtc(dataEnvioPge);
    if (sentDate === null) {
        return null;
    }

    var endDate = null;
    if (homologacaoCode === 'ENVIADO_PGE') {
        var today = new Date();
        endDate = Date.UTC(today.getFullYear(), today.getMonth(), today.getDate());
    } else if (['HOMOLOGADO', 'NAO_HOMOLOGADO'].indexOf(homologacaoCode) >= 0) {
        endDate = parseIsoDateUtc(dataHomologacao);
    }

    if (endDate === null) {
        return null;
    }

    return Math.round((endDate - sentDate) / 86400000);
}

function parseIsoDateUtc(value) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) {
        return null;
    }

    var parts = value.split('-').map(Number);
    var timestamp = Date.UTC(parts[0], parts[1] - 1, parts[2]);
    var parsed = new Date(timestamp);

    if (parsed.getUTCFullYear() !== parts[0] || parsed.getUTCMonth() !== parts[1] - 1 || parsed.getUTCDate() !== parts[2]) {
        return null;
    }

    return timestamp;
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

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-panel-map]').forEach(function (shell) {
        var canvas = shell.querySelector('[data-panel-map-canvas]');
        var status = shell.querySelector('[data-panel-map-status]');
        var mapSection = shell.closest('.panel-map-section') || document;
        var toggles = Array.prototype.slice.call(mapSection.querySelectorAll('[data-panel-layer-toggle]'));
        var defaultLat = parseCoordinate(shell.dataset.defaultLat) || -3.79;
        var defaultLng = parseCoordinate(shell.dataset.defaultLng) || -52.48;
        var layers = parseLayers(shell.dataset.layers || '{}');
        var tileErrorCount = 0;

        if (!(canvas instanceof HTMLElement)) {
            return;
        }

        function parseCoordinate(value) {
            if (typeof value !== 'string' && typeof value !== 'number') {
                return null;
            }

            var normalized = String(value).trim().replace(',', '.');

            if (normalized === '' || Number.isNaN(Number(normalized))) {
                return null;
            }

            return Number(normalized);
        }

        function parseLayers(raw) {
            try {
                var parsed = JSON.parse(raw);
                var result = {
                    compdecs: [],
                    ubms: [],
                    desastres: []
                };

                Object.keys(result).forEach(function (layerName) {
                    var source = Array.isArray(parsed[layerName]) ? parsed[layerName] : [];

                    result[layerName] = source.reduce(function (items, point) {
                        var lat = parseCoordinate(point.latitude);
                        var lng = parseCoordinate(point.longitude);

                        if (lat === null || lng === null || lat < -35 || lat > 6 || lng < -75 || lng > -30) {
                            return items;
                        }

                        point.lat = lat;
                        point.lng = lng;
                        point.layer = layerName;
                        items.push(point);

                        return items;
                    }, []);
                });

                return result;
            } catch (error) {
                return {
                    compdecs: [],
                    ubms: [],
                    desastres: []
                };
            }
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[char];
            });
        }

        function setStatus(message, warning) {
            if (!(status instanceof HTMLElement)) {
                return;
            }

            status.textContent = message;
            status.classList.toggle('is-warning', warning === true);
        }

        function activeLayers() {
            return toggles
                .filter(function (toggle) {
                    return toggle.checked;
                })
                .map(function (toggle) {
                    return toggle.value;
                });
        }

        function visiblePoints() {
            var selected = activeLayers();

            return selected.reduce(function (items, layerName) {
                return items.concat(layers[layerName] || []);
            }, []);
        }

        function layerLabel(layerName) {
            return {
                compdecs: 'COMPDEC',
                ubms: 'UBM',
                desastres: 'Desastre'
            }[layerName] || 'Ponto';
        }

        function isCompdecRegistered(point) {
            return Number(point.tem_compdec || 0) === 1;
        }

        function pointLayerKey(point) {
            if (point.layer === 'compdecs') {
                return isCompdecRegistered(point) ? 'compdecs-com' : 'compdecs-sem';
            }

            return point.layer || 'default';
        }

        function pointLayerLabel(point) {
            if (point.layer === 'compdecs') {
                return isCompdecRegistered(point) ? 'Com COMPDEC' : 'Sem COMPDEC';
            }

            return layerLabel(point.layer);
        }

        function appUrl(path) {
            var baseUrl = document.documentElement.dataset.appBaseUrl || '';
            return baseUrl.replace(/\/$/, '') + '/' + String(path || '').replace(/^\//, '');
        }

        function pointTitle(point) {
            if (point.layer === 'ubms') {
                return point.nome || 'UBM';
            }

            return point.municipio || point.nome || 'Ponto territorial';
        }

        function pointTooltip(point) {
            if (point.layer === 'compdecs') {
                return point.municipio || 'Munic\u00edpio n\u00e3o informado';
            }

            if (point.layer === 'ubms') {
                return point.nome || 'UBM n\u00e3o informada';
            }

            if (point.layer === 'desastres') {
                return point.protocolo_dgd || 'Protocolo DGD n\u00e3o informado';
            }

            return pointTitle(point);
        }

        function tooltipOptions() {
            return {
                className: 'panel-map-tooltip',
                direction: 'top',
                opacity: 0.95,
                sticky: true
            };
        }

        function pointValue(point) {
            if (point.layer === 'desastres') {
                return Number(point.total_desastres || 1);
            }

            if (point.layer === 'ubms') {
                return Number(point.municipios_vinculados || 1);
            }

            return Number(point.tem_compdec || 0) === 1 ? 1 : 0;
        }

        function markerClass(point) {
            return 'is-' + String(pointLayerKey(point)).replace(/[^a-z0-9_-]/gi, '');
        }

        function createIcon(point) {
            var value = Math.max(1, pointValue(point));
            var label = point.layer === 'compdecs' ? (isCompdecRegistered(point) ? 'C' : 'S') : value;

            return L.divIcon({
                className: 'panel-map-marker ' + markerClass(point),
                html: '<span><b>' + escapeHtml(label) + '</b></span>',
                iconSize: [46, 52],
                iconAnchor: [23, 48],
                popupAnchor: [0, -42]
            });
        }

        function pointPopup(point) {
            var rows = [
                '<header><strong>' + escapeHtml(pointTitle(point)) + '</strong><span>' + escapeHtml(pointLayerLabel(point)) + '</span></header>'
            ];

            if (point.municipio && point.layer === 'ubms') {
                rows.push('<div><b>Município referência</b><span>' + escapeHtml(point.municipio) + '</span></div>');
            }

            if (point.codigo_ibge) {
                rows.push('<div><b>Código IBGE</b><span>' + escapeHtml(point.codigo_ibge) + '</span></div>');
            }

            if (point.regiao_integracao) {
                rows.push('<div><b>Região</b><span>' + escapeHtml(point.regiao_integracao) + '</span></div>');
            }

            if (point.layer === 'desastres') {
                rows.push('<div><b>Registros</b><span>' + escapeHtml(point.total_desastres || 0) + '</span></div>');
                rows.push('<div><b>Afetados</b><span>' + escapeHtml(point.total_afetados || 0) + '</span></div>');
                rows.push('<div><b>Homologados</b><span>' + escapeHtml(point.homologados || 0) + '</span></div>');

                if (point.cobrade_tipo) {
                    rows.push('<div><b>Tipo COBRADE</b><span>' + escapeHtml(point.cobrade_tipo) + '</span></div>');
                }

                if (point.municipio_id) {
                    rows.push('<a href="' + escapeHtml(appUrl('/decretos?municipio_id=' + encodeURIComponent(point.municipio_id))) + '">Abrir decretos do município</a>');
                }
            }

            if (point.layer === 'compdecs') {
                rows.push('<div><b>Situação</b><span>' + (isCompdecRegistered(point) ? 'Com COMPDEC' : 'Sem COMPDEC') + '</span></div>');

                if (point.coordenador) {
                    rows.push('<div><b>Coordenador</b><span>' + escapeHtml(point.coordenador) + '</span></div>');
                }

                if (point.ubm_nome) {
                    rows.push('<div><b>UBM atuante</b><span>' + escapeHtml(point.ubm_nome) + '</span></div>');
                }

                if (point.id) {
                    rows.push('<a href="' + escapeHtml(appUrl('/compdecs/' + encodeURIComponent(point.id))) + '">Abrir COMPDEC</a>');
                }
            }

            if (point.layer === 'ubms') {
                rows.push('<div><b>Municípios vinculados</b><span>' + escapeHtml(point.municipios_vinculados || 1) + '</span></div>');
            }

            return '<div class="panel-map-popup">' + rows.join('') + '</div>';
        }

        function createClusterIcon(cluster) {
            var totals = cluster.points.reduce(function (summary, point) {
                var key = pointLayerKey(point);
                summary[key] = (summary[key] || 0) + 1;
                return summary;
            }, {});
            var total = cluster.points.length;
            var badges = [
                ['desastres', 'D', totals.desastres || 0],
                ['compdecs-com', 'C', totals['compdecs-com'] || 0],
                ['compdecs-sem', 'S', totals['compdecs-sem'] || 0],
                ['ubms', 'U', totals.ubms || 0]
            ].filter(function (item) {
                return item[2] > 0;
            }).map(function (item) {
                return '<em data-layer="' + escapeHtml(item[0]) + '"><i>' + escapeHtml(item[1]) + '</i>' + escapeHtml(item[2]) + '</em>';
            }).join('');

            return L.divIcon({
                className: 'panel-map-cluster',
                html: '<span><b>' + escapeHtml(total) + '</b><small>pontos</small></span><strong>' + badges + '</strong>',
                iconSize: [76, 72],
                iconAnchor: [38, 36],
                popupAnchor: [0, -26]
            });
        }

        function clusterPopup(cluster) {
            var totals = cluster.points.reduce(function (summary, point) {
                var key = pointLayerKey(point);
                summary[key] = (summary[key] || 0) + 1;
                return summary;
            }, {});
            var items = cluster.points
                .slice()
                .sort(function (a, b) {
                    return String(pointTitle(a)).localeCompare(String(pointTitle(b)), 'pt-BR');
                })
                .slice(0, 9)
                .map(function (point) {
                    return '<li><strong>' + escapeHtml(pointTitle(point)) + '</strong><span>' + escapeHtml(pointLayerLabel(point)) + '</span></li>';
                });

            if (cluster.points.length > items.length) {
                items.push('<li><strong>Outros pontos</strong><span>+' + escapeHtml(cluster.points.length - items.length) + '</span></li>');
            }

            return '<div class="panel-map-popup is-cluster">' +
                '<header><strong>' + escapeHtml(cluster.points.length) + ' pontos agrupados</strong><span>Aproxime o zoom para expandir</span></header>' +
                '<div><b>Desastres</b><span>' + escapeHtml(totals.desastres || 0) + '</span></div>' +
                '<div><b>Com COMPDEC</b><span>' + escapeHtml(totals['compdecs-com'] || 0) + '</span></div>' +
                '<div><b>Sem COMPDEC</b><span>' + escapeHtml(totals['compdecs-sem'] || 0) + '</span></div>' +
                '<div><b>UBM</b><span>' + escapeHtml(totals.ubms || 0) + '</span></div>' +
                '<section><b>Pontos no agrupamento</b><ul>' + items.join('') + '</ul></section>' +
                '</div>';
        }

        function countPointsByLayer(points) {
            return points.reduce(function (summary, point) {
                var key = pointLayerKey(point);
                summary[key] = (summary[key] || 0) + 1;
                return summary;
            }, {
                desastres: 0,
                'compdecs-com': 0,
                'compdecs-sem': 0,
                ubms: 0
            });
        }

        function updateLegendCounts(points) {
            var totals = countPointsByLayer(points);

            mapSection.querySelectorAll('[data-panel-legend-count]').forEach(function (element) {
                var key = element.getAttribute('data-panel-legend-count') || '';
                element.textContent = Number(totals[key] || 0).toLocaleString('pt-BR');
            });
        }

        if (typeof L === 'undefined') {
            shell.classList.add('is-map-unavailable');
            setStatus('Biblioteca do mapa indisponível. Os indicadores continuam disponíveis.', true);
            return;
        }

        var map = L.map(canvas, {
            scrollWheelZoom: true,
            zoomControl: true
        }).setView([defaultLat, defaultLng], 6);

        var tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19
        });

        tileLayer.on('tileerror', function () {
            tileErrorCount++;

            if (tileErrorCount <= 2) {
                setStatus('O mapa base não carregou completamente. Os pontos cadastrados continuam disponíveis.', true);
            }
        });

        tileLayer.on('load', function () {
            tileErrorCount = 0;
        });

        tileLayer.addTo(map);

        var markerLayer = L.layerGroup().addTo(map);

        function clusterRadius() {
            var zoom = map.getZoom();

            if (zoom >= 11) {
                return 0;
            }

            if (zoom >= 9) {
                return 42;
            }

            if (zoom >= 8) {
                return 58;
            }

            if (zoom >= 7) {
                return 74;
            }

            return 92;
        }

        function buildClusters(points) {
            var radius = clusterRadius();

            if (radius <= 0) {
                return points.map(function (point) {
                    return {
                        lat: point.lat,
                        lng: point.lng,
                        points: [point]
                    };
                });
            }

            return points.reduce(function (clusters, point) {
                var screenPoint = map.latLngToLayerPoint([point.lat, point.lng]);
                var targetCluster = clusters.find(function (cluster) {
                    return screenPoint.distanceTo(cluster.screenPoint) <= radius;
                });

                if (targetCluster) {
                    targetCluster.points.push(point);
                    targetCluster.lat = targetCluster.points.reduce(function (total, clusterPoint) {
                        return total + clusterPoint.lat;
                    }, 0) / targetCluster.points.length;
                    targetCluster.lng = targetCluster.points.reduce(function (total, clusterPoint) {
                        return total + clusterPoint.lng;
                    }, 0) / targetCluster.points.length;
                    targetCluster.screenPoint = map.latLngToLayerPoint([targetCluster.lat, targetCluster.lng]);
                    return clusters;
                }

                clusters.push({
                    lat: point.lat,
                    lng: point.lng,
                    points: [point],
                    screenPoint: screenPoint
                });

                return clusters;
            }, []);
        }

        function renderMarkers() {
            var points = visiblePoints();
            markerLayer.clearLayers();
            updateLegendCounts(points);

            buildClusters(points).forEach(function (cluster) {
                if (cluster.points.length === 1) {
                    var point = cluster.points[0];

                    L.marker([point.lat, point.lng], {
                        icon: createIcon(point)
                    }).bindPopup(pointPopup(point), {
                        maxWidth: 340,
                        closeButton: true
                    }).bindTooltip(escapeHtml(pointTooltip(point)), tooltipOptions()).addTo(markerLayer);

                    return;
                }

                L.marker([cluster.lat, cluster.lng], {
                    icon: createClusterIcon(cluster)
                }).bindPopup(clusterPopup(cluster), {
                    maxWidth: 390,
                    closeButton: true
                }).bindTooltip(cluster.points.length + ' pontos agrupados', tooltipOptions()).addTo(markerLayer);
            });

            setStatus(points.length === 0 ? 'Nenhum ponto ativo para as camadas selecionadas.' : points.length + ' ponto(s) ativo(s) no mapa.', points.length === 0);
        }

        function fitVisiblePoints() {
            var points = visiblePoints();

            if (points.length === 0) {
                map.setView([defaultLat, defaultLng], 6);
                return;
            }

            var bounds = points.map(function (point) {
                return [point.lat, point.lng];
            });

            if (bounds.length === 1) {
                map.setView(bounds[0], 10);
                return;
            }

            map.fitBounds(bounds, {
                padding: [44, 44],
                maxZoom: 9
            });
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                renderMarkers();
            });
        });

        fitVisiblePoints();
        renderMarkers();
        map.on('zoomend', renderMarkers);

        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(function () {
                map.invalidateSize();
            }).observe(canvas);
        }

        window.setTimeout(function () {
            map.invalidateSize();
        }, 150);
        window.setTimeout(function () {
            map.invalidateSize();
        }, 700);
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var dataElement = document.getElementById('compdec-form-map-data');

    if (!dataElement) {
        return;
    }

    function parseData() {
        try {
            return JSON.parse(dataElement.textContent || '{}');
        } catch (error) {
            return {};
        }
    }

    function toNumber(value) {
        var normalized = String(value || '').trim().replace(',', '.');

        if (normalized === '') {
            return null;
        }

        var number = Number.parseFloat(normalized);
        return Number.isFinite(number) ? number : null;
    }

    function formatCoordinate(value) {
        return Number(value).toFixed(8);
    }

    function isCoordinatePair(latitude, longitude) {
        return Number.isFinite(latitude)
            && Number.isFinite(longitude)
            && latitude >= -90
            && latitude <= 90
            && longitude >= -180
            && longitude <= 180;
    }

    function normalizeText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ')
            .trim()
            .toUpperCase();
    }

    var data = parseData();
    var compdecLatitudeInput = document.getElementById('compdec_latitude');
    var compdecLongitudeInput = document.getElementById('compdec_longitude');
    var ubmIdInput = document.getElementById('ubm_id');
    var ubmNameInput = document.getElementById('ubm_nome');
    var ubmLatitudeInput = document.getElementById('ubm_latitude');
    var ubmLongitudeInput = document.getElementById('ubm_longitude');
    var ubmSmartPanel = document.getElementById('ubm-smart-panel');
    var compdecMapApi = null;
    var ubmMapApi = null;

    function setMapFeedback(id, message, state) {
        var feedback = document.getElementById(id);

        if (feedback instanceof HTMLElement) {
            feedback.textContent = message;
            feedback.dataset.state = state || 'neutral';
        }
    }

    function createUbmIcon() {
        return L.divIcon({
            className: 'operational-unit-map-icon',
            html: '<span class="operational-unit-map-icon-house" aria-hidden="true"></span>',
            iconSize: [36, 36],
            iconAnchor: [18, 34],
            popupAnchor: [0, -32],
        });
    }

    function createCompdecIcon() {
        return L.divIcon({
            className: 'compdec-point-map-icon',
            html: '<span class="compdec-point-map-icon-dot" aria-hidden="true"></span>',
            iconSize: [34, 42],
            iconAnchor: [17, 40],
            popupAnchor: [0, -34],
        });
    }

    function createPointMap(config) {
        var mapElement = document.getElementById(config.mapId);
        var latitudeInput = document.getElementById(config.latitudeId);
        var longitudeInput = document.getElementById(config.longitudeId);
        var feedback = document.getElementById(config.feedbackId);

        if (!(mapElement instanceof HTMLElement) || !(latitudeInput instanceof HTMLInputElement) || !(longitudeInput instanceof HTMLInputElement)) {
            return null;
        }

        var defaultCenter = [-3.5, -52];
        var initialLatitude = toNumber(latitudeInput.value);
        var initialLongitude = toNumber(longitudeInput.value);
        var hasInitialPoint = isCoordinatePair(initialLatitude, initialLongitude);
        var center = hasInitialPoint ? [initialLatitude, initialLongitude] : defaultCenter;
        var readOnly = mapElement.getAttribute('data-readonly') === 'true';
        var map = L.map(mapElement, {
            zoomControl: true,
            scrollWheelZoom: true,
            minZoom: 5,
            maxZoom: 20,
        }).setView(center, hasInitialPoint ? 14 : 6);
        var marker = null;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        function setFeedback(message, state) {
            if (feedback instanceof HTMLElement) {
                feedback.textContent = message;
                feedback.dataset.state = state || 'neutral';
            }
        }

        function refreshSize() {
            map.invalidateSize();
            window.setTimeout(function () { map.invalidateSize(); }, 180);
        }

        function setPoint(latitude, longitude, message, options) {
            options = options || {};

            if (!isCoordinatePair(latitude, longitude)) {
                setFeedback('Informe coordenadas válidas para ajustar o mapa.', 'error');
                return false;
            }

            latitudeInput.value = formatCoordinate(latitude);
            longitudeInput.value = formatCoordinate(longitude);

            if (!marker) {
                var markerOptions = { draggable: true };

                if (readOnly) {
                    markerOptions.draggable = false;
                }

                if (config.icon) {
                    markerOptions.icon = config.icon;
                }

                marker = L.marker([latitude, longitude], markerOptions).addTo(map);
                marker.on('dragend', function (event) {
                    var position = event.target.getLatLng();
                    setPoint(position.lat, position.lng, 'Ponto ajustado no mapa.', { pan: false });
                });
            } else {
                marker.setLatLng([latitude, longitude]);
            }

            if (options.pan !== false) {
                map.setView([latitude, longitude], Math.max(map.getZoom(), 14), { animate: true });
            }

            setFeedback(message || 'Ponto atualizado no mapa.', 'success');
            return true;
        }

        function syncFromInputs() {
            var latitude = toNumber(latitudeInput.value);
            var longitude = toNumber(longitudeInput.value);

            if (latitudeInput.value.trim() === '' && longitudeInput.value.trim() === '') {
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }

                setFeedback(config.emptyMessage || 'Clique no mapa para definir o ponto.', 'neutral');
                return;
            }

            if (isCoordinatePair(latitude, longitude)) {
                setPoint(latitude, longitude, 'Mapa sincronizado com os campos.', { pan: false });
            }
        }

        if (!readOnly) {
            map.on('click', function (event) {
                setPoint(event.latlng.lat, event.latlng.lng, 'Ponto definido pelo clique no mapa.');
            });

            latitudeInput.addEventListener('input', syncFromInputs);
            longitudeInput.addEventListener('input', syncFromInputs);
        }

        if (hasInitialPoint) {
            setPoint(initialLatitude, initialLongitude, config.readyMessage || 'Ponto carregado no mapa.', { pan: false });
        } else {
            setFeedback(config.emptyMessage || 'Clique no mapa para definir o ponto.', 'neutral');
        }

        window.setTimeout(refreshSize, 120);

        if ('ResizeObserver' in window) {
            new ResizeObserver(refreshSize).observe(mapElement);
        }

        return {
            setPoint: setPoint,
            refreshSize: refreshSize,
            centerOnCurrentFields: function () {
                var latitude = toNumber(latitudeInput.value);
                var longitude = toNumber(longitudeInput.value);

                refreshSize();

                if (!isCoordinatePair(latitude, longitude)) {
                    setFeedback('Informe latitude e longitude válidas antes de centralizar.', 'error');
                    return false;
                }

                return setPoint(latitude, longitude, 'Mapa atualizado com as coordenadas informadas.');
            },
            clear: function (message) {
                latitudeInput.value = '';
                longitudeInput.value = '';

                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }

                refreshSize();
                setFeedback(message || 'Ponto removido.', 'neutral');
            },
        };
    }

    function setupMaps() {
        if (typeof L === 'undefined') {
            setMapFeedback('compdec-map-feedback', 'Não foi possível carregar a biblioteca do mapa.', 'error');
            setMapFeedback('ubm-map-feedback', 'Não foi possível carregar a biblioteca do mapa.', 'error');
            return;
        }

        if (L.Icon && L.Icon.Default) {
            L.Icon.Default.mergeOptions({
                iconRetinaUrl: appBaseUrl() + '/assets/vendor/leaflet/images/marker-icon-2x.png',
                iconUrl: appBaseUrl() + '/assets/vendor/leaflet/images/marker-icon.png',
                shadowUrl: appBaseUrl() + '/assets/vendor/leaflet/images/marker-shadow.png',
            });
        }

        compdecMapApi = createPointMap({
            mapId: 'compdec-map',
            latitudeId: 'compdec_latitude',
            longitudeId: 'compdec_longitude',
            feedbackId: 'compdec-map-feedback',
            icon: createCompdecIcon(),
            readyMessage: 'Ponto da COMPDEC carregado. Arraste o marcador para ajustar.',
            emptyMessage: 'Clique no mapa para definir o ponto da COMPDEC.',
        });
        ubmMapApi = createPointMap({
            mapId: 'ubm-map',
            latitudeId: 'ubm_latitude',
            longitudeId: 'ubm_longitude',
            feedbackId: 'ubm-map-feedback',
            icon: createUbmIcon(),
            readyMessage: 'Ponto da UBM carregado. Arraste o ícone da unidade para ajustar.',
            emptyMessage: 'Informe a coordenada da UBM ou clique no mapa para definir o ponto.',
        });

        [
            ['compdec-open-map', compdecMapApi, 'center'],
            ['compdec-clear', compdecMapApi, 'clear'],
            ['ubm-open-map', ubmMapApi, 'center'],
            ['ubm-clear', ubmMapApi, 'clear'],
        ].forEach(function (item) {
            var button = document.getElementById(item[0]);

            if (!(button instanceof HTMLButtonElement) || !item[1]) {
                return;
            }

            button.addEventListener('click', function () {
                if (item[2] === 'clear') {
                    item[1].clear('Coordenadas removidas do formulário.');
                    return;
                }

                item[1].centerOnCurrentFields();
            });
        });

        var ubmUseCompdecButton = document.getElementById('ubm-use-compdec');

        if (ubmUseCompdecButton instanceof HTMLButtonElement && ubmMapApi && compdecLatitudeInput && compdecLongitudeInput) {
            ubmUseCompdecButton.addEventListener('click', function () {
                ubmMapApi.setPoint(
                    toNumber(compdecLatitudeInput.value),
                    toNumber(compdecLongitudeInput.value),
                    'Ponto da UBM preenchido com a coordenada da COMPDEC. Ajuste se necessário.'
                );
            });
        }

        [
            ['compdec-use-current', compdecMapApi],
            ['ubm-use-current', ubmMapApi],
        ].forEach(function (item) {
            var button = document.getElementById(item[0]);
            var mapApi = item[1];

            if (!(button instanceof HTMLButtonElement) || !mapApi) {
                return;
            }

            button.addEventListener('click', function () {
                if (!('geolocation' in navigator)) {
                    return;
                }

                button.disabled = true;
                navigator.geolocation.getCurrentPosition(function (position) {
                    mapApi.setPoint(position.coords.latitude, position.coords.longitude, 'Localização atual carregada. Ajuste o marcador se necessário.');
                    button.disabled = false;
                }, function () {
                    button.disabled = false;
                }, {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0,
                });
            });
        });

        window.addEventListener('resize', function () {
            if (compdecMapApi) {
                compdecMapApi.refreshSize();
            }

            if (ubmMapApi) {
                ubmMapApi.refreshSize();
            }
        });
    }

    function setupUbmSmartSelection() {
        if (!(ubmNameInput instanceof HTMLInputElement) || !(ubmIdInput instanceof HTMLInputElement) || !(ubmSmartPanel instanceof HTMLElement)) {
            return;
        }

        var currentUbmId = Number(ubmIdInput.value || 0);
        var rawUbmOptions = Array.isArray(data.ubms) ? data.ubms.filter(function (item) {
            return item && item.id && item.nome;
        }).map(function (item) {
            return {
                id: Number(item.id),
                nome: String(item.nome || ''),
                municipio: String(item.municipio || ''),
                regiao: String(item.regiao_integracao || ''),
                latitude: item.latitude,
                longitude: item.longitude,
                ativo: Boolean(item.ativo),
                searchable: normalizeText([item.nome, item.municipio, item.regiao_integracao].join(' ')),
            };
        }) : [];
        var ubmOptionsByName = {};

        rawUbmOptions.forEach(function (option) {
            var key = normalizeText(option.nome);
            var current = ubmOptionsByName[key];
            var optionHasGeo = isCoordinatePair(toNumber(option.latitude), toNumber(option.longitude));
            var currentHasGeo = current ? isCoordinatePair(toNumber(current.latitude), toNumber(current.longitude)) : false;

            if (
                !current
                || option.id === currentUbmId
                || (current.id !== currentUbmId && optionHasGeo && !currentHasGeo)
                || (current.id !== currentUbmId && optionHasGeo === currentHasGeo && option.ativo && !current.ativo)
            ) {
                ubmOptionsByName[key] = option;
            }
        });

        var ubmOptions = Object.keys(ubmOptionsByName)
            .map(function (key) {
                return ubmOptionsByName[key];
            })
            .sort(function (a, b) {
                return a.nome.localeCompare(b.nome, 'pt-BR');
            });

        function setPanel(state, title, message, matches) {
            matches = matches || [];
            ubmSmartPanel.dataset.state = state || 'neutral';
            ubmSmartPanel.innerHTML = '';

            var strong = document.createElement('strong');
            strong.textContent = title;
            var span = document.createElement('span');
            span.textContent = message;
            ubmSmartPanel.append(strong, span);

            if (matches.length === 0) {
                return;
            }

            var list = document.createElement('div');
            list.className = 'compdec-ubm-smart-list';

            matches.slice(0, 6).forEach(function (option) {
                var button = document.createElement('button');
                var name = document.createElement('strong');
                var meta = document.createElement('span');
                var hasGeo = isCoordinatePair(toNumber(option.latitude), toNumber(option.longitude));

                button.type = 'button';
                button.className = 'compdec-ubm-smart-option';
                button.dataset.ubmId = String(option.id);
                name.textContent = option.nome;
                meta.textContent = [
                    option.municipio || 'Município não informado',
                    option.regiao || 'Região não informada',
                    hasGeo ? 'Com geolocalização' : 'Sem geolocalização',
                ].join(' | ');
                button.append(name, meta);
                list.appendChild(button);
            });

            ubmSmartPanel.appendChild(list);
        }

        function selectOption(option, message) {
            if (!option) {
                return;
            }

            ubmIdInput.value = String(option.id);
            ubmNameInput.value = option.nome;

            var latitude = toNumber(option.latitude);
            var longitude = toNumber(option.longitude);

            if (isCoordinatePair(latitude, longitude)) {
                if (ubmMapApi) {
                    ubmMapApi.setPoint(latitude, longitude, message || 'UBM selecionada. Latitude e longitude preenchidas automaticamente.');
                } else if (ubmLatitudeInput && ubmLongitudeInput) {
                    ubmLatitudeInput.value = formatCoordinate(latitude);
                    ubmLongitudeInput.value = formatCoordinate(longitude);
                }
            } else if (ubmLatitudeInput && ubmLongitudeInput) {
                ubmLatitudeInput.value = '';
                ubmLongitudeInput.value = '';
                if (ubmMapApi) {
                    ubmMapApi.clear('UBM selecionada, mas ainda sem coordenadas cadastradas.');
                }
            }

            setPanel('success', 'UBM selecionada da base local', [option.nome, option.municipio, option.regiao].filter(Boolean).join(' | ') || option.nome);
        }

        function refreshSuggestions() {
            var query = normalizeText(ubmNameInput.value);

            if (query === '') {
                ubmIdInput.value = '';
                setPanel('neutral', 'Seleção inteligente da UBM', 'Digite o nome da unidade e selecione uma opção cadastrada para preencher latitude e longitude automaticamente.');
                return;
            }

            var exactMatches = ubmOptions.filter(function (option) {
                return normalizeText(option.nome) === query;
            });

            if (exactMatches.length === 1) {
                selectOption(exactMatches[0], 'UBM localizada na base local. Latitude e longitude preenchidas automaticamente.');
                return;
            }

            ubmIdInput.value = '';
            var matches = ubmOptions.filter(function (option) {
                return option.searchable.indexOf(query) !== -1;
            }).slice(0, 6);

            if (matches.length > 0) {
                setPanel('neutral', matches.length + ' UBM(s) encontrada(s)', 'Selecione a unidade correta abaixo para evitar grafia divergente.', matches);
                return;
            }

            setPanel('warning', 'UBM ainda não localizada', 'Confira a grafia antes de salvar. Se for uma unidade nova, o sistema criará o cadastro local.');
        }

        var initialOption = ubmOptions.find(function (option) {
            return option.id === currentUbmId;
        });

        if (initialOption) {
            setPanel('success', 'UBM vinculada da base local', [initialOption.nome, initialOption.municipio].filter(Boolean).join(' | '));
        }

        ubmNameInput.addEventListener('input', function () {
            ubmIdInput.value = '';
            refreshSuggestions();
        });
        ubmNameInput.addEventListener('change', refreshSuggestions);
        ubmNameInput.addEventListener('blur', function () {
            window.setTimeout(refreshSuggestions, 120);
        });
        ubmSmartPanel.addEventListener('click', function (event) {
            var target = event.target instanceof Element ? event.target.closest('.compdec-ubm-smart-option') : null;

            if (!target) {
                return;
            }

            var selectedId = Number(target.dataset.ubmId || 0);
            selectOption(ubmOptions.find(function (option) {
                return option.id === selectedId;
            }));
        });
    }

    function setupPhotoUploader() {
        var input = document.querySelector('[data-compdec-photo-input]');
        var dropzone = document.querySelector('[data-compdec-photo-dropzone]');
        var preview = document.querySelector('[data-compdec-photo-preview]');
        var feedback = document.querySelector('[data-compdec-photo-feedback]');
        var clearButton = document.querySelector('[data-compdec-photo-clear]');

        if (!(input instanceof HTMLInputElement) || !(dropzone instanceof HTMLElement) || !(preview instanceof HTMLElement)) {
            return;
        }

        var originalHtml = preview.innerHTML;
        var originalEmpty = preview.dataset.empty || '1';
        var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        function setFeedback(message, state) {
            if (feedback instanceof HTMLElement) {
                feedback.textContent = message;
                feedback.dataset.state = state || 'neutral';
            }
        }

        function restorePreview() {
            preview.innerHTML = originalHtml;
            preview.dataset.empty = originalEmpty;
        }

        function setInputFile(file) {
            if (typeof DataTransfer === 'undefined') {
                setFeedback('Este navegador não permite anexar por arrastar ou colar. Use o seletor de arquivo.', 'error');
                return false;
            }

            var transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            return true;
        }

        function handleFile(file) {
            if (!file) {
                restorePreview();
                setFeedback('Formatos aceitos: JPG, PNG ou WEBP. Limite de 5 MB.', 'neutral');
                if (clearButton instanceof HTMLButtonElement) {
                    clearButton.hidden = true;
                }
                return;
            }

            if (allowedTypes.indexOf(file.type) === -1) {
                input.value = '';
                restorePreview();
                setFeedback('Formato inválido. Selecione uma imagem JPG, PNG ou WEBP.', 'error');
                return;
            }

            if (file.size <= 0 || file.size > 5242880) {
                input.value = '';
                restorePreview();
                setFeedback('A imagem excede o limite permitido de 5 MB.', 'error');
                return;
            }

            var objectUrl = URL.createObjectURL(file);
            var image = document.createElement('img');
            image.src = objectUrl;
            image.alt = 'Prévia da nova foto do coordenador';
            image.onload = function () {
                URL.revokeObjectURL(objectUrl);
            };
            preview.innerHTML = '';
            preview.appendChild(image);
            preview.dataset.empty = '0';
            setFeedback('Nova foto selecionada: ' + file.name, 'success');

            if (clearButton instanceof HTMLButtonElement) {
                clearButton.hidden = false;
            }
        }

        input.addEventListener('change', function () {
            handleFile(input.files && input.files[0] ? input.files[0] : null);
        });

        dropzone.addEventListener('click', function (event) {
            if (event.target === input || event.target === clearButton) {
                return;
            }

            input.click();
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
                setFeedback('Solte a imagem para anexar a foto do coordenador.', 'neutral');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function () {
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', function (event) {
            event.preventDefault();
            var file = event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0]
                ? event.dataTransfer.files[0]
                : null;

            if (file && setInputFile(file)) {
                handleFile(file);
            }
        });

        dropzone.addEventListener('paste', function (event) {
            var items = event.clipboardData && event.clipboardData.items ? Array.from(event.clipboardData.items) : [];
            var imageItem = items.find(function (item) {
                return item.kind === 'file' && String(item.type || '').indexOf('image/') === 0;
            });
            var file = imageItem ? imageItem.getAsFile() : null;

            if (file && setInputFile(file)) {
                event.preventDefault();
                handleFile(file);
            }
        });

        if (clearButton instanceof HTMLButtonElement) {
            clearButton.addEventListener('click', function (event) {
                event.preventDefault();
                input.value = '';
                restorePreview();
                setFeedback('Nova foto descartada. A foto atual será mantida ao salvar.', 'neutral');
                clearButton.hidden = true;
            });
        }
    }

    setupMaps();
    setupUbmSmartSelection();
    setupPhotoUploader();
});
