import { useForm, usePage } from '@inertiajs/react';
import FrontendLayout from '../../Components/FrontendLayout';
import { PageHeading, SectionTitle } from '../../Components/PublicPage';

const inputClass = 'w-full rounded-xl border border-line bg-white px-4 py-3 text-sm text-ink placeholder:text-muted/75 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/15';

function Field({ form, name, label, type = 'text', ...props }) {
    const id = `contact-${name}`;
    return (
        <div>
            <label htmlFor={id} className="mb-2 block text-sm font-semibold text-ink">{label}</label>
            <input id={id} name={name} type={type} className={inputClass}
                value={form.data[name]} onChange={(event) => form.setData(name, event.target.value)}
                aria-invalid={Boolean(form.errors[name])} aria-describedby={form.errors[name] ? `${id}-error` : undefined}
                required {...props} />
            {form.errors[name] && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-700">{form.errors[name]}</p>}
        </div>
    );
}

export default function Contact({ seo }) {
    const { site, frontendRoutes } = usePage().props;
    const form = useForm({ name: '', email: '', phone: '', subject: '', message: '' });

    function submit(event) {
        event.preventDefault();
        form.post(frontendRoutes.contactSubmit);
    }

    return (
        <FrontendLayout title={seo?.title || 'Contact'} seo={seo}>
            <PageHeading title="A good conversation starts here" description="Questions about a service or need help finding your next step? Our team is ready to listen." />
            <section className="mx-auto grid max-w-7xl gap-10 px-5 py-16 sm:px-8 sm:py-20 lg:grid-cols-[.8fr_1.2fr]">
                <div>
                    <SectionTitle eyebrow="Get in touch" title="We’re here to help" description="Reach out using the form or contact us directly. We’ll get back to you as soon as we can." />
                    <div className="space-y-4">
                        {[
                            ['Location', site.address, 'geo-alt'],
                            ['Phone', site.phone, 'telephone'],
                            ['Email', site.email, 'envelope'],
                            ['Working hours', site.workingHours, 'clock'],
                        ].filter(([, value]) => value).map(([label, value, icon]) => (
                            <div key={label} className="flex gap-4 rounded-xl border border-line bg-white p-4">
                                <span className="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-teal-pale text-teal"><i className={`bi bi-${icon}`} aria-hidden="true" /></span>
                                <div><p className="m-0 text-xs font-bold uppercase tracking-wider text-muted">{label}</p><p className="mb-0 mt-1 text-sm font-semibold text-ink">{value}</p></div>
                            </div>
                        ))}
                    </div>
                </div>
                <form onSubmit={submit} className="rounded-2xl border border-line bg-white p-6 sm:p-9">
                    <h2 className="font-display text-2xl">Send us a message</h2>
                    {form.wasSuccessful && <div role="status" className="mt-4 rounded-xl bg-teal-pale p-4 text-sm text-teal-dark">Your message has been sent. Thank you for contacting us.</div>}
                    {form.hasErrors && <div role="alert" className="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-800">Please correct the highlighted fields.</div>}
                    <div className="mt-6 grid gap-5 sm:grid-cols-2">
                        <Field form={form} name="name" label="Your name" autoComplete="name" />
                        <Field form={form} name="email" label="Email address" type="email" autoComplete="email" />
                        <Field form={form} name="phone" label="Phone number" type="tel" autoComplete="tel" />
                        <Field form={form} name="subject" label="Subject" />
                        <div className="sm:col-span-2">
                            <label htmlFor="contact-message" className="mb-2 block text-sm font-semibold text-ink">Message</label>
                            <textarea id="contact-message" name="message" rows="5" className={inputClass} value={form.data.message}
                                onChange={(event) => form.setData('message', event.target.value)} aria-invalid={Boolean(form.errors.message)} required />
                            {form.errors.message && <p role="alert" className="mt-1 text-xs text-red-700">{form.errors.message}</p>}
                        </div>
                    </div>
                    <button type="submit" disabled={form.processing} className="mt-6 rounded-full bg-teal px-6 py-3 text-sm font-bold text-white hover:bg-teal-dark disabled:opacity-60">
                        {form.processing ? 'Sending…' : 'Send message'} <span className="ml-2" aria-hidden="true">↗</span>
                    </button>
                </form>
            </section>
            {site.mapEmbedUrl && <section className="h-[360px] border-y border-line"><iframe title="MediCare location map" src={site.mapEmbedUrl} className="h-full w-full border-0" loading="lazy" referrerPolicy="no-referrer-when-downgrade" allowFullScreen /></section>}
        </FrontendLayout>
    );
}
