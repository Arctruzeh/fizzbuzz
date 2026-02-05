/**
 * FizzBuzz Benchmark Application - Client-Side JavaScript
 * 
 * Handles UI interactions, code execution, benchmarking, and result visualization.
 */

// Embed the PHP strategies data into JS
const strategies = STRATEGIES_DATA;
let currentVersion = null;
let benchmarkResults = null; // Store results for re-sorting
let activeSortMethod = 'avg'; // Track active sort

function selectVersion(key) {
    currentVersion = key;
    const s = strategies[key];

    // Update UI Text
    document.getElementById('displayTitle').innerText = s.name;
    document.getElementById('displayDesc').innerText = s.description;

    // Update Code View
    const codeHTML = highlightCode(s.code);
    document.getElementById('codeDisplay').innerHTML = codeHTML;

    // Reset Output and Timer
    document.getElementById('outputDisplay').innerText = "Ready to execute...";
    document.getElementById('executionTime').innerText = "";

    // Highlight Sidebar
    document.querySelectorAll('.version-item').forEach(el => el.classList.remove('active'));
    document.getElementById('item-' + key).classList.add('active');

    // Auto-close mobile menu if open
    const aside = document.querySelector('aside');
    if (aside.classList.contains('mobile-open')) {
        toggleMobileMenu();
    }
}

// Robust Syntax Highlighting Helper
function highlightCode(code) {
    // Escaping first to prevent HTML injection from the code itself
    code = code.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

    const placeholders = { strings: [], comments: [] };

    // 1. Extract Strings (replace with placeholder)
    code = code.replace(/(['"])(.*?)\1/g, function (match) {
        placeholders.strings.push(match);
        return '___STR' + (placeholders.strings.length - 1) + '___';
    });

    // 2. Extract Comments (replace with placeholder)
    // Note: We extracted strings first, so "//" inside a string won't trigger this.
    code = code.replace(/(\/\/.*)/g, function (match) {
        placeholders.comments.push(match);
        return '___COM' + (placeholders.comments.length - 1) + '___';
    });

    // 3. Highlight Keywords & Numbers (now safe from strings/comments)
    code = code.replace(/\b(for|if|else|echo|return|function|array_map)\b/g, '<span class="kwd">$1</span>');
    code = code.replace(/\b(\d+)\b/g, '<span class="num">$1</span>');

    // 4. Restore Comments
    code = code.replace(/___COM(\d+)___/g, function (match, id) {
        return '<span class="com">' + placeholders.comments[id] + '</span>';
    });

    // 5. Restore Strings
    code = code.replace(/___STR(\d+)___/g, function (match, id) {
        return '<span class="str">' + placeholders.strings[id] + '</span>';
    });

    return code;
}

async function runCode() {
    if (!currentVersion) return;

    const btn = document.querySelector('.run-btn');
    const output = document.getElementById('outputDisplay');
    const timeDisplay = document.getElementById('executionTime');

    // UI State
    btn.innerHTML = 'Running...';
    btn.classList.add('loading');
    output.innerText = 'Calculating...';
    timeDisplay.innerText = '';

    // Track client-side time
    const clientStart = performance.now();

    try {
        const formData = new FormData();
        formData.append('action', 'run');
        formData.append('version', currentVersion);

        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });

        const clientEnd = performance.now();
        const totalTime = clientEnd - clientStart;

        const result = await response.json();

        if (result.error) {
            output.innerText = "Error: " + result.error;
        } else {
            output.innerHTML = result.output;

            // Display timing information
            timeDisplay.innerHTML = `Exec: ${result.executionTime}ms <span style="color: var(--border-color);">|</span> Total: ${totalTime.toFixed(2)}ms`;
        }

    } catch (e) {
        output.innerText = "Error executing code: " + e.message;
    } finally {
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" /></svg> Run';
        btn.classList.remove('loading');
    }
}

async function benchmarkAll() {
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;

    // Clear previous times and classes
    document.querySelectorAll('.v-time').forEach(el => {
        el.innerHTML = '';
        el.classList.remove('fastest', 'slowest');
    });

    // UI State
    btn.disabled = true;
    btn.innerHTML = 'Running (1000x each)...';

    try {
        const formData = new FormData();
        formData.append('action', 'benchmark');

        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.results) {
            // Find fastest and slowest
            const times = Object.values(data.results).map(r => r.time);
            const fastest = Math.min(...times);
            const slowest = Math.max(...times);

            // Display results with rankings
            for (const [key, result] of Object.entries(data.results)) {
                const timeEl = document.getElementById('time-' + key);

                // Get ordinal suffix (1st, 2nd, 3rd, etc.)
                const getOrdinal = (n) => {
                    const s = ['th', 'st', 'nd', 'rd'];
                    const v = n % 100;
                    return n + (s[(v - 20) % 10] || s[v] || s[0]);
                };

                timeEl.innerHTML = `<span class="stat-avg">${result.time}ms</span> (avg of ${result.runs}) <span class="v-rank">${getOrdinal(result.rank)}</span>`;

                // Add min/max info
                const variance = result.max - result.min;
                const minMaxEl = document.createElement('span');
                minMaxEl.className = 'v-minmax';
                minMaxEl.innerHTML = `min: <span class="stat-min">${result.min}ms</span> | max: <span class="stat-max">${result.max}ms</span> | var: <span class="stat-variance">${variance.toFixed(3)}ms</span>`;
                timeEl.appendChild(minMaxEl);

                // Add overall ranking if available
                if (result.overallRank) {
                    const overallEl = document.createElement('span');
                    overallEl.className = 'v-overall';
                    overallEl.innerHTML = `Overall: <span class="stat-overall">${result.overallRank} (score: ${result.overallScore})</span>`;
                    timeEl.appendChild(overallEl);
                }

                // Highlight fastest/slowest
                if (result.time === fastest && fastest !== slowest) {
                    timeEl.classList.add('fastest');
                } else if (result.time === slowest && fastest !== slowest) {
                    timeEl.classList.add('slowest');
                }
            }

            // Sort the sidebar items by rank
            const versionList = document.getElementById('versionList');
            const items = Array.from(versionList.children);

            // Create a map of key to rank
            const rankMap = {};
            for (const [key, result] of Object.entries(data.results)) {
                rankMap[key] = result.rank;
            }

            // Sort items by rank
            items.sort((a, b) => {
                const keyA = a.id.replace('item-', '');
                const keyB = b.id.replace('item-', '');
                return (rankMap[keyA] || 999) - (rankMap[keyB] || 999);
            });

            // Re-append in sorted order
            items.forEach(item => versionList.appendChild(item));

            // Calculate overall rankings
            calculateOverallRankings(data.results);

            // Store results for re-sorting
            benchmarkResults = data.results;

            // Show sort controls
            document.getElementById('sortControls').style.display = 'flex';

            // Apply the currently active sort method
            sortResults(activeSortMethod);
        }
    } catch (e) {
        alert('Benchmark failed: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
}

function sortResults(sortBy) {
    if (!benchmarkResults) return;

    // Track active sort method
    activeSortMethod = sortBy;

    // Update active button
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.sort === sortBy);
    });

    // Remove existing highlights
    document.querySelectorAll('.stat-highlight').forEach(el => el.classList.remove('stat-highlight'));

    // Add new highlight
    const highlightClass = 'stat-' + sortBy;
    if (sortBy === 'avg') { // map 'avg' sort to '.stat-avg' class
        document.querySelectorAll('.stat-avg').forEach(el => el.classList.add('stat-highlight'));
    } else {
        document.querySelectorAll('.' + highlightClass).forEach(el => el.classList.add('stat-highlight'));
    }


    const versionList = document.getElementById('versionList');
    const items = Array.from(versionList.children);

    // Create sort map
    const sortMap = {};
    for (const [key, result] of Object.entries(benchmarkResults)) {
        const variance = result.max - result.min;
        sortMap[key] = {
            avg: result.time,
            min: result.min,
            max: result.max,
            variance: variance,
            overall: result.overallScore || 999
        };
    }

    // Sort items
    items.sort((a, b) => {
        const keyA = a.id.replace('item-', '');
        const keyB = b.id.replace('item-', '');
        const valA = sortMap[keyA]?.[sortBy] || 999;
        const valB = sortMap[keyB]?.[sortBy] || 999;
        return valA - valB;
    });

    // Re-append in sorted order
    items.forEach(item => versionList.appendChild(item));
}

function calculateOverallRankings(results) {
    // Create arrays for each metric
    const metrics = ['time', 'min', 'max'];
    const rankings = {};

    // Calculate variance and add to results
    for (const [key, result] of Object.entries(results)) {
        result.variance = result.max - result.min;
    }

    // Rank each metric (lower is better for all)
    metrics.push('variance');

    for (const metric of metrics) {
        const sorted = Object.entries(results).sort((a, b) => a[1][metric] - b[1][metric]);
        sorted.forEach(([key, _], index) => {
            if (!rankings[key]) rankings[key] = {};
            rankings[key][metric] = index + 1; // 1-based ranking
        });
    }

    // Calculate overall score (sum of all ranks, lower is better)
    const overallScores = {};
    for (const [key, ranks] of Object.entries(rankings)) {
        overallScores[key] = Object.values(ranks).reduce((sum, rank) => sum + rank, 0);
    }

    // Sort by overall score
    const overallSorted = Object.entries(overallScores).sort((a, b) => a[1] - b[1]);

    // Add overall rankings to results
    overallSorted.forEach(([key, score], index) => {
        results[key].overallScore = score;
        results[key].overallRank = index + 1;
        results[key].rankBreakdown = rankings[key];
    });

    // Display overall winner
    const winner = overallSorted[0];
    const winnerKey = winner[0];
    const winnerName = results[winnerKey].name;

    // Add winner badge to the winner's name
    const winnerItem = document.getElementById('item-' + winnerKey);
    const winnerNameEl = winnerItem.querySelector('.v-name');
    if (!winnerNameEl.querySelector('.v-winner-badge')) {
        const badge = document.createElement('span');
        badge.className = 'v-winner-badge';
        badge.textContent = 'Overall Winner';

        // Add a space before the badge if it doesn't exist
        if (winnerNameEl.lastChild.nodeType === Node.TEXT_NODE) {
            // ensure space
        } else {
            winnerNameEl.appendChild(document.createTextNode(' '));
        }

        winnerNameEl.appendChild(badge);
    }

    // Update all items with overall ranking info
    for (const [key, result] of Object.entries(results)) {
        const timeEl = document.getElementById('time-' + key);
        let overallEl = timeEl.querySelector('.v-overall');

        if (!overallEl) {
            overallEl = document.createElement('span');
            overallEl.className = 'v-overall';
            timeEl.appendChild(overallEl);
        }

        overallEl.innerHTML = `Overall: <span class="stat-overall">${result.overallRank} (score: ${result.overallScore})</span>`;
    }
}

// Auto-select the original version on load
selectVersion('original');

// Mobile menu toggle
function toggleMobileMenu() {
    const aside = document.querySelector('aside');
    const backdrop = document.querySelector('.mobile-backdrop');

    aside.classList.toggle('mobile-open');
    backdrop.classList.toggle('active');
}
