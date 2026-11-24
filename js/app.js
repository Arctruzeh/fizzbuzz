import { createApiClient } from './apiClient.js';
import { clearBenchmarkUI, renderBenchmarkResults, sortBenchmarkList } from './benchmarkView.js';
import { highlightCode } from './highlighter.js';

const RUN_BTN_HTML =
    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">' +
    '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />' +
    '</svg> Run';

export function createApp({
    strategies,
    defaultLimit = 1000,
    doc = document,
    win = window,
    fetchImpl = fetch,
    schedule = setTimeout,
}) {
    const api = createApiClient(fetchImpl);

    let currentVersion = null;
    let currentLimit = typeof defaultLimit === 'number' ? defaultLimit : 1000;
    let benchmarkResults = null;
    let activeSortMethod = 'median';
    let runAbortController = null;
    let runRequestId = 0;
    let isBenchmarking = false;

    function resetRunButton() {
        const runBtn = doc.getElementById('runCodeBtn');
        if (!runBtn) return;
        runBtn.disabled = false;
        runBtn.innerHTML = RUN_BTN_HTML;
        runBtn.classList.remove('loading');
    }

    function toggleMobileMenu() {
        const aside = doc.querySelector('aside');
        const backdrop = doc.querySelector('.mobile-backdrop');
        const toggleBtn = doc.querySelector('.mobile-menu-toggle');
        if (!aside || !backdrop) return;

        const isOpen = aside.classList.toggle('mobile-open');
        backdrop.classList.toggle('active', isOpen);
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
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

        doc.getElementById('displayTitle').textContent = s.name;
        doc.getElementById('displayDesc').textContent = s.description;

        const complexityEl = doc.getElementById('displayComplexity');
        if (complexityEl) {
            complexityEl.textContent = s.complexity || '';
            complexityEl.style.display = s.complexity ? 'inline-block' : 'none';
        }

        doc.getElementById('codeDisplay').innerHTML = highlightCode(s.code);
        doc.getElementById('outputDisplay').textContent = 'Ready to execute...';
        doc.getElementById('executionTime').textContent = '';

        resetRunButton();

        doc.querySelectorAll('.version-item').forEach((el) => el.classList.remove('active'));
        const activeItem = doc.getElementById('item-' + key);
        if (activeItem) {
            activeItem.classList.add('active');
        }

        const aside = doc.querySelector('aside');
        if (aside && aside.classList.contains('mobile-open')) {
            toggleMobileMenu();
        }
    }

    async function runCode() {
        if (!currentVersion) return;

        if (runAbortController) {
            runAbortController.abort();
        }
        const controller = new AbortController();
        runAbortController = controller;
        const requestId = ++runRequestId;

        const btn = doc.getElementById('runCodeBtn');
        const output = doc.getElementById('outputDisplay');
        const timeDisplay = doc.getElementById('executionTime');

        btn.disabled = true;
        btn.textContent = 'Running...';
        btn.classList.add('loading');
        output.textContent = 'Calculating...';
        timeDisplay.textContent = '';

        const clientStart = performance.now();

        try {
            const { ok, status, data: result } = await api.runStrategy(
                currentVersion,
                currentLimit,
                controller.signal
            );
            const totalTime = performance.now() - clientStart;

            if (requestId !== runRequestId) return;

            if (!ok || result.error) {
                output.textContent = 'Error: ' + (result.error || `HTTP ${status}`);
            } else {
                output.textContent = result.output;

                timeDisplay.textContent = '';
                timeDisplay.appendChild(doc.createTextNode(`Exec: ${result.executionTime}ms `));
                const sep = doc.createElement('span');
                sep.className = 'time-sep';
                sep.textContent = '|';
                timeDisplay.appendChild(sep);
                timeDisplay.appendChild(doc.createTextNode(` Total: ${totalTime.toFixed(2)}ms`));
                if (typeof result.memoryKb === 'number') {
                    const memSep = doc.createElement('span');
                    memSep.className = 'time-sep';
                    memSep.textContent = ' | ';
                    timeDisplay.appendChild(memSep);
                    timeDisplay.appendChild(doc.createTextNode(`Mem: ${result.memoryKb}KB`));
                }
                timeDisplay.appendChild(doc.createTextNode(` (N=${result.limit})`));
            }
        } catch (e) {
            if (e.name === 'AbortError' || requestId !== runRequestId) {
                return;
            }
            output.textContent = 'Error executing code: ' + e.message;
        } finally {
            if (requestId === runRequestId) {
                runAbortController = null;
                resetRunButton();
            }
        }
    }

    function sortResults(sortBy) {
        if (!benchmarkResults) return;
        activeSortMethod = sortBy;
        sortBenchmarkList(doc, benchmarkResults, sortBy);
    }

    function processBenchmarkResults(results, limit) {
        renderBenchmarkResults(doc, results, limit);
        benchmarkResults = results;
        sortResults(activeSortMethod);

        const output = doc.getElementById('outputDisplay');
        output.textContent = '';
        const doneSpan = doc.createElement('span');
        doneSpan.className = 'status-ok';
        doneSpan.textContent = `Benchmark Complete (N=${limit})!`;
        output.appendChild(doneSpan);

        if (win.innerWidth <= 900) {
            const aside = doc.querySelector('aside');
            if (aside && !aside.classList.contains('mobile-open')) {
                toggleMobileMenu();
            }
            output.appendChild(doc.createTextNode('\nResults are now visible in the menu.'));
        } else {
            output.appendChild(doc.createTextNode('\nSee sidebar for rankings.'));
        }
    }

    async function benchmarkAll() {
        if (isBenchmarking) return;
        isBenchmarking = true;

        const benchButtons = Array.from(doc.querySelectorAll('.benchmark-btn, .mobile-benchmark-btn'));
        const originalButtonStates = benchButtons.map((b) => ({ el: b, html: b.innerHTML }));
        const limitSelect = doc.getElementById('limitSelect');

        clearBenchmarkUI(doc);
        benchmarkResults = null;

        benchButtons.forEach((b) => {
            b.disabled = true;
            b.textContent = 'Running...';
        });
        if (limitSelect) limitSelect.disabled = true;

        const output = doc.getElementById('outputDisplay');
        output.textContent = '';
        const initSpan = doc.createElement('span');
        initSpan.className = 'status-warn';
        initSpan.textContent = `Initializing Benchmark Protocol (N=${currentLimit})...`;
        output.appendChild(initSpan);
        output.appendChild(doc.createTextNode('\nRunning interleaved warmup + timed rounds across all strategies.'));

        try {
            const { ok, status, data } = await api.runBenchmark(currentLimit);
            if (!ok || data.error) {
                throw new Error(data.error || `HTTP ${status}`);
            }
            if (data.results) {
                processBenchmarkResults(data.results, data.limit ?? currentLimit);
            }
        } catch (e) {
            output.textContent = '';
            const errSpan = doc.createElement('span');
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

    function getStrategyKeys() {
        return Array.from(doc.querySelectorAll('.version-item')).map((item) => item.dataset.key);
    }

    function stepStrategy(offset) {
        const keys = getStrategyKeys();
        const currentIndex = keys.indexOf(currentVersion);
        if (currentIndex === -1) return;

        const targetIndex = (currentIndex + offset + keys.length) % keys.length;
        selectVersion(keys[targetIndex]);

        const codePanel = doc.getElementById('codeDisplay');
        if (codePanel) codePanel.scrollTop = 0;
    }

    function nextStrategy() {
        stepStrategy(1);
    }

    function prevStrategy() {
        stepStrategy(-1);
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

        doc.getElementById('outputDisplay').textContent = `Limit updated to N=${currentLimit}. Ready to execute...`;
        doc.getElementById('executionTime').textContent = '';

        resetRunButton();
        clearBenchmarkUI(doc);
        benchmarkResults = null;
    }

    function init() {
        const versionList = doc.getElementById('versionList');
        if (versionList) {
            versionList.addEventListener('click', (event) => {
                const item = event.target.closest('.version-item');
                if (item && item.dataset.key) {
                    selectVersion(item.dataset.key);
                }
            });
        }

        doc.getElementById('runCodeBtn')?.addEventListener('click', runCode);
        doc.getElementById('benchmarkBtn')?.addEventListener('click', benchmarkAll);
        doc.getElementById('mobileBenchmarkBtn')?.addEventListener('click', benchmarkAll);

        const sortControls = doc.getElementById('sortControls');
        if (sortControls) {
            sortControls.addEventListener('click', (event) => {
                const btn = event.target.closest('.sort-btn');
                if (btn && btn.dataset.sort) {
                    sortResults(btn.dataset.sort);
                }
            });
        }

        doc.getElementById('limitSelect')?.addEventListener('change', handleLimitChange);
        doc.querySelector('.mobile-menu-toggle')?.addEventListener('click', toggleMobileMenu);
        doc.querySelector('.mobile-backdrop')?.addEventListener('click', toggleMobileMenu);
        doc.getElementById('prevStrategyBtn')?.addEventListener('click', prevStrategy);
        doc.getElementById('nextStrategyBtn')?.addEventListener('click', nextStrategy);

        doc.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                const aside = doc.querySelector('aside');
                if (aside && aside.classList.contains('mobile-open')) {
                    toggleMobileMenu();
                }
            }
        });

        selectVersion('original');

        if (win.innerWidth <= 900) {
            schedule(() => {
                const aside = doc.querySelector('aside');
                if (aside && !aside.classList.contains('mobile-open')) {
                    toggleMobileMenu();
                }
            }, 500);
        }
    }

    return {
        init,
        selectVersion,
        runCode,
        benchmarkAll,
        processBenchmarkResults,
        sortResults,
        toggleMobileMenu,
        nextStrategy,
        prevStrategy,
        handleLimitChange,
    };
}

if (typeof document !== 'undefined' && typeof window !== 'undefined') {
    const configEl = document.getElementById('appConfig');
    if (configEl) {
        const config = JSON.parse(configEl.textContent || '{}');
        createApp({
            strategies: config.strategies ?? {},
            defaultLimit: config.defaultLimit ?? 1000,
        }).init();
    }
}
