<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-white leading-tight">
            👤 {{ __('Perfil de Usuario') }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto space-y-4 sm:space-y-6">
            <div class="p-4 sm:p-6 lg:p-8 bg-white shadow-xs rounded-xl sm:rounded-2xl border border-gray-100">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-6 lg:p-8 bg-white shadow-xs rounded-xl sm:rounded-2xl border border-gray-100">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-6 lg:p-8 bg-white shadow-xs rounded-xl sm:rounded-2xl border border-gray-100">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
