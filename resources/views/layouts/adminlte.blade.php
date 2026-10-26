<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel - Ferretería Jack Sparkys')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-900 text-slate-100 flex flex-col min-h-screen">
    <header class="bg-slate-800 border-b border-slate-700/80 sticky top-0 z-40 px-4 lg:px-6 py-2.5 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-3">
            <button onclick="toggleSidebar()" class="text-slate-300 hover:text-white p-2 rounded-lg bg-slate-700/50 hover:bg-slate-700 transition lg:hidden">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-black text-xl shadow-md shadow-amber-500/20">
                    <i class="fa-solid fa-hammer"></i>
                </div>
                <div>
                    <div class="font-extrabold text-sm sm:text-base text-amber-400 tracking-tight flex items-center gap-1.5">
                        JACK SPARKY'S
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30">SAC</span>
                    </div>
                    <div class="text-[10px] text-slate-400 italic hidden sm:block">"Tu mejor aliado, en tus proyectos"</div>
                </div>
            </a>
        </div>

        <div class="hidden md:flex items-center gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-full bg-slate-700/60 text-slate-300 border border-slate-600/50">
                <i class="fa-solid fa-building text-amber-400 mr-1"></i> RUC: <strong>20459862922</strong>
            </span>
            <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                <i class="fa-solid fa-receipt mr-1"></i> SUNAT: Boleta / Factura (IGV 18%)
            </span>
        </div>

        <div class="flex items-center gap-3">
            @php
                $user = Auth::user();
                $rolNombre = $user->rol->nombre ?? 'Usuario';
                $badgeColor = match($user->id_rol ?? 1) {
                    1 => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                    2 => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
                    3 => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                    4 => 'bg-purple-500/20 text-purple-300 border-purple-500/40',
                    default => 'bg-slate-700 text-slate-300 border-slate-600'
                };
            @endphp
            <div class="hidden sm:flex flex-col text-right">
                <span class="text-xs font-bold text-slate-200">{{ $user->nombre ?? 'Usuario' }}</span>
                <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded border {{ $badgeColor }} inline-block w-fit ml-auto">
                    {{ $rolNombre }}
                </span>
            </div>

            <button onclick="abrirModalPerfil()" class="relative p-1 rounded-xl bg-slate-700/60 border border-slate-600 hover:border-amber-500 transition" title="Configurar Perfil">
                <div class="w-8 h-8 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-xs">
                    {{ strtoupper(substr($user->nombre ?? 'U', 0, 2)) }}
                </div>
            </button>

            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white border border-rose-500/20 transition text-xs font-bold flex items-center gap-1.5" title="Cerrar Sesión">
                    <i class="fa-solid fa-power-off"></i>
                    <span class="hidden sm:inline">Salir</span>
                </button>
            </form>
        </div>
    </header>    <div class="flex flex-1 overflow-hidden">
        <aside id="sidebar" class="w-64 bg-slate-800 border-r border-slate-700/80 flex-shrink-0 flex flex-col justify-between overflow-y-auto transition-all duration-300 hidden lg:flex">
            <div>
                <div class="p-4 border-b border-slate-700/60 bg-slate-850 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 flex items-center justify-center font-extrabold text-sm shadow">
                        {{ strtoupper(substr($user->nombre ?? 'U', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-slate-200 truncate">{{ $user->nombre ?? 'Usuario' }}</div>
                        <div class="text-[11px] text-amber-400 font-semibold truncate">{{ $rolNombre }}</div>
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] text-slate-400">En línea</span>
                        </div>
                    </div>
                </div>

                <nav class="p-3 space-y-1 text-xs font-semibold">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Menú Principal</div>

                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                        <span>Panel Principal</span>
                    </a>

                    @if($user->esAdmin() || $user->esVendedor() || $user->esCajero())
                        <div class="pt-2 px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ventas y Caja</div>

                        <a href="{{ url('/ventas/pos') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ventas.pos') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-cart-shopping w-4 text-center"></i>
                            <span>Registrar Venta (POS)</span>
                        </a>

                        <a href="{{ url('/ventas') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ventas.index') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-receipt w-4 text-center"></i>
                            <span>Historial de Ventas</span>
                        </a>

                        <a href="{{ url('/caja') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('caja.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-cash-register w-4 text-center"></i>
                            <span>Control de Caja Chica</span>
                        </a>
                    @endif

                    @if($user->esAdmin() || $user->esVendedor() || $user->esCobrador())
                        <div class="pt-2 px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alquileres y Moras</div>

                        <a href="{{ url('/alquileres') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('alquileres.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-screwdriver-wrench w-4 text-center"></i>
                            <span>Alquiler de Equipos</span>
                        </a>

                        <a href="{{ url('/cobranzas') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('cobranzas.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-hand-holding-dollar w-4 text-center"></i>
                            <span>Gestión de Cobranzas</span>
                            <span class="ml-auto bg-rose-500/20 text-rose-300 text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-rose-500/30">Moras</span>
                        </a>
                    @endif                    @if($user->esAdmin() || $user->esVendedor() || $user->esAlmacenero())
                        <div class="pt-2 px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Inventario & Logística</div>

                        <a href="{{ url('/productos') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('productos.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-boxes-stacked w-4 text-center"></i>
                            <span>Catálogo de Productos</span>
                        </a>

                        <a href="{{ url('/categorias') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('categorias.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-tags w-4 text-center"></i>
                            <span>Categorías</span>
                        </a>

                        <a href="{{ url('/marcas') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('marcas.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-certificate w-4 text-center"></i>
                            <span>Marcas</span>
                        </a>

                        <a href="{{ url('/proveedores') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('proveedores.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-truck-field w-4 text-center"></i>
                            <span>Proveedores</span>
                        </a>
                    @endif

                    <div class="pt-2 px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Clientes y Contactos</div>

                    <a href="{{ url('/clientes') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('clientes.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                        <i class="fa-solid fa-users w-4 text-center"></i>
                        <span>Directorio de Clientes</span>
                    </a>

                    @if($user->esAdmin())
                        <div class="pt-2 px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Administración del Sistema</div>

                        <a href="{{ url('/usuarios') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('usuarios.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-user-shield w-4 text-center"></i>
                            <span>Gestión de Usuarios</span>
                        </a>

                        <a href="{{ url('/auditoria') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('auditoria.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-clock-rotate-left w-4 text-center"></i>
                            <span>Registro de Auditoría</span>
                        </a>

                        <a href="{{ url('/configuracion') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('configuracion.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                            <i class="fa-solid fa-sliders w-4 text-center"></i>
                            <span>Configuración de Empresa</span>
                        </a>
                    @endif
                </nav>
            </div>            <div class="p-3 border-t border-slate-700/80 bg-slate-850">
                <div class="text-[10px] text-slate-400 font-bold uppercase mb-1.5 flex items-center justify-between">
                    <span>Cambiar de Perfil (Demo)</span>
                    <i class="fa-solid fa-rotate text-amber-500"></i>
                </div>
                <div class="grid grid-cols-2 gap-1 text-[11px]">
                    <a href="{{ route('quick.login', 'admin') }}" class="px-2 py-1 rounded bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-medium transition text-center border border-slate-700">👑 Admin</a>
                    <a href="{{ route('quick.login', 'vendedor') }}" class="px-2 py-1 rounded bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-medium transition text-center border border-slate-700">🛒 Vendedor</a>
                    <a href="{{ route('quick.login', 'almacen') }}" class="px-2 py-1 rounded bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-medium transition text-center border border-slate-700">📦 Almacén</a>
                    <a href="{{ route('quick.login', 'cobranzas') }}" class="px-2 py-1 rounded bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-medium transition text-center border border-slate-700">📋 Cobranzas</a>
                </div>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto bg-slate-900 p-4 lg:p-6 flex flex-col">
            @if(session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between shadow-lg">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="mb-5 p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-sm flex items-center justify-between shadow-lg">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-rose-400 text-lg"></i>
                        <span>{{ session('error') ?? $errors->first() }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>    <!-- Modal Mi Perfil de Usuario -->
    <div id="modalPerfil" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-2xl max-w-md w-full border border-slate-700 shadow-2xl overflow-hidden">
            <div class="p-5 border-b border-slate-700 flex items-center justify-between bg-slate-850">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-100 text-sm">Configuración de Mi Perfil</h3>
                        <p class="text-[11px] text-slate-400">Actualizar datos de contacto y contraseña</p>
                    </div>
                </div>
                <button onclick="cerrarModalPerfil()" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="p-5 space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nombre Completo</label>
                    <input type="text" name="nombre" value="{{ $user->nombre ?? '' }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 focus:outline-none focus:border-amber-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Teléfono / Celular</label>
                        <input type="text" name="telefono" value="{{ $user->telefono ?? '' }}" placeholder="987654321" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 focus:outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Rol en Sistema</label>
                        <input type="text" disabled value="{{ $rolNombre }}" class="w-full bg-slate-900/50 border border-slate-700/60 rounded-xl p-2.5 text-slate-400 cursor-not-allowed">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Dirección</label>
                    <input type="text" name="direccion" value="{{ $user->direccion ?? '' }}" placeholder="Av. Principal 123" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Sobre Mí</label>
                    <textarea name="sobre_mi" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 focus:outline-none focus:border-amber-500">{{ $user->sobre_mi ?? '' }}</textarea>
                </div>
                <div class="pt-2 border-t border-slate-700">
                    <label class="block font-semibold text-amber-400 mb-1">Cambiar Contraseña (opcional)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="password" name="password" placeholder="Nueva clave" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 focus:outline-none focus:border-amber-500">
                        <input type="password" name="password_confirmation" placeholder="Confirmar clave" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <div class="pt-3 flex gap-2">
                    <button type="button" onclick="cerrarModalPerfil()" class="w-1/2 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl font-bold transition">Cancelar</button>
                    <button type="submit" class="w-1/2 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl font-bold transition">Guardar</button>
                </div>
            </form>
        </div>
    </div>    <!-- Modal Universal de Confirmación CRUD -->
    <div id="modalConfirmacion" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-2xl max-w-sm w-full p-6 text-center border border-slate-700 shadow-2xl">
            <div class="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center text-xl mx-auto mb-3 border border-rose-500/30">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 id="modalConfirmacionTitulo" class="text-base font-bold text-slate-100">¿Confirmar Acción?</h3>
            <p id="modalConfirmacionMensaje" class="text-xs text-slate-400 mt-2 leading-relaxed">¿Está seguro de proceder con esta operación?</p>
            <div class="mt-5 flex gap-2">
                <button type="button" onclick="cerrarModalConfirmacion()" class="w-1/2 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl font-bold text-xs transition">Cancelar</button>
                <button type="button" id="btnEjecutarConfirmacion" class="w-1/2 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-xs transition">Aceptar</button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
        }
        function abrirModalPerfil() {
            document.getElementById('modalPerfil').classList.remove('hidden');
        }
        function cerrarModalPerfil() {
            document.getElementById('modalPerfil').classList.add('hidden');
        }
        let confirmCallback = null;
        function confirmarAccion(titulo, mensaje, cb) {
            document.getElementById('modalConfirmacionTitulo').innerText = titulo;
            document.getElementById('modalConfirmacionMensaje').innerText = mensaje;
            confirmCallback = cb;
            document.getElementById('modalConfirmacion').classList.remove('hidden');
        }
        document.getElementById('btnEjecutarConfirmacion').onclick = function() {
            if (confirmCallback) confirmCallback();
            cerrarModalConfirmacion();
        };
        function cerrarModalConfirmacion() {
            document.getElementById('modalConfirmacion').classList.add('hidden');
            confirmCallback = null;
        }
    </script>
    @yield('scripts')
</body>
</html>