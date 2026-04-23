<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f7; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; }
        .content { padding: 32px; color: #374151; line-height: 1.6; }
        .content p { margin: 0 0 16px; }
        .btn { display: inline-block; background: #4f46e5; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600; font-size: 15px; margin-top: 8px; }
        .btn:hover { background: #4338ca; }
        .footer { padding: 20px 32px; background: #f9fafb; text-align: center; color: #9ca3af; font-size: 13px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>📦 Your Export is Ready</h1>
        </div>
        <div class="content">
            <p>Hi {{ $export->user->name }},</p>
            <p>Your <strong>{{ $export->type }}</strong> export has been processed and is ready for download.</p>
            <p>
                <a href="{{ route('admin.exports.download', $export) }}" class="btn">
                    ⬇ Download CSV
                </a>
            </p>
            <p style="color: #6b7280; font-size: 14px; margin-top: 24px;">
                This link will remain active as long as the file is available on the server.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
