<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Short Order Template List</title>
    <style>
        body { font-family: 'bangla', sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid black; }
        th { text-align: left; }
    </style>
</head>
<body>
    <h2>Short Order Template List</h2>
    <table>
        <tbody>
            @forelse ($shortOrderTemplateList as $key => $shortOrderTemplate)
            @php
                $trialDate = explode(" ", $shortOrderTemplate->created_at);
            @endphp
            <tr>
                <th width="100">{{ en2bn(++$key) }} - নম্বর:</th>
                <th>{{ en2bn(date_formater_helpers_make_bd_v2($trialDate[0])) }}</th>
                <th>{{ $shortOrderTemplate->template_name }}</th>
                <th width="100">
                    <a href="{{ route('appeal.getShortOrderSheets', ['id' => $shortOrderTemplate->id]) }}" target="_blank">
                        <span>দেখুন</span>
                    </a>
                </th>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">তথ্য খুঁজে পাওয়া যায়নি...</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
