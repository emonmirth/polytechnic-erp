<x-filament::page>
    <form wire:submit.prevent="generateReport">
        {{ $this->form }}
    </form>

    @if($reportData)
        <div class="mt-8 overflow-x-auto bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="p-4 border-b border-gray-200 dark:border-gray-800 font-bold text-sm">Roll No</th>
                        <th class="p-4 border-b border-gray-200 dark:border-gray-800 font-bold text-sm">Student Name</th>
                        @foreach($reportData['subject_headers'] as $header)
                            <th class="p-4 border-b border-gray-200 dark:border-gray-800 font-bold text-sm text-center">
                                {{ $header }}
                            </th>
                        @endforeach
                        <th class="p-4 border-b border-gray-200 dark:border-gray-800 font-bold text-sm text-center">GPA</th>
                        <th class="p-4 border-b border-gray-200 dark:border-gray-800 font-bold text-sm text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($reportData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="p-4 text-sm font-medium">{{ $row['roll_no'] }}</td>
                            <td class="p-4 text-sm">{{ $row['student_name'] }}</td>
                            @foreach($reportData['subject_headers'] as $header)
                                <td class="p-4 text-sm text-center">
                                    <div class="flex flex-col">
                                        <span class="font-bold">{{ $row['subjects'][$header]['grade'] }}</span>
                                        <span class="text-xs text-gray-500">{{ $row['subjects'][$header]['gp'] }}</span>
                                    </div>
                                </td>
                            @endforeach
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $row['gpa'] >= 2.0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ number_format($row['gpa'], 2) }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="text-xs font-bold {{ $row['status'] === 'PASSED' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="mt-8 p-12 text-center bg-gray-50 dark:bg-gray-800/50 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-800">
            <p class="text-gray-500">Please select filters and click "Load Tabulation Sheet" to view results.</p>
        </div>
    @endif
</x-filament::page>
