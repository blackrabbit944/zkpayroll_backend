<!-- Stored in resources/views/layouts/app.blade.php -->
@extends('mail.layout')

@section('content')
    <div class="newsletter-mail" style="padding: 30px 0;">
        <div class="newsletter-h1" style="font-weight: bold;font-size: 32px;color: #1F2129;text-align: center;line-height: 1.2;margin-bottom: 20px;color: #FBC400;text-align: center;">Weekly <br />Newsletter</div>
        <div class="newsletter-sub" style="font-weight: bold;text-align: center;color: #0c0f1d;"># No. {{$post->issue_no}} ·  {{date('M d',$post->create_time)}}</div>
        
        <div class="newsletter-icon-wapper" style="text-align: center;padding: 40px 0;">
            <img src="https://dexduck-interface-qllsjis2f-blackrabbit944.vercel.app/img/mail/icon.png" class="newsletter-icon" style="width: 96px;" />
        </div>
        <div class="newsletter-btn" style="padding: 20px 20px 0 20px;">
            <a class="yellow-btn" href="{{$view_url}}" style="width: 100%;display: block;background-color: #FBC400;font-weight: bold;color: #0C0F1D;border-radius: 8px;padding:12px 0;font-size: 16px;text-align: center;">Read Now</a>
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