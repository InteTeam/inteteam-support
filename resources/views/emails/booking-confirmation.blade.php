<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; }
        .container { background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #3B82F6; padding-bottom: 16px; }
        .header h1 { color: #3B82F6; margin: 0; font-size: 24px; }
        .booking-ref { background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 6px; padding: 16px; text-align: center; margin: 20px 0; }
        .booking-ref span { font-size: 12px; color: #64748B; display: block; }
        .booking-ref strong { font-size: 20px; color: #1E40AF; }
        .details { margin: 24px 0; }
        .details h3 { color: #1E293B; font-size: 16px; margin-bottom: 12px; border-bottom: 1px solid #E2E8F0; padding-bottom: 8px; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #F1F5F9; }
        .detail-label { color: #64748B; width: 120px; flex-shrink: 0; }
        .detail-value { color: #1E293B; }
        .message { background: #F8FAFC; border-left: 4px solid #3B82F6; padding: 16px; margin: 24px 0; white-space: pre-line; }
        .calendar-btn { display: inline-block; background: #3B82F6; color: #fff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; margin: 8px 4px; }
        .calendar-btn:hover { background: #2563EB; }
        .calendar-section { text-align: center; margin: 24px 0; padding: 20px; background: #F0FDF4; border-radius: 8px; }
        .calendar-section p { margin: 0 0 12px 0; color: #166534; }
        .footer { text-align: center; margin-top: 32px; padding-top: 16px; border-top: 1px solid #E2E8F0; color: #64748B; font-size: 14px; }
        .spam-notice { background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 6px; padding: 12px; margin-top: 16px; font-size: 13px; color: #92400E; }
        .company-info { margin-top: 16px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $company->name }}</h1>
        </div>

        <div class="booking-ref">
            <span>Booking Reference</span>
            <strong>{{ $visit->booking_reference }}</strong>
        </div>

        <div class="message">
            {{ $settings['template_content'] }}
        </div>

        <div class="details">
            <h3>Booking Details</h3>
            
            @if($settings['include_booking_datetime'])
            <div class="detail-row">
                <span class="detail-label">Date & Time</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($visit->start_time)->format('l, j F Y \a\t g:i A') }}</span>
            </div>
            @endif

            @if($settings['include_location'] && $visit->location)
            <div class="detail-row">
                <span class="detail-label">Location</span>
                <span class="detail-value">{{ $visit->location->name }}</span>
            </div>
            @endif

            @if($settings['include_services'] && $visit->notes)
            <div class="detail-row">
                <span class="detail-label">Services</span>
                <span class="detail-value">{{ $visit->notes }}</span>
            </div>
            @endif
        </div>

        @if($settings['include_customer_name'] || $settings['include_customer_email'] || $settings['include_customer_phone'])
        <div class="details">
            <h3>Your Details</h3>
            
            @if($settings['include_customer_name'] && $visit->customer_name)
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value">{{ $visit->customer_name }}</span>
            </div>
            @endif

            @if($settings['include_customer_email'] && $visit->customer_email)
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $visit->customer_email }}</span>
            </div>
            @endif

            @if($settings['include_customer_phone'] && $visit->customer_phone)
            <div class="detail-row">
                <span class="detail-label">Phone</span>
                <span class="detail-value">{{ $visit->customer_phone }}</span>
            </div>
            @endif
        </div>
        @endif

        <div class="calendar-section">
            <p>📅 Add this booking to your calendar</p>
            <a href="{{ $calendarUrl }}" class="calendar-btn" target="_blank">Add to Google Calendar</a>
        </div>

        <div class="spam-notice">
            <strong>📧 Check your spam folder!</strong> If you don't see future emails from us, please check your spam or junk folder and mark our emails as "not spam".
        </div>

        <div class="footer">
            <strong>{{ $company->name }}</strong>
            
            @if($business['show_address_in_emails'] && ($business['address_line_1'] || $business['town'] || $business['postcode']))
            <div class="company-info">
                @if($business['address_line_1']){{ $business['address_line_1'] }}<br>@endif
                @if($business['town']){{ $business['town'] }} @endif
                @if($business['postcode']){{ $business['postcode'] }}@endif
            </div>
            @endif
            
            @if($business['phone'])
            <div class="company-info">
                📞 {{ $business['phone'] }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
