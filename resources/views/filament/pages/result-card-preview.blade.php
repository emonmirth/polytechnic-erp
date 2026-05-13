<div class="p-8 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-lg print:shadow-none print:border-none">
    <div class="text-center mb-8 border-b-2 border-primary-500 pb-4">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white uppercase tracking-tight">Polytechnic Institute</h1>
        <h2 class="text-xl font-semibold text-primary-600 dark:text-primary-400 mt-1 uppercase">Academic Transcript / Marksheet</h2>
    </div>

    <div class="grid grid-cols-2 gap-8 mb-8 bg-gray-50 dark:bg-gray-800/50 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="space-y-2">
            <p class="text-sm"><span class="font-bold text-gray-500 uppercase">Student Name:</span> <span class="text-gray-900 dark:text-white font-semibold">{{ $result->student->name }}</span></p>
            <p class="text-sm"><span class="font-bold text-gray-500 uppercase">Roll Number:</span> <span class="text-gray-900 dark:text-white font-semibold">{{ $result->student->roll_no }}</span></p>
            <p class="text-sm"><span class="font-bold text-gray-500 uppercase">Registration:</span> <span class="text-gray-900 dark:text-white font-semibold">{{ $result->student->reg_no }}</span></p>
        </div>
        <div class="space-y-2">
            <p class="text-sm"><span class="font-bold text-gray-500 uppercase">Department:</span> <span class="text-gray-900 dark:text-white font-semibold">{{ $result->student->department->name }}</span></p>
            <p class="text-sm"><span class="font-bold text-gray-500 uppercase">Session:</span> <span class="text-gray-900 dark:text-white font-semibold">{{ $result->session->session_year }}</span></p>
            <p class="text-sm"><span class="font-bold text-gray-500 uppercase">Semester:</span> <span class="text-gray-900 dark:text-white font-semibold">{{ $result->semester->name }}</span></p>
        </div>
    </div>

    <table class="w-full text-left border-collapse mb-8 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <thead>
            <tr class="bg-primary-500 text-white uppercase text-xs tracking-wider">
                <th class="p-3 border border-primary-600">Subject Code</th>
                <th class="p-3 border border-primary-600">Subject Name</th>
                <th class="p-3 border border-primary-600 text-center">Credit</th>
                <th class="p-3 border border-primary-600 text-center">Grade Point</th>
                <th class="p-3 border border-primary-600 text-center">Letter Grade</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            @foreach($result->items as $item)
                <tr class="text-sm hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                    <td class="p-3 border border-gray-200 dark:border-gray-700 font-medium">{{ $item->subject_code_snapshot }}</td>
                    <td class="p-3 border border-gray-200 dark:border-gray-700">{{ $item->subject_name_snapshot }}</td>
                    <td class="p-3 border border-gray-200 dark:border-gray-700 text-center font-bold">{{ $item->credit_snapshot }}</td>
                    <td class="p-3 border border-gray-200 dark:border-gray-700 text-center font-bold {{ $item->grade_point >= 2.0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($item->grade_point, 2) }}
                    </td>
                    <td class="p-3 border border-gray-200 dark:border-gray-700 text-center font-black">
                        <span class="inline-block px-2 py-1 rounded {{ $item->letter_grade !== 'F' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $item->letter_grade }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="flex justify-between items-center bg-gray-900 text-white p-6 rounded-xl shadow-inner">
        <div class="text-center">
            <p class="text-xs uppercase text-gray-400 mb-1">Total Credits</p>
            <p class="text-2xl font-black">{{ $result->items->sum('credit_snapshot') }}</p>
        </div>
        <div class="text-center border-l border-r border-gray-700 px-12">
            <p class="text-xs uppercase text-gray-400 mb-1">Semester GPA</p>
            <p class="text-4xl font-black text-yellow-400">{{ number_format($result->gpa, 2) }}</p>
        </div>
        <div class="text-center">
            <p class="text-xs uppercase text-gray-400 mb-1">Final Status</p>
            <p class="text-2xl font-black {{ $result->status === 'PASSED' ? 'text-green-400' : 'text-red-400' }}">{{ $result->status }}</p>
        </div>
    </div>

    <div class="mt-12 flex justify-between items-end px-4">
        <div class="text-center border-t border-gray-300 pt-2 w-48">
            <p class="text-xs text-gray-500 uppercase">Controller of Exams</p>
        </div>
        <div class="text-center">
            <p class="text-[10px] text-gray-400 italic">Generated by Polytechnic_ERP on {{ now()->format('d M, Y h:i A') }}</p>
        </div>
        <div class="text-center border-t border-gray-300 pt-2 w-48">
            <p class="text-xs text-gray-500 uppercase">Principal Signature</p>
        </div>
    </div>

    <div class="mt-8 flex justify-center no-print">
        <button onclick="window.print()" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-all transform hover:scale-105 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Result Card
        </button>
    </div>
</div>
