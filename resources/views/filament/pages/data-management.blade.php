<x-filament::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Manajemen Data Pengguna & Siswa</x-slot>
            <x-slot name="description">
                Import & Export data pengguna dan siswa dalam format Excel.
            </x-slot>

            <div class="flex flex-wrap gap-3">
                {{ $this->getAction('exportUsers') }}
                {{ $this->getAction('importUsers') }}
                {{ $this->getAction('exportStudents') }}
                {{ $this->getAction('importStudents') }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Data Pelanggaran & Backup</x-slot>
            <x-slot name="description">
                Export data pelanggaran siswa dan buat backup database.
            </x-slot>

            <div class="flex flex-wrap gap-3">
                {{ $this->getAction('exportViolations') }}
                {{ $this->getAction('backupDatabase') }}
            </div>
        </x-filament::section>
    </div>
</x-filament::page>
