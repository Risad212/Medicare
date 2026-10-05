import FrontendLayout, { publicImage } from '../../../Components/FrontendLayout';
import { PageHeading, SectionTitle, ServiceCards } from '../../../Components/PublicPage';

export default function ServicesIndex({ pageTitle, serviceSettings = {}, services = [], seo }) {
    return (
        <FrontendLayout title={seo?.title || pageTitle} seo={seo}>
            <PageHeading title={pageTitle || 'Our services'} description="Care shaped around you, delivered by people who bring skill and compassion to every visit." />
            <section className="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20">
                <SectionTitle eyebrow="Care, considered" title="Find the support you need" description="Explore our services and connect with a team ready to help." />
                <ServiceCards services={services} routes={{ services: '/service' }} />
                {!services.length && <p className="rounded-xl border border-line bg-white p-8 text-center text-muted">Service information is being updated. Please contact us for help.</p>}
            </section>
            <section className="bg-teal-dark text-white">
                <div className="mx-auto grid max-w-7xl items-center gap-8 px-5 py-14 sm:px-8 lg:grid-cols-2">
                    <div className="overflow-hidden rounded-2xl bg-white/10">
                        <img src={publicImage(serviceSettings.emergency_image, '/frontend-assets/media/service/emargency.jpg')} alt="" className="max-h-[380px] w-full object-cover" />
                    </div>
                    <div>
                        <p className="text-xs font-bold uppercase tracking-[.2em] text-gold">{serviceSettings.emergency_subtitle || 'Emergency treatment'}</p>
                        <h2 className="mt-3 font-display text-3xl leading-tight sm:text-4xl">{serviceSettings.emergency_title || 'Need urgent care? We are here.'}</h2>
                        <p className="mt-4 text-sm leading-7 text-white/75">{serviceSettings.emergency_description}</p>
                        <div className="mt-6 flex flex-wrap gap-4">
                            {serviceSettings.emergency_phone && <a href={`tel:${serviceSettings.emergency_phone}`} className="rounded-full bg-gold px-5 py-3 text-sm font-bold text-ink no-underline">Call {serviceSettings.emergency_phone}</a>}
                            {serviceSettings.emergency_email && <a href={`mailto:${serviceSettings.emergency_email}`} className="rounded-full border border-white/30 px-5 py-3 text-sm font-bold text-white no-underline">Email our team</a>}
                        </div>
                    </div>
                </div>
            </section>
            {(serviceSettings.prevention_title || serviceSettings.prevention_subtitle) && (
                <section className="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20">
                    <SectionTitle eyebrow={serviceSettings.prevention_subtitle || 'Everyday wellbeing'} title={serviceSettings.prevention_title || 'Small steps make a difference'} />
                    <div className="grid gap-5 md:grid-cols-2">
                        {[1, 2, 3, 4].filter((index) => serviceSettings[`prevention_${index}_title`]).map((index) => (
                            <article key={index} className="rounded-2xl border border-line bg-white p-6">
                                <span className="text-sm font-bold text-gold">0{index}</span>
                                <h3 className="mt-3 font-display text-xl">{serviceSettings[`prevention_${index}_title`]}</h3>
                                <p className="mt-2 text-sm leading-7 text-muted">{serviceSettings[`prevention_${index}_desc`]}</p>
                            </article>
                        ))}
                    </div>
                </section>
            )}
        </FrontendLayout>
    );
}
