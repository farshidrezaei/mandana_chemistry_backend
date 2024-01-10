{{--<p class="px-4 py-3  rounded-lg">--}}
{{--    <span class="font-medium">--}}
{{--        Email address:--}}
{{--    </span>--}}

{{--    <span>--}}
{{--        {{ $getRecord()->product->name }}--}}
{{--    </span>--}}
{{--</p>--}}
<table class="w-full text-sm text-right rtl:text-right text-gray-500 dark:text-gray-400 rounded-2xl" dir="rtl">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
    <tr>
        <th scope="col" class="px-6 py-3">
            عنوان تست
        </th>
        <th scope="col" class="px-6 py-3">
            مدت زمان
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($getRecord()->product->tests as $test)
    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{$test->title}}
        </th>
        <td class="px-6 py-4">
            {{$test->duration}} دقیقه
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
