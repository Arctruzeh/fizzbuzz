export function getOrdinal(n) {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
}

/**
 * Syntax highlighting with single-pass token extraction so comments inside strings
 * and quotes inside comments cannot interfere with each other.
 */
export function highlightCode(code) {
    let escaped = code
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');

    const tokens = [];

    escaped = escaped.replaceAll(/(\/\/.*$|'(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/gm, function (match) {
        const type = match.startsWith('//') ? 'com' : 'str';
        const index = tokens.length;
        tokens.push({ type, text: match });
        return '___TOK' + index + '___';
    });

    escaped = escaped.replaceAll(
        /\b(for|foreach|as|if|elseif|else|switch|case|break|default|match|echo|return|function|use|yield|goto|void|int|string)\b/g,
        '<span class="kwd">$1</span>'
    );

    escaped = escaped.replaceAll(
        /\b(array_map|array_walk|range|implode|str_repeat|floor|is_int|isset|defined|define|function_exists)\b/g,
        '<span class="func">$1</span>'
    );

    escaped = escaped.replaceAll(/(\$[a-zA-Z_]\w*)/g, '<span class="var">$1</span>');
    escaped = escaped.replaceAll(/\b(\d+)\b/g, '<span class="num">$1</span>');

    escaped = escaped.replaceAll(/___TOK(\d+)___/g, function (_match, id) {
        const tok = tokens[Number(id)];
        return '<span class="' + tok.type + '">' + tok.text + '</span>';
    });

    return escaped;
}
