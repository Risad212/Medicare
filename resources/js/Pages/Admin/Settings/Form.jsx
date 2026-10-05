import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-teal focus:ring-2 focus:ring-teal/15';

const settings = {
    general: [
        {
            title: 'Site identity',
            description: 'Logo, favicon, and name.',
            fields: [
                { name: 'site_name', label: 'Site name', full: true },
                { name: 'logo', label: 'Site logo (200 x 60 px)', type: 'file', full: true, accept: 'image/png,image/jpeg,image/webp,image/svg+xml' },
                { name: 'favicon', label: 'Favicon (32 x 32 px)', type: 'file', full: true, accept: 'image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon' },
            ],
        },
        {
            title: 'Header',
            description: 'Address, hours, and social links.',
            fields: [
                { name: 'address', label: 'Address', type: 'textarea', rows: 3, full: true },
                { name: 'working_hours', label: 'Working hours', type: 'textarea', rows: 3, full: true },
                { name: 'facebook', label: 'Facebook URL', type: 'url' },
                { name: 'twitter', label: 'Twitter / X URL', type: 'url' },
                { name: 'linkedin', label: 'LinkedIn URL', type: 'url' },
                { name: 'youtube', label: 'YouTube URL', type: 'url' },
                { name: 'map_embed_url', label: 'Google Maps embed URL', type: 'textarea', rows: 2, full: true, hint: 'Paste the src URL from Google Maps > Share > Embed a map.' },
            ],
        },
        {
            title: 'Footer',
            description: 'Contact information and copyright.',
            fields: [
                { name: 'footer_logo', label: 'Footer logo', type: 'file', full: true, accept: 'image/png,image/jpeg,image/webp,image/svg+xml' },
                { name: 'phone', label: 'Phone number', type: 'tel' },
                { name: 'email', label: 'Email', type: 'email' },
                { name: 'footer_description', label: 'Short description', type: 'textarea', rows: 4, full: true },
                { name: 'copyright', label: 'Copyright text', full: true },
            ],
        },
    ],
    home: [
        {
            title: 'Homepage about section',
            description: 'Introductory copy and supporting images.',
            fields: [
                { name: 'about_title', label: 'Title' },
                { name: 'about_button_text', label: 'Button text' },
                { name: 'about_description', label: 'Description', type: 'textarea', rows: 4, full: true },
                { name: 'about_image_one', label: 'Left top image (370 x 270 px)', type: 'file', accept: 'image/png,image/jpeg,image/webp' },
                { name: 'about_image_two', label: 'Left bottom image (370 x 270 px)', type: 'file', accept: 'image/png,image/jpeg,image/webp' },
                { name: 'about_image_three', label: 'Right image (501 x 750 px)', type: 'file', accept: 'image/png,image/jpeg,image/webp' },
            ],
        },
        {
            title: 'Homepage counters',
            description: 'Statistics shown on the homepage.',
            fields: [1, 2, 3, 4].flatMap((number) => {
                const key = ['one', 'two', 'three', 'four'][number - 1];
                return [
                    { name: `counter_${key}_text`, label: `Counter ${number} — label` },
                    { name: `counter_${key}_number`, label: `Counter ${number} — number`, type: 'number', min: 0 },
                ];
            }),
        },
    ],
    about: [
        {
            title: 'Intro section',
            description: 'Heading, copy, call to action, and images.',
            fields: [
                { name: 'subtitle', label: 'Subtitle' },
                { name: 'title', label: 'Title' },
                { name: 'tagline', label: 'Tagline', full: true },
                { name: 'description', label: 'Description', type: 'textarea', rows: 4, full: true },
                { name: 'button_text', label: 'Button text' },
                { name: 'button_url', label: 'Button URL' },
                { name: 'image_one', label: 'Image one', type: 'file', accept: 'image/png,image/jpeg,image/webp' },
                { name: 'image_two', label: 'Image two', type: 'file', accept: 'image/png,image/jpeg,image/webp' },
            ],
        },
        {
            title: 'Highlights',
            description: 'Mission, planning, and vision blocks.',
            fields: [
                { name: 'mission_title', label: 'Mission title' },
                { name: 'mission_description', label: 'Mission description', type: 'textarea', rows: 3, full: true },
                { name: 'planning_title', label: 'Planning title' },
                { name: 'planning_description', label: 'Planning description', type: 'textarea', rows: 3, full: true },
                { name: 'vision_title', label: 'Vision title' },
                { name: 'vision_description', label: 'Vision description', type: 'textarea', rows: 3, full: true },
            ],
        },
    ],
    service: [
        {
            title: 'Emergency section',
            description: 'Heading, copy, contact details, and image.',
            fields: [
                { name: 'emergency_subtitle', label: 'Subtitle' },
                { name: 'emergency_title', label: 'Title' },
                { name: 'emergency_description', label: 'Description', type: 'textarea', rows: 4, full: true },
                { name: 'emergency_phone', label: 'Phone', type: 'tel' },
                { name: 'emergency_email', label: 'Email', type: 'email' },
                { name: 'emergency_image', label: 'Image', type: 'file', full: true, accept: 'image/png,image/jpeg,image/webp' },
            ],
        },
        {
            title: 'Prevention section',
            description: 'Page heading and eight prevention items.',
            fields: [
                { name: 'prevention_subtitle', label: 'Subtitle' },
                { name: 'prevention_title', label: 'Title' },
                ...Array.from({ length: 8 }, (_, index) => index + 1).flatMap((number) => [
                    { name: `prevention_${number}_title`, label: `Item ${number} — title` },
                    { name: `prevention_${number}_desc`, label: `Item ${number} — description`, type: 'textarea', rows: 2 },
                ]),
            ],
        },
    ],
};

function errorId(name) {
    return `setting-${name}-error`;
}

function Field({ form, field, storageUrl, currentValue = null }) {
    const { name, label, type = 'text', full = false, hint, rows = 4, ...attributes } = field;
    const error = form.errors[name];
    const id = `setting-${name}`;
    const existingImage = type === 'file' && currentValue
        ? `${storageUrl}/${currentValue.replace(/^\/+/, '')}`
        : null;
    const common = {
        id,
        name,
        className: `${inputClass} ${error ? 'border-red-500' : ''}`,
        'aria-invalid': Boolean(error),
        'aria-describedby': error ? errorId(name) : hint ? `${id}-hint` : undefined,
    };

    return (
        <div className={`mc-f ${full ? 'full' : ''}`}>
            <label htmlFor={id}>{label}</label>
            {type === 'textarea' ? (
                <textarea {...common} rows={rows} value={form.data[name] ?? ''} onChange={(event) => form.setData(name, event.target.value)} />
            ) : (
                <input
                    {...common}
                    {...attributes}
                    type={type}
                    value={type === 'file' ? undefined : form.data[name] ?? ''}
                    onChange={(event) => form.setData(name, type === 'file' ? event.target.files?.[0] ?? null : event.target.value)}
                />
            )}
            {existingImage && <img src={existingImage} alt={`Current ${label.toLowerCase()}`} className="mt-2 max-h-24 max-w-48 rounded-lg border border-line object-contain" />}
            {error && <p id={errorId(name)} role="alert" className="mt-1 text-xs text-red-t">{error}</p>}
            {!error && hint && <span id={`${id}-hint`} className="mc-hint">{hint}</span>}
        </div>
    );
}

function SeoForm({ seo = {}, seoSubmitUrl, flash = {} }) {
    const form = useForm({
        page: seo.page ?? '',
        meta_title: seo.meta_title ?? '',
        meta_description: seo.meta_description ?? '',
        meta_keywords: seo.meta_keywords ?? '',
    });

    function submit(event) {
        event.preventDefault();
        form.post(seoSubmitUrl, { preserveScroll: true });
    }

    return (
        <form onSubmit={submit} className="mb-5">
            <section className="mc-sec">
                <div className="mc-sec-hd"><span className="no">SEO</span><h3>Search metadata</h3><p>How this page appears in search results.</p></div>
                <div className="mc-sec-bd">
                    {flash.seoSuccess && <div role="status" className="full rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{flash.seoSuccess}</div>}
                    {flash.seoError && <div role="alert" className="full rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{flash.seoError}</div>}
                    {[
                        { name: 'meta_title', label: 'Meta title', type: 'text' },
                        { name: 'meta_description', label: 'Meta description', type: 'textarea' },
                        { name: 'meta_keywords', label: 'Meta keywords', type: 'text', hint: 'Separate keywords with commas.' },
                    ].map((field) => (
                        <Field
                            key={field.name}
                            form={form}
                            storageUrl=""
                            field={{ ...field, full: true, rows: 4 }}
                        />
                    ))}
                </div>
                <div className="border-t border-line-2 px-4 py-3">
                    <button type="submit" className="mc-btn" disabled={form.processing}>
                        {form.processing ? 'Saving…' : 'Save SEO settings'}
                    </button>
                </div>
            </section>
        </form>
    );
}

export default function SettingsForm({
    type,
    title,
    setting = {},
    seo,
    submitUrl,
    seoSubmitUrl,
    storageUrl = '',
    serviceCardsUrl,
    routes,
}) {
    const { flash = {} } = usePage().props;
    const sections = settings[type] ?? [];
    const fields = sections.flatMap((section) => section.fields);
    const initialValues = Object.fromEntries(fields.map(({ name, type: fieldType }) => [
        name,
        fieldType === 'file' ? null : setting[name] ?? '',
    ]));
    const form = useForm(initialValues);
    const active = type === 'seo' ? `settings-seo-${seo?.page ?? 'page'}` : `settings-${type}`;

    function submit(event) {
        event.preventDefault();
        form.post(submitUrl, { forceFormData: true, preserveScroll: true });
    }

    return (
        <AdminLayout title={title} active={active} routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Website configuration</p>
                    <h1 className="mc-title">{type === 'seo' ? title.replace(' SEO', '') : title.split(' ')[0]} <em>{type === 'seo' ? 'SEO' : 'Settings'}</em></h1>
                    <p className="mc-sub">Manage the content and details displayed across the public website.</p>
                </div>
                {type === 'service' && serviceCardsUrl && (
                    <div className="mc-head-acts"><a href={serviceCardsUrl} className="mc-btn ghost"><i aria-hidden="true" className="bi bi-grid" /> Manage service cards</a></div>
                )}
            </div>

            {sections.length > 0 && (
                <form onSubmit={submit} encType="multipart/form-data">
                    {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the highlighted fields and try again.</div>}
                    {form.recentlySuccessful && !flash.success && <div role="status" className="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">Settings saved successfully.</div>}
                    {sections.map((section, index) => (
                        <section key={section.title} className="mc-sec mb-4">
                            <div className="mc-sec-hd"><span className="no">{String(index + 1).padStart(2, '0')}</span><h3>{section.title}</h3><p>{section.description}</p></div>
                            <div className="mc-sec-bd">
                                {section.fields.map((field) => <Field key={field.name} form={form} field={field} storageUrl={storageUrl} currentValue={setting[field.name]} />)}
                            </div>
                        </section>
                    ))}
                    <div className="mc-formacts">
                        <button type="submit" className="mc-btn" disabled={form.processing}>
                            <i aria-hidden="true" className="bi bi-save" /> {form.processing ? 'Saving…' : 'Save settings'}
                        </button>
                    </div>
                </form>
            )}

            {seo && <SeoForm seo={seo} seoSubmitUrl={seoSubmitUrl} flash={flash} />}
        </AdminLayout>
    );
}
