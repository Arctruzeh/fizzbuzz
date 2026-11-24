/**
 * FizzBuzz Benchmark Application - Client-Side JavaScript
 *
 * Handles UI interactions, code execution, benchmarking, and result visualization.
 */

const strategies = STRATEGIES_DATA;
let currentVersion = null;
let currentLimit = typeof DEFAULT_LIMIT === 'number' ? DEFAULT_LIMIT : 1000;
let benchmarkResults = null;
let activeSortMethod = 'median';
let runAbortController = null;
let runRequestId = 0;
let isBenchmarking = false;

const RUN_BTN_HTML =
    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">' +
    '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />' +
    '</svg> Run';

function getOrdinal(n) {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
}

function selectVersion(key) {
    if (!strategies[key]) return;

    if (runAbortController) {
        runAbortController.abort();
        runAbortController = null;
    }
    runRequestId++;

    currentVersion = key;
    const s = strategies[key];

    document.getElementById('displayTitle').textContent = s.name;
    document.getElementById('displayDesc').textContent = s.description;

    const complexityEl = document.getElementById('displayComplexity');
    if (complexityEl) {
        complexityEl.textContent = s.complexity || '';
        complexityEl.style.display = s.complexity ? 'inline-block' : 'none';
    }

    document.getElementById('codeDisplay').innerHTML = highlightCode(s.code);
    document.getElementById('outputDisplay').textContent = 'Ready to execute...';
    document.getElementById('executionTime').textContent = '';

    const runBtn = document.getElementById('runCodeBtn');
    runBtn.disabled = false;
    runBtn.innerHTML = RUN_BTN_HTML;
    runBtn.classList.remove('loading');

    document.querySelectorAll('.version-item').forEach(el => el.classList.remove('active'));
    const activeItem = document.getElementById('item-' + key);
    if (activeItem) {
        activeItem.classList.add('active');
    }

    const aside = document.querySelector('aside');
    if (aside && aside.classList.contains('mobile-open')) {
        toggleMobileMenu();
    }
}

/**
 * Syntax highlighting with single-pass token extraction so comments inside strings
 * and quotes inside comments cannot interfere with each other.
 */
function highlightCode(code) {
    let escaped = code
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');

    const tokens = [];

    // Single pass: match line comments, single-quoted strings, or double-quoted strings
    escaped = escaped.replaceAll(/(\/\/.*$|'(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/gm, function (match) {
        const type = match.startsWith('//') ? 'com' : 'str';
        const index = tokens.length;
        tokens.push({ type, text: match });
        return '___TOK' + index + '___';
    });

    // Highlight keywords
    escaped = escaped.replaceAll(
        /\b(for|foreach|as|if|elseif|else|switch|case|break|default|match|echo|return|function|use|yield|goto|void|int|string)\b/g,
        '<span class="kwd">$1</span>'
    );

    // Highlight built-in functions
    escaped = escaped.replaceAll(
        /\b(array_map|array_walk|range|implode|str_repeat|floor|is_int|isset|defined|define|function_exists)\b/g,
        '<span class="func">$1</span>'
    );

    // Highlight PHP variables
    escaped = escaped.replaceAll(/(\$[a-zA-Z_]\w*)/g, '<span class="var">$1</span>');

    // Highlight standalone numeric literals
    escaped = escaped.replaceAll(/\b(\d+)\b/g, '<span class="num">$1</span>');

    // Restore comment and string tokens
    escaped = escaped.replaceAll(/___TOK(\d+)___/g, function (_match, id) {
        const tok = tokens[Number(id)];
        return '<span class="' + tok.type + '">' + tok.text + '</span>';
    });

    return escaped;
}

async function runCode() {
    if (!currentVersion) return;

    if (runAbortController) {
        runAbortController.abort();
    }
    const controller = new AbortController();
    runAbortController = controller;
    const requestId = ++runRequestId;

    const btn = document.getElementById('runCodeBtn');
    const output = document.getElementById('outputDisplay');
    const timeDisplay = document.getElementById('executionTime');

    btn.disabled = true;
    btn.textContent = 'Running...';
    btn.classList.add('loading');
    output.textContent = 'Calculating...';
    timeDisplay.textContent = '';

    const clientStart = performance.now();

    try {
        const formData = new FormData();
        formData.append('action', 'run');
        formData.append('version', currentVersion);
        formData.append('limit', String(currentLimit));

        const response = await fetch('api.php', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });

        const clientEnd = performance.now();
        const totalTime = clientEnd - clientStart;
        const result = await response.json();

        if (requestId !== runRequestId) return;

        if (!response.ok || result.error) {
            output.textContent = 'Error: ' + (result.error || `HTTP ${response.status}`);
        } else {
            output.textContent = result.output;

            timeDisplay.textContent = '';
            timeDisplay.appendChild(document.createTextNode(`Exec: ${result.executionTime}ms `));
            const sep = document.createElement('span');
            sep.className = 'time-sep';
            sep.textContent = '|';
            timeDisplay.appendChild(sep);
            timeDisplay.appendChild(document.createTextNode(` Total: ${totalTime.toFixed(2)}ms`));
            if (typeof result.memoryKb === 'number') {
                const memSep = document.createElement('span');
                memSep.className = 'time-sep';
                memSep.textContent = ' | ';
                timeDisplay.appendChild(memSep);
                timeDisplay.appendChild(document.createTextNode(`Mem: ${result.memoryKb}KB`));
            }
            timeDisplay.appendChild(document.createTextNode(` (N=${result.limit})`));
        }
    } catch (e) {
        if (e.name === 'AbortError' || requestId !== runRequestId) {
            return;
        }
        output.textContent = 'Error executing code: ' + e.message;
    } finally {
        if (requestId === runRequestId) {
            runAbortController = null;
            btn.disabled = false;
            btn.innerHTML = RUN_BTN_HTML;
            btn.classList.remove('loading');
        }
    }
}

function clearBenchmarkUI() {
    document.querySelectorAll('.v-time').forEach(el => {
        el.textContent = '';
        el.classList.remove('fastest', 'slowest');
    });
    document.querySelectorAll('.v-winner-badge').forEach(el => el.remove());
    document.getElementById('sortControls').style.display = 'none';
    benchmarkResults = null;
}

async function benchmarkAll() {
    if (isBenchmarking) return;
    isBenchmarking = true;

    const benchButtons = Array.from(document.querySelectorAll('.benchmark-btn, .mobile-benchmark-btn'));
    const originalButtonStates = benchButtons.map(b => ({ el: b, html: b.innerHTML }));
    const limitSelect = document.getElementById('limitSelect');

    clearBenchmarkUI();

    benchButtons.forEach(b => {
        b.disabled = true;
        b.textContent = 'Running...';
    });
    if (limitSelect) limitSelect.disabled = true;

    const output = document.getElementById('outputDisplay');
    output.textContent = '';
    const initSpan = document.createElement('span');
    initSpan.className = 'status-warn';
    initSpan.textContent = `Initializing Benchmark Protocol (N=${currentLimit})...`;
    output.appendChild(initSpan);
    output.appendChild(document.createTextNode('\nRunning interleaved warmup + timed rounds across all strategies.'));

    try {
        const formData = new FormData();
        formData.append('action', 'benchmark');
        formData.append('limit', String(currentLimit));

        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!response.ok || data.error) {
            throw new Error(data.error || `HTTP ${response.status}`);
        }

        if (data.results) {
            processBenchmarkResults(data.results, data.limit ?? currentLimit);
        }
    } catch (e) {
        output.textContent = '';
        const errSpan = document.createElement('span');
        errSpan.className = 'status-error';
        errSpan.textContent = 'Benchmark failed: ' + e.message;
        output.appendChild(errSpan);
    } finally {
        isBenchmarking = false;
        originalButtonStates.forEach(({ el, html }) => {
            el.disabled = false;
            el.innerHTML = html;
        });
        if (limitSelect) limitSelect.disabled = false;
    }
}

function createStatSpan(className, text) {
    const span = document.createElement('span');
    span.className = className;
    span.textContent = text;
    return span;
}

function processBenchmarkResults(results, limit) {
    const medians = Object.values(results).map(r => r.median);
    const fastest = Math.min(...medians);
    const slowest = Math.max(...medians);

    for (const [key, result] of Object.entries(results)) {
        const timeEl = document.getElementById('time-' + key);
        const itemEl = document.getElementById('item-' + key);
        if (!timeEl || !itemEl) continue;

        timeEl.textContent = '';

        // Primary headline: median + avg + rank
        timeEl.appendChild(createStatSpan('stat-median', `${result.median}ms`));
        timeEl.appendChild(document.createTextNode(` med (${result.runs}x, N=${limit}) `));
        timeEl.appendChild(createStatSpan('v-rank', getOrdinal(result.rank)));

        // Secondary stats row
        const detailEl = document.createElement('span');
        detailEl.className = 'v-minmax';
        detailEl.appendChild(document.createTextNode('avg: '));
        detailEl.appendChild(createStatSpan('stat-avg', `${result.time}ms`));
        detailEl.appendChild(document.createTextNode(' | min: '));
        detailEl.appendChild(createStatSpan('stat-min', `${result.min}ms`));
        detailEl.appendChild(document.createTextNode(' | p95: '));
        detailEl.appendChild(createStatSpan('stat-p95', `${result.p95}ms`));
        detailEl.appendChild(document.createTextNode(' | max: '));
        detailEl.appendChild(createStatSpan('stat-max', `${result.max}ms`));
        detailEl.appendChild(document.createTextNode(' | σ: '));
        detailEl.appendChild(createStatSpan('stat-stddev', `${result.stddev}ms`));
        if (typeof result.memoryKb === 'number') {
            detailEl.appendChild(document.createTextNode(' | mem: '));
            detailEl.appendChild(createStatSpan('stat-memory', `${result.memoryKb}KB`));
        }
        timeEl.appendChild(detailEl);

        // Overall ranking row
        const overallEl = document.createElement('span');
        overallEl.className = 'v-overall';
        overallEl.appendChild(document.createTextNode('Overall: '));
        overallEl.appendChild(
            createStatSpan('stat-overall', `${getOrdinal(result.overallRank)} (score: ${result.overallScore})`)
        );
        timeEl.appendChild(overallEl);

        // Highlight fastest / slowest by median
        if (result.median === fastest && fastest !== slowest) {
            timeEl.classList.add('fastest');
        } else if (result.median === slowest && fastest !== slowest) {
            timeEl.classList.add('slowest');
        }

        // Badge all strategies tied for overallRank === 1
        if (result.overallRank === 1) {
            const nameEl = itemEl.querySelector('.v-name');
            if (nameEl && !nameEl.querySelector('.v-winner-badge')) {
                const badge = document.createElement('span');
                badge.className = 'v-winner-badge';
                badge.textContent = 'Overall Winner';
                nameEl.appendChild(document.createTextNode(' '));
                nameEl.appendChild(badge);
            }
        }
    }

    benchmarkResults = results;
    document.getElementById('sortControls').style.display = 'flex';
    sortResults(activeSortMethod);

    const output = document.getElementById('outputDisplay');
    output.textContent = '';
    const doneSpan = document.createElement('span');
    doneSpan.className = 'status-ok';
    doneSpan.textContent = `Benchmark Complete (N=${limit})!`;
    output.appendChild(doneSpan);

    if (window.innerWidth <= 900) {
        const aside = document.querySelector('aside');
        if (aside && !aside.classList.contains('mobile-open')) {
            toggleMobileMenu();
        }
        output.appendChild(document.createTextNode('\nResults are now visible in the menu.'));
    } else {
        output.appendChild(document.createTextNode('\nSee sidebar for rankings.'));
    }
}

function sortResults(sortBy) {
    if (!benchmarkResults) return;

    activeSortMethod = sortBy;

    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.sort === sortBy);
    });

    document.querySelectorAll('.stat-highlight').forEach(el => el.classList.remove('stat-highlight'));
    document.querySelectorAll('.stat-' + sortBy).forEach(el => el.classList.add('stat-highlight'));

    const versionList = document.getElementById('versionList');
    const items = Array.from(versionList.children);

    const sortMap = {};
    for (const [key, result] of Object.entries(benchmarkResults)) {
        sortMap[key] = {
            avg: result.time,
            median: result.median,
            min: result.min,
            stddev: result.stddev,
            memory: result.memoryKb ?? Number.MAX_SAFE_INTEGER,
            overall: result.overallScore ?? Number.MAX_SAFE_INTEGER
        };
    }

    items.sort((a, b) => {
        const valA = sortMap[a.dataset.key]?.[sortBy] ?? Number.MAX_SAFE_INTEGER;
        const valB = sortMap[b.dataset.key]?.[sortBy] ?? Number.MAX_SAFE_INTEGER;
        return valA - valB;
    });

    items.forEach(item => versionList.appendChild(item));
}

function toggleMobileMenu() {
    const aside = document.querySelector('aside');
    const backdrop = document.querySelector('.mobile-backdrop');
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    if (!aside || !backdrop) return;

    const isOpen = aside.classList.toggle('mobile-open');
    backdrop.classList.toggle('active', isOpen);
    if (toggleBtn) {
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }
}

function getStrategyKeys() {
    return Array.from(document.querySelectorAll('.version-item')).map(item => item.dataset.key);
}

function nextStrategy() {
    const keys = getStrategyKeys();
    const currentIndex = keys.indexOf(currentVersion);
    if (currentIndex === -1) return;

    const nextIndex = (currentIndex + 1) % keys.length;
    selectVersion(keys[nextIndex]);

    const codePanel = document.getElementById('codeDisplay');
    if (codePanel) codePanel.scrollTop = 0;
}

function prevStrategy() {
    const keys = getStrategyKeys();
    const currentIndex = keys.indexOf(currentVersion);
    if (currentIndex === -1) return;

    const prevIndex = (currentIndex - 1 + keys.length) % keys.length;
    selectVersion(keys[prevIndex]);

    const codePanel = document.getElementById('codeDisplay');
    if (codePanel) codePanel.scrollTop = 0;
}

function handleLimitChange(event) {
    const nextLimit = Number.parseInt(event.target.value, 10);
    if (!Number.isFinite(nextLimit)) return;

    currentLimit = nextLimit;

    if (runAbortController) {
        runAbortController.abort();
        runAbortController = null;
    }
    runRequestId++;

    document.getElementById('outputDisplay').textContent = `Limit updated to N=${currentLimit}. Ready to execute...`;
    document.getElementById('executionTime').textContent = '';

    const runBtn = document.getElementById('runCodeBtn');
    runBtn.disabled = false;
    runBtn.innerHTML = RUN_BTN_HTML;
    runBtn.classList.remove('loading');

    clearBenchmarkUI();
}

function init() {
    const versionList = document.getElementById('versionList');
    if (versionList) {
        versionList.addEventListener('click', (event) => {
            const item = event.target.closest('.version-item');
            if (item && item.dataset.key) {
                selectVersion(item.dataset.key);
            }
        });
    }

    const runBtn = document.getElementById('runCodeBtn');
    if (runBtn) runBtn.addEventListener('click', runCode);

    const benchBtn = document.getElementById('benchmarkBtn');
    if (benchBtn) benchBtn.addEventListener('click', benchmarkAll);

    const mobileBenchBtn = document.getElementById('mobileBenchmarkBtn');
    if (mobileBenchBtn) mobileBenchBtn.addEventListener('click', benchmarkAll);

    const sortControls = document.getElementById('sortControls');
    if (sortControls) {
        sortControls.addEventListener('click', (event) => {
            const btn = event.target.closest('.sort-btn');
            if (btn && btn.dataset.sort) {
                sortResults(btn.dataset.sort);
            }
        });
    }

    const limitSelect = document.getElementById('limitSelect');
    if (limitSelect) {
        limitSelect.addEventListener('change', handleLimitChange);
    }

    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    if (mobileToggle) mobileToggle.addEventListener('click', toggleMobileMenu);

    const mobileBackdrop = document.querySelector('.mobile-backdrop');
    if (mobileBackdrop) mobileBackdrop.addEventListener('click', toggleMobileMenu);

    const prevBtn = document.getElementById('prevStrategyBtn');
    if (prevBtn) prevBtn.addEventListener('click', prevStrategy);

    const nextBtn = document.getElementById('nextStrategyBtn');
    if (nextBtn) nextBtn.addEventListener('click', nextStrategy);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const aside = document.querySelector('aside');
            if (aside && aside.classList.contains('mobile-open')) {
                toggleMobileMenu();
            }
        }
    });

    selectVersion('original');

    if (window.innerWidth <= 900) {
        setTimeout(() => {
            const aside = document.querySelector('aside');
            if (aside && !aside.classList.contains('mobile-open')) {
                toggleMobileMenu();
            }
        }, 500);
    }
}

init();
