<x-filament::section>
    <div class="">
        <a href="{{ \App\Filament\Resources\Teachers\TeacherResource::getUrl('index', ['filters' => ['status' => ['value' => 'pending']]]) }}"
            class="px-4 py-2 bg-yellow-500/10 text-yellow-600 rounded-lg flex items-center gap-2">
            Pending
            <x-filament::badge color="warning">{{ $this->getData()['pending'] }}</x-filament::badge>
        </a>

        <a href="{{ \App\Filament\Resources\Teachers\TeacherResource::getUrl('index', ['filters' => ['status' => ['value' => 'approved']]]) }}"
            class="px-4 py-2 bg-green-500/10 text-green-600 rounded-lg flex items-center gap-2">
            Approved
            <x-filament::badge color="success">{{ $this->getData()['approved'] }}</x-filament::badge>
        </a>

        <a href="{{ \App\Filament\Resources\Teachers\TeacherResource::getUrl('index', ['filters' => ['status' => ['value' => 'rejected']]]) }}"
            class="px-4 py-2 bg-red-500/10 text-red-600 rounded-lg flex items-center gap-2">
            Rejected
            <x-filament::badge color="danger">{{ $this->getData()['rejected'] }}</x-filament::badge>
        </a>

        <a href="{{ \App\Filament\Resources\Teachers\TeacherResource::getUrl('index', ['filters' => ['status' => ['value' => 'suspended']]]) }}"
            class="px-4 py-2 bg-orange-500/10 text-orange-600 rounded-lg flex items-center gap-2">
            Suspended
            <x-filament::badge color="warning">{{ $this->getData()['suspended'] }}</x-filament::badge>
        </a>

        <a href="{{ \App\Filament\Resources\Teachers\TeacherResource::getUrl('index') }}"
            class="px-4 py-2 bg-orange-500/10 text-orange-600 rounded-lg flex items-center gap-2">
            Reset
            <x-filament::badge color="">x</x-filament::badge>
        </a>

    </div>
</x-filament::section>
