<!-- Stored in resources/views/layouts/app.blade.php -->
@extends('mail.system')

@section('content')
    <div class="newsletter-mail" style="padding: 60px 0;">
        <div style="padding:0 40px">
            <div style="font-weight: bold;text-align: left;color: #0c0f1d;margin-bottom:6px;">您收到这封邮件是因为我们收到了您账户的密码重置请求。</div>
            <div style="font-weight: bold;text-align: left;color: #0c0f1d;margin-bottom:6px;">这个密码重置链接将在60分钟后失效。</div>
            <div style="font-weight: bold;text-align: left;color: #0c0f1d;margin-bottom:6px;">如果您没有要求重置密码，请忽略这封邮件。</div>
        </div>

        <div class="newsletter-btn" style="padding: 20px 20px 0 20px;text-align:center">
            <a class="yellow-btn" href="{{$view_url}}" style="display: inline-block;background-color: #FBC400;font-weight: bold;color: #0C0F1D;border-radius: 8px;padding:12px 40px;font-size: 16px;text-align: center;text-decoration:none;">重置我的密码</a>
        </div>
    </div>
    <style> 
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500;700;800&display=swap');

        .newsletter-mail .newsletter-h1{
            font-family: 'poppins';
        }

        .newsletter-mail .newsletter-sub{
            font-family: 'poppins';
        }

        .yellow-btn {
            font-family: 'poppins';
        }
     
    </style>
@endsection