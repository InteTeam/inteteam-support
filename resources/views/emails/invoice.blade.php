<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .content {
            margin-bottom: 30px;
        }
        .invoice-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .invoice-details p {
            margin: 5px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Invoice from {{ $invoice->company->name }}</h2>
    </div>

    <div class="content">
        {!! nl2br(e($emailMessage)) !!}
    </div>

    <div class="invoice-details">
        <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
        <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
        <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
        <p><strong>Total Amount:</strong> £{{ number_format($invoice->total, 2) }}</p>
    </div>

    @if($pdfPath)
    <p>Please find your invoice attached as a PDF document.</p>
    @endif

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        @if($invoice->company->email)
        <p>For any questions, please contact us at {{ $invoice->company->email }}</p>
        @endif
    </div>
</body>
</html>
