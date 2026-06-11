<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Notification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; }
        .container { background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #10B981; padding-bottom: 16px; }
        .header h1 { color: #10B981; margin: 0; font-size: 24px; }
        .alert-badge { background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; padding: 16px; text-align: center; margin: 20px 0; }
        .alert-badge span { font-size: 12px; color: #059669; display: block; }
        .alert-badge strong { font-size: 20px; color: #047857; }
        .details { margin: 24px 0; }
        .details h3 { color: #1E293B; font-size: 16px; margin-bottom: 12px; border-bottom: 1px solid #E2E8F0; padding-bottom: 8px; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #F1F5F9; }
        .detail-label { color: #64748B; width: 140px; flex-shrink: 0; font-weight: 500; }
        .detail-value { color: #1E293B; }
        .action-btn { display: inline-block; background: #10B981; color: #fff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; margin: 8px 4px; }
        .action-btn:hover { background: #059669; }
        .action-section { text-align: center; margin: 24px 0; padding: 20px; background: #F0FDF4; border-radius: 8px; }
        .action-section p { margin: 0 0 12px 0; color: #166534; }
        .footer { text-align: center; margin-top: 32px; padding-top: 16px; border-top: 1px solid #E2E8F0; color: #64748B; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Booking Received</h1>
        </div>

        <div class="alert-badge">
            <span>Booking Reference</span>
            <strong>{{ $visit->booking_reference }}</strong>
        </div>

        <div class="details">
            <h3>Customer Details</h3>

            @if($visit->customer_name)
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value">{{ $visit->customer_name }}</span>
            </div>
            @endif

            @if($visit->customer_email)
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $visit->customer_email }}</span>
            </div>
            @endif

            @if($visit->customer_phone)
            <div class="detail-row">
                <span class="detail-label">Phone</span>
                <span class="detail-value">{{ $visit->customer_phone }}</span>
            </div>
            @endif
        </div>

        <div class="details">
            <h3>Booking Details</h3>

            <div class="detail-row">
                <span class="detail-label">Date & Time</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($visit->start_time)->format('l, j F Y \a\t g:i A') }}</span>
            </div>

            @if($visit->location)
            <div class="detail-row">
                <span class="detail-label">Location</span>
                <span class="detail-value">{{ $visit->location->name }}</span>
            </div>
            @endif

            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">{{ ucfirst($visit->stage) }}</span>
            </div>
        </div>

        <div class="action-section">
            <p>View this booking in your admin dashboard</p>
            <a href="{{ url('/admin/bookings/incoming') }}" class="action-btn">View Bookings</a>
        </div>

        <div class="footer">
            <strong>{{ $company->name }}</strong>
            <p>This is an automated notification from InteTeam CRM</p>
        </div>
    </div>
</body>
</html>
