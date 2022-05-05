<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-12 bg-gray-200">

    <div>
        
        <div class="shadow-xl bg-white rounded-lg mt-12 py-12 mx-auto max-w-screen-md px-6 break-all">
            <div class="font-bold text-center text-3xl text-red-500">{{ $title }} </div>
            <div class="text-gray-500 text-center mt-12">will close in 5 seconds.</div>
        </div>

        <script> 
            setTimeout("window.close()",5000) 
        </script>


    </div>

</body>
</html>
