import { Head, Link } from '@inertiajs/react';
import FrontendLayout, { publicImage } from '../../Components/FrontendLayout';
import { BlogCards, DoctorCards, SectionTitle, ServiceCards } from '../../Components/PublicPage';

const features = [
    ['01', 'Quality & safety', 'Thoughtful clinical standards and care at every step.'],
    ['02', 'Modern technology', 'Clear answers supported by modern diagnostics.'],
    ['03', 'Care that listens', 'Experienced people who take time to understand.'],
];

const testimonials = [
    ['“The doctors explained everything clearly, and the care team watched over my father every step of the way.”', 'Rahim Uddin', 'Recovered heart patient'],
    ['“Booking took a minute online. We saw the pediatrician the same morning and felt at ease.”', 'Fatema Begum', 'Mother of two'],
    ['“From diagnosis to follow-ups, the team listens and helps me manage my health with confidence.”', 'Karim Sheikh', 'Regular patient'],
];

export default function Home({ home = {}, sliders = [], doctors = [], recentBlogs = [], services = [], seo }) {
    const hero = sliders[0] || {};
    const counters = [
        [home.counter_one_number, home.counter_one_text],
        [home.counter_two_number, home.counter_two_text],
        [home.counter_three_number, home.counter_three_text],
        [home.counter_four_number, home.counter_four_text],
    ].filter(([number, text]) => number || text);

    return (
        <FrontendLayout title={seo?.title || 'Care that meets you where you are'} seo={seo}>
            <Head>
                <meta head-key="og:type" property="og:type" content="website" />
            </Head>
            <section className="relative overflow-hidden bg-paper-dark">
                <div className="mx-auto grid max-w-7xl items-center gap-10 px-5 py-12 sm:px-8 sm:py-20 lg:grid-cols-[1.02fr_.98fr] lg:gap-16">
                    <div className="relative z-10">
                        <p className="flex items-center gap-3 text-xs font-bold uppercase tracking-[.24em] text-teal">
                            <span className="h-px w-8 bg-gold" /> MediCare Hospital · Here for you
                        </p>
                        <h1 className="mt-6 max-w-3xl font-display text-5xl font-medium leading-[1.03] text-ink sm:text-6xl lg:text-7xl">
                            {hero.title || home.about_title || 'Care with clarity. Expertise with compassion.'}
                        </h1>
                        <p className="mt-6 max-w-xl text-base leading-8 text-muted">
                            {hero.description || home.about_description || 'Meet a team that listens, explains, and supports you through every step of your care.'}
                        </p>
                        <div className="mt-8 flex flex-wrap items-center gap-4">
                            <Link href="/appointment" className="inline-flex items-center gap-3 rounded-full bg-teal px-6 py-3.5 text-sm font-bold text-white no-underline transition hover:bg-teal-dark">
                                Book an appointment <span aria-hidden="true">↗</span>
                            </Link>
                            <Link href="/service" className="inline-flex items-center gap-2 py-3 text-sm font-bold text-ink no-underline hover:text-teal">
                                Explore our services <span aria-hidden="true">→</span>
                            </Link>
                        </div>
                        <div className="mt-12 flex items-center gap-4 border-t border-line pt-6">
                            <div className="flex -space-x-3" aria-hidden="true">
                                {doctors.slice(0, 3).map((doctor) => (
                                    <span key={doctor.id} className="grid h-11 w-11 place-items-center overflow-hidden rounded-full border-2 border-paper-dark bg-teal-pale text-teal">
                                        {doctor.image ? <img src={publicImage(doctor.image)} alt="" className="h-full w-full object-cover" /> : <i className="bi bi-person" />}
                                    </span>
                                ))}
                            </div>
                            <p className="m-0 text-sm text-muted"><strong className="text-ink">{doctors.length || 'Our'} specialists</strong><br />ready to help you feel better</p>
                        </div>
                    </div>
                    <div className="relative mx-auto w-full max-w-xl lg:ml-auto">
                        <div className="absolute -right-6 -top-7 h-40 w-40 rounded-full border border-gold/60 sm:-right-10 sm:-top-10 sm:h-56 sm:w-56" />
                        <div className="relative aspect-[4/4.2] overflow-hidden rounded-[46%_46%_1.5rem_1.5rem] bg-teal/15">
                            {hero.backgroundImage
                                ? <img src={publicImage(hero.backgroundImage)} alt="" className="h-full w-full object-cover" />
                                : <img src="/frontend-assets/media/home/slider-1.jpg" alt="" className="h-full w-full object-cover" />}
                            <div className="absolute inset-0 bg-gradient-to-t from-ink/60 via-transparent to-transparent" />
                            <div className="absolute bottom-5 left-5 right-5 flex items-end justify-between gap-4 rounded-2xl border border-white/20 bg-white/90 p-5 backdrop-blur sm:bottom-8 sm:left-8 sm:right-8">
                                <div>
                                    <p className="text-xs font-bold uppercase tracking-widest text-teal">Your health, in good hands</p>
                                    <p className="mb-0 mt-1 font-display text-xl text-ink">Care made personal</p>
                                </div>
                                <span className="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-teal text-xl text-white" aria-hidden="true">+</span>
                            </div>
                        </div>
                        <div className="absolute -left-4 top-1/3 hidden rounded-xl bg-white px-4 py-3 shadow-xl sm:block">
                            <p className="m-0 text-xs font-bold text-ink"><span className="mr-2 text-teal">●</span>Appointments available</p>
                            <p className="m-0 mt-1 text-xs text-muted">A good first step starts here.</p>
                        </div>
                    </div>
                </div>
                <div className="absolute -bottom-20 -left-20 h-60 w-60 rounded-full bg-teal/5" />
            </section>

            <section className="mx-auto grid max-w-7xl gap-4 px-5 py-10 sm:px-8 md:grid-cols-3">
                {features.map(([number, title, text]) => (
                    <article key={number} className="flex gap-4 border-b border-line pb-5 md:border-b-0 md:border-r md:pb-0 md:pr-6 last:border-0">
                        <span className="font-display text-sm text-gold">{number}</span>
                        <div><h2 className="m-0 text-sm font-bold text-ink">{title}</h2><p className="mb-0 mt-2 text-sm leading-6 text-muted">{text}</p></div>
                    </article>
                ))}
            </section>

            <section className="bg-white px-5 py-16 sm:px-8 sm:py-20">
                <div className="mx-auto max-w-7xl">
                    <div className="grid items-center gap-8 lg:grid-cols-[.8fr_1.2fr]">
                        <div>
                            <SectionTitle eyebrow="About MediCare" title={home.about_title || 'A better kind of care starts with listening.'} />
                            <p className="text-sm leading-8 text-muted">{home.about_description}</p>
                            <Link href="/about" className="mt-3 inline-flex items-center gap-2 font-bold text-teal no-underline">Get to know us <span aria-hidden="true">↗</span></Link>
                        </div>
                        <div className="grid grid-cols-2 gap-3 sm:gap-5">
                            {[home.about_image_one, home.about_image_two, home.about_image_three].filter(Boolean).map((image, index) => (
                                <img key={image} src={publicImage(image)} alt="" className={`h-48 w-full rounded-2xl object-cover sm:h-64 ${index === 1 ? 'mt-8' : ''}`} />
                            ))}
                            {!home.about_image_one && <div className="col-span-2 grid min-h-48 place-items-center rounded-2xl bg-teal-pale text-teal"><i className="bi bi-heart-pulse text-5xl" /></div>}
                        </div>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20">
                <SectionTitle eyebrow="Our services" title="Thoughtful care for every stage of life" description="From the first consultation to ongoing treatment, find the service that fits your needs." />
                <ServiceCards services={services} routes={{ services: '/service' }} limit={6} />
                <div className="mt-8 text-center"><Link href="/service" className="inline-flex rounded-full border border-teal px-6 py-3 text-sm font-bold text-teal no-underline hover:bg-teal-pale">View all services</Link></div>
            </section>

            {counters.length > 0 && (
                <section className="bg-teal-dark text-white">
                    <div className="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-5 py-12 sm:px-8 md:grid-cols-4">
                        {counters.map(([number, text], index) => <div key={`${text}-${index}`} className="border-l border-gold/60 pl-4"><p className="font-display text-4xl">{number}</p><p className="mt-1 text-sm text-white/70">{text}</p></div>)}
                    </div>
                </section>
            )}

            <section className="bg-paper-dark px-5 py-16 sm:px-8 sm:py-20">
                <div className="mx-auto max-w-7xl">
                    <SectionTitle eyebrow="Our doctors" title="Experienced specialists, personal care" description="Get to know the people who will be with you on your care journey." />
                    <DoctorCards doctors={doctors} />
                    <div className="mt-8 text-center"><Link href="/doctor" className="inline-flex rounded-full border border-teal px-6 py-3 text-sm font-bold text-teal no-underline hover:bg-white">Meet all doctors</Link></div>
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20">
                <SectionTitle eyebrow="Patient stories" title="Care remembered by the people who matter" />
                <div className="grid gap-5 md:grid-cols-3">
                    {testimonials.map(([quote, name, role]) => <figure key={name} className="m-0 rounded-2xl border border-line bg-white p-6"><span className="font-display text-4xl text-gold" aria-hidden="true">“</span><blockquote className="mt-2 text-sm leading-7 text-ink">{quote.replace(/^“|”$/g, '')}</blockquote><figcaption className="mt-5 border-t border-line pt-4"><strong className="block text-sm">{name}</strong><span className="text-xs text-muted">{role}</span></figcaption></figure>)}
                </div>
            </section>

            <section className="bg-teal-dark px-5 py-16 text-white sm:px-8 sm:py-20">
                <div className="mx-auto max-w-7xl">
                    <SectionTitle eyebrow="From the journal" title="Helpful perspectives for healthier days" />
                    <BlogCards blogs={recentBlogs} />
                    <div className="mt-8"><Link href="/blog" className="text-sm font-bold text-white underline decoration-gold underline-offset-4">Read all articles ↗</Link></div>
                </div>
            </section>
        </FrontendLayout>
    );
}
