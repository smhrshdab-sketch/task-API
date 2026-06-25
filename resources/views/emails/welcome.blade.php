<!DOCTYPE html>
<html>
<head>
    <title>خوش آمدید</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1 style="color: #333;">سلام {{ $user->name }}</h1>
        
        <p style="color: #666;">به سایت ما خوش آمدید!</p>
        
        <div style="background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>ایمیل شما:</strong> {{ $user->email }}</p>
            <p><strong>تاریخ ثبت نام:</strong> {{ $user->created_at }}</p>
        </div>
        
        <p style="color: #666;">
            با تشکر از اینکه ما را انتخاب کردید<br>
            تیم پشتیبانی {{ config('app.name') }}
        </p>
    </div>
</body>
</html>