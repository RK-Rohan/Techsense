const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('public/js/list-sort.js', 'utf8');

function setup() {
    const handlers = {}, data = new Map();
    const $ = element => ({on: (name, fn) => {handlers[name] = fn;}, closest: () => ({length: element.inForm ? 1 : 0})});
    $.fn = {dataTable: {defaults: {aaSorting: [[0, 'asc']]}}};
    $.extend = (deep, target, value) => JSON.parse(JSON.stringify(value));
    const APP = {USER_ID: '1', BUSINESS_ID: '10'};
    const window = {APP, location: {pathname: '/sells'}};
    vm.runInNewContext(source, {jQuery: $, document: {}, APP, window, localStorage: {
        getItem: key => data.get(key) || null, setItem: (key, value) => data.set(key, value)
    }});
    const settings = () => ({nTable: {id: 'sell_table'}, aoColumns: [{mData:'date', bSortable:true}, {mData:'amount', bSortable:true}], oFeatures:{bSort:true}, oInit:{aaSorting:[[0,'desc']]}, aaSorting:[[0,'desc']]});
    return {handlers, data, APP, window, settings};
}

test('remembers both directions and isolates users and businesses', () => {
    const env = setup();
    let table = env.settings();
    env.handlers['preInit.dt']({}, table);
    table.aaSorting = [[1, 'asc']];
    env.handlers['order.dt']({}, table);
    table = env.settings();
    env.handlers['preInit.dt']({}, table);
    assert.equal(JSON.stringify(table.aaSorting), '[[1,"asc"]]');
    table.aaSorting = [[1, 'desc']];
    env.handlers['order.dt']({}, table);
    let restored = env.settings();
    env.handlers['preInit.dt']({}, restored);
    assert.equal(JSON.stringify(restored.aaSorting), '[[1,"desc"]]');
    for (const field of ['USER_ID', 'BUSINESS_ID']) {
        const before = env.APP[field];
        env.APP[field] = 'different';
        restored = env.settings();
        restored.oLoadedState = {};
        restored.aaSorting = [[1,'asc']];
        env.handlers['preInit.dt']({}, restored);
        assert.equal(JSON.stringify(restored.aaSorting), '[[0,"desc"]]');
        env.APP[field] = before;
    }
});

test('excludes forms and rejects invalid saved column indices', () => {
    const env = setup();
    let table = env.settings();
    table.nTable.inForm = true;
    env.handlers['preInit.dt']({}, table);
    assert.equal(table._userSortKey, undefined);
    table = env.settings();
    env.handlers['preInit.dt']({}, table);
    env.data.set(table._userSortKey, '[[999,"asc"]]');
    table = env.settings();
    env.handlers['preInit.dt']({}, table);
    assert.equal(JSON.stringify(table.aaSorting), '[[0,"desc"]]');
});
