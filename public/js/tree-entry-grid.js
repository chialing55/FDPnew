(function () {
    const instances = new Map();
    const specialInstances = new Map();
    const pendingSpecialOpens = new Map();

    function normalizeRows(rows, columnDefinitions) {
        return (rows || []).map((sourceRow) => {
            const row = {
                _entryLock: sourceRow._entryLock || null,
                _alternote: sourceRow.alternote || '',
            };

            columnDefinitions.forEach((column) => {
                let value = sourceRow[column.data];

                if ((column.emptyValues || []).map(String).includes(String(value))) {
                    value = '';
                }
                if (column.emptyWhenZero && Number(value || 0) === 0) {
                    value = '';
                }

                row[column.data] = value ?? '';
            });

            return row;
        });
    }

    function specialModificationRenderer(instance, td, row, col, prop, value, cellProperties, payload) {
        Handsontable.dom.empty(td);
        const button = document.createElement('button');
        const isLocked = Boolean(instance.getSourceDataAtRow(row)?._entryLock);
        td.classList.toggle('tree-entry-locked-cell', isLocked);
        button.type = 'button';
        button.className = 'alternotehover tree-entry-special-button';
        button.setAttribute('aria-label', '特殊修改');
        button.title = isLocked ? '此筆資料不需輸入' : '特殊修改';
        button.innerHTML = '<i class="fa-regular fa-note-sticky"></i>';
        button.disabled = isLocked;
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (!isLocked) {
                if (typeof payload.saveBeforeSpecialModification === 'function') {
                    payload.saveBeforeSpecialModification(instance.getSourceDataAtRow(row));
                } else {
                    openSpecialModification(instance.getSourceDataAtRow(row), payload);
                }
            }
        });
        td.appendChild(button);
        return td;
    }

    function createColumns(columnDefinitions, payload) {
        return columnDefinitions.map((definition) => {
            const column = { ...definition };
            delete column.header;
            delete column.width;
            delete column.emptyValues;
            delete column.emptyWhenZero;

            if (column.renderer === 'special-modification') {
                column.renderer = function () {
                    specialModificationRenderer(...arguments, payload);
                };
            }

            return column;
        });
    }

    function parseAlternote(value) {
        if (!value) return {};
        if (typeof value === 'object') return value;

        try {
            return JSON.parse(value);
        } catch (error) {
            return {};
        }
    }

    function openSpecialModification(record, payload) {
        const definition = payload.specialModification;
        const dialogId = `${payload.containerId}-special`;
        const dialog = document.getElementById(dialogId);
        const container = document.getElementById(`${dialogId}-hot`);
        if (!dialog || !container || !definition?.columns?.length) return;

        const previous = specialInstances.get(dialogId);
        if (previous) previous.hot.destroy();

        const stemid = record.stemid || '';
        const data = {
            ...parseAlternote(record._alternote),
            stemid,
        };
        const definitions = definition.columns;
        const columns = definitions.map((item) => {
            const column = { ...item };
            delete column.header;
            delete column.width;
            delete column.hidden;
            delete column.optionSource;
            if (item.optionSource) {
                column.source = payload.options?.[item.optionSource] || [];
            }
            return column;
        });
        const hiddenColumns = definitions.reduce((indexes, item, index) => {
            if (item.hidden) indexes.push(index);
            return indexes;
        }, []);
        const visibleTableWidth = definitions.reduce((width, item) => {
            return width + (item.hidden ? 0 : Number(item.width || 80));
        }, 30);
        const tableWidth = Math.min(visibleTableWidth, Math.max(320, window.innerWidth - 120));
        const saveButton = document.getElementById(`${dialogId}-save`);
        const feedback = document.getElementById(`${dialogId}-feedback`);

        document.getElementById(`${dialogId}-title`).textContent = definition.title || '特殊修改';
        document.getElementById(`${dialogId}-note`).textContent = definition.note || '';
        document.getElementById(`${dialogId}-stemid`).textContent = stemid;
        dialog.style.display = 'flex';
        if (feedback) feedback.textContent = '';
        container.style.width = `${tableWidth}px`;
        container.style.height = '82px';

        const hot = new Handsontable(container, {
            data: [data],
            columns,
            colHeaders: definitions.map((item) => item.header || ''),
            colWidths: definitions.map((item) => item.width || 80),
            hiddenColumns: {
                columns: hiddenColumns,
                indicators: false,
            },
            rowHeaders: true,
            rowHeaderWidth: 25,
            rowHeights: 35,
            width: tableWidth,
            height: 82,
            manualColumnResize: true,
            licenseKey: 'non-commercial-and-evaluation',
        });
        const state = { hot, definitions, dialog, saveButton, feedback };
        specialInstances.set(dialogId, state);

        if (saveButton) saveButton.onclick = async () => {
            const componentElement = dialog.closest('[wire\\:id]');
            const component = componentElement ? Livewire.find(componentElement.getAttribute('wire:id')) : null;
            if (!component) {
                if (feedback) feedback.textContent = '找不到輸入元件，請關閉視窗後再試。';
                return;
            }

            saveButton.disabled = true;
            if (feedback) {
                feedback.style.color = 'forestgreen';
                feedback.textContent = '特殊修改檢查與儲存中……';
            }
            try {
                await component.call('saveSpecialModification', hot.getSourceDataAtRow(0));
            } catch (error) {
                saveButton.disabled = false;
                if (feedback) {
                    feedback.style.color = '#b71c1c';
                    feedback.textContent = '特殊修改儲存失敗，輸入內容仍保留在畫面上。';
                }
            }
        };
    }

    function closeSpecialModification(dialogId) {
        const dialog = document.getElementById(dialogId);
        if (dialog) dialog.style.display = 'none';
    }

    function lockedValueRenderer(instance, td, row, col, prop) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        const record = instance.getSourceDataAtRow(row);
        td.textContent = record?._entryLock?.display || '';
        return td;
    }

    function mount(payload) {
        const container = document.getElementById(payload.containerId);
        if (!container || typeof Handsontable === 'undefined') {
            return;
        }

        const previous = instances.get(payload.containerId);
        if (previous) {
            previous.hot.destroy();
        }

        const columnDefinitions = payload.columns || [];
        if (columnDefinitions.length === 0) {
            return;
        }

        const allRows = normalizeRows(payload.records, columnDefinitions);
        let pageSize = Number(payload.pageSize || 20);
        let page = 1;
        let renderedStart = 0;
        let hasRenderedPage = false;
        let validationErrors = [];

        const totalElement = document.getElementById(`${payload.containerId}-total`);
        const pageElement = document.getElementById(`${payload.containerId}-page`);
        const previousButton = document.getElementById(`${payload.containerId}-previous`);
        const nextButton = document.getElementById(`${payload.containerId}-next`);
        const sizeSelect = document.getElementById(`${payload.containerId}-size`);
        const saveButton = document.getElementById(`${payload.containerId}-save`);
        const feedback = document.getElementById(`${payload.containerId}-feedback`);
        const feedbackTop = document.getElementById(`${payload.containerId}-feedback-top`);

        let submitSave = null;
        payload.saveBeforeSpecialModification = (record) => {
            if (submitSave) submitSave(record);
        };
        const columns = createColumns(columnDefinitions, payload);

        const hot = new Handsontable(container, {
            data: [],
            columns,
            colHeaders: columnDefinitions.map((column) => column.header || ''),
            colWidths: columnDefinitions.map((column) => column.width || 80),
            rowHeaders: true,
            rowHeaderWidth: 25,
            rowHeights: 35,
            manualColumnResize: true,
            currentRowClassName: 'currentRow',
            licenseKey: 'non-commercial-and-evaluation',
            cells(row, col) {
                const record = this.instance.getSourceDataAtRow(row);
                const lock = record?._entryLock;
                const absoluteRow = renderedStart + row;
                const field = columnDefinitions[col]?.data;
                const hasError = validationErrors.some((error) => Number(error.row) === absoluteRow && error.field === field);
                const classes = [];
                const properties = {};

                if (lock) {
                    properties.readOnly = true;
                    classes.push('tree-entry-locked-cell');
                }
                if (lock && field === lock.displayColumn) {
                    properties.renderer = lockedValueRenderer;
                }
                if (hasError) classes.push('tree-entry-error-cell');
                if (classes.length) properties.className = classes.join(' ');

                return properties;
            },
            afterChange(changes, source) {
                if (!changes || source === 'loadData') {
                    return;
                }
                changes.forEach(([row, prop, oldValue, newValue]) => {
                    if (oldValue !== newValue) {
                        const cell = hot.getCell(row, hot.propToCol(prop));
                        if (cell) cell.style.color = 'forestgreen';
                    }
                });
            },
        });

        function syncCurrentPage() {
            if (!hasRenderedPage) return;
            hot.getSourceData().forEach((row, index) => {
                allRows[renderedStart + index] = row;
            });
        }

        function renderPage() {
            const pageCount = Math.max(1, Math.ceil(allRows.length / pageSize));
            page = Math.min(page, pageCount);
            renderedStart = (page - 1) * pageSize;
            hot.loadData(allRows.slice(renderedStart, renderedStart + pageSize));
            hasRenderedPage = true;
            if (totalElement) totalElement.textContent = `共有 ${allRows.length} 筆資料。`;
            const hasPagination = pageCount > 1;
            if (pageElement) {
                pageElement.textContent = `第 ${page} / ${pageCount} 頁`;
                pageElement.style.display = hasPagination ? '' : 'none';
            }
            if (previousButton) {
                previousButton.style.display = hasPagination ? '' : 'none';
                previousButton.style.visibility = page > 1 ? 'visible' : 'hidden';
            }
            if (nextButton) {
                nextButton.style.display = hasPagination ? '' : 'none';
                nextButton.style.visibility = page < pageCount ? 'visible' : 'hidden';
            }
        }

        function showFeedback(message, ok) {
            [feedbackTop, feedback].forEach((element) => {
                if (!element) return;
                element.textContent = message || '';
                element.style.color = ok ? 'forestgreen' : '#b71c1c';
            });
        }

        function handleSaveResult(result) {
            validationErrors = result.errors || [];
            showFeedback(result.message || '', Boolean(result.ok));
            if (saveButton) saveButton.disabled = false;

            if (!result.ok && validationErrors.length) {
                const first = validationErrors[0];
                page = Math.floor(Number(first.row || 0) / pageSize) + 1;
                renderPage();
                const column = columnDefinitions.findIndex((definition) => definition.data === first.field);
                const row = Number(first.row || 0) - renderedStart;
                if (column >= 0 && row >= 0 && row < hot.countRows()) {
                    hot.selectCell(row, column);
                }
            } else {
                hot.render();
            }
        }

        if (previousButton) previousButton.onclick = () => { syncCurrentPage(); page -= 1; renderPage(); };
        if (nextButton) nextButton.onclick = () => { syncCurrentPage(); page += 1; renderPage(); };
        if (sizeSelect) sizeSelect.onchange = () => {
            syncCurrentPage();
            pageSize = Number(sizeSelect.value);
            page = 1;
            renderPage();
        };
        submitSave = async (specialRecord = null) => {
            syncCurrentPage();
            validationErrors = [];
            hot.render();
            showFeedback('資料檢查與儲存中……', true);
            saveButton.disabled = true;
            if (specialRecord) {
                pendingSpecialOpens.set(payload.containerId, { record: { ...specialRecord }, payload });
            } else {
                pendingSpecialOpens.delete(payload.containerId);
            }

            const componentElement = container.closest('[wire\\:id]');
            const component = componentElement ? Livewire.find(componentElement.getAttribute('wire:id')) : null;
            if (!component) {
                showFeedback('找不到輸入元件，資料仍保留在畫面上，請重新整理後再試。', false);
                saveButton.disabled = false;
                pendingSpecialOpens.delete(payload.containerId);
                return;
            }

            const rows = allRows.map((row) => Object.fromEntries(
                columnDefinitions.map((definition) => [definition.data, row[definition.data] ?? ''])
            ));
            try {
                await component.call('saveRows', rows);
            } catch (error) {
                showFeedback('儲存失敗，資料仍保留在畫面上，請稍後再試。', false);
                saveButton.disabled = false;
                pendingSpecialOpens.delete(payload.containerId);
            }
        };
        if (saveButton && !saveButton.disabled) saveButton.onclick = () => submitSave();

        instances.set(payload.containerId, { hot, handleSaveResult });
        renderPage();
    }

    window.TreeEntryGrid = { mount, closeSpecialModification };

    document.addEventListener('livewire:init', () => {
        Livewire.on('geo-tree-entry-grid-data', (payload) => {
            window.requestAnimationFrame(() => mount(payload));
        });
        Livewire.on('geo-tree-entry-save-result', (payload) => {
            window.requestAnimationFrame(() => {
                instances.get(payload.containerId)?.handleSaveResult(payload);
                const pending = pendingSpecialOpens.get(payload.containerId);
                if (!pending) return;
                pendingSpecialOpens.delete(payload.containerId);
                if (payload.ok) {
                    openSpecialModification(pending.record, pending.payload);
                }
            });
        });
        Livewire.on('geo-tree-special-save-result', (payload) => {
            window.requestAnimationFrame(() => {
                const state = specialInstances.get(payload.dialogId);
                if (!state) return;
                if (state.saveButton) state.saveButton.disabled = false;

                if (!payload.ok) {
                    if (state.feedback) {
                        state.feedback.style.color = '#b71c1c';
                        state.feedback.textContent = payload.message || '特殊修改檢查未通過。';
                    }
                    const errorFields = new Set((payload.errors || []).map((error) => error.field));
                    state.hot.updateSettings({
                        cells(row, col) {
                            return errorFields.has(state.definitions[col]?.data)
                                ? { className: 'tree-entry-error-cell' }
                                : {};
                        },
                    });
                    state.hot.render();
                    return;
                }

                closeSpecialModification(payload.dialogId);
                const mainFeedback = document.getElementById(`${payload.dialogId.replace(/-special$/, '')}-feedback`);
                if (mainFeedback) {
                    mainFeedback.style.color = 'forestgreen';
                    mainFeedback.textContent = payload.message || '特殊修改已儲存。';
                }
            });
        });
    });
})();
