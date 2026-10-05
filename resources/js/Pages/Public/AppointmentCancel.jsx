import { useForm } from '@inertiajs/react';
import FrontendLayout from '../../Components/FrontendLayout';
import { PageHeading } from '../../Components/PublicPage';

const statuses = {
    0: ['Pending', 'bg-amber-50 text-amber-800'],
    1: ['Approved', 'bg-teal-pale text-teal-dark'],
    2: ['Completed', 'bg-paper-dark text-muted'],
    3: ['Cancelled', 'bg-red-50 text-red-800'],
};

export default function AppointmentCancel({ token, appointment, seo }) {
    const form = useForm({});
    const [status, style] = statuses[appointment.status] || statuses[3];

    function submit(event) {
        event.preventDefault();
        if (window.confirm('Are you sure you want to cancel this appointment?')) {
            form.put(`/appointment/cancel/${encodeURIComponent(token)}`);
        }
    }

    return (
        <FrontendLayout title={seo?.title || 'Cancel appointment'} seo={seo}>
            <PageHeading title="Appointment details" description="Review your booking and choose whether you need to keep or cancel it." />
            <section className="mx-auto max-w-3xl px-5 py-14 sm:px-8 sm:py-18">
                <article className="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                    <div className="bg-teal-dark px-6 py-5 text-white"><p className="text-xs font-bold uppercase tracking-[.2em] text-gold">MediCare · Booking</p><h2 className="mt-2 font-display text-2xl">Hello {appointment.name}</h2></div>
                    <dl className="divide-y divide-line px-6">
                        {[
                            ['Doctor', appointment.doctor],
                            ['Date', appointment.date],
                            ['Time', appointment.time],
                            ['Visit type', appointment.visitType],
                        ].map(([label, value]) => <div key={label} className="flex justify-between gap-5 py-4 text-sm"><dt className="font-semibold text-muted">{label}</dt><dd className="m-0 text-right text-ink">{value}</dd></div>)}
                        <div className="flex justify-between gap-5 py-4 text-sm"><dt className="font-semibold text-muted">Status</dt><dd className="m-0"><span className={`rounded-full px-3 py-1 text-xs font-bold ${style}`}>{status}</span></dd></div>
                    </dl>
                    <div className="border-t border-line p-6">
                        {appointment.status === 0 || appointment.status === 1 ? (
                            <form onSubmit={submit}>
                                {form.errors.token && <p role="alert" className="mb-4 text-sm text-red-700">{form.errors.token}</p>}
                                <button type="submit" disabled={form.processing} className="rounded-full bg-red-700 px-6 py-3 text-sm font-bold text-white hover:bg-red-800 disabled:opacity-60">{form.processing ? 'Cancelling…' : 'Cancel my appointment'}</button>
                            </form>
                        ) : <p className="mb-0 text-sm text-muted">This appointment can no longer be cancelled.</p>}
                        <p className="mb-0 mt-5 text-xs leading-6 text-muted">This link is unique to your appointment. Please keep it private.</p>
                    </div>
                </article>
            </section>
        </FrontendLayout>
    );
}
