import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');
const scriptSource = fs.readFileSync(path.join(projectRoot, 'script.js'), 'utf8');

const STRATEGIES_DATA = JSON.parse(
    execFileSync(
        'php',
        ['-r', "require 'vendor/autoload.php'; echo json_encode(FizzBuzz\\Strategies::getPublicData());"],
        { cwd: projectRoot, encoding: 'utf8' }
    )
);

class MockClassList {
    constructor(el) {
        this.el = el;
        this.set = new Set();
    }
    add(...names) {
        for (const n of names) {
            if (n) this.set.add(n);
        }
    }
    remove(...names) {
        for (const n of names) {
            this.set.delete(n);
        }
    }
    toggle(name, force) {
        if (force === undefined) {
            if (this.set.has(name)) {
                this.set.delete(name);
                return false;
            }
            this.set.add(name);
            return true;
        }
        if (force) {
            this.set.add(name);
            return true;
        }
        this.set.delete(name);
        return false;
    }
    contains(name) {
        return this.set.has(name);
    }
}

class MockTextNode {
    constructor(text) {
        this.nodeType = 3;
        this._text = String(text);
        this.parentNode = null;
    }
    get textContent() {
        return this._text;
    }
    set textContent(v) {
        this._text = String(v);
    }
}

class MockElement {
    constructor(tagName, doc) {
        this.nodeType = 1;
        this.tagName = tagName.toUpperCase();
        this._doc = doc;
        this.id = '';
        this.classList = new MockClassList(this);
        this.children = [];
        this.childNodes = [];
        this.parentNode = null;
        this.attributes = {};
        this.style = {};
        this.disabled = false;
        this.value = '';
        this.scrollTop = 0;
        this._listeners = {};
        this._innerHTML = '';
        this.dataset = new Proxy(
            {},
            {
                get: (_target, prop) => this.attributes['data-' + String(prop)],
                set: (_target, prop, val) => {
                    this.attributes['data-' + String(prop)] = String(val);
                    return true;
                },
            }
        );
    }

    set className(val) {
        this.classList.set = new Set(String(val).split(/\s+/).filter(Boolean));
    }

    get className() {
        return Array.from(this.classList.set).join(' ');
    }

    setAttribute(name, val) {
        this.attributes[name] = String(val);
        if (name === 'id') this.id = String(val);
        if (name === 'class') this.className = String(val);
    }

    getAttribute(name) {
        return Object.prototype.hasOwnProperty.call(this.attributes, name) ? this.attributes[name] : null;
    }

    appendChild(node) {
        if (node.parentNode) {
            node.parentNode.removeChild(node);
        }
        node.parentNode = this;
        this.childNodes.push(node);
        if (node.nodeType === 1) {
            this.children.push(node);
        }
        return node;
    }

    removeChild(node) {
        this.childNodes = this.childNodes.filter(n => n !== node);
        this.children = this.children.filter(n => n !== node);
        node.parentNode = null;
        return node;
    }

    remove() {
        if (this.parentNode) {
            this.parentNode.removeChild(this);
        }
    }

    get textContent() {
        return this.childNodes.map(n => n.textContent).join('');
    }

    set textContent(val) {
        this.childNodes = [];
        this.children = [];
        this._innerHTML = '';
        if (val !== '' && val !== null && val !== undefined) {
            this.appendChild(new MockTextNode(val));
        }
    }

    get innerHTML() {
        if (this._innerHTML) return this._innerHTML;
        return this.childNodes
            .map(n => (n.nodeType === 3 ? n.textContent : `<${n.tagName.toLowerCase()} class="${n.className}">${n.innerHTML}</${n.tagName.toLowerCase()}>`))
            .join('');
    }

    set innerHTML(val) {
        this.childNodes = [];
        this.children = [];
        this._innerHTML = String(val);
    }

    matches(selector) {
        const parts = selector.split(',').map(s => s.trim()).filter(Boolean);
        return parts.some(part => {
            if (part.startsWith('#')) return this.id === part.slice(1);
            if (part.startsWith('.')) return this.classList.contains(part.slice(1));
            return this.tagName === part.toUpperCase();
        });
    }

    closest(selector) {
        let cur = this;
        while (cur) {
            if (cur.nodeType === 1 && cur.matches(selector)) return cur;
            cur = cur.parentNode;
        }
        return null;
    }

    querySelector(selector) {
        return this.querySelectorAll(selector)[0] || null;
    }

    querySelectorAll(selector) {
        const results = [];
        const walk = (node) => {
            for (const child of node.children) {
                if (child.matches(selector)) results.push(child);
                walk(child);
            }
        };
        walk(this);
        return results;
    }

    addEventListener(type, fn) {
        if (!this._listeners[type]) this._listeners[type] = [];
        this._listeners[type].push(fn);
    }

    dispatchEvent(event) {
        if (!event.target) event.target = this;
        const handlers = this._listeners[event.type] || [];
        for (const fn of handlers) {
            fn(event);
        }
    }
}

function createHarness(fetchImpl) {
    const docListeners = {};
    const root = new MockElement('body', null);
    const idMap = new Map();

    const doc = {
        createElement(tag) {
            return new MockElement(tag, doc);
        },
        createTextNode(text) {
            return new MockTextNode(text);
        },
        getElementById(id) {
            return idMap.get(id) || null;
        },
        querySelector(selector) {
            return root.querySelector(selector);
        },
        querySelectorAll(selector) {
            return root.querySelectorAll(selector);
        },
        addEventListener(type, fn) {
            if (!docListeners[type]) docListeners[type] = [];
            docListeners[type].push(fn);
        },
        dispatchEvent(event) {
            for (const fn of docListeners[event.type] || []) {
                fn(event);
            }
        },
    };

    function el(tag, attrs = {}, parent = root) {
        const node = new MockElement(tag, doc);
        for (const [k, v] of Object.entries(attrs)) {
            node.setAttribute(k, v);
            if (k === 'id') idMap.set(v, node);
        }
        if (parent) parent.appendChild(node);
        return node;
    }

    // Build DOM structure matching index.php
    const toggleBtn = el('button', { class: 'mobile-menu-toggle', 'aria-expanded': 'false' });
    el('button', { class: 'mobile-backdrop' });

    const aside = el('aside', { id: 'strategySidebar' });
    el('button', { class: 'benchmark-btn', id: 'benchmarkBtn' }, aside).innerHTML = 'Benchmark';
    const sortControls = el('div', { class: 'sort-controls', id: 'sortControls' }, aside);
    sortControls.style.display = 'none';
    for (const [sortKey, label] of [
        ['avg', 'Avg'],
        ['median', 'Med'],
        ['min', 'Min'],
        ['stddev', 'Std'],
        ['memory', 'Mem'],
        ['overall', 'All'],
    ]) {
        const b = el('button', { class: sortKey === 'median' ? 'sort-btn active' : 'sort-btn', 'data-sort': sortKey }, sortControls);
        b.textContent = label;
    }

    const versionList = el('ul', { class: 'version-list', id: 'versionList' }, aside);
    for (const [key, data] of Object.entries(STRATEGIES_DATA)) {
        const li = el('li', { 'data-key': key }, versionList);
        const btn = el('button', { class: 'version-item', 'data-key': key, id: 'item-' + key }, li);
        const nameEl = el('span', { class: 'v-name' }, btn);
        nameEl.textContent = data.name;
        const timeEl = el('span', { class: 'v-time', id: 'time-' + key }, btn);
        timeEl.textContent = '';
    }

    el('h2', { id: 'displayTitle' });
    el('span', { class: 'complexity-badge', id: 'displayComplexity' });
    el('p', { id: 'displayDesc' });

    const limitSelect = el('select', { id: 'limitSelect' });
    limitSelect.value = '1000';

    el('button', { id: 'prevStrategyBtn' });
    el('button', { id: 'nextStrategyBtn' });
    el('button', { class: 'nav-btn mobile-benchmark-btn', id: 'mobileBenchmarkBtn' }).innerHTML = 'Run Benchmark (Compare All)';
    el('div', { id: 'codeDisplay' });
    el('div', { id: 'outputDisplay' });
    el('span', { id: 'executionTime' });
    el('button', { class: 'run-btn', id: 'runCodeBtn' }).innerHTML = 'Run';

    const sandbox = {
        STRATEGIES_DATA,
        ALLOWED_LIMITS: [100, 500, 1000, 2000],
        DEFAULT_LIMIT: 1000,
        document: doc,
        window: { innerWidth: 1200 },
        FormData,
        AbortController,
        performance,
        setTimeout: () => 0,
        fetch: fetchImpl || (async () => {
            throw new Error('fetch not stubbed');
        }),
    };

    vm.createContext(sandbox);
    vm.runInContext(scriptSource, sandbox);

    return { sandbox, doc, idMap, toggleBtn, aside, versionList, limitSelect, sortControls };
}

test('highlightCode escapes HTML, keeps apostrophes in comments safe, and applies token spans without leaking placeholders', () => {
    const { sandbox } = createHarness();
    const sample = [
        "// If output is empty, it's just the number",
        'if ($i < 10 && $i > 0) {',
        '    $msg = "// not a comment & <b>tag</b>";',
        '    return str_repeat($msg, 1);',
        '}',
    ].join('\n');

    const html = sandbox.highlightCode(sample);

    assert.ok(!html.includes('___TOK'), 'Should not leak placeholder tokens');
    assert.ok(html.includes('&lt;b&gt;tag&lt;/b&gt;'), 'Should HTML-escape tags inside strings');
    assert.ok(html.includes('&amp;&amp;'), 'Should HTML-escape ampersands');
    assert.ok(html.includes('<span class="com">// If output is empty, it\'s just the number</span>'), 'Should isolate comment with apostrophe');
    assert.ok(html.includes('<span class="str">"// not a comment &amp; &lt;b&gt;tag&lt;/b&gt;"</span>'), 'Should not treat // inside string as comment');
    assert.ok(html.includes('<span class="kwd">if</span>'));
    assert.ok(html.includes('<span class="kwd">return</span>'));
    assert.ok(html.includes('<span class="func">str_repeat</span>'));
    assert.ok(html.includes('<span class="var">$msg</span>'));
    assert.ok(html.includes('<span class="num">10</span>'));
});

test('selectVersion updates UI metadata, highlights code, marks active sidebar item, and closes open mobile drawer', () => {
    const { sandbox, doc, aside, toggleBtn } = createHarness();

    // Open mobile drawer first
    sandbox.toggleMobileMenu();
    assert.equal(aside.classList.contains('mobile-open'), true);
    assert.equal(toggleBtn.getAttribute('aria-expanded'), 'true');

    // Simulate delegated click on child .v-name inside #item-match
    const matchBtn = doc.getElementById('item-match');
    const nameSpan = matchBtn.querySelector('.v-name');
    doc.getElementById('versionList').dispatchEvent({ type: 'click', target: nameSpan });

    assert.equal(doc.getElementById('displayTitle').textContent, STRATEGIES_DATA.match.name);
    assert.equal(doc.getElementById('displayDesc').textContent, STRATEGIES_DATA.match.description);
    assert.equal(doc.getElementById('displayComplexity').textContent, STRATEGIES_DATA.match.complexity);
    assert.equal(matchBtn.classList.contains('active'), true);
    assert.equal(doc.getElementById('item-original').classList.contains('active'), false);
    assert.equal(aside.classList.contains('mobile-open'), false);
    assert.equal(toggleBtn.getAttribute('aria-expanded'), 'false');
});

test('runCode renders untrusted output literally via textContent, includes memoryKb, and aborts stale responses on version switch', async () => {
    let pendingRequests = [];
    const { sandbox, doc } = createHarness((_url, options) => {
        return new Promise((resolve, reject) => {
            const entry = { signal: options.signal, resolve, reject };
            options.signal.addEventListener('abort', () => {
                const err = new Error('Aborted');
                err.name = 'AbortError';
                reject(err);
            });
            pendingRequests.push(entry);
        });
    });

    // 1. XSS safety + memoryKb display
    const run1 = sandbox.runCode();
    assert.equal(pendingRequests.length, 1);
    pendingRequests[0].resolve({
        ok: true,
        status: 200,
        json: async () => ({
            output: '<img src=x onerror=alert(1)>\nFizz\n',
            executionTime: 0.1234,
            memoryKb: 12.5,
            limit: 1000,
        }),
    });
    await run1;

    const outputEl = doc.getElementById('outputDisplay');
    assert.equal(outputEl.textContent, '<img src=x onerror=alert(1)>\nFizz\n');
    assert.equal(outputEl.children.length, 0, 'Output should be plain text node without HTML elements');
    assert.ok(doc.getElementById('executionTime').textContent.includes('Mem: 12.5KB'));

    // 2. Race protection: start a run on original, switch to classic before it resolves
    pendingRequests = [];
    const staleRun = sandbox.runCode();
    assert.equal(pendingRequests[0].signal.aborted, false);

    sandbox.selectVersion('classic');
    assert.equal(pendingRequests[0].signal.aborted, true, 'Switching version should abort in-flight run');
    await staleRun;

    assert.equal(doc.getElementById('outputDisplay').textContent, 'Ready to execute...');
});

test('benchmarkAll locks buttons, badges tied Overall Winners, and sortResults handles all 6 sort modes', async () => {
    let resolveBench;
    const { sandbox, doc, versionList, limitSelect, sortControls } = createHarness(() => {
        return new Promise((resolve) => {
            resolveBench = resolve;
        });
    });

    const benchBtn = doc.getElementById('benchmarkBtn');
    const mobileBenchBtn = doc.getElementById('mobileBenchmarkBtn');

    const benchPromise = sandbox.benchmarkAll();
    // Second concurrent call should be ignored
    const secondCall = sandbox.benchmarkAll();

    assert.equal(benchBtn.disabled, true);
    assert.equal(mobileBenchBtn.disabled, true);
    assert.equal(limitSelect.disabled, true);

    const keys = Object.keys(STRATEGIES_DATA);
    const mockResults = {};
    keys.forEach((key, idx) => {
        // Make 'classic' and 'clean' tie at overallRank === 1, and give 'precomputed' lowest memory
        let overallRank = idx + 3;
        let overallScore = (idx + 3) * 5;
        if (key === 'classic' || key === 'clean') {
            overallRank = 1;
            overallScore = 6;
        }
        mockResults[key] = {
            name: STRATEGIES_DATA[key].name,
            time: (idx + 1) * 0.05,
            median: key === 'precomputed' ? 0.01 : (idx + 1) * 0.04,
            p95: (idx + 1) * 0.06,
            min: key === 'goto_loop' ? 0.005 : (idx + 1) * 0.03,
            max: (idx + 1) * 0.09,
            stddev: key === 'match' ? 0.001 : (idx + 1) * 0.01,
            memoryKb: key === 'precomputed' ? 0.5 : (keys.length - idx) * 4,
            runs: 300,
            limit: 1000,
            rank: idx + 1,
            overallScore,
            overallRank,
        };
    });

    resolveBench({
        ok: true,
        status: 200,
        json: async () => ({ results: mockResults, limit: 1000 }),
    });
    await Promise.all([benchPromise, secondCall]);

    assert.equal(benchBtn.disabled, false);
    assert.equal(mobileBenchBtn.disabled, false);
    assert.equal(limitSelect.disabled, false);
    assert.equal(sortControls.style.display, 'flex');

    const winnerBadges = doc.querySelectorAll('.v-winner-badge');
    assert.equal(winnerBadges.length, 2, 'Should badge all strategies tied at overallRank === 1');

    // Verify sorting by each of the 6 sort modes
    const expectedFirstBySort = {
        avg: ['original'],
        median: ['precomputed'],
        min: ['goto_loop'],
        stddev: ['match'],
        memory: ['precomputed'],
        overall: ['classic', 'clean'],
    };

    for (const [mode, allowedFirstKeys] of Object.entries(expectedFirstBySort)) {
        sandbox.sortResults(mode);
        assert.ok(
            allowedFirstKeys.includes(versionList.children[0].dataset.key),
            `Expected one of [${allowedFirstKeys.join(', ')}] first when sorting by ${mode}, got ${versionList.children[0].dataset.key}`
        );
        const highlights = doc.querySelectorAll('.stat-highlight');
        assert.equal(highlights.length, keys.length, `Expected .stat-highlight on all ${keys.length} items for ${mode}`);
        assert.ok(highlights[0].classList.contains('stat-' + mode));
    }
});

test('limit changes reset state, mobile navigation wraps, and Escape closes mobile menu', () => {
    const { sandbox, doc, aside, toggleBtn, limitSelect } = createHarness();

    // Put a benchmark result in place first
    sandbox.processBenchmarkResults(
        {
            original: {
                name: STRATEGIES_DATA.original.name,
                time: 0.1,
                median: 0.1,
                p95: 0.2,
                min: 0.05,
                max: 0.3,
                stddev: 0.02,
                memoryKb: 10,
                runs: 300,
                limit: 1000,
                rank: 1,
                overallScore: 5,
                overallRank: 1,
            },
        },
        1000
    );
    assert.equal(doc.getElementById('sortControls').style.display, 'flex');

    // Change limit -> clears benchmark UI
    limitSelect.value = '2000';
    limitSelect.dispatchEvent({ type: 'change', target: limitSelect });
    assert.equal(doc.getElementById('sortControls').style.display, 'none');
    assert.equal(doc.getElementById('time-original').textContent, '');
    assert.ok(doc.getElementById('outputDisplay').textContent.includes('N=2000'));

    // Prev from first strategy wraps to last strategy; Next wraps back to first
    sandbox.selectVersion('original');
    sandbox.prevStrategy();
    assert.equal(doc.getElementById('displayTitle').textContent, STRATEGIES_DATA.math_division.name);
    sandbox.nextStrategy();
    assert.equal(doc.getElementById('displayTitle').textContent, STRATEGIES_DATA.original.name);

    // Escape closes open mobile menu and updates aria-expanded
    sandbox.toggleMobileMenu();
    assert.equal(aside.classList.contains('mobile-open'), true);
    assert.equal(toggleBtn.getAttribute('aria-expanded'), 'true');
    doc.dispatchEvent({ type: 'keydown', key: 'Escape' });
    assert.equal(aside.classList.contains('mobile-open'), false);
    assert.equal(toggleBtn.getAttribute('aria-expanded'), 'false');
});
