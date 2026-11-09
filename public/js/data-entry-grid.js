(function () {
    const instances = new Map();

    function columnSettings(definitions) {
        return definitions.map((definition) => {
            const column = { ...definition };
            delete column.header;
            delete column.width;
            delete column.hidden;
            return column;
        });
    }

    function mount(payload) {
        const container = document.getElementById(payload.containerId);
        if (!container || typeof Handsontable === 'undefined') return;

        const previous = instances.get(payload.containerId);
        if (previous) previous.hot.destroy();

        const definitions = payload.columns || [];
        const records = (payload.records || []).map((record) => ({ ...record }));
        const rowHeight = 35;
        const tableHeight = Math.max(90, 44 + (records.length * rowHeight));
        const hiddenColumns = definitions.reduce((indexes, definition, index) => {
            if (definition.hidden) indexes.push(index);
            return indexes;
        }, []);
        let validationErrors = payload.initialErrors || [];
        let dirty = Boolean(payload.initialDirty);
        let applyingStatus = false;

        const feedbackTop = document.getElementById(payload.containerId + '-feedback-top');
        const feedback = document.getElementById(payload.containerId + '-feedback');
        const saveButton = document.getElementById(payload.containerId + '-save');

        const showFeedback = (message, status = 'error') => {
            [feedbackTop, feedback].forEach((element) => {
                if (!element) return;
                element.textContent = message || '';
                element.classList.remove(
                    'app-feedback-note--success',
                    'app-feedback-note--warning',
                    'app-feedback-note--error',
                    'app-feedback-note--info'
                );
                const modifier = status === 'partial' ? 'warning' : status;
                if (['success', 'warning', 'error', 'info'].includes(modifier)) {
                    element.classList.add(`app-feedback-note--${modifier}`);
                }
            });
        };

        const hot = new Handsontable(container, {
            data: records,
            columns: columnSettings(definitions),
            colHeaders: definitions.map((definition) => definition.header || ''),
            colWidths: definitions.map((definition) => definition.width || 80),
            hiddenColumns: {
                columns: hiddenColumns,
                indicators: false,
            },
            rowHeaders: true,
            rowHeaderWidth: 34,
            rowHeights: rowHeight,
            renderAllRows: true,
            viewportRowRenderingOffset: records.length,
            manualColumnResize: true,
            stretchH: 'last',
            width: '100%',
            height: tableHeight,
            currentRowClassName: 'currentRow',
            licenseKey: 'non-commercial-and-evaluation',
            cells(row, col) {
                const field = definitions[col]?.data;
                const hasError = validationErrors.some(
                    (error) => Number(error.row) === row && error.field === field
                );

                return hasError ? { className: 'data-entry-error-cell' } : {};
            },
            afterChange(changes, source) {
                if (!changes || source === 'loadData') return;

                dirty = true;
                changes.forEach(([row, prop, oldValue, newValue]) => {
                    if (oldValue !== newValue) {
                        const col = hot.propToCol(prop);
                        const cell = hot.getCell(row, col);
                        if (cell) cell.style.color = 'forestgreen';
                    }
                });

                if (applyingStatus) return;
                const statusChanges = changes.filter((change) => change[1] === 'status');
                if (statusChanges.length === 0) return;

                applyingStatus = true;
                statusChanges.forEach(([row, , , status]) => {
                    const normalized = String(status || '').toUpperCase();
                    if (normalized === 'D' || normalized === 'G') {
                        ['length', 'cotyledon_count', 'leaf_count'].forEach((field) => {
                            hot.setDataAtRowProp(row, field, -4, 'status-rule');
                        });
                    } else if (normalized === 'A') {
                        ['length', 'leaf_count'].forEach((field) => {
                            if (Number(hot.getSourceDataAtRow(row)[field]) === -4) {
                                hot.setDataAtRowProp(row, field, '', 'status-rule');
                            }
                        });
                        if (Number(hot.getSourceDataAtRow(row).cotyledon_count) === -4) {
                            hot.setDataAtRowProp(row, 'cotyledon_count', 0, 'status-rule');
                        }
                    }
                });
                applyingStatus = false;
            },
        });

        if (payload.initialMessage) {
            showFeedback(payload.initialMessage, payload.initialStatus || (payload.initialResultOk ? 'success' : 'error'));
        }

        const componentElement = container.closest('[wire\\:id]');
        const component = componentElement
            ? Livewire.find(componentElement.getAttribute('wire:id'))
            : null;

        if (saveButton) {
            saveButton.onclick = async () => {
                if (!component) {
                    showFeedback('找不到輸入元件：輸入內容仍保留，請重新整理後再試。', 'error');
                    return;
                }

                validationErrors = [];
                hot.render();
                saveButton.disabled = true;
                showFeedback('資料檢查與儲存中……', 'info');

                try {
                    await component.call('saveRows', hot.getSourceData().map((row) => ({ ...row })));
                } catch (error) {
                    showFeedback('儲存失敗：輸入內容仍保留在畫面上，請稍後再試。', 'error');
                    saveButton.disabled = false;
                }
            };
        }

        const state = {
            hot,
            isDirty: () => dirty,
            component,
            handleSaveResult(result) {
                validationErrors = result.errors || [];
                showFeedback(result.message || '', result.status || (result.ok ? 'success' : 'error'));
                if (saveButton) saveButton.disabled = false;

                if (result.ok) {
                    dirty = false;
                    hot.render();
                    return;
                }

                if (validationErrors.length > 0) {
                    const first = validationErrors[0];
                    const column = definitions.findIndex((definition) => definition.data === first.field);
                    if (column >= 0 && Number(first.row) < hot.countRows()) {
                        hot.selectCell(Number(first.row), column);
                    }
                    hot.render();
                }
            },
        };

        instances.set(payload.containerId, state);
    }

    async function navigate(containerId, plotIndex) {
        const state = instances.get(containerId);
        if (!state?.component) return;

        if (state.isDirty() && !window.confirm('目前 Plot 有尚未儲存的修改，確定要離開嗎？')) {
            return;
        }

        await state.component.call('selectPlot', Number(plotIndex));
    }

    window.DataEntryGrid = { mount, navigate };

    window.addEventListener('beforeunload', (event) => {
        if (![...instances.values()].some((state) => state.isDirty())) return;
        event.preventDefault();
        event.returnValue = '';
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('seedling-experiment-entry-grid-data', (payload) => {
            window.requestAnimationFrame(() => mount(payload));
        });
        Livewire.on('seedling-experiment-entry-save-result', (payload) => {
            window.requestAnimationFrame(() => {
                instances.get(payload.containerId)?.handleSaveResult(payload);
            });
        });
    });
})();
