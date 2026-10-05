import { Link } from '@inertiajs/react';
import FrontendLayout, { publicImage } from './FrontendLayout';

export function PageHeading({ eyebrow, title, description, image }) {
    return (
        <section className="relative isolate overflow-hidden bg-teal-dark text-white">
            {image && <img src={publicImage(image)} alt="" className="absolute inset-0 -z-20 h-full w-full object-cover opacity-20" />}
            <div className="absolute inset-0 -z-10 bg-gradient-to-r from-teal-dark via-teal-dark/90 to-teal-dark/55" />
            <div className="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20">
                <p className="text-xs font-bold uppercase tracking-[0.24em] text-gold">{eyebrow || 'MediCare Hospital'}</p>
                <h1 className="mt-4 max-w-3xl font-display text-4xl font-medium leading-tight sm:text-6xl">{title}</h1>
                {description && <p className="mt-5 max-w-2xl text-base leading-7 text-white/75">{description}</p>}
            </div>
        </section>
    );
}

export function SectionTitle({ eyebrow, title, description, centered = false }) {
    return (
        <div className={`mb-9 ${centered ? 'text-center' : ''}`}>
            <p className="text-xs font-bold uppercase tracking-[0.22em] text-teal">{eyebrow}</p>
            <h2 className="mt-3 font-display text-3xl font-medium leading-tight text-ink sm:text-4xl">{title}</h2>
            {description && <p className="mt-3 max-w-2xl text-sm leading-7 text-muted">{description}</p>}
        </div>
    );
}

export function ServiceCards({ services = [], routes, limit }) {
    const visible = limit ? services.slice(0, limit) : services;
    return (
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {visible.map((service, index) => (
                <article key={service.id} className="group rounded-2xl border border-line bg-white p-7 transition hover:-translate-y-1 hover:border-teal/50 hover:shadow-xl">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-pale text-teal">
                        {service.icon
                            ? <img src={publicImage(service.icon)} alt="" className="h-8 w-8 object-contain" />
                            : <span className="font-display text-2xl">{['+', '♡', '✳'][index % 3]}</span>}
                    </div>
                    <h3 className="mt-5 font-display text-2xl font-medium text-ink">{service.title}</h3>
                    <p className="mt-3 text-sm leading-7 text-muted">{service.excerpt || service.description}</p>
                    {service.buttonUrl
                        ? <a href={service.buttonUrl} className="mt-5 inline-flex items-center gap-2 text-sm font-bold text-teal no-underline hover:text-teal-dark">{service.buttonText || 'Explore service'} <span aria-hidden="true">↗</span></a>
                        : <Link href={service.slug ? `/service/${service.slug}` : routes.services} className="mt-5 inline-flex items-center gap-2 text-sm font-bold text-teal no-underline hover:text-teal-dark">{service.buttonText || 'Explore service'} <span aria-hidden="true">↗</span></Link>}
                </article>
            ))}
        </div>
    );
}

export function DoctorCards({ doctors = [] }) {
    return (
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {doctors.map((doctor) => (
                <article key={doctor.id} className="overflow-hidden rounded-2xl border border-line bg-white">
                    <Link href={`/doctor/${doctor.id}`} className="block overflow-hidden bg-paper-dark">
                        {doctor.image
                            ? <img src={publicImage(doctor.image)} alt={doctor.name} className="h-72 w-full object-cover transition duration-500 hover:scale-[1.03]" />
                            : <div className="grid h-72 place-items-center text-6xl text-teal/50"><i className="bi bi-person-badge" /></div>}
                    </Link>
                    <div className="p-6">
                        <p className="text-xs font-bold uppercase tracking-widest text-teal">{doctor.department || doctor.specialist || 'Medical specialist'}</p>
                        <h3 className="mt-2 font-display text-2xl font-medium">{doctor.name}</h3>
                        {doctor.degree && <p className="mt-1 text-sm text-muted">{doctor.degree}</p>}
                        <Link href={`/doctor/${doctor.id}#book-appointment`} className="mt-5 inline-flex items-center gap-2 text-sm font-bold text-teal no-underline">Book a visit <span aria-hidden="true">↗</span></Link>
                    </div>
                </article>
            ))}
        </div>
    );
}

export function BlogCards({ blogs = [] }) {
    return (
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {blogs.map((blog) => (
                <article key={blog.id} className="overflow-hidden rounded-2xl border border-line bg-white">
                    <Link href={`/blog/${blog.slug}`} className="block aspect-[16/10] overflow-hidden bg-paper-dark">
                        {blog.image && <img src={publicImage(blog.image)} alt={blog.title} className="h-full w-full object-cover transition duration-500 hover:scale-[1.03]" />}
                    </Link>
                    <div className="p-6">
                        <p className="text-xs font-semibold text-muted">{blog.author ? `By ${blog.author} · ` : ''}{blog.date}</p>
                        <h3 className="mt-3 font-display text-2xl font-medium leading-snug"><Link href={`/blog/${blog.slug}`} className="text-ink no-underline hover:text-teal">{blog.title}</Link></h3>
                        <p className="mt-3 text-sm leading-7 text-muted">{blog.excerpt}</p>
                        <Link href={`/blog/${blog.slug}`} className="mt-4 inline-flex items-center gap-2 text-sm font-bold text-teal no-underline">Read article <span aria-hidden="true">↗</span></Link>
                    </div>
                </article>
            ))}
        </div>
    );
}

export function Pagination({ pagination }) {
    if (!pagination || pagination.lastPage <= 1) return null;

    const labelText = (label) => label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&amp;/g, '&')
        .replace(/<[^>]*>/g, '');

    return (
        <nav aria-label="Pagination" className="mt-10 flex items-center justify-center gap-2">
            {pagination.links.map((link) => link.url ? (
                <Link key={`${link.label}-${link.url}`} href={link.url} aria-current={link.active ? 'page' : undefined}
                    className={`grid h-10 min-w-10 place-items-center rounded-full px-3 text-sm font-semibold no-underline ${link.active ? 'bg-teal text-white' : 'border border-line text-ink hover:border-teal hover:text-teal'}`}
                    aria-label={labelText(link.label)}>
                    {labelText(link.label)}
                </Link>
            ) : (
                <span key={link.label} className="grid h-10 min-w-10 place-items-center rounded-full px-3 text-sm text-muted" aria-hidden="true">
                    {labelText(link.label)}
                </span>
            ))}
        </nav>
    );
}

export { FrontendLayout };
