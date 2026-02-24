<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="0;url={{ route('tenant.login') }}">
    <title>{{ config('app.name', 'VigiaContratos') }}</title>
</head>
<body>
    <p>Redirecionando para o login...</p>
    <script>window.location.href = "{{ route('tenant.login') }}";</script>
</body>
</html>
