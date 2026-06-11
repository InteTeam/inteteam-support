<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; }
        .invoice-title { font-size: 20px; margin: 20px 0; }
        .details-table { width: 100%; margin: 20px 0; }
        .details-table td { padding: 5px; }
        .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items-table th, .items-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .items-table th { background: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .totals { margin-top: 20px; float: right; width: 300px; }
        .totals-table { width: 100%; }
        .totals-table td { padding: 5px; }
        .grand-total { font-size: 16px; font-weight: bold; border-top: 2px solid #333; }
        .footer { margin-top: 50px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        @if($company->address)
            <div>{{ $company->address }}</div>
        @endif
        @if($settings && $settings->vat_number)
            <div>VAT: {{ $settings->vat_number }}</div>
        @endif
    </div>

    <div class="invoice-title">INVOICE #{{ $invoice->invoice_number }}</div>

    <table class="details-table">
        <tr>
            <td style="width: 50%;">
                <strong>Bill To:</strong><br>
                {{ $invoice->customer_name }}<br>
                @if($invoice->customer_email)
                    {{ $invoice->customer_email }}<br>
                @endif
            </td>
            <td style="width: 50%;">
                <strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}<br>
                <strong>Due Date:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
                <strong>Status:</strong> {{ ucfirst($invoice->status) }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lineItems as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">£{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">£{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">£{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->vat_amount > 0)
            <tr>
                <td>VAT ({{ $settings->vat_rate ?? 20 }}%):</td>
                <td class="text-right">£{{ number_format($invoice->vat_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>Total:</td>
                <td class="text-right">£{{ number_format($invoice->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    @if($settings && $settings->default_payment_terms)
    <div style="margin-top: 30px;">
        <strong>Payment Terms:</strong><br>
        {{ $settings->default_payment_terms }}
    </div>
    @endif

    @if($settings && $settings->bank_name)
    <div style="margin-top: 20px;">
        <strong>Bank Details:</strong><br>
        Bank: {{ $settings->bank_name }}<br>
        @if($settings->bank_sort_code)
            Sort Code: {{ $settings->bank_sort_code }}<br>
        @endif
        @if($settings->bank_account_number)
            Account: {{ $settings->bank_account_number }}<br>
        @endif
        @if($settings->bank_account_name)
            Account Name: {{ $settings->bank_account_name }}<br>
        @endif
    </div>
    @endif

    @if($settings && $settings->default_notes)
    <div style="margin-top: 20px;">
        {{ $settings->default_notes }}
    </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
