@extends('layouts/seedling')

@section('pagejs')
<script type="text/javascript">
  $('.list3').addClass('now');
  $('.list34').addClass('now');
  $('.list3 hr').css('color', '#91A21C');
</script>
@endsection

@section('rightbox')
<style>
  @font-face {
    font-family: 'IansuiPractice';
    src: url('{{ asset('/fonts/iansui.ttf') }}') format('truetype');
    font-weight: 400;
    font-style: normal;
    font-display: swap;
  }

  .copybook-workspace {
    width: min(1180px, calc(100vw - 48px));
    display: grid;
    grid-template-columns: minmax(260px, 320px) minmax(0, 1fr);
    gap: 22px;
    align-items: start;
    justify-content: center;
    padding: 8px 0 32px;
  }

  .copybook-panel {
    position: sticky;
    top: 16px;
    background: #fff;
    border: 1px solid #d8d8d8;
    border-radius: 8px;
    padding: 18px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
  }

  .copybook-panel h2 {
    margin: 0 0 14px;
    font-size: 22px;
    font-weight: 500;
  }

  .copybook-field {
    display: grid;
    gap: 6px;
    margin-bottom: 14px;
  }

  .copybook-field label {
    font-size: 15px;
    color: #333;
  }

  .copybook-input,
  .copybook-textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #b8b8b8;
    border-radius: 6px;
    padding: 9px 10px;
    font-size: 15px;
    line-height: 1.5;
    background: #fff;
  }

  .copybook-textarea {
    min-height: 220px;
    resize: vertical;
    font-family: 'IansuiPractice', 'Noto Serif TC', serif;
    font-size: 20px;
  }

  .copybook-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
  }

  .copybook-actions button {
    min-height: 36px;
    padding: 7px 14px;
    border: 1px solid #7f8e18;
    border-radius: 6px;
    background: #91a21c;
    color: #fff;
    cursor: pointer;
  }

  .copybook-actions button.secondary {
    color: #333;
    background: #fff;
    border-color: #b8b8b8;
  }

  .copybook-meta {
    margin-top: 12px;
    color: #666;
    font-size: 14px;
  }

  .copybook-preview {
    display: grid;
    justify-items: center;
    gap: 18px;
    overflow-x: auto;
    padding-bottom: 10px;
  }

  .practice-page {
    width: 210mm;
    height: 297mm;
    box-sizing: border-box;
    padding: 13mm 12mm 12mm;
    background: #fff;
    border: 1px solid #c9c9c9;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    page-break-after: always;
    break-after: page;
    color: #222;
  }

  .practice-header {
    height: 12mm;
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 18mm;
    align-items: start;
    font-size: 14px;
  }

  .practice-title {
    font-size: 22px;
    font-weight: 400;
    letter-spacing: 0;
    text-align: left;
  }

  .practice-line {
    min-width: 34mm;
    border-bottom: 1px solid #333;
    padding: 0 3mm 1mm;
    line-height: 1.2;
    white-space: nowrap;
  }

  .practice-grid {
    height: 258mm;
    display: grid;
    grid-template-columns: repeat(6, 1fr) 4mm repeat(6, 1fr);
    grid-template-rows: repeat(16, 1fr);
    border-top: 1.2px solid #222;
    border-left: 1.2px solid #222;
  }

  .practice-gap {
    grid-row: 1 / -1;
    border-left: 1.2px solid #222;
    border-right: 1.2px solid #222;
    background: #fff;
  }

  .practice-cell {
    position: relative;
    display: grid;
    place-items: center;
    border-right: 1.2px solid #222;
    border-bottom: 1.2px solid #222;
    overflow: hidden;
  }

  .practice-cell::before,
  .practice-cell::after {
    content: '';
    position: absolute;
    pointer-events: none;
    opacity: 0.85;
  }

  .practice-cell::before {
    left: 50%;
    top: 1.5mm;
    bottom: 1.5mm;
    border-left: 1px dashed #ffb7b7;
  }

  .practice-cell::after {
    top: 50%;
    left: 1.5mm;
    right: 1.5mm;
    border-top: 1px dashed #ffb7b7;
  }

  .practice-char {
    position: relative;
    z-index: 1;
    font-family: 'IansuiPractice', 'Noto Serif TC', serif;
    font-size: 12.5mm;
    line-height: 1;
    font-weight: 400;
  }

  .practice-char.model {
    color: #1e1e1e;
  }

  .practice-char.trace {
    color: rgba(80, 80, 80, 0.28);
  }

  .practice-empty {
    color: transparent;
  }

  @media (max-width: 980px) {
    .copybook-workspace {
      grid-template-columns: 1fr;
      width: min(100%, calc(100vw - 24px));
    }

    .copybook-panel {
      position: static;
    }
  }

  @media print {
    body * {
      visibility: hidden !important;
    }

    .copybook-preview,
    .copybook-preview * {
      visibility: visible !important;
    }

    .copybook-preview {
      display: block;
      padding: 0;
      overflow: visible;
    }

    .practice-page {
      margin: 0;
      border: 0;
      box-shadow: none;
    }

    @page {
      size: A4 portrait;
      margin: 0;
    }
  }
</style>

<div class="copybook-workspace">
  <section class="copybook-panel">
    <h2>硬筆書法練習</h2>
    <div class="copybook-field">
      <label for="copybook-title">標題</label>
      <input id="copybook-title" class="copybook-input" type="text" value="硬筆書法練習紙">
    </div>
    <div class="copybook-field">
      <label for="copybook-name">姓名</label>
      <input id="copybook-name" class="copybook-input" type="text" value="">
    </div>
    <div class="copybook-field">
      <label for="copybook-date">日期</label>
      <input id="copybook-date" class="copybook-input" type="date" value="{{ now()->toDateString() }}">
    </div>
    <div class="copybook-field">
      <label for="copybook-text">文字</label>
      <textarea id="copybook-text" class="copybook-textarea">風淡雲輕江山如畫天水一色柳暗花明</textarea>
    </div>
    <div class="copybook-actions">
      <button type="button" id="copybook-print">列印</button>
      <button type="button" id="copybook-clear" class="secondary">清空</button>
    </div>
    <div class="copybook-meta" id="copybook-meta"></div>
  </section>

  <section class="copybook-preview" id="copybook-preview" aria-live="polite"></section>
</div>

<script type="text/javascript">
(function () {
  const rowsPerPage = 16;
  const columnsPerPage = 12;
  const groupsPerPage = 2;
  const charsPerPage = rowsPerPage * groupsPerPage;
  const modelColumns = new Set([0, 6]);
  const traceColumns = new Set([1, 2, 7, 8]);
  const inputs = {
    title: document.getElementById('copybook-title'),
    name: document.getElementById('copybook-name'),
    date: document.getElementById('copybook-date'),
    text: document.getElementById('copybook-text'),
  };
  const preview = document.getElementById('copybook-preview');
  const meta = document.getElementById('copybook-meta');

  function normalizeText(value) {
    return Array.from((value || '').replace(/[\r\n\t ]+/g, ''));
  }

  function cellClass(column) {
    if (modelColumns.has(column)) return 'model';
    if (traceColumns.has(column)) return 'trace';
    return 'practice-empty';
  }

  function displayDate(value) {
    if (!value) return '';
    return value.replaceAll('-', '/');
  }

  function renderPage(chars, pageIndex, totalPages) {
    const page = document.createElement('article');
    page.className = 'practice-page';

    const header = document.createElement('header');
    header.className = 'practice-header';
    header.innerHTML = `
      <div class="practice-title"></div>
      <div>姓名：<span class="practice-line practice-name"></span></div>
      <div>日期：<span class="practice-line practice-date"></span></div>
    `;
    header.querySelector('.practice-title').textContent = totalPages > 1
      ? `${inputs.title.value || '硬筆書法練習紙'} ${pageIndex + 1}/${totalPages}`
      : (inputs.title.value || '硬筆書法練習紙');
    header.querySelector('.practice-name').textContent = inputs.name.value || '';
    header.querySelector('.practice-date').textContent = displayDate(inputs.date.value);
    page.appendChild(header);

    const grid = document.createElement('div');
    grid.className = 'practice-grid';

    for (let row = 0; row < rowsPerPage; row += 1) {
      for (let column = 0; column < columnsPerPage; column += 1) {
        if (row === 0 && column === 6) {
          const gap = document.createElement('div');
          gap.className = 'practice-gap';
          gap.style.gridColumn = '7';
          grid.appendChild(gap);
        }

        const cell = document.createElement('div');
        cell.className = 'practice-cell';
        cell.style.gridColumn = String(column < 6 ? column + 1 : column + 2);
        cell.style.gridRow = String(row + 1);

        const span = document.createElement('span');
        span.className = `practice-char ${cellClass(column)}`;
        const group = column < 6 ? 0 : 1;
        span.textContent = chars[group * rowsPerPage + row] || '';
        cell.appendChild(span);
        grid.appendChild(cell);
      }
    }

    page.appendChild(grid);
    return page;
  }

  function render() {
    const chars = normalizeText(inputs.text.value);
    const pages = Math.max(1, Math.ceil(chars.length / charsPerPage));
    preview.innerHTML = '';

    for (let pageIndex = 0; pageIndex < pages; pageIndex += 1) {
      const start = pageIndex * charsPerPage;
      preview.appendChild(renderPage(chars.slice(start, start + charsPerPage), pageIndex, pages));
    }

    meta.textContent = `${chars.length} 字，${pages} 頁`;
  }

  Object.values(inputs).forEach((input) => {
    input.addEventListener('input', render);
    input.addEventListener('change', render);
  });

  document.getElementById('copybook-print').addEventListener('click', function () {
    window.print();
  });

  document.getElementById('copybook-clear').addEventListener('click', function () {
    inputs.text.value = '';
    inputs.text.focus();
    render();
  });

  render();
})();
</script>
@endsection
