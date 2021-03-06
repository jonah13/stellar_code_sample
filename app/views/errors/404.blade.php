<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stellar - Error 404</title>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- bootstrap framework -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <!-- google webfonts -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans&subset=latin,latin-ext' rel='stylesheet'
          type='text/css'>
    <!-- main stylesheet -->
    <link href="assets/css/main.min.css" rel="stylesheet" media="screen">

</head>
<body class="error_page">

<div id="error_wrapper">
    <div id="error_wrapper_inner">
        <h1 class="error_heading">Error 404</h1>
        <h2 class="error_subheading">The requested URL was not found on this server.</h2>
        <p><a href="{{ URL::route('index') }}">Go Back</a></p>
    </div>
</div>

</body>
</html>