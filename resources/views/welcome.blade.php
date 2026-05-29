<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Psico-Guía | Tu bienestar emocional en buenas manos</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
            .glass-nav {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            }
            .hero-gradient {
                background: radial-gradient(circle at top right, rgba(186, 230, 253, 0.3) 0%, transparent 40%),
                            radial-gradient(circle at bottom left, rgba(186, 230, 253, 0.3) 0%, transparent 40%);
            }
            .feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            }
        </style>
    </head>
    <body class="antialiased bg-slate-50 text-slate-900">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 glass-nav">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex justify-between h-20 items-center">
                    <!-- Logo -->
                    <a href="#" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 bg-sky-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-sky-200 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xl font-black text-slate-900 leading-none">Psico-Guía</span>
                            
                        </div>
                    </a>

                    <!-- Links -->
                    <div class="hidden md:flex items-center gap-8">
                        <a href="#home" class="text-sm font-bold text-slate-600 hover:text-sky-600 transition-colors">Inicio</a>
                        <a href="#vision" class="text-sm font-bold text-slate-600 hover:text-sky-600 transition-colors">Misión y Visión</a>
                        <a href="#servicios" class="text-sm font-bold text-slate-600 hover:text-sky-600 transition-colors">Servicios</a>
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-sky-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-sky-200 hover:bg-sky-600 transition-all">Panel de Control</a>
                            @else
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('login') }}" class="px-5 py-2.5 text-sky-600 text-sm font-bold hover:bg-sky-50 rounded-xl transition-colors">Iniciar Sesión</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="px-5 py-2.5 bg-sky-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-sky-200 hover:bg-sky-600 transition-all">Registrarse</a>
                                    @endif
                                </div>
                            @endauth
                        @endif
                    </div>

                    <!-- Mobile Menu Btn -->
                    <button class="md:hidden p-2 text-slate-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main id="home" class="pt-32 pb-20 hero-gradient overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-sky-50 border border-sky-100 rounded-full mb-8">
                    <span class="flex h-2 w-2 rounded-full bg-sky-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-sky-700 uppercase tracking-wider">Plataforma Psicológica de la Universidad Politécnica Territorial de Portuguesa "Juan de Jesús Montilla"</span>
                </div>

                <!-- Headline -->
                <h1 class="text-5xl md:text-7xl font-black text-slate-900 tracking-tight leading-[1.1] mb-8">
                    Tu bienestar emocional<br>
                    en buenas <span class="text-sky-500">manos</span>
                </h1>

                <!-- Subheadline -->
                <p class="max-w-2xl mx-auto text-lg text-slate-500 leading-relaxed mb-12 font-medium">
                    Acompañamos tu salud mental con atención accesible, estructurada y empática para toda la comunidad universitaria.
                </p>

                <!-- CTA -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-20">
                    <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-sky-500 text-white font-black rounded-2xl shadow-xl shadow-sky-200 hover:bg-sky-600 hover:-translate-y-1 transition-all">
                        Solicitar cita
                    </a>
                    <a href="#vision" class="w-full sm:w-auto px-8 py-4 bg-white text-slate-700 font-black rounded-2xl border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all">
                        Conocer más
                    </a>
                </div>

                <!-- Stats Bar -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto bg-white/60 backdrop-blur-sm p-2 rounded-[2rem] border border-white/50 shadow-xl shadow-slate-200/50">
                    <div class="flex flex-col p-8 rounded-3xl bg-white shadow-sm border border-slate-100">
                        <span class="text-4xl font-black text-sky-600 mb-1">+300</span>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pacientes Atendidos</span>
                    </div>
                    <div class="flex flex-col p-8 rounded-3xl bg-white shadow-sm border border-slate-100">
                        <span class="text-4xl font-black text-sky-600 mb-1">12</span>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Psicólogos Activos</span>
                    </div>
                    <div class="flex flex-col p-8 rounded-3xl bg-white shadow-sm border border-slate-100">
                        <span class="text-4xl font-black text-sky-600 mb-1">98%</span>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Satisfacción</span>
                    </div>
                </div>
            </div>
        </main>

        <!-- Services Grid -->
        <section id="servicios" class="py-24 bg-white relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="text-xs font-black text-sky-500 uppercase tracking-[0.3em] mb-4 block">¿Qué ofrecemos?</span>
                    <h2 class="text-4xl font-black text-slate-900">Todo lo que necesitas, en un solo lugar</h2>
                    <p class="mt-4 text-slate-500 font-medium">Herramientas pensadas para cada rol dentro del sistema clínico universitario.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature Cards -->
                    <div class="feature-card p-8 rounded-[2rem] bg-slate-50 border border-slate-100 transition-all">
                        <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Gestión de citas</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Agenda, reagenda y cancela consultas fácilmente desde cualquier dispositivo.</p>
                    </div>

                    <div class="feature-card p-8 rounded-[2rem] bg-slate-50 border border-slate-100 transition-all">
                        <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Historial clínico</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Expediente completo por paciente con registro de evolución y evolución.</p>
                    </div>

                    <div class="feature-card p-8 rounded-[2rem] bg-slate-50 border border-slate-100 transition-all">
                        <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Mensajería segura</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Comunicación directa entre paciente y psicólogo, privada y cifrada.</p>
                    </div>

                    <div class="feature-card p-8 rounded-[2rem] bg-slate-50 border border-slate-100 transition-all">
                        <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Gestión de pacientes</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Panel detallado con información personal, patológica y seguimiento activo.</p>
                    </div>

                    <div class="feature-card p-8 rounded-[2rem] bg-slate-50 border border-slate-100 transition-all">
                        <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Reportes y estadísticas</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Visualización técnica de atención, asistencia y evaluación de servicio.</p>
                    </div>

                    <div class="feature-card p-8 rounded-[2rem] bg-slate-50 border border-slate-100 transition-all">
                        <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Horarios y bloques</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Gestión de disponibilidad y bloques de atención de forma flexible.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Profiles Section -->
        <section class="py-24 bg-slate-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="text-xs font-black text-sky-500 uppercase tracking-[0.3em] mb-4 block">Para todos</span>
                    <h2 class="text-4xl font-black text-slate-900">Un sistema, tres perfiles</h2>
                    <p class="mt-4 text-slate-500 font-medium">Cada usuario tiene su propio espacio adaptado a su rol en el sistema.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Admin -->
                    <div class="p-8 rounded-[2rem] bg-white border border-slate-100 shadow-sm">
                        <div class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center font-bold mb-6">A</div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Administrador</span>
                        <h3 class="text-lg font-black text-slate-900 mb-4">Control total del sistema</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Gestiona usuarios, configura accesos y revisa reportes globales del servicio.</p>
                    </div>

                    <!-- Psicologo -->
                    <div class="p-8 rounded-[2rem] bg-sky-50 border border-sky-100 shadow-sm">
                        <div class="w-10 h-10 bg-sky-500 text-white rounded-xl flex items-center justify-center font-bold mb-6">P</div>
                        <span class="text-[10px] font-black text-sky-600 uppercase tracking-widest mb-2 block">Psicólogo</span>
                        <h3 class="text-lg font-black text-slate-900 mb-4">Tu consulta, digitalizada</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Accede a expedientes, registra sesiones y gestiona tu agenda fácilmente.</p>
                    </div>

                    <!-- Paciente -->
                    <div class="p-8 rounded-[2rem] bg-emerald-50 border border-emerald-100 shadow-sm">
                        <div class="w-10 h-10 bg-emerald-500 text-white rounded-xl flex items-center justify-center font-bold mb-6">E</div>
                        <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2 block">Paciente</span>
                        <h3 class="text-lg font-black text-slate-900 mb-4">Tu bienestar, primero</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Solicita citas, revisa tu historial y mantente en contacto con tu psicólogo.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quote Section -->
        <section id="vision" class="py-24 bg-white">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <svg class="w-12 h-12 text-sky-200 mx-auto mb-8" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H15.017C14.4647 8 14.017 8.44772 14.017 9V11C14.017 11.5523 13.5693 12 13.017 12H12.017V21H14.017ZM5.0166 21L5.0166 18C5.0166 16.8954 5.91198 16 7.0166 16H10.0166C10.5689 16 11.0166 15.5523 11.0166 15V9C11.0166 8.44772 10.5689 8 10.0166 8H6.0166C5.46432 8 5.0166 8.44772 5.0166 9V11C5.0166 11.5523 4.56888 12 4.0166 12H3.0166V21H5.0166Z" /></svg>
                <blockquote class="text-2xl md:text-3xl font-medium text-slate-700 italic leading-snug mb-10">
                    "Cultivando equilibrio mental para un rendimiento académico sostenible."
                </blockquote>
                <p class="text-xs font-black text-slate-400 uppercase tracking-[0.5em]">Psico-Guía UPTP • Misión Institucional</p>
            </div>
        </section>

        <!-- Simple Footer -->
        <footer class="py-12 bg-slate-50 border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-black text-slate-900">Psico-Guía</span>
                    <span class="text-xs font-bold text-sky-600">UPTP</span>
                </div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center">
                    © 2026 Psico-Guía UPTP • Todos los derechos reservados
                </div>
                <div class="flex gap-6">
                    <a href="#" class="text-xs font-bold text-slate-400 hover:text-sky-600 transition-colors">Soporte</a>
                    <a href="#" class="text-xs font-bold text-slate-400 hover:text-sky-600 transition-colors">Privacidad</a>
                </div>
            </div>
        </footer>
    </body>
</html>
