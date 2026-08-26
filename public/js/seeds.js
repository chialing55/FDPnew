// console.log(type);
//重新選擇工作項目
const seedsConfig = window.seedsConfig || {};
const seedsRoutes = window.seedsRoutes || {};
const urlbase = seedsRoutes.base || '/admin/fushan/seeds';
const sectionBase = seedsRoutes.sectionBase || urlbase;
const saveDataBase = seedsRoutes.saveDataBase || `${urlbase}/data`;
const saveData1Base = seedsRoutes.saveData1Base || `${urlbase}/data1`;
const deleteDataBase = seedsRoutes.deleteDataBase || `${urlbase}/data`;
const finishUrl = seedsRoutes.finish || `${urlbase}/finish`;
const currentUser = seedsConfig.user || '';
const isAdmin = Boolean(seedsConfig.isAdmin);
const defaultSeedsSortMode = seedsConfig.defaultSort === 'trap' ? 'trap' : 'input';
const isUnknownAllView = seedsConfig.viewMode === 'unknown-all';

let fdata = [];
let seedsEmptyTable = [];
let realemptytable = [];
let seedsCsplist = [];
let currentCensus = null;
let ppsall = 29;
const seedIdentifierOptions = ['黃小俊', '張楊家豪'];
let seedsSortMode = defaultSeedsSortMode;
let seedsInsertedHighlightIds = [];

const seedsPage = {
  context: {
    entry: 1,
    user: currentUser,
    plotType: 'fsseeds',
  },
  state: {
    pps: ppsall,
    realemptytable,
  },
  noteTone(message, explicitTone = '') {
    if (explicitTone === 'success' || explicitTone === 'error') {
      return explicitTone;
    }
    return '';
  },
  applyNoteTone(target, message, explicitTone = '') {
    if (!target || !target.length) {
      return;
    }

    const tone = this.noteTone(message, explicitTone);
    target.removeClass('app-feedback-note--success app-feedback-note--error');

    if (tone === 'success') {
      target.addClass('app-feedback-note--success');
    } else if (tone === 'error') {
      target.addClass('app-feedback-note--error');
    }
  },
  clearNotes() {
    const notes = $('.seedssavenote, .finishnote');
    notes.html('');
    notes.removeClass('app-feedback-note--success app-feedback-note--error');
  },
  setNote(selector, message, tone = '') {
    const target = $(selector);
    target.html(message || '');
    this.applyNoteTone(target, message, tone);
  },
  syncState(nextState = {}) {
    this.state = {
      ...this.state,
      ...nextState,
    };
  },
};

window.seedsPage = seedsPage;

function normalizeSeedsSortValue(value) {
  return `${value ?? ''}`.trim();
}

function compareSeedsNumericText(a, b) {
  const textA = normalizeSeedsSortValue(a);
  const textB = normalizeSeedsSortValue(b);
  const numA = Number(textA);
  const numB = Number(textB);
  const isNumA = textA !== '' && Number.isFinite(numA);
  const isNumB = textB !== '' && Number.isFinite(numB);

  if (isNumA && isNumB && numA !== numB) {
    return numA - numB;
  }

  return textA.localeCompare(textB, 'zh-Hant');
}

function getSeedsDisplayData() {
  if (seedsSortMode !== 'trap') {
    return [...fdata];
  }

  return [...fdata].sort((left, right) => {
    if (isUnknownAllView) {
      const censusCompare = compareSeedsNumericText(left?.census, right?.census);
      if (censusCompare !== 0) {
        return censusCompare;
      }
    }

    const trapCompare = compareSeedsNumericText(left?.trap, right?.trap);
    if (trapCompare !== 0) {
      return trapCompare;
    }

    const cspCompare = compareSeedsNumericText(left?.csp, right?.csp);
    if (cspCompare !== 0) {
      return cspCompare;
    }

    return compareSeedsNumericText(left?.code, right?.code);
  });
}

function setSeedsInsertedHighlights(ids = []) {
  seedsInsertedHighlightIds = Array.isArray(ids)
    ? ids.map((id) => `${id}`).filter((id) => id !== '')
    : [];
}

function resolveSeedsTargetPage(displayData, insertedIds = [], fallbackPage = 1, pps = 29) {
  const normalizedIds = Array.isArray(insertedIds)
    ? insertedIds.map((id) => `${id}`).filter((id) => id !== '')
    : [];

  if (!normalizedIds.length) {
    return fallbackPage;
  }

  const firstMatchIndex = displayData.findIndex((row) => normalizedIds.includes(`${row?.id ?? ''}`));
  if (firstMatchIndex === -1) {
    return fallbackPage;
  }

  return Math.floor(firstMatchIndex / pps) + 1;
}

function updateSeedsSortButtons() {
  $('[data-seeds-sort-select]').val(seedsSortMode);
}

function renderSeedsTable(page = 1) {
  const displayData = getSeedsDisplayData();
  const site = seedsTableSite(seedsEmptyTable);
  const container = $(`#datatable${site}`);

  if (container.length && container.data('handsontable')) {
    fsseedstableupdate(displayData, page, 29);
  } else {
    seedstable(displayData, page, 29, seedsEmptyTable);
  }

  updateSeedsSortButtons();
}

function currentSeedsPage() {
  const current = Number($('.next').first().attr('thispage') || $('.prev').first().attr('thispage') || 1);
  return Number.isFinite(current) && current > 0 ? current : 1;
}

function clampSeedsPage(dataLength, requestedPage, pps = 29) {
  const totalPages = Math.max(1, Math.ceil((dataLength || 0) / pps));
  const page = Number(requestedPage);
  if (!Number.isFinite(page) || page < 1) {
    return 1;
  }

  return Math.min(page, totalPages);
}

function seedsTableSite(emptytable = []) {
  if (isUnknownAllView) {
    return currentCensus || 'unknown';
  }

  return emptytable?.[0]?.census ?? '';
}

$('.listlink').on('click', function () {
  let type = $(this).attr('type');
  if (typeof type != 'undefined') {
    if (type == 'websplist') {
      window.open('/splist', '_blank');
    } else {
      location.href = `${sectionBase}/${type}`;
    }

  }

})


function refreshSeedsDataViewerTable() {
  const $table = $("#sptable");
  if (!$table.length) {
    return;
  }

  if ($table.hasClass('tablesorter')) {
    $table.trigger('destroy');
  }

  $table.tablesorter();
}

refreshSeedsDataViewerTable();

//unknown照片顯示
Fancybox.bind('[data-fancybox="gallery"]', {
  // Your custom options
});

// window.addEventListener('resptable', event => {

//   $("#sptable").trigger("updateAll");

// });



// 使用 //上方表單
handleHoverEvents('.list4', '.list4inner');
handleHoverEvents('.list6', '.list6inner');

var plotType = 'fsseeds';
var thispage = 1;
var entry = 1;

$(document).on('click', 'button[name=creattable]', function () {
  $('#seedstableout').hide();
  $('#seedstableout_empty').show();
  seedsPage.setNote('.seedssavenote', '');
});

$(document).on('click', 'button[name=show_seedstable]', function () {

  $('#seedstableout').show();
  $('#seedstableout_empty').hide();
  renderSeedsTable(1);
});

$(document).on('change', '[data-seeds-sort-select]', function () {
  const requestedMode = $(this).val();
  if (!requestedMode || requestedMode === seedsSortMode) {
    updateSeedsSortButtons();
    return;
  }

  seedsSortMode = requestedMode;
  renderSeedsTable(1);
});


document.addEventListener('livewire:init', () => {
  if (window.__boundSeedsDataEvent) return;
  window.__boundSeedsDataEvent = true;

  Livewire.on('data', ({ record, emptytable: eventEmptytable, census: eventCensus, csplist: eventCsplist }) => {
    $('.entrytableout').show();
    $('.keepenter').hide();
    $('.dateinfo').hide();

    fdata = record;
    seedsEmptyTable = eventEmptytable;
    currentCensus = eventCensus;
    seedsCsplist = eventCsplist;

    realemptytable = deepCopy(eventEmptytable);
    seedsPage.syncState({
      pps: ppsall,
      realemptytable,
    });

    if (fdata.length > 0 || isUnknownAllView) {
      $('#seedstableout').show();
      $('#seedstableout_empty').hide();

      renderSeedsTable(1);
      if (!isUnknownAllView) {
        emptyseedstable(seedsEmptyTable);
      }
    } else {
      $('#seedstableout').hide();
      $('#seedstableout_empty').show();

      renderSeedsTable(1);
      if (!isUnknownAllView) {
        emptyseedstable(seedsEmptyTable);
      }
    }
  });
});

document.addEventListener('livewire:initialized', () => {
  if (!window.Livewire || typeof window.Livewire.hook !== 'function' || window.__boundSeedsViewerTableHook) {
    return;
  }

  window.__boundSeedsViewerTableHook = true;
  window.Livewire.hook('message.processed', () => {
    requestAnimationFrame(() => {
      refreshSeedsDataViewerTable();
    });
  });
});



function handleSuccessAllTable(res, tableType, handsontable) {
  var noteProperty = `${tableType}savenote`;

  if (res[noteProperty] != '') {
    seedsPage.setNote(`.${noteProperty}`, res[noteProperty], res[`${noteProperty}_type`] || '');
  }

  if (tableType === 'addseedsdata') {
    if (res.seedssavenote != '') {
      seedsPage.setNote('.seedssavenote', res.seedssavenote, res.seedssavenote_type || '');
    }
    // console.log(emptytable);
    emptytable2 = deepCopy(realemptytable);
    handsontable.updateData(emptytable2);

    totalpage = Math.ceil(res.data.length / 29);

    $('#seedstableout').show();
    $('#seedstableout_empty').hide();

    fdata = res.data;
    setSeedsInsertedHighlights(res.inserted_ids || []);
    const displayData = getSeedsDisplayData();
    const targetPage = seedsSortMode === 'trap'
      ? resolveSeedsTargetPage(displayData, res.inserted_ids, totalpage, 29)
      : 1;
    fsseedstableupdate(displayData, targetPage, 29);
  } else if (tableType == 'data') {
    if (res.seedssavenote != '') {
      seedsPage.setNote('.seedssavenote', res.seedssavenote, res.seedssavenote_type || '');
    }
    fdata = res.data;
    setSeedsInsertedHighlights(res.inserted_ids || []);
    const displayData = getSeedsDisplayData();
    const hasInsertedRows = Array.isArray(res.inserted_ids) && res.inserted_ids.length > 0;
    const targetPage = (seedsSortMode === 'trap' && hasInsertedRows)
      ? resolveSeedsTargetPage(displayData, res.inserted_ids, currentSeedsPage(), 29)
      : currentSeedsPage();
    fsseedstableupdate(displayData, targetPage, 29);
  }
}

const codeValidator = (value, callback) => {
  if ([1, 2, 3, 4, 5, 6, ''].includes(value)) {   //允許1234和空格
    callback(true);
  } else {
    callback(false);
  }
};

const sexValidator = (value, callback) => {
  if (['F', 'M', 'MF', ''].includes(value)) {   //允許1234和空格
    callback(true);
  } else {
    callback(false);
  }
};

function cellfunction(tableType, container, row, col, prop) {
  var cellProperties = {};
  const classNames = [];

  if (tableType === 'data') {
    const rowData = container.handsontable('getSourceDataAtRow', row);
    const rowId = `${rowData?.id ?? ''}`;
    if (seedsInsertedHighlightIds.includes(rowId)) {
      classNames.push('seeds-new-row-highlight');
    }

    var data = container.handsontable('getData');

    // 安全防呆檢查
    if (Array.isArray(data) && data[row] && data[row][12] !== undefined) {
      var curData = data[row][12];

      if (curData !== '') {
        classNames.push('text-red-500');
      }
    }

    if (classNames.length) {
      cellProperties.className = classNames.join(' ');
    }

    return cellProperties;
  }
}


function emptyseedstable(emptytable) {
  if (!Array.isArray(emptytable) || emptytable.length === 0) {
    return;
  }

  const site = seedsTableSite(emptytable);
  $(`button[name=newdatasave${site}]`).off();
  var container = $(`#seedstable_empty${site}`);
  var saveButtonName = `newdatasave${site}`;
  var tableType = 'addseedsdata';
  // var emptytable=emptytable;
  var columns = [
    { data: "id" },
    { data: "census" },
    { data: "trap", allowInvalid: false },
    { data: "csp", type: 'autocomplete', source: seedsCsplist, strict: true, visibleRows: 10, allowInvalid: false },
    { data: "code", type: 'numeric', allowInvalid: false, validator: codeValidator },
    { data: "count", type: 'numeric', allowInvalid: false },
    { data: "seeds" },
    { data: "viability" },
    { data: "fragments", type: 'numeric', allowInvalid: false },

    { data: "sex", allowInvalid: false, validator: sexValidator },
    { data: "identifier", type: 'autocomplete', source: seedIdentifierOptions, allowInvalid: true, visibleRows: 20 },
    { data: "note" },

  ];

  var colWidths = [10, 40, 50, 120, 50, 50, 60, 50, 70, 40, 100, 160];
  var colHeaders = ["id", "census", "Trap", "種類", "類別", "數量", "種子數", "活性", "碎片3數量", "性別", "鑑定者", "備註"];

  var hiddenColumns = {
    columns: isUnknownAllView ? [0] : [0, 1],
  };
  var handsontable = createHandsontable(container, columns, emptytable, saveButtonName, `${saveData1Base}/record`, tableType, colWidths, hiddenColumns, colHeaders, thispage);


  //更新大表
  container.parent().find(`button[name=newdatasave2${site}]`).off('click.seedsSaveNewFullData').on('click.seedsSaveNewFullData', function () {
    seedsPage.clearNotes();

    const saveUrl2 = `${saveData1Base}/fulldata`;
    var ajaxData = {
      data: handsontable.getSourceData(),
      entry: entry,
      user: currentUser,
      plotType: plotType,
      currentCensus: isUnknownAllView ? null : currentCensus,
      thispage: thispage,
    };
    var ajaxType = 'post';
    // ceartAjax
    makeAjaxRequest(
      saveUrl2, ajaxData, ajaxType,
      function (res) {
        handleSuccessAllTable(res, tableType, handsontable);
      },
      function (err) {
        const detail = err?.response?.seedssavenote
          || err?.response?.message
          || err?.error
          || '儲存失敗';
        const tone = err?.response?.seedssavenote_type || 'error';
        seedsPage.setNote('.seedssavenote', detail, tone);
      }
    );

  });

}




function seedstable(data, thispage, pps, emptytable) {
  if (!Array.isArray(emptytable) || emptytable.length === 0) {
    return;
  }

  // console.log(data);
  // const census=data[0]['census'];

  totalpage = Math.ceil(data.length / pps);
  $('.totalnum').html(`共有 ${data.length} 筆資料。`);

  var site = seedsTableSite(emptytable);
  var container = $(`#datatable${site}`);

  var saveButtonName = `datasave${site}`;
  var tableType = 'data';
  ppsall = pps;
  var data2 = processDataTable(data, thispage, pps, site, plotType);

  var columns = [
    { data: "id" },
    { data: "census" },
    { data: "trap", allowInvalid: false },
    { data: "csp", type: 'autocomplete', source: seedsCsplist, strict: true, visibleRows: 10, allowInvalid: false },
    { data: "code", type: 'numeric', allowInvalid: false, validator: codeValidator },
    { data: "count", type: 'numeric', allowInvalid: false },
    { data: "seeds" },
    { data: "viability" },
    { data: "fragments", type: 'numeric', allowInvalid: false },

    { data: "sex", allowInvalid: false, validator: sexValidator },
    { data: "identifier", type: 'autocomplete', source: seedIdentifierOptions, allowInvalid: true, visibleRows: 20 },
    { data: "note" },
    { data: "checknote", readOnly: true },
    { data: "d", readOnly: true, renderer: "html" }

  ];

  var colWidths = isUnknownAllView
    ? [30, 60, 60, 150, 50, 50, 60, 50, 90, 50, 120, 180, 220, 40]
    : [30, 40, 50, 120, 50, 50, 60, 50, 70, 40, 100, 160, 200, 40];
  var colHeaders = ["id", "census", "Trap", "種類", "類別", "數量", "種子數", "活性", "碎片3數量", "性別", "鑑定者", "備註", "檢查", ""];

  var hiddenColumns = {
    columns: isUnknownAllView ? [0] : [0, 1],
  };

  var handsontable = createHandsontable(container, columns, data2, saveButtonName, `${saveDataBase}/record`, tableType, colWidths, hiddenColumns, colHeaders, thispage);



  //更新大表
  container.parent().find(`button[name=datasave2${site}]`).off('click.seedsSaveFullData').on('click.seedsSaveFullData', function () {

    seedsPage.clearNotes();

    const saveUrl2 = `${saveDataBase}/fulldata`;
    var ajaxData = {
      data: handsontable.getSourceData(),
      entry: entry,
      user: currentUser,
      plotType: plotType,
      currentCensus: isUnknownAllView ? null : currentCensus,
      thispage: thispage,
    };
    var ajaxType = 'post';
    // ceartAjax
    makeAjaxRequest(
      saveUrl2, ajaxData, ajaxType,
      function (res) {
        handleSuccessAllTable(res, tableType, handsontable);
      },
      function (err) {
        const detail = err?.response?.seedssavenote
          || err?.response?.message
          || err?.error
          || '儲存失敗';
        const tone = err?.response?.seedssavenote_type || 'error';
        seedsPage.setNote('.seedssavenote', detail, tone);
      }
    );

  });


}


function fsseedstableupdate(data, thispage, pps) {


  var site = isUnknownAllView ? (currentCensus || 'unknown') : (data[0]?.census || seedsTableSite(seedsEmptyTable));


  var tableType = 'data';
  dataTableUpdate(data, thispage, pps, plotType, tableType, site);

}

// function emptytableupdate(emptytable3){

// 	// $('#seedstableout').hide();
// 	// $('#seedstableout_empty').show();
// 	var container = $("#seedstable_empty");
// 	var handsontable = container.data('handsontable');

// 	handsontable.updateData(emptytable3);
// 	handsontable.updateSettings({
//     cells: function (row, col, prop) {

//    	 },
//     });


// }

function deleteid(id, info, thispage, type) {

  if (confirm('確定刪除 ' + info + ' 種子雨資料??')) {
    seedsPage.clearNotes();

    var saveUrl = `${deleteDataBase}/${id}/${info}/${thispage}/${type}`;
    var ajaxData = { _method: 'DELETE' };
    var ajaxType = 'post';

    function handleSuccess(res) {
      if (res.seedssavenote != '') {
        seedsPage.setNote('.seedssavenote', res.seedssavenote, res.seedssavenote_type || '');
      }
      setSeedsInsertedHighlights([]);
      fdata = res.data;
      const displayData = getSeedsDisplayData();
      const targetPage = clampSeedsPage(displayData.length, currentSeedsPage(), 29);
      fsseedstableupdate(displayData, targetPage, 29);
    }
    makeAjaxRequest(
      saveUrl, ajaxData, ajaxType,
      handleSuccess,
      function (err) {
        const detail = err?.response?.seedssavenote
          || err?.response?.message
          || err?.error
          || '刪除失敗';
        const tone = err?.response?.seedssavenote_type || 'error';
        seedsPage.setNote('.seedssavenote', detail, tone);
      }
    );
  }
}




function finish() {
  // console.log(entry);

  var saveUrl = finishUrl;
  var ajaxData = {};
  var ajaxType = 'post';
  seedsPage.setNote('.finishnote', '');

  function handleSuccess(res) {
    if (res.finishnote != '') {
      seedsPage.setNote('.finishnote', res.finishnote, res.finishnote_type || '');
    } else {
      location.href = `${urlbase}/entry`;
    }
  }
  makeAjaxRequest(
    saveUrl, ajaxData, ajaxType,
    handleSuccess,
    function (err) {
      const detail = err?.response?.finishnote
        || err?.response?.message
        || err?.error
        || '輸入完成檢查失敗';
      const tone = err?.response?.finishnote_type || 'error';
      seedsPage.setNote('.finishnote', detail, tone);
    }
  );
}


if (isAdmin) {
  $(".editunkDesShow").show();
  $(".editDesShow").show();
}
