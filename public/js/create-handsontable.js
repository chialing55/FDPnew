

//深拷貝，讓realemptytable不會跟著變動
function deepCopy(obj) {
  if (obj === null || typeof obj !== 'object') {
    return obj;
  }

  if (Array.isArray(obj)) {
    return obj.map(deepCopy);
  }

  const copy = {};
  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      copy[key] = deepCopy(obj[key]);
    }
  }

  return copy;
}

function getPageContext() {
  const appPage = window.seedlingPage || window.seedsPage || {};
  const context = appPage.context || {};
  const state = appPage.state || {};

  return {
    entry: context.entry ?? null,
    user: context.user ?? '',
    plotType: context.plotType ?? (typeof plotType !== 'undefined' ? plotType : ''),
    maxid: state.maxid ?? (typeof maxid !== 'undefined' ? maxid : null),
    pps: state.pps ?? (typeof ppsall !== 'undefined' ? ppsall : null),
    realemptytable: state.realemptytable ?? (typeof realemptytable !== 'undefined' ? realemptytable : []),
    setNote: typeof appPage.setNote === 'function'
      ? appPage.setNote.bind(appPage)
      : (selector, message) => $(selector).html(message || ''),
    clearNotes: typeof appPage.clearNotes === 'function'
      ? appPage.clearNotes.bind(appPage)
      : () => $('.savenote').html(''),
    syncState: typeof appPage.syncState === 'function'
      ? appPage.syncState.bind(appPage)
      : () => {},
  };
}

function getScopedPageElements(plotType, site) {
  if (plotType === 'fsseedling' && window.seedlingPage && typeof window.seedlingPage.paginationElements === 'function') {
    return window.seedlingPage.paginationElements(site);
  }

  return {
    scope: $(),
    pages: $('.pages').first(),
    totalnum: $('.totalnum').first(),
    pagenote: $('.pagenote').first(),
    prev: $('.prev').first(),
    next: $('.next').first(),
    pageSize: $(),
    pageSizeSelect: $(),
  };
}

function getAlternoteScope(plotType) {
  const currentSite = window.seedlingPage && typeof window.seedlingPage.currentSite === 'function'
    ? window.seedlingPage.currentSite()
    : null;

  if (
    plotType === 'fsseedling'
    && window.seedlingPage
    && typeof window.seedlingPage.scope === 'function'
  ) {
    const scope = window.seedlingPage.scope('alternote', currentSite);
    if (scope.length) {
      return scope;
    }
  }

  return $('.alternotetalbeouter').first();
}

function makeAjaxRequest(url, requestData, requstType, successCallback, errorCallback, requestOptions = {}) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    },
  });
  $.ajax({
    url: url,
    data: requestData,
    type: requstType,
    contentType: requestOptions.contentType || undefined,
    processData: requestOptions.processData ?? true,
    dataType: requestOptions.dataType || undefined,
    success: function (res) {
      if (res.result === 'ok') {
        try {
          successCallback(res);
        } catch (callbackError) {
          const detail = callbackError?.message || String(callbackError || "前端處理儲存回應失敗");
          console.error("Save success callback failed.", callbackError);
          if (errorCallback) {
            errorCallback({ error: "前端處理儲存回應失敗：" + detail, xhr: null, status: "callback", response: res });
          } else {
            console.log(detail);
          }
        }
      } else {
        const detail = res?.message || res?.datasavenote || res?.error || (res ? "後端回傳錯誤但沒有訊息：" + JSON.stringify(res) : 'Save error');
        if (errorCallback) {
          errorCallback({ error: detail, xhr: null, status: 'application', response: res });
        } else {
          console.log(detail);
        }
      }
    },
    error: function (xhr, status, error) {
      console.log('Save error. '+url, xhr?.status, error || status, xhr?.responseText);
      if (errorCallback) {
        const response = xhr?.responseJSON || {};
        const statusText = xhr?.status
          ? `HTTP ${xhr.status}${error ? ` ${error}` : ''}`
          : (error || status || 'Save error');
        const detail = response?.message
          || response?.datasavenote
          || response?.seedssavenote
          || response?.finishnote
          || xhr?.responseText
          || statusText;
        errorCallback({ error: detail, xhr: xhr, status: status, response: response });
      }
    },

  });
}

function getSeedlingRollTrap(container) {
  const trapMatch = `${container.attr('id') || ''}`.match(/^slrolltable(.+)$/);
  return trapMatch?.[1] ?? '';
}

function isSeedlingRollPlaceholderRow(row) {
  if (!row || typeof row !== 'object') {
    return false;
  }

  const hasId = `${row.id ?? ''}`.trim() !== '' && `${row.id}` !== '0';
  const hasDelete = `${row.delete ?? ''}`.trim() !== '';
  const hasYear = `${row.year ?? ''}`.trim() !== '';
  const hasMonth = `${row.month ?? ''}`.trim() !== '';
  const hasEditableContent = [row.date, row.plot, row.tag, row.note].some(
    (value) => value !== null && value !== undefined && `${value}`.trim() !== '',
  );

  return !hasId && !hasDelete && !hasYear && !hasMonth && !hasEditableContent;
}

function buildSeedlingRollDisplayRows(rows, trap, blankCount = 5) {
  const sourceRows = Array.isArray(rows) ? rows : [];
  const normalizedRows = sourceRows.map((row) => {
    if (!row || typeof row !== 'object') {
      return row;
    }

    if (isSeedlingRollPlaceholderRow(row)) {
      return {
        ...row,
        trap: trap,
      };
    }

    return row;
  });

  const placeholderRows = normalizedRows.filter(isSeedlingRollPlaceholderRow).length;
  const rowsToAdd = Math.max(0, blankCount - placeholderRows);

  for (let i = 0; i < rowsToAdd; i += 1) {
    normalizedRows.push({
      date: '',
      trap: trap,
      plot: '',
      tag: '',
      note: '',
      delete: '',
      id: '',
      year: '',
      month: '',
    });
  }

  return normalizedRows;
}



function createHandsontable(container, columns, sourceData, saveButtonName, saveUrl, tableType, colWidths, hiddenColumns, colHeaders, thispage) {
  var cellChanges = [];
  var parent = container.parent();
  const pageContext = getPageContext();


  container.handsontable({
    data: sourceData,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    rowHeights: 35,
    colWidths: colWidths,
    colHeaders: colHeaders,
    licenseKey: 'non-commercial-and-evaluation',
    columns: columns,
    currentRowClassName: 'currentRow',
    manualColumnResize: true,
    hiddenColumns: hiddenColumns,
    cells: function (row, col, prop) {
      return cellfunction(tableType, container, row, col, prop);
    },
    afterCreateRow: function (index, amount, source) {
      if (
        pageContext.plotType === 'fsseeds'
        && (tableType === 'data' || tableType === 'addseedsdata')
      ) {
        const tableData = container.handsontable('getSourceData') || [];
        const sampleRow = tableData.find((row) => row && `${row.census ?? ''}` !== '') || {};

        for (let offset = 0; offset < amount; offset += 1) {
          const targetIndex = index + offset;
          const targetRow = tableData[targetIndex];

          if (!targetRow || typeof targetRow !== 'object') {
            continue;
          }

          targetRow.id = '';
          targetRow.census = sampleRow.census ?? '';
          targetRow.trap = targetRow.trap ?? '';
          targetRow.csp = targetRow.csp ?? '';
          targetRow.code = targetRow.code ?? '';
          targetRow.count = targetRow.count ?? '';
          targetRow.seeds = targetRow.seeds ?? '';
          targetRow.viability = targetRow.viability ?? '';
          targetRow.fragments = targetRow.fragments ?? '';
          targetRow.sex = targetRow.sex ?? '';
          targetRow.identifier = targetRow.identifier ?? sampleRow.identifier ?? '';
          targetRow.note = targetRow.note ?? '';

          if (tableType === 'data') {
            targetRow.checknote = '';
            targetRow.d = '';
          }
        }

        container.handsontable('render');
      }
    },

    afterChange: function (changes, source) {
      if (tableType!='recruit' && tableType!='addcov' && tableType!='alternote' && tableType!='addseedsdata'){
        if (!changes) {
            return;
        }
            $.each(changes, function (index, element) {
                var change = element;
                var rowIndex = change[0];
                var columnIndex = change[1];
                var oldValue = change[2];
                var newValue = change[3];
                col=container.handsontable('propToCol', columnIndex);
                // console.log(col);
                var td = container.handsontable('getCell', rowIndex, col);
                var cellChange = {
                    'rowIndex': rowIndex,
                    'columnIndex': col, 
                    'td': td
                };
                // console.log(td);
                if(oldValue != newValue){
                    cellChanges.push(cellChange);
                    td.style.color = 'forestgreen';
                }
            });
      }
    },
    afterRender: function () {
      if (tableType=='recruit' && tableType!='addcov' && tableType!='alternote' && tableType!='addseedsdata'){
            // var instance = container.handsontable('getInstance');
            $.each(cellChanges, function (index, element) {
                var cellChange = element;
                var rowIndex = cellChange['rowIndex'];
                var columnIndex = cellChange['columnIndex'];
                // var grilla = $('#grilla');
                var td=cellChange['td'];
                // var td = container.handsontable('getCell', rowIndex, columnIndex);
                td.style.color = 'forestgreen'; 
                // cell.style.background = backgroundColor;
                // console.log(td);
            });
      }
    },
  });
  if (tableType === 'recruit' || tableType === 'addcov') {
    container.handsontable('updateSettings', {
      contextMenu: ['row_above', 'row_below', 'remove_row'],
    });
  }

  if (window.seedsConfig?.viewMode === 'unknown-all' && tableType === 'data') {
    container.handsontable('updateSettings', {
      contextMenu: false,
      allowInsertRow: false,
    });
  } else if (pageContext.plotType === 'fsseeds' && (tableType === 'data' || tableType === 'addseedsdata')) {
    container.handsontable('updateSettings', {
      contextMenu: {
        items: {
          row_above: {
            name: '上方插入一列',
          },
          row_below: {
            name: '下方插入一列',
          },
        },
      },
    });
  }

  if (tableType === 'roll') {
    container.handsontable('updateSettings', {
      minSpareRows: 0,
    });
  }

  var noteProperty = `${tableType}savenote`;
  var handsontable = container.data('handsontable');
  // var noteClass=`${tableType}savenote`;
  
// console.log(tableType);
  container.parent().find(`button[name=${saveButtonName}]`).off('click.createHandsontableSave').on('click.createHandsontableSave', function () {
    const pageContext = getPageContext();
    pageContext.clearNotes();
    let requestRows = handsontable.getSourceData();

    var ajaxData={
          data: requestRows,
          entry: pageContext.entry,
          user: pageContext.user,
          plotType: pageContext.plotType,
          currentCensus: window.seedsConfig?.viewMode === 'unknown-all'
            ? null
            : (typeof currentCensus !== 'undefined' ? currentCensus : null),
          thispage: thispage,
          pps: pageContext.pps,
        };
    var ajaxType='post';
  // ceartAjax
    makeAjaxRequest(
      saveUrl, ajaxData, ajaxType,
      function(res) {
        handleSuccessAllTable(res, tableType, handsontable);
      },
      function (err) {
        const noteSelector = pageContext.plotType === 'fsseeds'
          ? '.seedssavenote'
          : `.${noteProperty}`;
        const detail = err?.response?.[noteProperty]
          || err?.response?.seedssavenote
          || err?.response?.datasavenote
          || err?.response?.message
          || err?.error
          || '儲存失敗';
        const tone = err?.response?.[`${noteProperty}_type`]
          || err?.response?.seedssavenote_type
          || err?.response?.datasavenote_type
          || 'error';
        pageContext.setNote(noteSelector, detail, tone);
      }
    );

  });
//新增資料表更新
  parent.find('button[name=clearrecruittable]').click(function () {
    const pageContext = getPageContext();
    pageContext.setNote('.recruitsavenote', '');
    emptytable2=deepCopy(pageContext.realemptytable);
    handsontable.updateData(emptytable2);
    // console.log('ww');
  });
//新增地被資料表更新
  parent.find('button[name=clearaddcovtable]').click(function () {
    const pageContext = getPageContext();
    pageContext.setNote('.addcovsavenote', '');
    emptytable2=deepCopy(pageContext.realemptytable);
    handsontable.updateData(emptytable2);
  });

  parent.find('button[name=deletecov]').click(function () {
    const id=$(this).attr('deleteid');
    const entry2=$(this).attr('entry');
    deletecov(id, entry2);
  });


  return handsontable;
}


function handleAlternote(stemid, entry, thispage, saveUrl) {
  const pageContext = getPageContext();
  const alternoteScope = getAlternoteScope(pageContext.plotType);
  // console.log(stemid);
  if (pageContext.plotType === 'fsseedling' && typeof resetSeedlingAlternoteModal === 'function') {
    resetSeedlingAlternoteModal(alternoteScope);
  }
  if (pageContext.plotType === 'fsseedling' && window.seedlingPage?.setScopedNote) {
    window.seedlingPage.setScopedNote('alternote', window.seedlingPage.currentSite(), '');
  } else {
    pageContext.setNote('.altersavenote', '');
  }

  if (pageContext.plotType === 'fsseedling') {
    alternoteScope.css({
      display: 'block',
      visibility: 'visible',
    });
  } else {
    const clickEvent = window.event || event;
    const posX = clickEvent?.pageX || 0;
    const posY = clickEvent?.pageY || 0;

    alternoteScope.css({
      visibility: 'hidden',
      display: 'block',
    });

    const panelHeight = alternoteScope.outerHeight() || 0;
    const top = Math.max(20, posY - Math.round(panelHeight / 2));

    alternoteScope.css({
      top: top,
      left: posX - 650,
      visibility: 'visible',
    });
  }

  alternoteScope.find('.alterstemid').html(stemid);
  alternoteScope.find('.altertag').html(stemid);

    var ajaxData={};
    var ajaxType='get';

    function handleSuccess(res) {
          if (res.datasavenote !=''){
            if (pageContext.plotType === 'fsseedling' && window.seedlingPage?.setScopedNote) {
              window.seedlingPage.setScopedNote(
                'data',
                window.seedlingPage.currentSite(),
                res.datasavenote,
                res.datasavenote_type || '',
              );
            } else {
              pageContext.setNote('.datasavenote', res.datasavenote);
            }
          }

          if (pageContext.plotType === 'fsseedling') {
            setTimeout(() => {
              alternotetable(res.alterdata, stemid, entry, thispage);
            }, 30);
          } else {
            alternotetable(res.alterdata, stemid, entry, thispage);
          }
          if (res.havedata=='yes'){
            alternoteScope.find('.deletealternotebutton').show();
            if (pageContext.plotType === 'fsseedling') {
              alternoteScope.find('.deletealternotebutton').attr({'tag': stemid});
              alternoteScope.find('.deletealternotebutton').removeAttr('stemid');
            } else {
              alternoteScope.find('.deletealternotebutton').attr({'stemid': stemid, 'thispage': thispage});
              alternoteScope.find('.deletealternotebutton').removeAttr('tag');
            }
            
          } else {
            alternoteScope.find('.deletealternotebutton').hide();
          }
    }
    makeAjaxRequest(
      saveUrl, ajaxData, ajaxType,
      handleSuccess,
      function (err) {
        const detail = err?.xhr?.responseJSON?.message
          || err?.xhr?.responseText
          || `${err?.status || ''} ${err?.error || '讀取失敗'}`;
        if (pageContext.plotType === 'fsseedling' && window.seedlingPage?.setScopedNote) {
          window.seedlingPage.setScopedNote('alternote', window.seedlingPage.currentSite(), detail, 'error');
        } else {
          pageContext.setNote('.altersavenote', detail);
        }
      }
    );

  // alternotetable(stemid, entry);
}

function deletealternoteButtonClick(button){
  const pageContext = getPageContext();
  let stemid;
  if (pageContext.plotType === 'fsseedling') {
    const alternoteScope = getAlternoteScope(pageContext.plotType);
    stemid = $(button).attr('tag') || alternoteScope.find('.altertag').text().trim();
  } else {
    stemid = $(button).attr('stemid');
    if (typeof stemid === 'undefined') {
      stemid = $(button).attr('tag');
    }
  }
  const thispage = pageContext.plotType === 'fsseedling' ? null : $(button).attr('thispage');
  deletealternote(stemid, thispage);  
}

function handleDeleteAlternote(stemid, plotType, saveUrl){
      const pageContext = getPageContext();
      const alternoteScope = getAlternoteScope(plotType);
      if(confirm('確定刪除 '+stemid+' 特殊修改??')) 
    {
      if (plotType === 'fsseedling' && window.seedlingPage?.setScopedNote) {
        window.seedlingPage.setScopedNote('alternote', window.seedlingPage.currentSite(), '');
      } else {
        pageContext.setNote('.altersavenote', '');
      }

      var ajaxData={ _method: 'DELETE' };
      var ajaxType='post';

      function handleSuccess(res) {
          if (res.datasavenote !=''){
            if (plotType === 'fsseedling' && window.seedlingPage?.setScopedNote) {
              window.seedlingPage.setScopedNote(
                'alternote',
                window.seedlingPage.currentSite(),
                res.datasavenote,
                res.datasavenote_type || '',
              );
            } else {
              pageContext.setNote('.altersavenote', res.datasavenote);
            }
          }

          if (plotType=='ss10m' || plotType=='ss1ha'){
            ssdatatableupdate(res.data, res.thispage, pageContext.pps);
          } else if (plotType=='fstree'){
            fstreetableupdate(res.data, res.thispage, pageContext.pps);
          } else if (plotType=='fsseedling'){
            fsseedlingtableupdate(res.data, res.thispage, pageContext.pps, res.maxid);
          }

          if (plotType === 'fsseedling' && typeof renderSeedlingAlternoteForm === 'function') {
            renderSeedlingAlternoteForm(res.realterdata, stemid, res.thispage);
          } else {
            var container = alternoteScope.find('#alternotetable');
            var handsontable = container.data('handsontable');
            
            handsontable.updateData(res.realterdata);
          }
          alternoteScope.find('.deletealternotebutton').hide();
      }
      makeAjaxRequest(
        saveUrl, ajaxData, ajaxType,
        handleSuccess,
        function (err) {
          const detail = err?.xhr?.responseJSON?.datasavenote
            || err?.xhr?.responseText
            || `${err?.status || ''} ${err?.error || '刪除失敗'}`;
          if (plotType === 'fsseedling' && window.seedlingPage?.setScopedNote) {
            window.seedlingPage.setScopedNote('alternote', window.seedlingPage.currentSite(), detail, 'error');
          } else {
            pageContext.setNote('.altersavenote', detail);
          }
          console.error('seedling delete alternote failed', err);
        }
      );
    }
}

function handleDeleteid(stemid, saveUrl){
  // console.log(entry);
  $('.recruitsavenote').html('');
  if(confirm('確定刪除 '+stemid+' 新增樹資料??')) 
  {
    $('.datasavenote').html('');
    
    var ajaxData={};
    var ajaxType='get';

    function handleSuccess(res) {
          if (res.datasavenote !=''){
            $('.datasavenote').html(res.datasavenote);
          }
          if (plotType=='ss10m' || plotType=='ss1ha'){
            ssdatatableupdate(res.recruit, res.thispage, ppsall);
          } else if (plotType=='fstree'){
            fstreetableupdate(res.recruit, res.thispage, ppsall);
          } 
    }
    makeAjaxRequest(
      saveUrl, ajaxData, ajaxType,
      handleSuccess,
      function () {}
    );
  }
}

function processDataTable(data, thispage, pps, site, plotType) {
  // 分頁
  var totalpage = Math.ceil(data.length / pps);
  const pageElements = getScopedPageElements(plotType, site);
  const hasPagination = totalpage > 1;
  const isExpandedView = pps > 20;

  pageElements.prev.addClass(`prev${site}`);
  pageElements.next.addClass(`next${site}`);

  pageElements.pages.css('display', 'flex');
  configurePageSizeControl(pageElements, data, pps, plotType, site);

  if (isExpandedView && data.length <= 40) {
    pageElements.pagenote.hide();
    pageElements.prev.hide();
    pageElements.next.hide();
  } else {
    pageElements.pagenote.toggle(hasPagination);
    pageElements.prev.toggle(hasPagination);
    pageElements.next.toggle(hasPagination);
  }

  if (totalpage > 1) {
    datapage = pages(data, thispage, totalpage, pps, plotType, site);
    var data2 = datapage[1];
  } else {
    pageElements.pagenote.html('');
    var data2 = data;
  }

  for (let i = 0; i < data2.length; i++) {
    if (data2[i]['date'] === '0000-00-00') {
      data2[i]['date'] = '';
    }
  }
  // 返回处理后的数据
  return data2;
}


function pages(data, thispage, totalpage, pps, plotType, site) {
  const pageContext = getPageContext();
  const pageElements = getScopedPageElements(plotType, site);
  const isExpandedView = pps > 20;
  const hidePagingInExpandedView = isExpandedView && data.length <= 40;

  if (plotType === 'fsseedling' && window.seedlingPage?.syncState) {
    window.seedlingPage.syncState({ thispage, pps, record: data });
  }

  let start;
  let end;
  let data2;

  start = pps * (thispage - 1);
  end = start + pps;
  data2 = data.slice(start, end);

  pageElements.pages.css('display', 'flex');
  pageElements.pagenote.html(`第 ${thispage} ／ ${totalpage} 頁`);
  pageElements.prev.attr('thispage', thispage);
  pageElements.next.attr('thispage', thispage);
  configurePageSizeControl(pageElements, data, pps, plotType, site);

  if (hidePagingInExpandedView) {
    pageElements.pagenote.hide();
    pageElements.prev.hide();
    pageElements.next.hide();
  } else if (totalpage > 1) {
    pageElements.pagenote.show();
    pageElements.prev.show();
    pageElements.next.show();
    if (thispage === 1) {
      pageElements.prev.css('visibility', 'hidden');
      pageElements.next.css('visibility', 'visible');
    } else if (thispage === totalpage) {
      pageElements.prev.css('visibility', 'visible');
      pageElements.next.css('visibility', 'hidden');
    } else {
      pageElements.prev.css('visibility', 'visible');
      pageElements.next.css('visibility', 'visible');
    }
  } else {
    pageElements.pagenote.hide();
    pageElements.prev.hide();
    pageElements.next.hide();
  }

  pageElements.prev.off('click').on('click', function () {
    handlePagination('prev', site, plotType, data, pps);
  });

  pageElements.next.off('click').on('click', function () {
    handlePagination('next', site, plotType, data, pps);
  });


  datapage=[data, data2, thispage];

  return datapage;
  
}

function handlePagination(action, site, plotType, data, pps) {
  const pageContext = getPageContext();
  const pageElements = getScopedPageElements(plotType, site);
  const trigger = action === 'prev' ? pageElements.prev : pageElements.next;
  thispage = trigger.attr('thispage');
  const gopage = (action === 'prev') ? parseInt(thispage) - 1 : parseInt(thispage) + 1;

  if (plotType === 'fsseedling' && window.seedlingPage?.syncState) {
    window.seedlingPage.syncState({ thispage: gopage, pps, record: data });
  }

  if (plotType === 'ss10m' || plotType === 'ss1ha')  {
    ssdatatableupdate(data, gopage, pps);
  } else if (plotType === 'fstree') {
    fstreetableupdate(data, gopage, pps);
  } else if (plotType === 'fsseedling') {
    fsseedlingtableupdate(data, gopage, pps, pageContext.maxid);
  } else if (plotType === 'fsseeds') {
    fsseedstableupdate(data, gopage, 29);
  }
}


function dataTableUpdate(data, thispage, pps, plotType, tableType, site){
  if (plotType === 'fsseedling' && window.seedlingPage?.setScopedNote) {
    window.seedlingPage.setScopedNote('data', site, '');
  } else {
    $('.datasavenote').html('');
  }
  var totalpage=Math.ceil(data.length/pps);
  const pageElements = getScopedPageElements(plotType, site);
  const isExpandedView = pps > 20;
  const hidePagingInExpandedView = isExpandedView && data.length <= 40;
  var container = $(`#datatable${site}`);
  var handsontable = container.data('handsontable');
  // console.log(data);
  
  pageElements.totalnum.html(`共有 ${data.length} 筆資料。`);
  pageElements.pages.css('display', 'flex');
  configurePageSizeControl(pageElements, data, pps, plotType, site);

  if (hidePagingInExpandedView) {
    pageElements.pagenote.hide();
    pageElements.prev.hide();
    pageElements.next.hide();
  } else if (totalpage > 1) {
    pageElements.pagenote.show();
    pageElements.prev.show();
    pageElements.next.show();
  } else {
    pageElements.pagenote.hide();
    pageElements.prev.hide();
    pageElements.next.hide();
  }

  var data3 = (totalpage > 1) ? pages(data, thispage, totalpage, pps, plotType, site)[1] : data;

    for (let i = 0; i < data3.length; i++) {
      if (data3[i]['date'] === '0000-00-00') {
          data3[i]['date'] = ''; // 使用单等号进行赋值
      }
  }

// console.log(data3);
  handsontable.updateData(data3, thispage);
  handsontable.updateSettings({
    cells: function (row, col, prop) {
  
      return cellfunction(tableType, container, row, col, prop);
    }
    });
}

function configurePageSizeControl(pageElements, data, pps, plotType, site) {
  const pageContext = getPageContext();
  const canChoosePageSize = data.length > 20;
  const selectedValue = pps > 20 ? '40' : '20';

  pageElements.pageSize.toggle(canChoosePageSize);

  if (!canChoosePageSize || !pageElements.pageSizeSelect.length) {
    return;
  }

  pageElements.pageSizeSelect.val(selectedValue);
  pageElements.pageSizeSelect.off('.seedlingPageSize').on('change.seedlingPageSize input.seedlingPageSize', function () {
    const selected = $(this).val() === '40' ? 40 : 20;
    const ppsall = selected === 40 ? Math.min(40, data.length) : 20;

    if (plotType === 'ss10m' || plotType === 'ss1ha') {
      ssdatatableupdate(data, 1, ppsall);
    } else if (plotType == 'fstree') {
      fstreetableupdate(data, 1, ppsall);
    } else if (plotType === 'fsseedling') {
      fsseedlingtableupdate(data, 1, ppsall, pageContext.maxid ?? window.seedlingPage?.state?.maxid ?? null);
    }
  });
}


  const numericValidator5 = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 5);
    }
  };

  const numericValidator10 = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 10);
    }
  };


  const numericValidator20 = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 20);
    }
  };

  const numericValidator100 = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 100);
    }
  };

  const qqValidator = (value, callback) => {
    if ([1, 2, ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

  const qqValidator4 = (value, callback) => {
    if ([1, 2, 3, 4, ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

  const layerValidator = (value, callback) => {
    if (['u', 'o', ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };
