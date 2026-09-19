Item: {{ $item->item_code }} - {{ $item->item_name }}
Ordered: {{ \App\Support\NumberFormatter::trim($item->ordered_qty) }} {{ $item->unit_name }}
Received: {{ \App\Support\NumberFormatter::trim($item->received_qty) }} {{ $item->unit_name }}
Outstanding: {{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }} {{ $item->unit_name }}
PO Date: {{ \Carbon\Carbon::parse($po->po_date)->format('d/m/Y') }}

Timeline:
@foreach ($timeline as $entry)
    {{ $loop->iteration }}. {{ $entry['date'] }} | {{ $entry['description'] }}
       Qty Order: {{ $entry['ordered_qty'] }} {{ $entry['ordered_qty'] !== '-' ? $item->unit_name : '' }}
       Qty Masuk: {{ $entry['received_qty'] }} {{ $entry['received_qty'] !== '-' ? $item->unit_name : '' }}
       Sisa (OS): {{ $entry['outstanding_qty'] }} {{ $entry['outstanding_qty'] !== '-' ? $item->unit_name : '' }}
       No Shipment: {{ $entry['shipment_number'] }}
       No GR: {{ $entry['gr_number'] }}
       Status: {{ $entry['status'] }}
@endforeach
