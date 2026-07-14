import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Select from 'react-select';

const controlStyles = {
    control: (base, state) => ({
        ...base,
        minHeight: '48px',
        borderRadius: '1rem',
        borderColor: state.isFocused ? '#94a3b8' : '#e2e8f0',
        backgroundColor: '#f8fafc',
        boxShadow: 'none',
        '&:hover': {
            borderColor: '#cbd5e1',
        },
    }),
    valueContainer: (base) => ({
        ...base,
        padding: '2px 12px',
    }),
    input: (base) => ({
        ...base,
        margin: 0,
        padding: 0,
    }),
    placeholder: (base) => ({
        ...base,
        color: '#64748b',
        fontSize: '0.875rem',
    }),
    singleValue: (base) => ({
        ...base,
        color: '#0f172a',
        fontSize: '0.875rem',
    }),
    menu: (base) => ({
        ...base,
        borderRadius: '1rem',
        overflow: 'hidden',
        border: '1px solid #e2e8f0',
        boxShadow: '0 10px 25px rgba(15, 23, 42, 0.08)',
        zIndex: 40,
    }),
    menuList: (base) => ({
        ...base,
        padding: '6px',
    }),
    option: (base, state) => ({
        ...base,
        borderRadius: '0.75rem',
        fontSize: '0.875rem',
        backgroundColor: state.isSelected
            ? '#0f172a'
            : state.isFocused
              ? '#f1f5f9'
              : 'transparent',
        color: state.isSelected ? '#ffffff' : '#334155',
        cursor: 'pointer',
    }),
    indicatorSeparator: () => ({
        display: 'none',
    }),
    dropdownIndicator: (base) => ({
        ...base,
        color: '#64748b',
        paddingRight: '12px',
        '&:hover': {
            color: '#334155',
        },
    }),
    clearIndicator: (base) => ({
        ...base,
        color: '#64748b',
        '&:hover': {
            color: '#334155',
        },
    }),
};

export default function SearchSelect({
    id,
    label,
    value,
    onChange,
    options = [],
    error,
    placeholder = 'Search and select...',
    isClearable = true,
    isDisabled = false,
    isSearchable = true,
}) {
    const normalizedOptions = options.map((option) => {
        if (typeof option === 'string') {
            return {
                value: option,
                label: option.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase()),
            };
        }

        return {
            value: String(option.value ?? option.id),
            label: option.label ?? option.name ?? String(option.value ?? option.id),
        };
    });

    const selected = normalizedOptions.find((option) => option.value === String(value ?? '')) ?? null;

    return (
        <div>
            {label ? <InputLabel htmlFor={id} value={label} /> : null}
            <div className={label ? 'mt-2' : undefined}>
                <Select
                    inputId={id}
                    instanceId={id}
                    value={selected}
                    onChange={(option) => onChange(option?.value ?? '')}
                    options={normalizedOptions}
                    placeholder={placeholder}
                    isClearable={isClearable}
                    isDisabled={isDisabled}
                    isSearchable={isSearchable}
                    styles={controlStyles}
                    classNamePrefix="smart-select"
                    noOptionsMessage={() => 'No matches found'}
                    menuPortalTarget={typeof document !== 'undefined' ? document.body : null}
                    menuPosition="fixed"
                />
            </div>
            <InputError className="mt-2" message={error} />
        </div>
    );
}
