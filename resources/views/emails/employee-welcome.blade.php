<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .header { background: #1d4ed8; color: #fff; padding: 20px 24px; border-radius: 6px 6px 0 0; }
        .body { background: #f9fafb; padding: 24px; border: 1px solid #e5e7eb; }
        .footer { font-size: 12px; color: #9ca3af; margin-top: 24px; }
        .credential { background: #fff; border: 1px solid #d1d5db; border-radius: 4px; padding: 12px 16px; margin: 12px 0; }
        .label { font-size: 12px; color: #6b7280; }
        .value { font-weight: bold; font-size: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">Welcome to MyPayroll</h2>
        </div>
        <div class="body">
            <p>Hi {{ $employee->name }},</p>
            <p>Your employee account has been created. You can log in to the employee portal using the credentials below.</p>

            <div class="credential">
                <div class="label">Employee ID</div>
                <div class="value">{{ $employee->employee_code }}</div>
            </div>

            <div class="credential">
                <div class="label">Login Email</div>
                <div class="value">{{ $employee->email }}</div>
            </div>

            <div class="credential">
                <div class="label">Temporary Password</div>
                <div class="value">{{ $password }}</div>
            </div>

            <p>Please log in and change your password as soon as possible.</p>
            <p>If you have any questions, contact your HR team.</p>
        </div>
        <div class="footer">
            This is an automated message from MyPayroll. Please do not reply to this email.
        </div>
    </div>
</body>
</html>
