# MediCare Admin Dashboard — Redesign Spec (shared input for 3 directions)

## 1. What this is
Redesign of the admin home page (`GET /admin`, Blade view `backend/home.blade.php`) of MediCare,
a Laravel 13 hospital website. The page is the daily operations screen for clinic admins.
Deliverable at this stage: ONE static single-file HTML mockup per direction (1440x900 desktop),
using the sample dataset below. The winning direction will later be rebuilt as Blade with live data.

## 2. Audience & usage
- Front-desk / clinic manager, desktop office, information-dense OK.
- Glanceable in the morning rush: "what needs action right now?" then drill into tables.
- Viewing distance ~1m laptop/desktop. Body text >= 14px, small labels >= 12px, contrast >= 4.5:1.

## 3. Content sections (same data in all 3 versions for comparability)
1. Header/greeting: "Good morning, Admin" + date line "Thursday, September 10, 2026 · 9 appointments today"
   + 2 actions: "Add doctor", "View appointments".
2. Four stat blocks: Doctors 24 (across 6 departments) | Appointments 132 total
   (12 pending · 86 confirmed · 28 completed · 6 cancelled, 65% confirmed) |
   Blog posts 48 | Pending comments 5.
3. Recent appointments table (6 rows): patient + phone/age | doctor + department |
   date + time slot | status badge (Pending/Confirmed/Completed/Cancelled) | "Open" link.
   Rows: Rahim Uddin/Dr. Ayesha Khan/Sep 10/10:00 AM/Pending; Fatema Begum/Dr. Tanvir Hasan/
   Sep 10/10:30 AM/Confirmed; Karim Sheikh/Dr. Ayesha Khan/Sep 10/11:00 AM/Confirmed;
   Nasrin Akter/Dr. Farhana Islam/Sep 09/04:00 PM/Completed; Jamal Hossain/Dr. Tanvir Hasan/
   Sep 09/03:30 PM/Cancelled; Salma Khatun/Dr. Rafiq Ahmed/Sep 10/11:30 AM/Pending.
4. "Appointments by status" bars: Pending 12, Confirmed 86, Completed 28, Cancelled 6.
5. "Doctors on duty" list (4): Dr. Ayesha Khan (Cardiology · 42 appts, active),
   Dr. Tanvir Hasan (Orthopedics · 38, active), Dr. Farhana Islam (Pediatrics · 31, active),
   Dr. Rafiq Ahmed (Neurology · 21, off duty).
6. "Pending comments" card (3): "What are the visiting hours on Friday?" — Hasan on
   "5 Tips for Heart Health"; "Is Dr. Khan available tomorrow?" — Mim on "Understanding Diabetes";
   "Great article, very helpful." — Arif on "Child Vaccination Guide". CTA "Moderate comments".
7. Slim left sidebar nav (visual context only): Dashboard, Doctors, Appointments, Patients,
   Time Slots, Blogs, Comments, Departments, Settings.

## 4. Tone keywords
Calm, clinical, trustworthy, precise. A hospital ops tool, not a startup landing page.
No playfulness, no marketing gradients, no decoration that carries no information.

## 5. Format (mandatory, identical for all 3)
- Single self-contained `.html` file, inline `<style>`, NO external CDN/fonts/images
  (must render pixel-identical offline from file://).
- System font stacks only. Viewport target 1440x900.
- Real sample copy above (no Lorem). Avatar = initial letter in a circle, never SVG faces.
- Icons: none or minimal geometric/text glyphs (arrows, dots). NO emoji as icons.
- Every element must earn its place; whitespace is composition, not filler.

## 6. Hard constraints (all 3)
- Brand accent teal #05d3b0 must appear as the primary signal color (converge per style).
- Status semantics fixed: pending amber, confirmed/completed green-teal family,
  cancelled neutral grey, attention red only where action is required.
- Forbidden AI-slop list: purple/blue gradients, emoji icons, rounded-card + left-border
  accent combos, Inter-as-display-voice, GitHub-dark neon glow, fake data/charts.
- Readability floor: body >= 14px, annotations >= 12px, contrast >= 4.5:1.
- Layout skeletons must DIFFER structurally across the 3 versions (not reskins).

## 7. Images
Tool-type dashboard: images are NOT content-required (honest test: removing all imagery
loses zero information). Use initials, type and rule-lines only. No stock, no placeholders.

## 8. Motif hypothesis (shared seed, interpret per direction)
The appointment queue as a living ledger: time-ordered rows, status as the only color,
one number per block set large enough to read across the room.
