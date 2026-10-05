import FrontendLayout, { publicImage } from '../../../Components/FrontendLayout';
import { PageHeading } from '../../../Components/PublicPage';
import PublicAppointmentForm from '../../../Components/PublicAppointmentForm';

export default function DoctorShow({ doctor, minDate }) {
    return (
        <FrontendLayout title={doctor.name}>
            <PageHeading title={doctor.name} description={doctor.specialist || doctor.department || 'Meet your MediCare specialist'} />
            <section className="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 sm:py-18 lg:grid-cols-[.75fr_1.25fr]">
                <div className="overflow-hidden rounded-2xl bg-paper-dark">
                    {doctor.image
                        ? <img src={publicImage(doctor.image)} alt={doctor.name} className="max-h-[560px] w-full object-cover" />
                        : <div className="grid min-h-80 place-items-center text-7xl text-teal"><i className="bi bi-person-badge" /></div>}
                </div>
                <div className="rounded-2xl border border-line bg-white p-7 sm:p-9">
                    <p className="text-xs font-bold uppercase tracking-[.2em] text-teal">Your care, your questions</p>
                    <h2 className="mt-3 font-display text-4xl">{doctor.name}</h2>
                    <dl className="mt-7 grid gap-5 sm:grid-cols-2">
                        {[
                            ['Degree', doctor.degree],
                            ['Department', doctor.department],
                            ['Specialty', doctor.specialist],
                            ['Availability', doctor.availability],
                            ['Services', doctor.services],
                            ['Phone', doctor.phone],
                        ].filter(([, value]) => value).map(([label, value]) => <div key={label} className="border-t border-line pt-3"><dt className="text-xs font-bold uppercase tracking-wider text-muted">{label}</dt><dd className="mt-1 text-sm text-ink">{value}</dd></div>)}
                    </dl>
                </div>
            </section>
            <section id="book-appointment" className="scroll-mt-24 bg-paper-dark px-5 py-16 sm:px-8 sm:py-20">
                <div className="mx-auto max-w-4xl"><PublicAppointmentForm doctors={[doctor]} selectedDoctorId={doctor.id} minDate={minDate} /></div>
            </section>
        </FrontendLayout>
    );
}
