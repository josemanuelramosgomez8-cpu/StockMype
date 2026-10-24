<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Controla tu inventario y ventas con StockMype.">
        <title>StockMype | Control de inventario para tu negocio</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900">
        <main class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-6 py-16">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-700">StockMype · Tumbes, Perú</p>
                <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Tu inventario bajo control, todos los días.</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600">
                    Registra tus productos y ventas en un solo lugar. StockMype te ayuda a cuidar el stock de tu negocio.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-700 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-600">
                        Crear cuenta de negocio
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                        Iniciar sesión
                    </a>
                </div>
            </div>
        </main>
    </body>
</html>
