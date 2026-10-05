import FrontendLayout from '../../Components/FrontendLayout';
import PublicAppointmentForm from '../../Components/PublicAppointmentForm';
import { PageHeading } from '../../Components/PublicPage';

export default function Appointment({ doctors, availableSlots, selectedDoctorId, minDate, seo }) {
    return (
        <FrontendLayout title={seo?.title || 'Book an appointment'} seo={seo}>
            <PageHeading title="Let’s find a time that works" description="Request a visit with one of our specialists. We’ll help take it from there." />
            <section className="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 sm:py-18 lg:grid-cols-[.7fr_1.3fr]">
                <aside className="pt-3">
                    <p className="text-xs font-bold uppercase tracking-[.2em] text-teal">Your next step</p>
                    <h2 className="mt-3 font-display text-3xl">A simpler way to arrange your care.</h2>
                    <p className="mt-4 text-sm leading-7 text-muted">Choose a doctor, date, and available time. Our team will confirm your appointment and share the details.</p>
                    <div className="mt-8 space-y-4">
                        {[
                            ['01', 'Choose your specialist', 'Pick the doctor you would like to see.'],
                            ['02', 'Find a suitable time', 'Only open and unbooked times are selectable.'],
                            ['03', 'We will be in touch', 'Add your email to receive an appointment update.'],
                        ].map(([number, title, text]) => <div key={number} className="flex gap-4 border-t border-line pt-4"><span className="font-display text-sm text-gold">{number}</span><div><h3 className="m-0 text-sm font-bold">{title}</h3><p className="mb-0 mt-1 text-sm text-muted">{text}</p></div></div>)}
                    </div>
                </aside>
                <PublicAppointmentForm doctors={doctors} availableSlots={availableSlots} selectedDoctorId={selectedDoctorId} minDate={minDate} />
            </section>
        </FrontendLayout>
    );
}
