const STORAGE_KEY = 'movikaa:comparison-vehicle-ids';
const MAX_COMPARISON_ITEMS = 4;

function canUseStorage() {
    return typeof window !== 'undefined' && typeof window.localStorage !== 'undefined';
}

export function getComparisonIds() {
    if (!canUseStorage()) {
        return [];
    }

    try {
        const raw = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || '[]');

        return Array.isArray(raw)
            ? raw.map((item) => Number(item)).filter((item) => Number.isInteger(item) && item > 0).slice(0, MAX_COMPARISON_ITEMS)
            : [];
    } catch {
        return [];
    }
}

export function saveComparisonIds(ids) {
    if (!canUseStorage()) {
        return [];
    }

    const normalized = [...new Set(ids.map((item) => Number(item)).filter((item) => Number.isInteger(item) && item > 0))].slice(0, MAX_COMPARISON_ITEMS);
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(normalized));

    return normalized;
}

export function toggleComparisonId(vehicleId) {
    const numericId = Number(vehicleId);
    const current = getComparisonIds();
    const exists = current.includes(numericId);

    if (exists) {
        return {
            ids: saveComparisonIds(current.filter((item) => item !== numericId)),
            compared: false,
            reason: null,
        };
    }

    if (current.length >= MAX_COMPARISON_ITEMS) {
        return {
            ids: current,
            compared: false,
            reason: 'El comparador admite hasta 4 autos al mismo tiempo.',
        };
    }

    return {
        ids: saveComparisonIds([...current, numericId]),
        compared: true,
        reason: null,
    };
}

export function clearComparisonIds() {
    return saveComparisonIds([]);
}

export function buildComparisonsUrl(baseUrl, ids = getComparisonIds()) {
    const normalized = ids.map((item) => Number(item)).filter((item) => Number.isInteger(item) && item > 0);

    if (!normalized.length) {
        return baseUrl;
    }

    const params = new URLSearchParams();
    normalized.forEach((item) => params.append('ids[]', String(item)));

    return `${baseUrl}?${params.toString()}`;
}

export { MAX_COMPARISON_ITEMS };
