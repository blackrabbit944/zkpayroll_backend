<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 500px) {
    .mail-wrapper {
        margin-top: 40px;
    }
}
</style>
</head>
<body style="background-color: #f4f5f6;padding:50px 0;">

    <div class="mail-wrapper" style="width: 700px;max-width: 90%;margin: 0 auto;background-color: #fff;border-radius: 4px;">
        <div class="mail-header" style="height: 100px;display: flex;justify-content: space-between;align-items: center;border-bottom: 1px solid #EDEDED;">
            <div class="logo" style="margin-left: 40px;padding:38px 0;">
                <img src="https://dexduck-interface-i3vofujdj-blackrabbit944.vercel.app/img/mail/logo_24.png" class="word" style="height: 24px;" />
            </div>
        </div>


        <div class="mail-content">
            @yield('content')
        </div>

        <div class="mail-footer" style="display: block;text-align: center;padding:20px 0;border-top: 1px solid #EDEDED;font-size: 14px;color: #1F2129;width: 100%;">
            © Dexduck.app
            <a href="{{$unsubscribe_url}}" style="color: #1F2129;opacity: 0.5;text-decoration: underline;margin: 0 10px;">退订周刊</a>
        </div>


    </div>

</body>
</html>
