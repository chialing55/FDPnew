describe('Walkontable.Selection', () => {
  const debug = false;

  beforeEach(function() {
    this.$wrapper = $('<div></div>').addClass('handsontable').css({ overflow: 'hidden' });
    this.$wrapper.width(100).height(200);
    this.$container = $('<div></div>');
    this.$table = $('<table></table>').addClass('htCore'); // create a table that is not attached to document
    this.$wrapper.append(this.$container);
    this.$container.append(this.$table);
    this.$wrapper.appendTo('body');
    createDataArray(100, 4);
  });

  afterEach(function() {
    if (!debug) {
      $('.wtHolder').remove();
    }

    this.$wrapper.remove();
    this.wotInstance.destroy();
  });

  it('should add/remove class to selection when cell is clicked', () => {
    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController(),
      onCellMouseDown(event, coords) {
        wt.selections.getCell().clear();
        wt.selections.getCell().add(coords);
        wt.draw();
      }
    });

    wt.draw();

    const $td1 = spec().$table.find('tbody td:eq(0)');
    const $td2 = spec().$table.find('tbody td:eq(1)');

    expect($td1.hasClass('current')).toEqual(false);

    $td1.simulate('mousedown');

    expect($td1.hasClass('current')).toEqual(true);

    $td2.simulate('mousedown');

    expect($td1.hasClass('current')).toEqual(false);
    expect($td2.hasClass('current')).toEqual(true);
  });

  it('should add class to selection on all overlays', function() {
    spec().$wrapper.width(300).height(300);

    this.data = createSpreadsheetData(10, 10);

    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController(),
      fixedColumnsStart: 2,
      fixedRowsTop: 2
    });

    const area = wt.selections.createOrGetArea();

    area.add(new Walkontable.CellCoords(1, 1));
    area.add(new Walkontable.CellCoords(1, 2));
    area.add(new Walkontable.CellCoords(2, 1));
    area.add(new Walkontable.CellCoords(2, 2));

    wt.draw();

    const tds = spec().$wrapper.find('td:contains(B2), td:contains(B3), td:contains(C2), td:contains(C3)');

    expect(tds.length).toBeGreaterThan(4);

    for (let i = 0, ilen = tds.length; i < ilen; i++) {
      expect(tds[i].className).toContain('area');
    }
  });

  it('should create the area selection that does not flicker when the table is scrolled back and forth near its left edge (#8317)', async function() {
    spec().$wrapper.width(300).height(300);

    this.data = createSpreadsheetData(10, 10);

    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController(),
      rowHeaders: [function(row, TH) {
        TH.innerHTML = row + 1;
      }],
      columnHeaders: [function(col, TH) {
        TH.innerHTML = col + 1;
      }],
    });

    const area1 = wt.selections.createOrGetArea({ layerLevel: 1 });

    area1.add(new Walkontable.CellCoords(0, 0));
    area1.add(new Walkontable.CellCoords(0, 2));
    area1.add(new Walkontable.CellCoords(2, 0));
    area1.add(new Walkontable.CellCoords(2, 2));

    const area2 = wt.selections.createOrGetArea({ layerLevel: 2 });

    area2.add(new Walkontable.CellCoords(2, 0));
    area2.add(new Walkontable.CellCoords(2, 2));
    area2.add(new Walkontable.CellCoords(4, 0));
    area2.add(new Walkontable.CellCoords(4, 2));

    wt.draw();

    wt.wtOverlays.inlineStartOverlay.setScrollPosition(1);

    await sleep(100);

    const tds = spec().$wrapper.find('td:contains(A2), td:contains(A3), td:contains(A4)');

    expect(tds.length).toBe(3);
    expect(tds[0].className).toBe('area');
    expect(tds[1].className).toBe('area area-1');
    expect(tds[2].className).toBe('area');

    wt.wtOverlays.inlineStartOverlay.setScrollPosition(0);

    await sleep(100);

    expect(tds.length).toBe(3);
    expect(tds[0].className).toBe('area');
    expect(tds[1].className).toBe('area area-1');
    expect(tds[2].className).toBe('area');
  });

  it('should not add class to selection until it is rerendered', () => {
    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController(),
    });

    wt.draw();
    wt.selections.getCell().add(new Walkontable.CellCoords(0, 0));

    const $td1 = spec().$table.find('tbody td:eq(0)');

    expect($td1.hasClass('current')).toEqual(false);

    wt.draw();
    expect($td1.hasClass('current')).toEqual(true);
  });

  it('should add/remove border to selection when cell is clicked', async() => {
    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController(),
      onCellMouseDown(event, coords) {
        wt.selections.getCell().clear();
        wt.selections.getCell().add(coords);
        wt.draw();
      }
    });

    wt.draw();

    await sleep(1500);
    const $td1 = spec().$table.find('tbody tr:eq(1) td:eq(0)');
    const $td2 = spec().$table.find('tbody tr:eq(2) td:eq(1)');
    const $top = $(wt.selections.getCell().getBorder(wt).top); // cheat... get border for ht_master

    $td1.simulate('mousedown');

    const pos1 = $top.position();

    expect(pos1.top).toBeGreaterThan(0);
    expect(pos1.left).toBe(0);

    $td2.simulate('mousedown');
    const pos2 = $top.position();

    expect(pos2.top).toBeGreaterThan(pos1.top);
    expect(pos2.left).toBeGreaterThan(pos1.left);
  });

  it('should add a selection that is outside of the viewport', () => {
    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController(),
    });

    wt.draw();

    wt.selections.getCell().add(new Walkontable.CellCoords(20, 0));

    expect(wt.wtTable.getCoords(spec().$table.find('tbody tr:first td:first')[0]))
      .toEqual(new Walkontable.CellCoords(0, 0));
  });

  it('should not scroll the viewport after selection is cleared', () => {
    const scrollbarWidth = getScrollbarWidth(); // normalize viewport size disregarding of the scrollbar size on any OS

    spec().$wrapper.width(100 + scrollbarWidth).height(200 + scrollbarWidth);

    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController(),
    });

    wt.draw();

    wt.selections.getCell().add(new Walkontable.CellCoords(0, 0));
    wt.draw();
    expect(wt.wtTable.getFirstVisibleRow()).toEqual(0);

    wt.scrollViewportVertically(17);
    wt.draw();

    const expectedFirstVisibleRow = 10;

    expect(wt.wtTable.getFirstVisibleRow()).toEqual(expectedFirstVisibleRow);
    expect(wt.wtTable.getLastVisibleRow()).toBeAroundValue(17);

    wt.selections.getCell().clear();
    expect(wt.wtTable.getFirstVisibleRow()).toEqual(expectedFirstVisibleRow);
    expect(wt.wtTable.getLastVisibleRow()).toBeAroundValue(17);
  });

  it('should clear a selection that has more than one cell', () => {
    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController(),
    });

    wt.draw();

    wt.selections.getCell().add(new Walkontable.CellCoords(0, 0));
    wt.selections.getCell().add(new Walkontable.CellCoords(0, 1));
    wt.selections.getCell().clear();

    expect(wt.selections.getCell().cellRange).toEqual(null);
  });

  it('should highlight cells in selected row & column', () => {
    spec().$wrapper.width(300);

    const customSelection = createSelection({
      highlightRowClassName: 'highlightRow',
      highlightColumnClassName: 'highlightColumn'
    });

    customSelection.add(new Walkontable.CellCoords(0, 0));
    customSelection.add(new Walkontable.CellCoords(0, 1));

    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController({
        custom: [customSelection],
      }),
    });

    wt.draw();

    expect(spec().$table.find('.highlightRow').length).toEqual(2);
    expect(spec().$table.find('.highlightColumn').length).toEqual((wt.wtTable.getRenderedRowsCount() * 2) - 2);
  });

  it('should highlight cells in selected row & column, when same class is shared between 2 selection definitions', () => {
    spec().$wrapper.width(300);

    const customSelection1 = createSelection({
      highlightRowClassName: 'highlightRow',
      highlightColumnClassName: 'highlightColumn'
    });

    customSelection1.add(new Walkontable.CellCoords(0, 0));

    const customSelection2 = createSelection({
      highlightRowClassName: 'highlightRow',
      highlightColumnClassName: 'highlightColumn'
    });

    customSelection2.add(new Walkontable.CellCoords(0, 0));

    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController({
        custom: [customSelection1, customSelection2],
      }),
    });

    wt.draw();

    expect(spec().$table.find('.highlightRow').length).toEqual(3);
    expect(spec().$table.find('.highlightColumn').length).toEqual(wt.wtTable.getRenderedRowsCount() - 1);
  });

  it('should remove highlight when selection is deselected', () => {
    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController({
        current: createSelection({
          highlightRowClassName: 'highlightRow',
          highlightColumnClassName: 'highlightColumn'
        }),
      }),
    });

    wt.draw();

    wt.selections.getCell().add(new Walkontable.CellCoords(0, 0));
    wt.selections.getCell().add(new Walkontable.CellCoords(0, 1));
    wt.draw();

    wt.selections.getCell().clear();
    wt.draw();

    expect(spec().$table.find('.highlightRow').length).toEqual(0);
    expect(spec().$table.find('.highlightColumn').length).toEqual(0);
  });

  it('should add/remove appropriate class to the row/column headers of selected cells', () => {
    spec().$wrapper.width(300);

    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      rowHeaders: [function(row, TH) {
        TH.innerHTML = row + 1;
      }],
      columnHeaders: [function(row, TH) {
        TH.innerHTML = row + 1;
      }],
      selections: createSelectionController({
        current: createSelection({
          highlightRowClassName: 'highlightRow',
          highlightColumnClassName: 'highlightColumn',
          border: {},
        }),
      }),
    });

    wt.draw();

    wt.selections.getCell().add(new Walkontable.CellCoords(1, 1));
    wt.selections.getCell().add(new Walkontable.CellCoords(2, 2));
    wt.draw();

    // left side:
    // -2 -> because one row is partially visible

    // right side:
    // *2 -> because there are 2 columns selected
    // +2 -> because there are the headers
    // -4 -> because 4 cells are selected = there are overlapping highlightRow class
    expect(spec().$table.find('.highlightRow').length)
      .toEqual((wt.wtViewport.columnsVisibleCalculator.count * 2) + 2 - 4);
    expect(spec().$table.find('.highlightColumn').length - 2)
      .toEqual((wt.wtViewport.rowsVisibleCalculator.count * 2) + 2 - 4);
    expect(spec().$table.find('.highlightColumn').length).toEqual(14);
    expect(getTableTopClone().find('.highlightColumn').length).toEqual(2);
    expect(getTableTopClone().find('.highlightRow').length).toEqual(0);
    expect(getTableInlineStartClone().find('.highlightColumn').length).toEqual(0);
    expect(getTableInlineStartClone().find('.highlightRow').length).toEqual(2);

    const $colHeaders = spec().$table.find('thead tr:first-child th');
    const $rowHeaders = spec().$table.find('tbody tr th:first-child');

    expect($colHeaders.eq(2).hasClass('highlightColumn')).toBe(true);
    expect($colHeaders.eq(3).hasClass('highlightColumn')).toBe(true);

    expect($rowHeaders.eq(1).hasClass('highlightRow')).toBe(true);
    expect($rowHeaders.eq(2).hasClass('highlightRow')).toBe(true);

    wt.selections.getCell().clear();
    wt.draw();

    expect(spec().$table.find('.highlightRow').length).toEqual(0);
    expect(spec().$table.find('.highlightColumn').length).toEqual(0);
    expect(getTableTopClone().find('.highlightColumn').length).toEqual(0);
    expect(getTableTopClone().find('.highlightRow').length).toEqual(0);
    expect(getTableInlineStartClone().find('.highlightColumn').length).toEqual(0);
    expect(getTableInlineStartClone().find('.highlightRow').length).toEqual(0);
  });

  it('should add/remove header classes only to the row/column headers closest to the cells when the ' +
     '"highlightOnlyClosestHeader" option is used', function() {
    spec().$wrapper.width(300).height(300);

    this.data = createSpreadsheetData(20, 10);

    const wt = walkontable({
      data: getData,
      totalRows: getTotalRows,
      totalColumns: getTotalColumns,
      selections: createSelectionController({
        activeHeader: createSelection({
          highlightHeaderClassName: 'active_highlight',
          highlightOnlyClosestHeader: true,
        }),
        header: createSelection({
          highlightHeaderClassName: 'highlight',
          highlightOnlyClosestHeader: true,
        }),
      }),
      columnHeaders: [
        (col, TH) => {
          TH.innerHTML = `L1: ${col + 1}`;
        },
        (col, TH) => {
          TH.innerHTML = `L2: ${col + 1}`;
        },
        (col, TH) => {
          TH.innerHTML = `L3: ${col + 1}`;
        },
      ],
      rowHeaders: [
        (row, TH) => {
          TH.innerHTML = `L1: ${row + 1}`;
        },
        (row, TH) => {
          TH.innerHTML = `L2: ${row + 1}`;
        },
        (row, TH) => {
          TH.innerHTML = `L3: ${row + 1}`;
        },
      ],
    });

    wt.selections.getHeader().add(new Walkontable.CellCoords(0, 0));
    wt.selections.getHeader().add(new Walkontable.CellCoords(1, 1));
    wt.selections.getActiveHeader().add(new Walkontable.CellCoords(1, 1));
    wt.selections.getActiveHeader().add(new Walkontable.CellCoords(2, 2));

    wt.draw();

    // Row headers
    expect(spec().$table.find('tbody tr:nth(0)').get(0).outerHTML).toMatchHTML(`
      <tr>
        <th class="">L1: 1</th>
        <th class="">L2: 1</th>
        <th class="highlight">L3: 1</th>
        <td class="">A1</td>
        <td class="">B1</td>
        <td class="">C1</td>
      </tr>
      `);
    expect(spec().$table.find('tbody tr:nth(1)').get(0).outerHTML).toMatchHTML(`
      <tr>
        <th class="">L1: 2</th>
        <th class="">L2: 2</th>
        <th class="highlight active_highlight">L3: 2</th>
        <td class="">A2</td>
        <td class="">B2</td>
        <td class="">C2</td>
      </tr>
      `);
    expect(spec().$table.find('tbody tr:nth(2)').get(0).outerHTML).toMatchHTML(`
      <tr>
        <th class="">L1: 3</th>
        <th class="">L2: 3</th>
        <th class="active_highlight">L3: 3</th>
        <td class="">A3</td>
        <td class="">B3</td>
        <td class="">C3</td>
      </tr>
      `);
    expect(spec().$table.find('tbody tr:nth(3)').get(0).outerHTML).toMatchHTML(`
      <tr>
        <th class="">L1: 4</th>
        <th class="">L2: 4</th>
        <th class="">L3: 4</th>
        <td class="">A4</td>
        <td class="">B4</td>
        <td class="">C4</td>
      </tr>
      `);
    // Column headers
    expect(spec().$table.find('thead tr:nth(0)').get(0).outerHTML).toMatchHTML(`
      <tr>
        <th class="">L1: -2</th>
        <th class="">L1: -1</th>
        <th class="">L1: 0</th>
        <th class="">L1: 1</th>
        <th class="">L1: 2</th>
        <th class="">L1: 3</th>
      </tr>
      `);
    expect(spec().$table.find('thead tr:nth(1)').get(0).outerHTML).toMatchHTML(`
      <tr>
        <th class="">L2: -2</th>
        <th class="">L2: -1</th>
        <th class="">L2: 0</th>
        <th class="">L2: 1</th>
        <th class="">L2: 2</th>
        <th class="">L2: 3</th>
      </tr>
      `);
    expect(spec().$table.find('thead tr:nth(2)').get(0).outerHTML).toMatchHTML(`
      <tr>
        <th class="">L3: -2</th>
        <th class="">L3: -1</th>
        <th class="">L3: 0</th>
        <th class="highlight">L3: 1</th>
        <th class="highlight active_highlight">L3: 2</th>
        <th class="active_highlight">L3: 3</th>
      </tr>
      `);
  });

  describe('replace', () => {
    it('should replace range from property and return true', () => {
      const wt = walkontable({
        data: getData,
        totalRows: getTotalRows,
        totalColumns: getTotalColumns,
        selections: createSelectionController(),
      });

      wt.selections.getCell().add(new Walkontable.CellCoords(1, 1));
      wt.selections.getCell().add(new Walkontable.CellCoords(3, 3));

      const result = wt.selections.getCell()
        .replace(new Walkontable.CellCoords(3, 3), new Walkontable.CellCoords(4, 4));

      expect(result).toBe(true);
      expect(wt.selections.getCell().getCorners()).toEqual([1, 1, 4, 4]);
    });
  });
});
