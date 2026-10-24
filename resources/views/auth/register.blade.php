<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <h1 class="text-lg font-semibold text-gray-900">Crea la cuenta de tu negocio</h1>
        <p class="mt-1 text-sm text-gray-600">Al registrarte serás dueño y administrador de este negocio.</p>

        <div>
            <x-input-label for="negocio_nombre" value="Nombre del negocio" />
            <x-text-input id="negocio_nombre" class="block mt-1 w-full" type="text" name="negocio_nombre" :value="old('negocio_nombre')" required autofocus autocomplete="organization" />
            <x-input-error :messages="$errors->get('negocio_nombre')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="rubro" value="Rubro del negocio" />
            <select id="rubro" name="rubro" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Selecciona una opción</option>
                <option value="gastronomia" @selected(old('rubro') === 'gastronomia')>Gastronomía</option>
                <option value="retail" @selected(old('rubro') === 'retail')>Retail / tienda</option>
            </select>
            <x-input-error :messages="$errors->get('rubro')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="ruc" value="RUC (opcional)" />
            <x-text-input id="ruc" class="block mt-1 w-full" type="text" name="ruc" :value="old('ruc')" inputmode="numeric" maxlength="11" autocomplete="off" />
            <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
        </div>

        <div class="mt-6 border-t border-gray-200 pt-4">
            <x-input-label for="name" value="Tu nombre" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                ¿Ya tienes cuenta? Inicia sesión
            </a>

            <x-primary-button class="ms-4">
                Crear negocio y cuenta
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
