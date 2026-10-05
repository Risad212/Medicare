import { Link } from '@inertiajs/react';
import FrontendLayout, { publicImage } from '../../Components/FrontendLayout';
import { DoctorCards, PageHeading, SectionTitle } from '../../Components/PublicPage';

export default function About({ about = {}, doctors = [], seo }) {
    const values = [
        [about.mission_title || 'Our mission', about.mission_description || about.mission],
        [about.vision_title || 'Our vision', about.vision_description || about.vision],
        [about.value_title || 'Our values', about.value_description || about.values],
    ].filter(([, text]) => text);

    return (
        <FrontendLayout title={seo?.title || 'About us'} seo={seo}>
            <PageHeading title={about.subtitle || 'Care built around people'} description={about.tagline || 'Meet the people, principles, and promise behind MediCare.'} />
            <section className="mx-auto grid max-w-7xl items-center gap-12 px-5 py-16 sm:px-8 sm:py-20 lg:grid-cols-2">
                <div className="relative min-h-[380px]">
                    <div className="absolute left-0 top-0 h-[75%] w-[72%] overflow-hidden rounded-[46%_46%_1.5rem_1.5rem] bg-teal-pale">
                        <img src={publicImage(about.image_one, '/frontend-assets/media/about/about-1.jpg')} alt="MediCare team providing care" className="h-full w-full object-cover" />
                    </div>
                    <div className="absolute bottom-0 right-0 h-[55%] w-[54%] overflow-hidden rounded-2xl border-8 border-paper bg-paper-dark">
                        <img src={publicImage(about.image_two, '/frontend-assets/media/about/about-2.jpg')} alt="A calm space at MediCare" className="h-full w-full object-cover" />
                    </div>
                    <span className="absolute right-12 top-6 grid h-20 w-20 place-items-center rounded-full bg-gold font-display text-3xl text-white">+</span>
                </div>
                <div>
                    <SectionTitle eyebrow={about.subtitle || 'About MediCare'} title={about.title || 'Compassionate care. Exceptional expertise.'} />
                    <p className="font-display text-xl text-teal">{about.tagline || 'Where technology meets humanity.'}</p>
                    <p className="mt-5 text-sm leading-8 text-muted">{about.description}</p>
                    {about.button_url && <a href={about.button_url} className="mt-4 inline-flex rounded-full bg-teal px-6 py-3 text-sm font-bold text-white no-underline hover:bg-teal-dark">{about.button_text || 'Learn more'} <span className="ml-2" aria-hidden="true">↗</span></a>}
                </div>
            </section>
            {values.length > 0 && (
                <section className="bg-paper-dark px-5 py-16 sm:px-8 sm:py-20">
                    <div className="mx-auto max-w-7xl">
                        <SectionTitle eyebrow="What guides us" title="A promise in every interaction" centered />
                        <div className="grid gap-5 md:grid-cols-3">
                            {values.map(([title, text], index) => <article key={title} className="rounded-2xl border border-line bg-white p-7"><span className="font-display text-sm text-gold">0{index + 1}</span><h3 className="mt-4 font-display text-2xl">{title}</h3><p className="mt-3 text-sm leading-7 text-muted">{text}</p></article>)}
                        </div>
                    </div>
                </section>
            )}
            <section className="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20">
                <div className="flex flex-wrap items-end justify-between gap-5">
                    <SectionTitle eyebrow="Our people" title="Expertise, with a human touch" />
                    <Link href="/doctor" className="mb-9 text-sm font-bold text-teal no-underline">Meet our doctors ↗</Link>
                </div>
                <DoctorCards doctors={doctors} />
            </section>
        </FrontendLayout>
    );
}
