<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Grading System') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --brand-navy: #0f2230;
            --brand-teal: #1f8a70;
            --brand-gold: #f2b134;
            --brand-paper: #f7f5ef;
            --brand-ink: #17212b;
        }

        body {
            font-family: 'Manrope', sans-serif;
            color: var(--brand-ink);
            background: radial-gradient(circle at 10% 10%, #d9efe8 0%, #f7f5ef 35%, #eef3f8 100%);
            min-height: 100vh;
        }

        .title-font {
            font-family: 'Space Grotesk', sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(15, 34, 48, 0.12);
            box-shadow: 0 12px 30px rgba(15, 34, 48, 0.09);
        }

        .float-in {
            animation: floatIn 0.7s ease-out both;
        }

        .float-in-delay {
            animation: floatIn 0.9s ease-out both;
        }

        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="relative overflow-hidden">
        <div class="absolute -top-20 -left-20 h-64 w-64 rounded-full bg-teal-200/60 blur-3xl"></div>
        <div class="absolute top-24 -right-10 h-72 w-72 rounded-full bg-amber-200/50 blur-3xl"></div>

        <header class="relative mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-6 sm:px-6 lg:px-8">
            <div>
                <p class="title-font text-xl font-bold tracking-tight" style="color: var(--brand-navy);">Grading System</p>
                <p class="text-xs font-medium text-slate-600">Academic Assessment Platform</p>
            </div>

            @if (Route::has('login'))
                <nav class="flex items-center gap-3 text-sm">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rounded-full px-4 py-2 font-semibold text-white transition" style="background: var(--brand-teal);">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-50">Log In</a>
                    @endauth
                </nav>
            @endif
        </header>

        <main class="relative mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <section class="grid items-center gap-8 py-6 lg:grid-cols-2 lg:py-10">
                <div class="float-in">
                    <p class="mb-3 inline-flex rounded-full border border-teal-700/20 bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-900">Built for schools and departments</p>
                    <h1 class="title-font text-4xl font-bold leading-tight text-slate-900 sm:text-5xl">
                        Manage assessments, marks, and progression from one intelligent grading hub.
                    </h1>
                    <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-700 sm:text-lg">
                        This grading system helps academic teams structure courses, track enrollments per academic year,
                        record assessment scores, and analyze student performance with clear role-based controls.
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-full px-5 py-3 text-sm font-bold text-white transition hover:opacity-90" style="background: var(--brand-teal);">Go to Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full px-5 py-3 text-sm font-bold text-white transition hover:opacity-90" style="background: var(--brand-navy);">Access Platform</a>
                        @endauth
                        <a href="#features" class="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Explore Features</a>
                    </div>
                </div>

                <div class="glass-card float-in-delay rounded-3xl p-6 sm:p-8">
                    <p class="title-font text-lg font-bold" style="color: var(--brand-navy);">What this platform covers</p>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4">
                            <p class="text-sm font-bold text-slate-800">Role & permission management</p>
                            <p class="mt-1 text-sm text-slate-600">Assign multiple roles to users and control module access using fine-grained permissions.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4">
                            <p class="text-sm font-bold text-slate-800">Academic year aware records</p>
                            <p class="mt-1 text-sm text-slate-600">Keep enrollments, course performance, and mark analytics aligned to the current academic year.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4">
                            <p class="text-sm font-bold text-slate-800">Performance analytics</p>
                            <p class="mt-1 text-sm text-slate-600">Visualize average, minimum, and maximum student totals per course for fast academic decisions.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="mt-8">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <h2 class="title-font text-2xl font-bold text-slate-900 sm:text-3xl">Core Features</h2>
                    <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide text-white" style="background: var(--brand-gold); color: #1b1b1b;">Operational + analytical</span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="glass-card rounded-2xl p-5 transition hover:bg-teal-50/70">
                        <p class="text-sm font-extrabold uppercase tracking-wide text-teal-800">Users & Roles</p>
                        <p class="mt-2 text-sm text-slate-700">Create users, assign one or many roles, and update permissions by role.</p>
                    </article>
                    <article class="glass-card rounded-2xl p-5 transition hover:bg-amber-50/80">
                        <p class="text-sm font-extrabold uppercase tracking-wide text-amber-800">Courses</p>
                        <p class="mt-2 text-sm text-slate-700">Manage departments, courses, and instructor/course assignments.</p>
                    </article>
                    <article class="glass-card rounded-2xl p-5 transition hover:bg-sky-50/80">
                        <p class="text-sm font-extrabold uppercase tracking-wide text-sky-800">Assessments</p>
                        <p class="mt-2 text-sm text-slate-700">Define assessments per course and keep mark entry structured and consistent.</p>
                    </article>
                    <article class="glass-card rounded-2xl p-5 transition hover:bg-emerald-50/80">
                        <p class="text-sm font-extrabold uppercase tracking-wide text-emerald-800">Dashboard</p>
                        <p class="mt-2 text-sm text-slate-700">View active year metrics, enrollment trends, and chart-based mark insights.</p>
                    </article>
                </div>
            </section>

            <section class="mt-10 rounded-3xl border border-slate-200 bg-white/85 p-6 shadow-sm sm:p-8">
                <h2 class="title-font text-2xl font-bold text-slate-900">How it works</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Step 1</p>
                        <p class="mt-2 text-sm font-bold text-slate-800">Set up structure</p>
                        <p class="mt-1 text-sm text-slate-600">Create departments, students, academic years, and course offerings.</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Step 2</p>
                        <p class="mt-2 text-sm font-bold text-slate-800">Capture grading data</p>
                        <p class="mt-1 text-sm text-slate-600">Enroll students, define assessments, and record marks per enrollment.</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Step 3</p>
                        <p class="mt-2 text-sm font-bold text-slate-800">Review outcomes</p>
                        <p class="mt-1 text-sm text-slate-600">Use dashboards to monitor performance patterns and make timely interventions.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
