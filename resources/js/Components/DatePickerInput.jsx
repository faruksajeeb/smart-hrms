import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import {
    formatDateTimeValue,
    formatDateValue,
    parseDateTimeValue,
    parseDateValue,
} from '@/lib/dateInput';
import DatePicker from 'react-datepicker';

export default function DatePickerInput({
    id,
    label,
    value,
    onChange,
    error,
    placeholder,
    showTimeSelect = false,
    timeIntervals = 15,
    dateFormat = showTimeSelect ? 'MMM d, yyyy h:mm aa' : 'MMM d, yyyy',
    isClearable = true,
    minDate,
    maxDate,
    isDisabled = false,
}) {
    const selected = showTimeSelect ? parseDateTimeValue(value) : parseDateValue(value);

    const handleChange = (date) => {
        onChange(showTimeSelect ? formatDateTimeValue(date) : formatDateValue(date));
    };

    return (
        <div>
            {label ? <InputLabel htmlFor={id} value={label} /> : null}
            <div className={label ? 'mt-2' : undefined}>
                <DatePicker
                    id={id}
                    selected={selected}
                    onChange={handleChange}
                    showTimeSelect={showTimeSelect}
                    timeIntervals={timeIntervals}
                    dateFormat={dateFormat}
                    placeholderText={placeholder ?? (showTimeSelect ? 'Pick date and time' : 'Pick a date')}
                    isClearable={isClearable}
                    minDate={minDate}
                    maxDate={maxDate}
                    disabled={isDisabled}
                    className="block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400"
                    calendarClassName="smart-datepicker"
                    popperClassName="smart-datepicker-popper"
                    autoComplete="off"
                />
            </div>
            <InputError className="mt-2" message={error} />
        </div>
    );
}
