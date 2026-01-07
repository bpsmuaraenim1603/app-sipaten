<x-main-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Profil
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-6 bg-white shadow sm:rounded-lg">
                <div class="flex items-start gap-6">
                    <div class="shrink-0">
                        @if(auth()->user()->foto_url)
                            <img src="{{ auth()->user()->foto_url }}" alt="Foto Profil"
                                 class="w-24 h-24 rounded-full object-cover border" />
                        @else
                            <div class="w-24 h-24 rounded-full bg-gray-100 border flex items-center justify-center text-gray-500">
                                N/A
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 space-y-2">
                        <div class="text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</div>
                        <div class="text-sm text-gray-600">{{ auth()->user()->username ?? '-' }}</div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 text-sm">
                            <div><span class="font-medium">NIP:</span> {{ auth()->user()->nip ?? '-' }}</div>
                            <div><span class="font-medium">Email:</span> {{ auth()->user()->email ?? '-' }}</div>

                            <div><span class="font-medium">Jabatan:</span> {{ auth()->user()->jabatan ?? '-' }}</div>
                            <div><span class="font-medium">Unit Kerja:</span> {{ auth()->user()->unit_kerja ?? '-' }}</div>

                            <div><span class="font-medium">Satker:</span> {{ auth()->user()->satker ?? '-' }}</div>
                            <div><span class="font-medium">Golongan:</span> {{ auth()->user()->golongan ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white shadow sm:rounded-lg">
                <h3 class="text-base font-semibold text-gray-900">Keluar</h3>
                <p class="text-sm text-gray-600 mt-1">Logout dari SIPATEN (SSO masih bisa tetap login di browser).</p>

                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Log Out
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-main-layout>
