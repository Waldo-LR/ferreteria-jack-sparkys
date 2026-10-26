<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - Ferretería Jack Sparkys</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .login-bg {
            background-image: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.92)), url('https://images.unsplash.com/photo-1581783898377-1c85bf937427?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="h-full login-bg font-sans antialiased text-slate-800 flex items-center justify-center p-4 min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-100">
            <div class="p-8 pb-6 text-center border-b border-slate-100 bg-gradient-to-b from-amber-50 to-white">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500 text-slate-950 text-3xl font-extrabold shadow-lg shadow-amber-500/30 mb-3">
                    <i class="fa-solid fa-hammer"></i>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">
                    Ferretería <span class="text-amber-600">Jack Sparkys</span>
                </h1>
                <p class="text-xs font-semibold text-amber-700 mt-1 italic tracking-wide">
                    "Tu mejor aliado, en tus proyectos"
                </p>
                <div class="mt-2 inline-block px-3 py-0.5 bg-slate-100 rounded-full text-[11px] font-medium text-slate-500">
                    :: Quillabamba - Perú ::
                </div>
            </div>

            <div class="p-8 pt-6">
                @if(session('success'))
                    <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="usuario" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Usuario o Correo Electrónico
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <input type="text" id="usuario" name="usuario" value="{{ old('usuario') }}" required autofocus placeholder="Ej: admin o usuario@jacksparkys.com" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-3 pl-4 pr-11 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Contraseña
                            </label>
                            <a href="javascript:void(0)" onclick="mostrarModalOlvide()" class="text-xs font-medium text-amber-600 hover:text-amber-700 hover:underline">
                                ¿Olvidé mi contraseña?
                            </a>
                        </div>
                        <div class="relative rounded-xl shadow-sm">
                            <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-3 pl-4 pr-11 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition focus:outline-none" title="Mostrar / Ocultar">
                                <i id="togglePasswordIcon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl shadow-lg shadow-amber-500/25 focus:outline-none focus:ring-2 focus:ring-amber-500 transition duration-150 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>Ingresar al Sistema</span>
                        </button>
                    </div>
                </form>                <!-- Selector de Perfiles Demo -->
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-500 text-center uppercase tracking-wider mb-2.5">
                        <i class="fa-solid fa-key text-amber-500 mr-1"></i> Acceso Rápido por Perfil (Demo)
                    </p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <a href="{{ route('quick.login', 'admin') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-semibold text-center transition border border-slate-200">
                            👑 Administrador
                        </a>
                        <a href="{{ route('quick.login', 'vendedor') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-semibold text-center transition border border-slate-200">
                            🛒 Vendedor
                        </a>
                        <a href="{{ route('quick.login', 'almacen') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-semibold text-center transition border border-slate-200">
                            📦 Almacenero
                        </a>
                        <a href="{{ route('quick.login', 'cobranzas') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-semibold text-center transition border border-slate-200">
                            📋 Cobranzas
                        </a>
                    </div>
                </div>
            </div>

            <div class="py-3 px-6 bg-slate-50 border-t border-slate-100 text-center text-[11px] text-slate-400">
                Ferretería Jack Sparkys S.A.C. &copy; {{ date('Y') }} — RUC 20459862922
            </div>
        </div>
    </div>

    <!-- Modal ¿Olvidé mi contraseña? -->
    <div id="modalOlvide" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl border border-slate-100">
            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xl mx-auto mb-3">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900">Recuperación de Contraseña</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Por políticas de seguridad, para restablecer tu clave de acceso solicita asistencia al Administrador del Sistema:
            </p>
            <div class="my-4 p-3 bg-slate-50 rounded-xl text-xs text-slate-700 text-left space-y-1 border border-slate-200">
                <div><i class="fa-solid fa-envelope text-amber-600 mr-1.5"></i> <strong>Soporte:</strong> soporte@jacksparkys.com</div>
                <div><i class="fa-solid fa-phone text-amber-600 mr-1.5"></i> <strong>Central:</strong> (01) 456-7890 anexo 102</div>
            </div>
            <button type="button" onclick="cerrarModalOlvide()" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition">
                Entendido, regresar
            </button>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        function mostrarModalOlvide() {
            document.getElementById('modalOlvide').classList.remove('hidden');
        }
        function cerrarModalOlvide() {
            document.getElementById('modalOlvide').classList.add('hidden');
        }
    </script>
</body>
</html>