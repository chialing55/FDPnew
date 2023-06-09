import GlobalMeta from '../globalMeta';
import { registerAllCellTypes, getCellType } from '../../../../cellTypes';

registerAllCellTypes();

describe('GlobalMeta', () => {
  it('should construct class with prepared meta object', () => {
    const meta = new GlobalMeta();

    expect(meta.metaCtor).toBeFunction();
    // Check if the meta object has one of many props from meta schema
    expect(meta.meta).toHaveProperty('activeHeaderClassName', 'ht__active_highlight');
    // Check if the meta object is a prototype
    expect(meta.meta).toHaveProperty('constructor', meta.metaCtor);
  });

  it('should set Handsontable instance in the meta prototype', () => {
    const hotMock = {};
    const meta = new GlobalMeta(hotMock);

    expect(meta.meta.instance).toBe(hotMock);
  });

  describe('getMetaConstructor()', () => {
    it('should returns meta constructor', () => {
      const meta = new GlobalMeta();

      expect(meta.getMetaConstructor()).toBe(meta.metaCtor);
    });
  });

  describe('getMeta()', () => {
    it('should returns meta object (prototype object)', () => {
      const meta = new GlobalMeta();

      expect(meta.getMeta()).toBe(meta.meta);
    });
  });

  describe('updateMeta()', () => {
    it('should update meta with custom settings', () => {
      const meta = new GlobalMeta();
      const settings = {
        activeHeaderClassName: 'foo',
        hiddenColumns: true,
        nestedHeaders: { row: 1, col: 2 },
        _myCustomKey: 'bar',
      };

      expect(meta.getMeta()).toHaveProperty('outsideClickDeselects', true);

      meta.updateMeta(settings);

      expect(meta.getMeta()).toHaveProperty('activeHeaderClassName', 'foo');
      expect(meta.getMeta()).toHaveProperty('hiddenColumns', true);
      expect(meta.getMeta()).toHaveProperty('nestedHeaders', settings.nestedHeaders);
      expect(meta.getMeta()).toHaveProperty('_myCustomKey', 'bar');
      expect(meta.getMeta()).toHaveProperty('outsideClickDeselects', true);
    });

    it('should merge "type" property as an object to meta settings', () => {
      const meta = new GlobalMeta();
      const settings = {
        type: {
          _customKey: true,
          copyable: true,
          editor: 'foo',
          renderer: 'bar',
          validator: 'baz',
        }
      };

      meta.updateMeta(settings);

      expect(meta.getMeta()).toHaveProperty('type', settings.type);
      expect(meta.getMeta()).toHaveProperty('_customKey', true);
      expect(meta.getMeta()).toHaveProperty('copyable', true);
      expect(meta.getMeta()).toHaveProperty('editor', 'foo');
      expect(meta.getMeta()).toHaveProperty('renderer', 'bar');
      expect(meta.getMeta()).toHaveProperty('validator', 'baz');
    });

    it('should expand "type" property as string to meta settings', () => {
      const meta = new GlobalMeta();
      const settings = {
        type: 'text',
      };

      meta.updateMeta(settings);

      expect(meta.getMeta()).toHaveProperty('type', 'text');
      expect(meta.getMeta().editor).toBe(getCellType('text').editor);
      expect(meta.getMeta().renderer).toBe(getCellType('text').renderer);
    });

    it('should expand "type" property as object but without overwriting already defined properties', () => {
      const meta = new GlobalMeta();
      const settings = {
        type: {
          copyPaste: true,
          _test: 'foo',
        },
      };

      meta.getMeta().copyPaste = false;
      meta.getMeta()._test = 'bar';

      meta.updateMeta(settings);

      expect(meta.getMeta()).toHaveProperty('copyPaste', true);
      expect(meta.getMeta()).toHaveProperty('_test', 'foo');

      // "copyPaste" from settings has priority over the props defined in "type" prop.
      settings.copyPaste = false;
      meta.updateMeta(settings);

      expect(meta.getMeta()).toHaveProperty('copyPaste', false);
      expect(meta.getMeta()).toHaveProperty('_test', 'foo');
    });

    it('should expand "type" property as string but without overwriting already defined properties ' +
       '(updates from simple cell type to more complex)', () => {
      const meta = new GlobalMeta();
      const myRenderer = (hot, TD) => { TD.innerText = '*'; };
      const myRenderer2 = (hot, TD) => { TD.innerText = '*'; };
      const myValidator = (value, callback) => callback(true);
      const settings = {
        type: 'text',
        renderer: myRenderer,
        validator: myValidator,
      };

      meta.getMeta().copyable = true;
      meta.getMeta()._test = 'bar';

      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('text').editor);
      expect(meta.getMeta().renderer).toBe(myRenderer);
      expect(meta.getMeta().validator).toBe(myValidator);
      expect(meta.getMeta()).toHaveProperty('copyPaste', true);
      expect(meta.getMeta()).toHaveProperty('_test', 'bar');

      settings.renderer = myRenderer2;
      settings.copyPaste = false;
      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('text').editor);
      expect(meta.getMeta().renderer).toBe(myRenderer2);
      expect(meta.getMeta().validator).toBe(myValidator);
      expect(meta.getMeta()).toHaveProperty('copyPaste', false);
      expect(meta.getMeta()).toHaveProperty('_test', 'bar');

      settings.type = 'numeric';
      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('numeric').editor);
      expect(meta.getMeta().renderer).toBe(myRenderer2);
      expect(meta.getMeta().validator).toBe(myValidator);
      expect(meta.getMeta()).toHaveProperty('copyPaste', false);
      expect(meta.getMeta()).toHaveProperty('_test', 'bar');
    });

    it('should expand "type" property as string but without overwriting already defined properties ' +
       '(updates from complex cell type to more simple)', () => {
      const meta = new GlobalMeta();
      const myRenderer = (hot, TD) => { TD.innerText = '*'; };
      const myRenderer2 = (hot, TD) => { TD.innerText = '*'; };
      const myValidator = (value, callback) => callback(true);
      const settings = {
        type: 'numeric',
        renderer: myRenderer,
        validator: myValidator,
      };

      meta.getMeta().copyable = true;
      meta.getMeta()._test = 'bar';

      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('numeric').editor);
      expect(meta.getMeta().renderer).toBe(myRenderer);
      expect(meta.getMeta().validator).toBe(myValidator);
      expect(meta.getMeta()).toHaveProperty('copyPaste', true);
      expect(meta.getMeta()).toHaveProperty('_test', 'bar');

      settings.renderer = myRenderer2;
      settings.copyPaste = false;
      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('numeric').editor);
      expect(meta.getMeta().renderer).toBe(myRenderer2);
      expect(meta.getMeta().validator).toBe(myValidator);
      expect(meta.getMeta()).toHaveProperty('copyPaste', false);
      expect(meta.getMeta()).toHaveProperty('_test', 'bar');

      settings.type = 'text';
      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('text').editor);
      expect(meta.getMeta().renderer).toBe(myRenderer2);
      expect(meta.getMeta().validator).toBe(myValidator);
      expect(meta.getMeta()).toHaveProperty('copyPaste', false);
      expect(meta.getMeta()).toHaveProperty('_test', 'bar');
    });

    it('should be possible to update "type" multiple times', () => {
      const meta = new GlobalMeta();
      const settings = {
        type: 'text',
      };

      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('text').editor);
      expect(meta.getMeta().renderer).toBe(getCellType('text').renderer);
      expect(meta.getMeta().validator).toBeUndefined();

      settings.type = 'autocomplete';
      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('autocomplete').editor);
      expect(meta.getMeta().renderer).toBe(getCellType('autocomplete').renderer);
      expect(meta.getMeta().validator).toBe(getCellType('autocomplete').validator);

      settings.type = 'text';
      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('text').editor);
      expect(meta.getMeta().renderer).toBe(getCellType('text').renderer);
      expect(meta.getMeta().validator).toBe(getCellType('autocomplete').validator);

      settings.type = 'numeric';
      meta.updateMeta(settings);

      expect(meta.getMeta().editor).toBe(getCellType('numeric').editor);
      expect(meta.getMeta().renderer).toBe(getCellType('numeric').renderer);
      expect(meta.getMeta().validator).toBe(getCellType('numeric').validator);
    });
  });
});
