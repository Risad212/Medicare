import { Link } from '@inertiajs/react';
import FrontendLayout, { publicImage } from '../../../Components/FrontendLayout';
import { PageHeading, SectionTitle, ServiceCards } from '../../../Components/PublicPage';

export default function ServiceShow({ service, others = [], seo }) {
    return (
        <FrontendLayout title={seo?.title || service.title} seo={seo}>
            <PageHeading title={service.title} description="Learn more about this service and how our team can support your care." />
            <article className="mx-auto max-w-4xl px-5 py-16 sm:px-8 sm:py-20">
                {service.icon && <img src={publicImage(service.icon)} alt="" className="mx-auto mb-8 h-20 w-20 object-contain" />}
                <div className="whitespace-pre-line text-base leading-8 text-muted">{service.description}</div>
                <Link href="/appointment" className="mt-9 inline-flex rounded-full bg-teal px-6 py-3.5 text-sm font-bold text-white no-underline hover:bg-teal-dark">Book an appointment <span className="ml-2" aria-hidden="true">↗</span></Link>
            </article>
            {others.length > 0 && <section className="bg-paper-dark px-5 py-16 sm:px-8 sm:py-20"><div className="mx-auto max-w-7xl"><SectionTitle eyebrow="More care" title="Explore other services" /><ServiceCards services={others} routes={{ services: '/service' }} /></div></section>}
        </FrontendLayout>
    );
}
