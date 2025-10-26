@props(['title' => '', 'heads' => [], 'href' => ''])

<div class="p-4 bg-white rounded-t-lg border dark:bg-gray-800 dark:border-gray-700">
    <div class='flex items-center gap-2 uppercase font-semibold text-sm dark:text-gray-200'>
        {{ $title }}
    </div>
</div>
<div class="bg-white rounded-b-lg border border-t-0 dark:bg-gray-800 dark:border-gray-700">
    <div class="w-full overflow-hidden overflow-x-auto border-collapse rounded-xl">
        <table class="w-full text-sm border-collapse">
            <thead class="border-b dark:border-gray-700">
                <tr>
                    @foreach ($heads as $head)
                        <th scope="col"
                            class="h-12 px-6 text-left align-middle font-medium whitespace-nowrap text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                            {{ $head }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
