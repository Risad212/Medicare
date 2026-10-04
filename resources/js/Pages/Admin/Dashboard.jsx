import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import AdminLayout from '../../Components/AdminLayout';

const money = (value, decimals = 0) => `$${Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
})}`;

const initials = (name) => (name || '?')
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase();

function Icon({ name, className = '' }) {
    return <i aria-hidden="true" className={`bi bi-${name} ${className}`} />;
}

function Panel({ title, description, action, children, className = '' }) {
    return (
        <section className={`welly-card ${className}`}>
            <div className="welly-card-hd">
                <div>
                    <h2 className="welly-card-title">{title}</h2>
                    {description && <p className="m-0 mt-0.5 text-[12px] text-mut">{description}</p>}
                </div>
                {action}
            </div>
            {children}
        </section>
    );
}

function Metric({ label, value, icon, note, accent = 'teal' }) {
    return (
        <article className="welly-stat group">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="welly-stat-num">{value}</p>
                    <p className="welly-stat-label">{label}</p>
                    {note && <p className="mt-2 text-[11px] text-mut">{note}</p>}
                </div>
                <span className={`welly-stat-icon ${accent === 'gold' ? '!bg-amber-bg !text-gold-dk' : ''}`}>
                    <Icon name={icon} />
                </span>
            </div>
        </article>
    );
}

function StatusBadge({ status }) {
    const values = {
        0: ['Pending', 'p-pending'],
        1: ['Approved', 'p-confirmed'],
        2: ['Completed', 'p-completed'],
        3: ['Cancelled', 'p-cancelled'],
        pending: ['Pending', 'p-pending'],
        'in-progress': ['In Progress', 'p-confirmed'],
    };
    const [label, className] = values[status] || ['Unknown', 'p-cancelled'];

    return <span className={`mc-pill ${className}`}><i />{label}</span>;
}

function AppointmentStatusForm({ appointmentId, status, routes, token, label, icon, className, confirmMessage }) {
    const action = routes.appointmentStatus.replace('__APPOINTMENT__', appointmentId);

    return (
        <form action={action} method="post" className="m-0" onSubmit={(event) => {
            if (confirmMessage && !window.confirm(confirmMessage)) event.preventDefault();
        }}>
            <input type="hidden" name="_token" value={token} />
            <input type="hidden" name="_method" value="PATCH" />
            <input type="hidden" name="status" value={status} />
            <button type="submit" className={className} aria-label={label} title={label}>
                <Icon name={icon} />
            </button>
        </form>
    );
}

function RangePanel({ metrics }) {
    const [range, setRange] = useState('weekly');
    const current = metrics.rangeStats[range];
    const sections = [
        { label: 'New requests', value: current.new, color: 'var(--color-gold)' },
        { label: 'Recovered', value: current.recovered, color: 'var(--color-teal)' },
        { label: 'In treatment', value: current.treating, color: 'var(--color-ink)' },
    ];
    const angleA = current.new;
    const angleB = current.new + current.recovered;
    const donut = `conic-gradient(from -90deg, var(--color-gold) 0 ${angleA}%, var(--color-teal) ${angleA}% ${angleB}%, var(--color-ink) ${angleB}% 100%)`;

    return (
        <Panel title="Patient percentage" description="Appointment outcomes by selected period">
            <div className="flex flex-wrap items-center justify-between gap-3 px-5 pt-3">
                <div className="welly-tabs" role="tablist" aria-label="Appointment period">
                    {Object.entries({ daily: 'Daily', weekly: 'Weekly', monthly: 'Monthly' }).map(([key, label]) => (
                        <button key={key} type="button" role="tab" aria-selected={range === key} onClick={() => setRange(key)}
                            className={`welly-tab ${range === key ? 'on' : ''}`}>{label}</button>
                    ))}
                </div>
                <span className="text-[12px] text-mut">{current.total} bookings</span>
            </div>

            <div className="mx-5 mt-3 flex items-center justify-between gap-3 rounded-lg bg-line-2 px-4 py-3">
                <div className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-md bg-teal-dk text-[20px] text-white"><Icon name="heart" /></span>
                    <div><p className="m-0 text-[11px] text-mut">Total patients</p><p className="m-0 text-[17px] font-extrabold tabular-nums text-teal-dk">{metrics.totalPatients.toLocaleString()}</p></div>
                </div>
                <div className="flex items-center pl-2">
                    {metrics.topDoctors.map((doctor, index) => (
                        <span key={doctor.id} title={doctor.name} className={`mc-av -ml-2 border-2 border-white ${['t', 'a', 'b', 'r', ''][doctor.id % 5]}`}>
                            {initials(doctor.name)}
                        </span>
                    ))}
                </div>
            </div>

            <div className="grid items-center gap-6 px-5 pb-5 pt-4 sm:grid-cols-[190px_1fr]">
                <div className="relative mx-auto h-[176px] w-[176px] rounded-full" style={{ background: donut }}>
                    <div className="absolute inset-[24px] rounded-full bg-white" />
                    <div className="absolute inset-[43px] flex flex-col items-center justify-center rounded-full border-[8px] border-line-2 border-t-transparent">
                        <span className="font-display text-[28px] font-bold text-ink">{current.total}</span>
                        <span className="text-[9px] font-bold uppercase tracking-widest text-mut">visits</span>
                    </div>
                </div>
                <div>
                    {sections.map((item) => (
                        <div key={item.label} className="welly-legend">
                            <span className="flex items-center gap-2.5">
                                <i className="welly-bar" style={{ backgroundColor: item.color }} />
                                <b>{item.value}%</b>
                            </span>
                            <span className="lbl">{item.label}</span>
                        </div>
                    ))}
                    <p className="mt-3 border-t border-line-2 pt-3 text-[11px] leading-relaxed text-mut">
                        {metrics.totalAppointments.toLocaleString()} total appointments · {metrics.pendingAppointments} pending · {metrics.cancelledAppointments} cancelled
                    </p>
                </div>
            </div>
        </Panel>
    );
}

function AppointmentCalendar({ calendar, schedule, routes, token }) {
    const weekdays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
    const monthUrl = (month) => `${routes.dashboard}?cal=${month}`;

    return (
        <Panel
            title="Appointment schedule"
            description="Upcoming visits and daily register"
            action={<a href={routes.appointments} className="welly-iconbtn !h-8 !w-8 !text-[16px]" aria-label="All appointments"><Icon name="arrow-up-right" /></a>}
        >
            <div className="px-5 pb-5 pt-2">
                <div className="flex items-center justify-between py-2">
                    <a href={monthUrl(calendar.previousMonth)} className="welly-iconbtn !h-8 !w-8 !text-[15px]" aria-label="Previous month"><Icon name="chevron-left" /></a>
                    <p className="m-0 text-[14px] font-extrabold text-ink">
                        {calendar.month}
                        {calendar.monthParam !== calendar.todayMonth &&
                            <a href={routes.dashboard} className="ml-2 text-[11px] font-bold text-teal-dk no-underline hover:underline">Today</a>}
                    </p>
                    <a href={monthUrl(calendar.nextMonth)} className="welly-iconbtn !h-8 !w-8 !text-[15px]" aria-label="Next month"><Icon name="chevron-right" /></a>
                </div>
                <div className="welly-cal">
                    {weekdays.map((day) => <span key={day} className="dow">{day}</span>)}
                    {calendar.days.map((day) => (
                        <span key={day.date} className={`day relative ${!day.inMonth ? 'muted' : ''} ${day.isToday ? 'today' : ''}`}>
                            {day.day}
                            {day.hasBookings && <i className={`absolute bottom-0.5 h-1 w-1 rounded-full ${day.isToday ? 'bg-white' : 'bg-teal'}`} />}
                        </span>
                    ))}
                </div>

                <div className="mt-3">
                    {schedule.length ? schedule.map((group) => (
                        <div key={group.day} className="welly-sched">
                            <p className="welly-sched-day">{group.day}</p>
                            {group.appointments.map((appointment) => (
                                <div key={appointment.id} className="mt-1 flex items-center justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="m-0 truncate text-[12px] font-semibold text-ink">{appointment.patientName}</p>
                                        <p className="m-0 truncate text-[11px] text-mut"><Icon name="clock" /> {appointment.time} · {appointment.doctorName}</p>
                                    </div>
                                    {appointment.status === 0 && (
                                        <div className="flex shrink-0 gap-2">
                                            <AppointmentStatusForm appointmentId={appointment.id} status={1} routes={routes} token={token}
                                                label="Approve appointment" icon="check-circle" className="border-0 bg-transparent p-0 text-[16px] text-teal hover:text-teal-dk" />
                                            <AppointmentStatusForm appointmentId={appointment.id} status={3} routes={routes} token={token}
                                                label="Cancel appointment" confirmMessage="Cancel this appointment?" icon="x-circle" className="border-0 bg-transparent p-0 text-[16px] text-red hover:text-red-t" />
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    )) : <p className="py-4 text-center text-[12px] text-mut">No upcoming appointments scheduled.</p>}
                </div>
                <a href={routes.appointments} className="mt-3 block text-center text-[12px] font-bold text-teal-dk no-underline hover:underline">View all appointments <Icon name="arrow-right" /></a>
            </div>
        </Panel>
    );
}

function MonthlyOverview({ admin, features }) {
    const appointments = admin.monthlyAppointments;
    const labOrders = admin.monthlyLabOrders;
    const max = Math.max(1, ...appointments, ...(features.lab ? labOrders : []));

    return (
        <Panel title="Patient overview" description="Appointments and lab orders · last six months">
            <div className="flex items-end gap-2 px-5 pb-2 pt-4">
                {admin.monthLabels.map((month, index) => (
                    <div key={`${month}-${index}`} className="flex flex-1 flex-col items-center gap-1.5">
                        <div className="flex h-[116px] items-end gap-1">
                            <i className="block w-3 rounded-t-sm bg-teal" style={{ height: `${Math.max(4, Math.round(appointments[index] / max * 108))}px` }}
                                title={`${appointments[index]} appointments`} />
                            {features.lab && <i className="block w-3 rounded-t-sm bg-gold" style={{ height: `${Math.max(4, Math.round(labOrders[index] / max * 108))}px` }}
                                title={`${labOrders[index]} lab orders`} />}
                        </div>
                        <span className="text-[10px] text-mut">{month}</span>
                    </div>
                ))}
            </div>
            <div className="flex flex-wrap items-center gap-4 border-t border-line-2 px-5 py-3 text-[11px] text-mut">
                <span><i className="mr-1.5 inline-block h-2.5 w-2.5 rounded-sm bg-teal align-[-1px]" />Appointments</span>
                {features.lab && <span><i className="mr-1.5 inline-block h-2.5 w-2.5 rounded-sm bg-gold align-[-1px]" />Lab orders</span>}
                <span className="ml-auto">Outstanding invoices <b className="text-ink">{money(admin.invoiceOutstanding, 2)}</b></span>
            </div>
        </Panel>
    );
}

function RevenuePanel({ admin, features }) {
    return (
        <Panel title="Revenue" description="Collected income and outstanding balances"
            action={<a href="/admin/invoices" className="text-[12px] font-bold text-teal-dk no-underline hover:underline">Invoices <Icon name="arrow-up-right" /></a>}>
            <div className="grid grid-cols-2 gap-x-4 gap-y-5 px-5 py-4 sm:grid-cols-4">
                {features.lab && <>
                    <div><p className="mc-kicker">Lab · this month</p><p className="mt-1 text-[19px] font-extrabold tabular-nums text-ink">{money(admin.revenueThisMonth, 2)}</p></div>
                    <div><p className="mc-kicker">Lab · all time</p><p className="mt-1 text-[19px] font-extrabold tabular-nums text-ink">{money(admin.revenueAllTime, 2)}</p></div>
                </>}
                <div><p className="mc-kicker">Collected this month</p><p className="mt-1 text-[19px] font-extrabold tabular-nums text-ink">{money(admin.invoicePaidThisMonth, 2)}</p></div>
                <div><p className="mc-kicker">Collected all time</p><p className="mt-1 text-[19px] font-extrabold tabular-nums text-ink">{money(admin.invoicePaidAllTime, 2)}</p></div>
                <div><p className="mc-kicker">Outstanding</p><p className="mt-1 text-[19px] font-extrabold tabular-nums text-ink">{money(admin.invoiceOutstanding, 2)}</p></div>
                <div><p className="mc-kicker">Unpaid invoices</p><p className="mt-1 text-[19px] font-extrabold tabular-nums text-ink">{admin.invoicesPendingCount}</p></div>
            </div>
        </Panel>
    );
}

function AppointmentStatusPanel({ metrics }) {
    const total = Math.max(1, metrics.pendingAppointments + metrics.confirmedAppointments + metrics.completedAppointments + metrics.cancelledAppointments);
    const rows = [
        ['Pending', metrics.pendingAppointments, 'bg-amber-dot'],
        ['Approved', metrics.confirmedAppointments, 'bg-teal'],
        ['Completed', metrics.completedAppointments, 'bg-bright'],
        ['Cancelled', metrics.cancelledAppointments, 'bg-faint'],
    ];

    return (
        <Panel title="Appointments by status" description={`${metrics.totalAppointments.toLocaleString()} total appointments`}>
            <div className="space-y-3 px-5 py-4">
                {rows.map(([label, count, color]) => (
                    <div key={label} className="grid grid-cols-[82px_1fr_34px] items-center gap-3 text-[12px]">
                        <span className="text-ink-2">{label}</span>
                        <span className="h-2 overflow-hidden rounded-full bg-grey-bg">
                            <i className={`block h-full rounded-full ${color}`} style={{ width: `${Math.round(count / total * 100)}%` }} />
                        </span>
                        <b className="text-right tabular-nums text-ink">{count}</b>
                    </div>
                ))}
            </div>
            <p className="m-0 border-t border-line-2 px-5 py-3 text-[11px] text-mut">Review the pending queue or book a visit from the front desk.</p>
        </Panel>
    );
}

function AppointmentQueue({ queue, routes }) {
    return (
        <Panel title="Today's appointments" description="Pending visits across all doctors"
            action={<a href={routes.appointments} className="text-[12px] font-bold text-teal-dk no-underline hover:underline">View all <Icon name="arrow-up-right" /></a>}>
            <div className="overflow-x-auto">
                <table className="mc-tbl w-full">
                    <thead><tr><th>Patient</th><th>Doctor</th><th>Time</th><th>Status</th><th /></tr></thead>
                    <tbody>
                        {queue.length ? queue.map((appointment) => (
                            <tr key={appointment.id}>
                                <td>
                                    <div className="mc-who">
                                        <div className="mc-av">{initials(appointment.patientName)}</div>
                                        <div><b>{appointment.patientName}</b><span className="mc-sub2">{appointment.phone || '—'} · {appointment.age ? `${appointment.age}y` : '—'}</span></div>
                                    </div>
                                </td>
                                <td><b className="block text-ink">{appointment.doctorName}</b><span className="text-[11px] text-mut">{appointment.department}</span></td>
                                <td>{appointment.date}<br /><span className="text-[11px] text-mut">{appointment.time}</span></td>
                                <td><StatusBadge status={appointment.status} /></td>
                                <td><a className="whitespace-nowrap text-[12px] font-bold text-teal-dk no-underline hover:underline" href={`/admin/appointments/${appointment.id}/edit`}>Open <Icon name="arrow-right" /></a></td>
                            </tr>
                        )) : <tr><td colSpan="5" className="py-7 text-center text-mut">No appointments scheduled for today.</td></tr>}
                    </tbody>
                </table>
            </div>
        </Panel>
    );
}

function DoctorPanel({ doctors, activeCount, totalDoctors, routes }) {
    return (
        <Panel title="Doctors on duty" description={`${activeCount} scheduled today`}
            action={<a href={routes.doctors} className="text-[12px] font-bold text-teal-dk no-underline hover:underline">All {totalDoctors} <Icon name="arrow-up-right" /></a>}>
            <div className="px-5 pb-3 pt-1">
                {doctors.length ? doctors.map((doctor) => (
                    <div key={doctor.name} className="flex items-center gap-2.5 border-b border-line-2 py-2.5 last:border-b-0">
                        <div className="mc-av">{initials(doctor.name)}</div>
                        <div className="min-w-0"><b className="block text-[13px] text-ink">{doctor.name}</b>
                            <span className="text-[11px] text-mut">{doctor.department} · {doctor.appointmentCount} today</span></div>
                        <span className={`ml-auto shrink-0 text-[10px] font-bold ${doctor.active ? 'text-teal-dk' : 'text-faint'}`}>● {doctor.active ? 'Active' : 'Off duty'}</span>
                    </div>
                )) : <p className="py-3 text-[12px] text-mut">Nobody on duty today.</p>}
            </div>
        </Panel>
    );
}

function DoctorLoad({ doctors }) {
    const max = Math.max(1, ...doctors.map((doctor) => doctor.appointmentCount));

    return (
        <Panel title="Doctor load" description="Active bookings by doctor">
            <div className="px-5 py-4">
                {doctors.length ? doctors.map((doctor, index) => (
                    <div key={doctor.name} className={index < doctors.length - 1 ? 'mb-3' : ''}>
                        <div className="flex justify-between gap-3 text-[12px]">
                            <b className="truncate text-ink">{doctor.name}</b><span className="font-bold tabular-nums text-ink">{doctor.appointmentCount}</span>
                        </div>
                        <div className="mt-1.5 h-1.5 overflow-hidden rounded-full bg-line-2"><i className="block h-full rounded-full bg-teal" style={{ width: `${Math.round(doctor.appointmentCount / max * 100)}%` }} /></div>
                    </div>
                )) : <p className="py-2 text-[12px] text-mut">No doctors found.</p>}
            </div>
        </Panel>
    );
}

function CommentsPanel({ admin, routes }) {
    return (
        <Panel title="Pending comments" description={`${admin.pendingComments} awaiting moderation`}>
            <div className="px-5 pb-1 pt-1">
                {admin.comments.length ? admin.comments.map((comment, index) => (
                    <div key={`${comment.name}-${index}`} className="border-b border-line-2 py-2.5 text-[12px] last:border-b-0">
                        <q className="block font-medium text-ink">{comment.comment}</q>
                        <span className="text-[10px] text-mut">{comment.name} on “{comment.blogTitle}”</span>
                    </div>
                )) : <p className="py-3 text-[12px] text-mut">No pending comments. All clear.</p>}
            </div>
            <a className="mx-5 mb-4 mt-2 block rounded-lg bg-ink py-2 text-center text-[12px] font-semibold text-white no-underline hover:bg-teal-dk" href={routes.comments}>Moderate comments</a>
        </Panel>
    );
}

function AnalyticsPanel({ analytics, admin }) {
    if (!analytics) return null;

    const max = Math.max(1, ...analytics.trendCounts);
    const statusData = [
        ['Pending', analytics.statusBreakdown.pending, 'bg-gold'],
        ['Approved', analytics.statusBreakdown.approved, 'bg-teal'],
        ['Completed', analytics.statusBreakdown.completed, 'bg-ink'],
        ['Cancelled', analytics.statusBreakdown.cancelled, 'bg-red'],
    ];

    return (
        <Panel title="Analytics" description="Bookings, patients and revenue at a glance">
            <div className="grid gap-4 px-5 py-4 sm:grid-cols-2 xl:grid-cols-4">
                <div><p className="mc-kicker">Appointments this month</p><p className="mt-1 text-[20px] font-extrabold text-ink">{analytics.appointmentsThisMonth} <small className={`text-[11px] ${analytics.appointmentMonthChange >= 0 ? 'text-teal-dk' : 'text-red-t'}`}>({analytics.appointmentMonthChange >= 0 ? '+' : ''}{analytics.appointmentMonthChange}%)</small></p></div>
                <div><p className="mc-kicker">Registered patients</p><p className="mt-1 text-[20px] font-extrabold text-ink">{analytics.totalRegisteredPatients}</p></div>
                <div><p className="mc-kicker">New patients this month</p><p className="mt-1 text-[20px] font-extrabold text-ink">{analytics.newPatientsThisMonth}</p></div>
                <div><p className="mc-kicker">Invoice revenue this month</p><p className="mt-1 text-[20px] font-extrabold text-ink">{money(admin.invoicePaidThisMonth)}</p><span className="text-[10px] text-mut">Last month: {money(analytics.invoicePaidLastMonth)}</span></div>
            </div>
            <div className="grid gap-6 border-t border-line-2 px-5 py-4 xl:grid-cols-2">
                <div>
                    <p className="mc-kicker mb-3">Appointments · last 30 days</p>
                    <div className="flex h-24 items-end gap-1" aria-label="Appointments over the last 30 days">
                        {analytics.trendCounts.map((count, index) => (
                            <i key={analytics.trendLabels[index]} className="min-w-0 flex-1 rounded-t-sm bg-teal/80" style={{ height: `${Math.max(3, count / max * 92)}px` }} title={`${analytics.trendLabels[index]}: ${count}`} />
                        ))}
                    </div>
                </div>
                <div>
                    <p className="mc-kicker mb-3">Appointment status breakdown</p>
                    <div className="space-y-2">
                        {statusData.map(([label, count, color]) => (
                            <div key={label} className="grid grid-cols-[78px_1fr_32px] items-center gap-2 text-[11px]">
                                <span className="text-ink-2">{label}</span>
                                <span className="h-1.5 overflow-hidden rounded bg-line-2"><i className={`block h-full ${color}`} style={{ width: `${Math.min(100, count / Math.max(1, Object.values(analytics.statusBreakdown).reduce((sum, value) => sum + value, 0)) * 100)}%` }} /></span>
                                <b className="text-right tabular-nums">{count}</b>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </Panel>
    );
}

function LabQueue({ laboratory, routes }) {
    return (
        <Panel title="Laboratory queue" description={`${laboratory.ordersPending} awaiting processing`}
            action={routes.labOrders && <a href={routes.labOrders} className="text-[12px] font-bold text-teal-dk no-underline hover:underline">All orders <Icon name="arrow-up-right" /></a>}>
            <div className="overflow-x-auto">
                <table className="mc-tbl w-full">
                    <thead><tr><th>Order</th><th>Patient</th><th>Tests</th><th>Status</th><th /></tr></thead>
                    <tbody>
                        {laboratory.pendingOrders.length ? laboratory.pendingOrders.map((order) => (
                            <tr key={order.id}>
                                <td><b className="block text-ink">#{order.id}</b><span className="text-[10px] text-mut">{order.createdAt} · {order.doctorName}</span></td>
                                <td><b className="block text-ink">{order.patientName}</b><span className="text-[10px] text-mut">{order.phone}</span></td>
                                <td>{order.testCount} test(s)</td>
                                <td><StatusBadge status={order.status} /></td>
                                <td>{routes.labOrders && <a className="text-[12px] font-bold text-teal-dk no-underline" href={`${routes.labOrders}/${order.id}`}>Open <Icon name="arrow-right" /></a>}</td>
                            </tr>
                        )) : <tr><td colSpan="5" className="py-6 text-center text-mut">Queue clear — no pending lab orders.</td></tr>}
                    </tbody>
                </table>
            </div>
        </Panel>
    );
}

function PrescriptionsPanel({ pharmacy, routes }) {
    return (
        <Panel title="Recent prescriptions" description="Latest written across doctors"
            action={<a href={routes.prescriptions} className="text-[12px] font-bold text-teal-dk no-underline hover:underline">All <Icon name="arrow-up-right" /></a>}>
            <div className="overflow-x-auto">
                <table className="mc-tbl w-full">
                    <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th /></tr></thead>
                    <tbody>
                        {pharmacy.prescriptions.length ? pharmacy.prescriptions.map((prescription) => (
                            <tr key={prescription.id}>
                                <td><b className="text-ink">{prescription.patientName}</b></td>
                                <td>{prescription.doctorName}</td>
                                <td>{prescription.createdAt}</td>
                                <td><a className="text-[12px] font-bold text-teal-dk no-underline" href={`${routes.prescriptions}/${prescription.id}`}>Open <Icon name="arrow-right" /></a></td>
                            </tr>
                        )) : <tr><td colSpan="4" className="py-6 text-center text-mut">No prescriptions yet.</td></tr>}
                    </tbody>
                </table>
            </div>
        </Panel>
    );
}

export default function Dashboard({ metrics, calendar, schedule, queue, dutyDoctors, doctorLoad, features, routes, csrfToken }) {
    const { auth } = usePage().props;
    const role = auth.user.role;
    const isAdmin = role === 'admin';
    const isFrontDesk = Boolean(metrics.frontDesk);
    const isLabStaff = Boolean(metrics.laboratory);
    const isPharmacyStaff = Boolean(metrics.pharmacy);

    const stats = [];
    if (isFrontDesk) {
        stats.push(
            { label: "Today's appointments", value: metrics.frontDesk.todayAppointments, icon: 'calendar-date' },
            { label: 'Total patients', value: metrics.frontDesk.totalPatients.toLocaleString(), icon: 'heart' },
            { label: 'Doctors', value: metrics.frontDesk.totalDoctors, icon: 'person-badge' },
        );
    }
    if (isAdmin) stats.push({ label: 'Hospital earnings', value: money(metrics.admin.hospitalEarning), icon: 'coin', accent: 'gold' });
    if (isLabStaff) {
        stats.push(
            { label: 'Pending lab orders', value: metrics.laboratory.ordersPending, icon: 'clipboard2-pulse' },
            { label: 'Lab orders this month', value: metrics.laboratory.ordersThisMonth, icon: 'graph-up' },
        );
    }
    if (isPharmacyStaff && !isAdmin) stats.push({ label: 'Recent prescriptions', value: metrics.pharmacy.prescriptions.length, icon: 'capsule' });

    return (
        <AdminLayout
            title="Hospital dashboard"
            active="dashboard"
            routes={routes}
            features={features}
            actions={<>
                {isFrontDesk && <a href={routes.appointmentCreate} className="mc-btn sm no-underline"><Icon name="plus-lg" /> <span className="hidden sm:inline">Book appointment</span><span className="sm:hidden">Book</span></a>}
                {isAdmin && <a href={routes.doctorCreate} className="welly-outline-btn !px-3 !py-2 no-underline"><Icon name="person-plus" /><span className="hidden sm:inline">Add doctor</span></a>}
            </>}
        >
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">Operations overview</p>
                    <h1 className="mt-1 font-display text-[32px] font-bold leading-tight tracking-tight text-ink sm:text-[38px]">Dashboard<span className="text-teal">.</span></h1>
                    <p className="mc-sub">A clear view of today’s care, people, and hospital activity.</p>
                </div>
            </div>

            {stats.length > 0 && <section className="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Key metrics">
                {stats.map((stat) => <Metric key={stat.label} {...stat} />)}
            </section>}

            {isFrontDesk && (
                <section className="mb-5 grid grid-cols-1 items-start gap-4 2xl:grid-cols-2">
                    {isAdmin && <RangePanel metrics={{
                        ...metrics.frontDesk,
                        rangeStats: metrics.admin.rangeStats,
                        topDoctors: metrics.admin.topDoctors,
                    }} />}
                    <AppointmentCalendar calendar={calendar} schedule={schedule} routes={routes}
                        token={csrfToken} />
                </section>
            )}

            {isAdmin && <div className="mb-5 grid grid-cols-1 items-start gap-4 2xl:grid-cols-2">
                <MonthlyOverview admin={metrics.admin} features={features} />
                <div className="flex flex-col gap-4">
                    <RevenuePanel admin={metrics.admin} features={features} />
                    <AppointmentStatusPanel metrics={metrics.frontDesk} />
                </div>
            </div>}

            {isAdmin && <div className="mb-5"><AnalyticsPanel analytics={metrics.admin.analytics} admin={metrics.admin} /></div>}

            {isFrontDesk && !isAdmin && <div className="mb-5"><AppointmentStatusPanel metrics={metrics.frontDesk} /></div>}

            {isFrontDesk && <div className="mb-5"><AppointmentQueue queue={queue} routes={routes} /></div>}

            {isFrontDesk && <section className={`mb-5 grid grid-cols-1 items-start gap-4 ${isAdmin ? 'xl:grid-cols-[1fr_1fr_1fr]' : 'xl:grid-cols-2'}`}>
                <DoctorPanel doctors={dutyDoctors} activeCount={metrics.frontDesk.activeDoctorsToday} totalDoctors={metrics.frontDesk.totalDoctors} routes={routes} />
                <DoctorLoad doctors={doctorLoad} />
                {isAdmin && <CommentsPanel admin={metrics.admin} routes={routes} />}
            </section>}

            {(isLabStaff || isPharmacyStaff) && (
                <section className="grid grid-cols-1 gap-4 2xl:grid-cols-2">
                    {isLabStaff && <LabQueue laboratory={metrics.laboratory} routes={routes} />}
                    {isPharmacyStaff && <PrescriptionsPanel pharmacy={metrics.pharmacy} routes={routes} />}
                </section>
            )}
        </AdminLayout>
    );
}
