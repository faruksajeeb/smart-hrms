import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

export default function PermissionForm({
    data,
    setData,
    errors,
    processing,
    submitLabel,
    isProtected = false,
}) {
    const fullPermissionName = data.group_name
        ? `${data.group_name}.${data.action}`
        : data.action;

    return (
        <div className="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">
                    Permission Details
                </h2>
                <p className="mt-2 text-sm text-slate-500">
                    Use permissions for granular backend authorization and
                    permission-aware navigation.
                </p>

                <div className="mt-8">
                    <InputLabel htmlFor="group_name" value="Permission Group" />
                    <TextInput
                        id="group_name"
                        value={data.group_name}
                        disabled={isProtected}
                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 disabled:cursor-not-allowed disabled:bg-slate-100"
                        onChange={(e) => setData('group_name', e.target.value)}
                        placeholder="employee"
                    />
                    <InputError className="mt-2" message={errors.group_name} />
                    <p className="mt-2 text-sm text-slate-500">
                        Group names help organize permissions like <span className="font-semibold">employee</span>.
                    </p>
                </div>

                <div className="mt-8">
                    <InputLabel htmlFor="action" value="Permission Action" />
                    <TextInput
                        id="action"
                        value={data.action}
                        disabled={isProtected}
                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 disabled:cursor-not-allowed disabled:bg-slate-100"
                        onChange={(e) => setData('action', e.target.value)}
                        placeholder="add"
                    />
                    <InputError className="mt-2" message={errors.action} />
                    <p className="mt-2 text-sm text-slate-500">
                        The final permission will be stored as <span className="font-semibold">{fullPermissionName || 'group.action'}</span>.
                    </p>
                    {isProtected && (
                        <p className="mt-2 text-sm text-slate-500">
                            Built-in permissions are protected because route
                            middleware and core access rules rely on them.
                        </p>
                    )}
                </div>

                <div className="mt-8">
                    <PrimaryButton
                        disabled={processing || isProtected}
                        className="rounded-2xl bg-slate-950 px-5 py-3"
                    >
                        {processing ? 'Saving permission...' : submitLabel}
                    </PrimaryButton>
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-sm">
                <h2 className="text-lg font-semibold">Permission Notes</h2>
                <div className="mt-6 space-y-3 text-sm leading-6 text-slate-300">
                    <p className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        Custom permissions are best for module-level expansion as your HR platform grows.
                    </p>
                    <p className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        Keep group names short and action names clear so the final permission stays easy to understand.
                    </p>
                </div>
            </section>
        </div>
    );
}
