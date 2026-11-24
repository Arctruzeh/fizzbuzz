export function createApiClient(fetchImpl = fetch, endpoint = 'api.php') {
    async function post(fields, signal) {
        const formData = new FormData();
        for (const [key, value] of Object.entries(fields)) {
            formData.append(key, String(value));
        }

        const options = {
            method: 'POST',
            body: formData,
        };
        if (signal) {
            options.signal = signal;
        }

        const response = await fetchImpl(endpoint, options);
        const data = await response.json();

        return {
            ok: response.ok,
            status: response.status,
            data,
        };
    }

    return {
        runStrategy(version, limit, signal) {
            return post({ action: 'run', version, limit }, signal);
        },
        runBenchmark(limit) {
            return post({ action: 'benchmark', limit });
        },
    };
}
