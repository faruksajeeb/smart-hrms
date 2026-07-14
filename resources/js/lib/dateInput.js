import { format, isValid, parse } from 'date-fns';

export function parseDateValue(value) {
    if (!value) {
        return null;
    }

    const parsed = parse(String(value).slice(0, 10), 'yyyy-MM-dd', new Date());

    return isValid(parsed) ? parsed : null;
}

export function parseDateTimeValue(value) {
    if (!value) {
        return null;
    }

    const normalized = String(value).replace(' ', 'T');
    const parsed = new Date(normalized);

    return isValid(parsed) ? parsed : null;
}

export function formatDateValue(date) {
    if (!date || !isValid(date)) {
        return '';
    }

    return format(date, 'yyyy-MM-dd');
}

export function formatDateTimeValue(date) {
    if (!date || !isValid(date)) {
        return '';
    }

    return format(date, 'yyyy-MM-dd HH:mm:ss');
}
