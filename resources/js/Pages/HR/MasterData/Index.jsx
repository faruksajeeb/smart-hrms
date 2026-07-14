import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import HRLayout from '@/Layouts/HRLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const titleCase = (value) =>
    String(value ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

const emptyForm = (category) => ({
    category,
    parent_id: '',
    code: '',
    name: '',
    description: '',
    status: 'active',
    sort_order: 0,
});

export default function Index({ items, filters, stats, options }) {
    const { authPermissions = [] } = usePage().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [editingItem, setEditingItem] = useState(null);
    const selectedCategory = filters.category;
    const form = useForm(emptyForm(selectedCategory));

    const selectedCategoryLabel = useMemo(
        () => options.categories.find((category) => category.value === selectedCategory)?.label ?? 'Master Data',
        [options.categories, selectedCategory],
    );

    const requiresParent = (options.parentCategories[selectedCategory] ?? []).length > 0;
    const canCreate = authPermissions.includes('master-data.create-master-data');
    const canEdit = authPermissions.includes('master-data.edit-master-data');
    const canDelete = authPermissions.includes('master-data.delete-master-data');

    const switchCategory = (category) => {
        setEditingItem(null);
        form.clearErrors();
        form.setData(emptyForm(category));

        router.get(
            route('hr.master-data.index'),
            { category, search: '', status: '' },
            { preserveState: false, preserveScroll: true, replace: true },
        );
    };

    const submitFilters = (event) => {
        event.preventDefault();

        router.get(
            route('hr.master-data.index'),
            { category: selectedCategory, search, status },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const startEdit = (item) => {
        setEditingItem(item);
        form.clearErrors();
        form.setData({
            category: item.category,
            parent_id: item.parent_id ?? '',
            code: item.code ?? '',
            name: item.name,
            description: item.description ?? '',
            status: item.status,
            sort_order: item.sort_order ?? 0,
        });
    };

    const resetForm = () => {
        setEditingItem(null);
        form.clearErrors();
        form.setData(emptyForm(selectedCategory));
    };

    const submit = (event) => {
        event.preventDefault();

        if (editingItem) {
            form.put(route('hr.master-data.update', editingItem.id), {
                preserveScroll: true,
                onSuccess: resetForm,
            });

            return;
        }

        form.post(route('hr.master-data.store'), {
            preserveScroll: true,
            onSuccess: resetForm,
        });
    };

    const destroyItem = (item) => {
        if (!window.confirm(`Delete ${item.name}? This cannot be undone.`)) {
            return;
        }

        router.delete(route('hr.master-data.destroy', item.id), {
            preserveScroll: true,
        });
    };

    const cards = [
        { label: 'All Records', value: stats.total },
        { label: 'Active', value: stats.active },
        { label: 'Inactive', value: stats.inactive },
        { label: selectedCategoryLabel, value: stats.current_category },
    ];

    return (
        <HRLayout
            heading="Master Data Management"
            subheading="Maintain reusable setup lists for group-company HR operations."
        >
            <Head title="Master Data Management" />

            <div className="grid gap-4 md:grid-cols-4">
                {cards.map((card) => (
                    <section key={card.label} className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{card.label}</p>
                        <p className="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{card.value}</p>
                    </section>
                ))}
            </div>

            <div className="mt-8 grid gap-6 xl:grid-cols-[280px_1fr]">
                <aside className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p className="px-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Categories</p>
                    <div className="mt-4 space-y-1">
                        {options.categories.map((category) => (
                            <button
                                key={category.value}
                                type="button"
                                onClick={() => switchCategory(category.value)}
                                className={`flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left text-sm font-medium transition ${
                                    selectedCategory === category.value
                                        ? 'bg-slate-950 text-white'
                                        : 'text-slate-700 hover:bg-slate-50'
                                }`}
                            >
                                <span>{category.label}</span>
                            </button>
                        ))}
                    </div>
                </aside>

                <div className="space-y-6">
                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <form onSubmit={submitFilters} className="grid flex-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
                                <div>
                                    <label className="text-sm font-medium text-slate-700">Search {selectedCategoryLabel}</label>
                                    <TextInput
                                        value={search}
                                        onChange={(event) => setSearch(event.target.value)}
                                        placeholder="Name, code, or description"
                                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                                    />
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-slate-700">Status</label>
                                    <select
                                        value={status}
                                        onChange={(event) => setStatus(event.target.value)}
                                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                                    >
                                        <option value="">All statuses</option>
                                        {options.statuses.map((option) => (
                                            <option key={option} value={option}>{titleCase(option)}</option>
                                        ))}
                                    </select>
                                </div>
                                <SecondaryButton type="submit" className="h-fit rounded-2xl px-5 py-3">
                                    Filter
                                </SecondaryButton>
                            </form>
                        </div>

                        <div className="mt-8 overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead>
                                    <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-400">
                                        <th className="pb-4">Name</th>
                                        <th className="pb-4">Code</th>
                                        <th className="pb-4">Parent</th>
                                        <th className="pb-4">Status</th>
                                        <th className="pb-4">Order</th>
                                        <th className="pb-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {items.data.map((item) => (
                                        <tr key={item.id}>
                                            <td className="py-4">
                                                <p className="font-semibold text-slate-900">{item.name}</p>
                                                <p className="text-sm text-slate-500">{item.description || 'No description'}</p>
                                            </td>
                                            <td className="py-4 text-sm text-slate-600">{item.code || 'Not set'}</td>
                                            <td className="py-4 text-sm text-slate-600">{item.parent?.label ?? 'None'}</td>
                                            <td className="py-4">
                                                <span className={`rounded-full px-3 py-1 text-xs font-semibold uppercase ${
                                                    item.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
                                                }`}>
                                                    {item.status}
                                                </span>
                                            </td>
                                            <td className="py-4 text-sm text-slate-600">{item.sort_order}</td>
                                            <td className="py-4">
                                                <div className="flex justify-end gap-2">
                                                    {canEdit && (
                                                        <button
                                                            type="button"
                                                            onClick={() => startEdit(item)}
                                                            className="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                                        >
                                                            Edit
                                                        </button>
                                                    )}
                                                    {canDelete && (
                                                        <DangerButton
                                                            type="button"
                                                            onClick={() => destroyItem(item)}
                                                            className="rounded-xl px-4 py-2 text-sm normal-case tracking-normal"
                                                        >
                                                            Delete
                                                        </DangerButton>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {items.data.length === 0 && (
                            <div className="mt-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                                No master data records matched the current filter.
                            </div>
                        )}

                        <Pagination links={items.links} />
                    </section>

                    {(canCreate || editingItem) && (
                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 className="text-lg font-semibold text-slate-950">
                                    {editingItem ? `Edit ${editingItem.name}` : `Add ${selectedCategoryLabel}`}
                                </h2>
                                <p className="mt-1 text-sm text-slate-500">
                                    Codes are optional, but useful for payroll, attendance devices, and imports.
                                </p>
                            </div>
                            {editingItem && (
                                <button type="button" onClick={resetForm} className="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700">
                                    Cancel Edit
                                </button>
                            )}
                        </div>

                        <form onSubmit={submit} className="mt-6 grid gap-5 md:grid-cols-2">
                            <input type="hidden" value={form.data.category} />

                            <div>
                                <InputLabel htmlFor="name" value="Name" />
                                <TextInput
                                    id="name"
                                    value={form.data.name}
                                    onChange={(event) => form.setData('name', event.target.value)}
                                    className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                                />
                                <InputError className="mt-2" message={form.errors.name} />
                            </div>

                            <div>
                                <InputLabel htmlFor="code" value="Code" />
                                <TextInput
                                    id="code"
                                    value={form.data.code}
                                    onChange={(event) => form.setData('code', event.target.value)}
                                    className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                                />
                                <InputError className="mt-2" message={form.errors.code} />
                            </div>

                            {requiresParent && (
                                <div>
                                    <InputLabel htmlFor="parent_id" value="Parent" />
                                    <select
                                        id="parent_id"
                                        value={form.data.parent_id ?? ''}
                                        onChange={(event) => form.setData('parent_id', event.target.value)}
                                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                                    >
                                        <option value="">No parent</option>
                                        {options.parentOptions.map((option) => (
                                            <option key={option.id} value={option.id}>{option.label}</option>
                                        ))}
                                    </select>
                                    <InputError className="mt-2" message={form.errors.parent_id} />
                                </div>
                            )}

                            <div>
                                <InputLabel htmlFor="status" value="Status" />
                                <select
                                    id="status"
                                    value={form.data.status}
                                    onChange={(event) => form.setData('status', event.target.value)}
                                    className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                                >
                                    {options.statuses.map((option) => (
                                        <option key={option} value={option}>{titleCase(option)}</option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={form.errors.status} />
                            </div>

                            <div>
                                <InputLabel htmlFor="sort_order" value="Sort Order" />
                                <TextInput
                                    id="sort_order"
                                    type="number"
                                    value={form.data.sort_order}
                                    onChange={(event) => form.setData('sort_order', event.target.value)}
                                    className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                                />
                                <InputError className="mt-2" message={form.errors.sort_order} />
                            </div>

                            <div className="md:col-span-2">
                                <InputLabel htmlFor="description" value="Description" />
                                <textarea
                                    id="description"
                                    value={form.data.description}
                                    onChange={(event) => form.setData('description', event.target.value)}
                                    rows="3"
                                    className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                                />
                                <InputError className="mt-2" message={form.errors.description} />
                            </div>

                            <div className="md:col-span-2">
                                <PrimaryButton disabled={form.processing} className="rounded-2xl bg-slate-950 px-5 py-3">
                                    {form.processing ? 'Saving...' : editingItem ? 'Save Changes' : `Add ${selectedCategoryLabel}`}
                                </PrimaryButton>
                            </div>
                        </form>
                    </section>
                    )}
                </div>
            </div>
        </HRLayout>
    );
}
