<!DOCTYPE html>
<html>
<head>
    <title>이메일 인증</title>
</head>
<body>
<h2>{{ $user->name }}님, 인증번호를 확인해주세요</h2>
<p>아래 코드를 앱에 입력해주세요:</p>

<div style="font-size: 24px; font-weight: bold; color: #2563eb;">
    {{ $verificationCode }}
</div>

<p>유효기간: 30분</p>
</body>
</html>