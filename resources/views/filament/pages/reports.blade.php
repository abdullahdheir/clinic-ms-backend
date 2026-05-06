<x-filament-panels::page>
    <form wire:submit="updateForm">
        {{ $this->form }}
        <div class="flex gap-3 mt-4">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                تحديث التقرير
            </x-filament::button>
        </div>
    </form>

    @if($this->from_date && $this->to_date)
        <div class="mt-8 space-y-6">
            {{-- Appointments by Clinic --}}
            <x-filament::section>
                <x-filament::section.heading>
                    المواعيد حسب العيادة
                </x-filament::section.heading>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->getReportData()['appointments_by_clinic'] as $clinic)
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="text-lg font-semibold text-gray-900">{{ $clinic->name }}</div>
                            <div class="text-2xl font-bold text-primary-600">{{ $clinic->count }}</div>
                            <div class="text-sm text-gray-500">موعد</div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

            {{-- Top Doctors --}}
            <x-filament::section>
                <x-filament::section.heading>
                    أفضل 10 أطباء حسب عدد المواعيد
                </x-filament::section.heading>
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الطبيب</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التخصص</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المواعيد</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->getReportData()['top_doctors'] as $doctor)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $doctor->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $doctor->specialization }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                            {{ $doctor->count }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- Appointments by Status --}}
            <x-filament::section>
                <x-filament::section.heading>
                    توزيع المواعيد حسب الحالة
                </x-filament::section.heading>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach($this->getReportData()['appointments_by_status'] as $status)
                        <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                            <div class="text-lg font-semibold">
                                @switch($status->status)
                                    @case('confirmed')
                                        <span class="text-green-600">مؤكّد</span>
                                        @break
                                    @case('pending')
                                        <span class="text-yellow-600">انتظار</span>
                                        @break
                                    @case('cancelled')
                                        <span class="text-red-600">ملغي</span>
                                        @break
                                    @case('done')
                                        <span class="text-blue-600">منتهي</span>
                                        @break
                                    @case('in_progress')
                                        <span class="text-purple-600">جارٍ</span>
                                        @break
                                    @case('no_show')
                                        <span class="text-gray-600">لم يحضر</span>
                                        @break
                                    @default
                                        {{ $status->status }}
                                @endswitch
                            </div>
                            <div class="text-2xl font-bold text-gray-900">{{ $status->count }}</div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
