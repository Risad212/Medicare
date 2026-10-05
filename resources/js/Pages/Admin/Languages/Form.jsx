import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function LanguageForm({ mode, language = null, labels, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        name: language?.name ?? '',
        code: language?.code ?? '',
        is_active: language?.isActive ?? true,
    });

    function submit(event) {
        event.preventDefault();
        if (isEdit) form.put(routes.update);
        else form.post(routes.store);
    }

    return (
        <AdminLayout title={isEdit ? labels.edit : labels.add} active="languages" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Settings</p><h1 className="mc-title">{isEdit ? labels.edit : labels.add}</h1></div></div>
            <form onSubmit={submit}>
                {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                <section className="mc-card"><div className="grid grid-cols-1 gap-4 p-4">
                    <Field form={form} name="name" label={labels.name} required placeholder={labels.namePlaceholder} />
                    <Field form={form} name="code" label={labels.code} required maxLength="10" pattern="[a-z]{2}(-[A-Z]{2})?" hint={labels.codeHint} />
                    <label htmlFor="language-active" className="flex items-center gap-2 text-sm font-medium text-ink-2"><input id="language-active" type="checkbox" checked={form.data.is_active} onChange={(event) => form.setData('is_active', event.target.checked)} />{labels.active}</label>
                </div></section>
                <div className="mc-formacts"><button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? labels.update : labels.save}</button><a href={routes.index} className="mc-btn ghost">{labels.back}</a></div>
            </form>
        </AdminLayout>
    );
}

function Field({ form, name, label, required = false, placeholder, maxLength, pattern, hint }) {
    const id = `language-${name}`;
    return <div><label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}{required && <span aria-hidden="true"> *</span>}</label><input id={id} name={name} type="text" className={inputClass} value={form.data[name]} onChange={(event) => form.setData(name, event.target.value)} required={required} placeholder={placeholder} maxLength={maxLength} pattern={pattern} aria-invalid={Boolean(form.errors[name])} />{hint && <p className="mc-hint">{hint}</p>}{form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}</div>;
}
