<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Vellix Tracking') }}</title>

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */ @layer properties{@supports (((-webkit-hyphens:none)) and (not (margin-trim:inline))) or ((-moz-orient:inline) and (not (color:rgb(from red r g b)))){*,:before,:after,::backdrop{--tw-translate-x:0;--tw-translate-y:0;--tw-translate-z:0;--tw-rotate-x:initial;--tw-rotate-y:initial;--tw-rotate-z:initial;--tw-skew-x:initial;--tw-skew-y:initial;--tw-space-x-reverse:0;--tw-border-style:solid;--tw-leading:initial;--tw-font-weight:initial;--tw-tracking:initial;--tw-shadow:0 0 #0000;--tw-shadow-color:initial;--tw-shadow-alpha:100%;--tw-inset-shadow:0 0 #0000;--tw-inset-shadow-color:initial;--tw-inset-shadow-alpha:100%;--tw-ring-color:initial;--tw-ring-shadow:0 0 #0000;--tw-inset-ring-color:initial;--tw-inset-ring-shadow:0 0 #0000;--tw-ring-inset:initial;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-offset-shadow:0 0 #0000;--tw-blur:initial;--tw-brightness:initial;--tw-contrast:initial;--tw-grayscale:initial;--tw-hue-rotate:initial;--tw-invert:initial;--tw-opacity:initial;--tw-saturate:initial;--tw-sepia:initial;--tw-drop-shadow:initial;--tw-drop-shadow-color:initial;--tw-drop-shadow-alpha:100%;--tw-drop-shadow-size:initial;--tw-duration:initial;--tw-ease:initial;--tw-content:""}}@layer theme{:root,:host{--font-sans:"Instrument Sans", ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";--font-serif:ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;--font-mono:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;--color-red-50:oklch(97.1% .013 17.38);--color-red-100:oklch(93.6% .032 17.717);--color-red-200:oklch(88.5% .062 18.334);--color-red-300:oklch(80.8% .114 19.571);--color-red-400:oklch(70.4% .191 22.216);--color-red-500:oklch(63.7% .237 25.331);--color-red-600:oklch(57.7% .245 27.325);--color-red-700:oklch(50.5% .213 27.518);--color-red-800:oklch(44.4% .177 26.899);--color-red-900:oklch(39.2% .141 27.275);--color-orange-50:oklch(98.1% .013 60.66);--color-orange-100:oklch(95.4% .038 59.773);--color-orange-200:oklch(90.7% .082 58.906);--color-orange-300:oklch(84.1% .132 58.611);--color-orange-400:oklch(75.8% .178 59.714);--color-orange-500:oklch(66.8% .207 59.229);--color-orange-600:oklch(60.2% .196 60.716);--color-orange-700:oklch(52.6% .159 60.736);--color-orange-800:oklch(46.6% .128 60.933);--color-orange-900:oklch(40.8% .105 60.714);--color-amber-50:oklch(98.1% .013 85.54);--color-amber-100:oklch(95.4% .041 84.812);--color-amber-200:oklch(90.7% .09 84.951);--color-amber-300:oklch(84.1% .151 85.134);--color-amber-400:oklch(75.8% .202 85.236);--color-amber-500:oklch(66.8% .236 85.433);--color-amber-600:oklch(60.2% .221 86.044);--color-amber-700:oklch(52.6% .182 86.044);--color-amber-800:oklch(46.6% .147 86.044);--color-amber-900:oklch(40.8% .121 86.044);--color-yellow-50:oklch(98.1% .015 110.58);--color-yellow-100:oklch(95.5% .048 109.781);--color-yellow-200:oklch(90.9% .105 109.951);--color-yellow-300:oklch(84.4% .172 110.113);--color-yellow-400:oklch(76.2% .23 110.248);--color-yellow-500:oklch(67.3% .268 110.455);--color-yellow-600:oklch(60.8% .251 111.119);--color-yellow-700:oklch(53.3% .207 111.119);--color-yellow-800:oklch(47.3% .167 111.119);--color-yellow-900:oklch(41.6% .138 111.119);--color-lime-50:oklch(98.1% .014 132.73);--color-lime-100:oklch(95.6% .042 131.934);--color-lime-200:oklch(91.1% .093 132.106);--color-lime-300:oklch(84.7% .154 132.274);--color-lime-400:oklch(76.6% .205 132.371);--color-lime-500:oklch(67.9% .239 132.567);--color-lime-600:oklch(61.5% .224 133.186);--color-lime-700:oklch(54.1% .185 133.186);--color-lime-800:oklch(48.2% .149 133.186);--color-lime-900:oklch(42.6% .123 133.186);--color-green-50:oklch(98.1% .012 154.62);--color-green-100:oklch(95.6% .042 153.828);--color-green-200:oklch(91.1% .092 153.998);--color-green-300:oklch(84.7% .152 154.165);--color-green-400:oklch(76.6% .202 154.262);--color-green-500:oklch(67.9% .236 154.457);--color-green-600:oklch(61.5% .221 155.076);--color-green-700:oklch(54.1% .182 155.076);--color-green-800:oklch(48.2% .147 155.076);--color-green-900:oklch(42.6% .121 155.076);--color-emerald-50:oklch(98.1% .012 164.19);--color-emerald-100:oklch(95.6% .042 163.398);--color-emerald-200:oklch(91.1% .092 163.568);--color-emerald-300:oklch(84.7% .152 163.735);--color-emerald-400:oklch(76.6% .202 163.832);--color-emerald-500:oklch(67.9% .236 164.027);--color-emerald-600:oklch(61.5% .221 164.646);--color-emerald-700:oklch(54.1% .182 164.646);--color-emerald-800:oklch(48.2% .147 164.646);--color-emerald-900:oklch(42.6% .121 164.646);--color-teal-50:oklch(98.1% .011 174.31);--color-teal-100:oklch(95.6% .041 173.518);--color-teal-200:oklch(91.1% .091 173.688);--color-teal-300:oklch(84.7% .151 173.855);--color-teal-400:oklch(76.6% .201 173.952);--color-teal-500:oklch(67.9% .235 174.147);--color-teal-600:oklch(61.5% .22 174.766);--color-teal-700:oklch(54.1% .181 174.766);--color-teal-800:oklch(48.2% .146 174.766);--color-teal-900:oklch(42.6% .12 174.766);--color-cyan-50:oklch(98.1% .011 187.06);--color-cyan-100:oklch(95.6% .041 186.268);--color-cyan-200:oklch(91.1% .091 186.438);--color-cyan-300:oklch(84.7% .151 186.605);--color-cyan-400:oklch(76.6% .201 186.702);--color-cyan-500:oklch(67.9% .235 186.897);--color-cyan-600:oklch(61.5% .22 187.516);--color-cyan-700:oklch(54.1% .181 187.516);--color-cyan-800:oklch(48.2% .146 187.516);--color-cyan-900:oklch(42.6% .12 187.516);--color-sky-50:oklch(98.1% .012 200.87);--color-sky-100:oklch(95.6% .042 200.078);--color-sky-200:oklch(91.1% .092 200.248);--color-sky-300:oklch(84.7% .152 200.415);--color-sky-400:oklch(76.6% .202 200.512);--color-sky-500:oklch(67.9% .236 200.707);--color-sky-600:oklch(61.5% .221 201.326);--color-sky-700:oklch(54.1% .182 201.326);--color-sky-800:oklch(48.2% .147 201.326);--color-sky-900:oklch(42.6% .121 201.326);--color-blue-50:oklch(98.1% .012 237.54);--color-blue-100:oklch(95.6% .042 236.748);--color-blue-200:oklch(91.1% .092 236.918);--color-blue-300:oklch(84.7% .152 237.085);--color-blue-400:oklch(76.6% .202 237.182);--color-blue-500:oklch(67.9% .236 237.377);--color-blue-600:oklch(61.5% .221 237.996);--color-blue-700:oklch(54.1% .182 237.996);--color-blue-800:oklch(48.2% .147 237.996);--color-blue-900:oklch(42.6% .121 237.996);--color-indigo-50:oklch(98.1% .013 252.87);--color-indigo-100:oklch(95.6% .042 252.078);--color-indigo-200:oklch(91.1% .092 252.248);--color-indigo-300:oklch(84.7% .152 252.415);--color-indigo-400:oklch(76.6% .202 252.512);--color-indigo-500:oklch(67.9% .236 252.707);--color-indigo-600:oklch(61.5% .221 253.326);--color-indigo-700:oklch(54.1% .182 253.326);--color-indigo-800:oklch(48.2% .147 253.326);--color-indigo-900:oklch(42.6% .121 253.326);--color-violet-50:oklch(98.1% .013 273.18);--color-violet-100:oklch(95.6% .042 272.388);--color-violet-200:oklch(91.1% .092 272.558);--color-violet-300:oklch(84.7% .152 272.725);--color-violet-400:oklch(76.6% .202 272.822);--color-violet-500:oklch(67.9% .236 273.017);--color-violet-600:oklch(61.5% .221 273.636);--color-violet-700:oklch(54.1% .182 273.636);--color-violet-800:oklch(48.2% .147 273.636);--color-violet-900:oklch(42.6% .121 273.636);--color-purple-50:oklch(98.1% .013 293.13);--color-purple-100:oklch(95.6% .042 292.338);--color-purple-200:oklch(91.1% .092 292.508);--color-purple-300:oklch(84.7% .152 292.675);--color-purple-400:oklch(76.6% .202 292.772);--color-purple-500:oklch(67.9% .236 292.967);--color-purple-600:oklch(61.5% .221 293.586);--color-purple-700:oklch(54.1% .182 293.586);--color-purple-800:oklch(48.2% .147 293.586);--color-purple-900:oklch(42.6% .121 293.586);--color-fuchsia-50:oklch(98.1% .013 315.41);--color-fuchsia-100:oklch(95.6% .042 314.618);--color-fuchsia-200:oklch(91.1% .092 314.788);--color-fuchsia-300:oklch(84.7% .152 314.955);--color-fuchsia-400:oklch(76.6% .202 315.052);--color-fuchsia-500:oklch(67.9% .236 315.247);--color-fuchsia-600:oklch(61.5% .221 315.866);--color-fuchsia-700:oklch(54.1% .182 315.866);--color-fuchsia-800:oklch(48.2% .147 315.866);--color-fuchsia-900:oklch(42.6% .121 315.866);--color-pink-50:oklch(98.1% .013 338.31);--color-pink-100:oklch(95.6% .042 337.518);--color-pink-200:oklch(91.1% .092 337.688);--color-pink-300:oklch(84.7% .152 337.855);--color-pink-400:oklch(76.6% .202 337.952);--color-pink-500:oklch(67.9% .236 338.147);--color-pink-600:oklch(61.5% .221 338.766);--color-pink-700:oklch(54.1% .182 338.766);--color-pink-800:oklch(48.2% .147 338.766);--color-pink-900:oklch(42.6% .121 338.766);--color-rose-50:oklch(98.1% .013 12.52);--color-rose-100:oklch(95.6% .042 11.728);--color-rose-200:oklch(91.1% .092 11.898);--color-rose-300:oklch(84.7% .152 12.065);--color-rose-400:oklch(76.6% .202 12.162);--color-rose-500:oklch(67.9% .236 12.357);--color-rose-600:oklch(61.5% .221 12.976);--color-rose-700:oklch(54.1% .182 12.976);--color-rose-800:oklch(48.2% .147 12.976);--color-rose-900:oklch(42.6% .121 12.976);--color-slate-50:oklch(98.3% .002 247.86);--color-slate-100:oklch(96.1% .013 247.86);--color-slate-200:oklch(92.4% .032 247.86);--color-slate-300:oklch(86.5% .062 247.86);--color-slate-400:oklch(76.1% .114 247.86);--color-slate-500:oklch(65.3% .191 247.86);--color-slate-600:oklch(56.1% .237 247.86);--color-slate-700:oklch(47.2% .213 247.86);--color-slate-800:oklch(41.3% .177 247.86);--color-slate-900:oklch(36.5% .141 247.86);--color-gray-50:oklch(98.3% .002 264.54);--color-gray-100:oklch(96.1% .013 264.54);--color-gray-200:oklch(92.4% .032 264.54);--color-gray-300:oklch(86.5% .062 264.54);--color-gray-400:oklch(76.1% .114 264.54);--color-gray-500:oklch(65.3% .191 264.54);--color-gray-600:oklch(56.1% .237 264.54);--color-gray-700:oklch(47.2% .213 264.54);--color-gray-800:oklch(41.3% .177 264.54);--color-gray-900:oklch(36.5% .141 264.54);--color-zinc-50:oklch(98.3% .002 281.23);--color-zinc-100:oklch(96.1% .013 281.23);--color-zinc-200:oklch(92.4% .032 281.23);--color-zinc-300:oklch(86.5% .062 281.23);--color-zinc-400:oklch(76.1% .114 281.23);--color-zinc-500:oklch(65.3% .191 281.23);--color-zinc-600:oklch(56.1% .237 281.23);--color-zinc-700:oklch(47.2% .213 281.23);--color-zinc-800:oklch(41.3% .177 281.23);--color-zinc-900:oklch(36.5% .141 281.23);--color-neutral-50:oklch(98.3% .002 264.54);--color-neutral-100:oklch(96.1% .013 264.54);--color-neutral-200:oklch(92.4% .032 264.54);--color-neutral-300:oklch(86.5% .062 264.54);--color-neutral-400:oklch(76.1% .114 264.54);--color-neutral-500:oklch(65.3% .191 264.54);--color-neutral-600:oklch(56.1% .237 264.54);--color-neutral-700:oklch(47.2% .213 264.54);--color-neutral-800:oklch(41.3% .177 264.54);--color-neutral-900:oklch(36.5% .141 264.54);--color-stone-50:oklch(98.3% .002 56.25);--color-stone-100:oklch(96.1% .013 56.25);--color-stone-200:oklch(92.4% .032 56.25);--color-stone-300:oklch(86.5% .062 56.25);--color-stone-400:oklch(76.1% .114 56.25);--color-stone-500:oklch(65.3% .191 56.25);--color-stone-600:oklch(56.1% .237 56.25);--color-stone-700:oklch(47.2% .213 56.25);--color-stone-800:oklch(41.3% .177 56.25);--color-stone-900:oklch(36.5% .141 56.25)}@layer base{*,::backdrop{border-color:var(--tw-zinc-200);color:var(--tw-zinc-950)}@layer utilities{.text-zinc-950{--tw-text-opacity:1;color:rgb(var(--color-zinc-950)/var(--tw-text-opacity))}}@media (prefers-color-scheme:dark){@layer base{*,::backdrop{border-color:var(--tw-zinc-800);color:var(--tw-zinc-50)}}@layer utilities{.dark\:text-zinc-50{--tw-text-opacity:1;color:rgb(var(--color-zinc-50)/var(--tw-text-opacity))}}}
            </style>
        @endif
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
                <div class="text-[13px] leading-[20px] flex-1 p-6 pb-6 lg:p-20 lg:pb-10 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
                    <h1 class="mb-1 font-medium">Link Tracking & Geo-Analytics</h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">Track your links with detailed analytics<br /> including geographic data and device information.</p>
                    <ul class="flex flex-col mb-4 lg:mb-6">
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:top-1/2 before:bottom-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px 1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>
                                Create short tracking URLs
                            </span>
                        </li>
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:bottom-1/2 before:top-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px 1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>
                                Track clicks and visitors
                            </span>
                        </li>
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:bottom-1/2 before:top-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px 1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>
                                Geographic analytics
                            </span>
                        </li>
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:bottom-1/2 before:top-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px 1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>
                                Device and browser detection
                            </span>
                        </li>
                    </ul>
                    <ul class="flex gap-3 text-sm leading-normal">
                        <li>
                            <a href="{{ route('login') }}" class="inline-block dark:bg-[#eeeeec] dark:border-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white dark:hover:border-white hover:bg-black hover:border-black px-5 py-1.5 bg-[#1b1b18] rounded-sm border border-black text-white text-sm leading-normal">
                                Get Started
                            </a>
                        </li>
                    </ul>

                    <p class="mt-6 lg:mt-10 text-[#706f6c] dark:text-[#A1A09A]">
                        v{{ app()->version() }}
                    </p>
                </div>
                <div class="bg-[#fff2f2] dark:bg-[#1D0002] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/364] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
                    {{-- Simple Logo --}}
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-6xl font-bold text-[#F53003] dark:text-[#F61500]">🔗</div>
                            <div class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mt-4">Vellix Tracking</div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>