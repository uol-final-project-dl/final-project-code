<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="react-url" content="/user/app/">

    <!-- I should add captcha not to get spammed -->
    <meta name="site-key" content="{{ config('googlerecaptchav3.site_key') }}">

    <title>Brainstorm to Prototype</title>
    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,600,700" rel="stylesheet">
</head>
<body style="margin: 0">

<div id="app"></div>

<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
