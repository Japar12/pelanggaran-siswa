<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Section: Manajemen Users --}}
        @if(auth()->user()->hasRole('admin'))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900">
                    <x-heroicon-o-user-group class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Manajemen Users</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Export, import, dan kelola data pengguna</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                @foreach($this->getUserActions() as $action)
                    @if($action->isVisible())
                        {{ $action }}
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Section: Manajemen Siswa --}}
        @if(auth()->user()->hasRole('admin'))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 dark:bg-green-900">
                    <x-heroicon-o-academic-cap class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Manajemen Siswa</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Export, import, dan kelola data siswa</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                @foreach($this->getStudentActions() as $action)
                    @if($action->isVisible())
                        {{ $action }}
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Section: Manajemen Pelanggaran --}}
        @if(auth()->user()->hasAnyRole(['admin', 'guru']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-100 dark:bg-yellow-900">
                    <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Manajemen Pelanggaran</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Export data pelanggaran dengan filter</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                @foreach($this->getViolationActions() as $action)
                    @if($action->isVisible())
                        {{ $action }}
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Info Card --}}
        <div class="bg-blue-50 dark:bg-blue-950 rounded-lg border border-blue-200 dark:border-blue-800 p-4">
            <div class="flex gap-2">
                <x-heroicon-o-information-circle class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                <div class="text-xs text-blue-800 dark:text-blue-300">
                    <p class="font-medium mb-1">Panduan Penggunaan:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-blue-700 dark:text-blue-400">
                        <li>Download template terlebih dahulu sebelum melakukan import</li>
                        <li>Pastikan format file Excel sesuai dengan template yang disediakan</li>
                        <li>Backup database dilakukan secara otomatis dan tersimpan di storage/app/backups</li>
                        <li>Export pelanggaran dapat difilter berdasarkan tanggal dan kelas</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
