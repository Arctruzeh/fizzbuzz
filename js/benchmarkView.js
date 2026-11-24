import { getOrdinal } from './highlighter.js';

function createStatSpan(doc, className, text) {
    const span = doc.createElement('span');
    span.className = className;
    span.textContent = text;
    return span;
}

export function clearBenchmarkUI(doc) {
    doc.querySelectorAll('.v-time').forEach((el) => {
        el.textContent = '';
        el.classList.remove('fastest', 'slowest');
    });
    doc.querySelectorAll('.v-winner-badge').forEach((el) => el.remove());

    const sortControls = doc.getElementById('sortControls');
    if (sortControls) {
        sortControls.style.display = 'none';
    }
}

export function renderBenchmarkResults(doc, results, limit) {
    const medians = Object.values(results).map((r) => r.median);
    const fastest = Math.min(...medians);
    const slowest = Math.max(...medians);

    for (const [key, result] of Object.entries(results)) {
        const timeEl = doc.getElementById('time-' + key);
        const itemEl = doc.getElementById('item-' + key);
        if (!timeEl || !itemEl) continue;

        timeEl.textContent = '';

        timeEl.appendChild(createStatSpan(doc, 'stat-median', `${result.median}ms`));
        timeEl.appendChild(doc.createTextNode(` med (${result.runs}x, N=${limit}) `));
        timeEl.appendChild(createStatSpan(doc, 'v-rank', getOrdinal(result.rank)));

        const detailEl = doc.createElement('span');
        detailEl.className = 'v-minmax';
        detailEl.appendChild(doc.createTextNode('avg: '));
        detailEl.appendChild(createStatSpan(doc, 'stat-avg', `${result.time}ms`));
        detailEl.appendChild(doc.createTextNode(' | min: '));
        detailEl.appendChild(createStatSpan(doc, 'stat-min', `${result.min}ms`));
        detailEl.appendChild(doc.createTextNode(' | p95: '));
        detailEl.appendChild(createStatSpan(doc, 'stat-p95', `${result.p95}ms`));
        detailEl.appendChild(doc.createTextNode(' | max: '));
        detailEl.appendChild(createStatSpan(doc, 'stat-max', `${result.max}ms`));
        detailEl.appendChild(doc.createTextNode(' | σ: '));
        detailEl.appendChild(createStatSpan(doc, 'stat-stddev', `${result.stddev}ms`));
        if (typeof result.memoryKb === 'number') {
            detailEl.appendChild(doc.createTextNode(' | mem: '));
            detailEl.appendChild(createStatSpan(doc, 'stat-memory', `${result.memoryKb}KB`));
        }
        timeEl.appendChild(detailEl);

        const overallEl = doc.createElement('span');
        overallEl.className = 'v-overall';
        overallEl.appendChild(doc.createTextNode('Overall: '));
        overallEl.appendChild(
            createStatSpan(doc, 'stat-overall', `${getOrdinal(result.overallRank)} (score: ${result.overallScore})`)
        );
        timeEl.appendChild(overallEl);

        if (result.median === fastest && fastest !== slowest) {
            timeEl.classList.add('fastest');
        } else if (result.median === slowest && fastest !== slowest) {
            timeEl.classList.add('slowest');
        }

        if (result.overallRank === 1) {
            const nameEl = itemEl.querySelector('.v-name');
            if (nameEl && !nameEl.querySelector('.v-winner-badge')) {
                const badge = doc.createElement('span');
                badge.className = 'v-winner-badge';
                badge.textContent = 'Overall Winner';
                nameEl.appendChild(doc.createTextNode(' '));
                nameEl.appendChild(badge);
            }
        }
    }

    const sortControls = doc.getElementById('sortControls');
    if (sortControls) {
        sortControls.style.display = 'flex';
    }
}

export function sortBenchmarkList(doc, benchmarkResults, sortBy) {
    doc.querySelectorAll('.sort-btn').forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.sort === sortBy);
    });

    doc.querySelectorAll('.stat-highlight').forEach((el) => el.classList.remove('stat-highlight'));
    doc.querySelectorAll('.stat-' + sortBy).forEach((el) => el.classList.add('stat-highlight'));

    const versionList = doc.getElementById('versionList');
    if (!versionList) return;

    const items = Array.from(versionList.children);
    const sortMap = {};

    for (const [key, result] of Object.entries(benchmarkResults)) {
        sortMap[key] = {
            avg: result.time,
            median: result.median,
            min: result.min,
            stddev: result.stddev,
            memory: result.memoryKb ?? Number.MAX_SAFE_INTEGER,
            overall: result.overallScore ?? Number.MAX_SAFE_INTEGER,
        };
    }

    items.sort((a, b) => {
        const valA = sortMap[a.dataset.key]?.[sortBy] ?? Number.MAX_SAFE_INTEGER;
        const valB = sortMap[b.dataset.key]?.[sortBy] ?? Number.MAX_SAFE_INTEGER;
        return valA - valB;
    });

    items.forEach((item) => versionList.appendChild(item));
}
