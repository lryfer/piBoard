<?php
require_once "pib/Ban.php";
require_once "pib/form_actions/utilities.php";
require_once "connection.php";
global $conn;
$bans = new \pib\Ban($conn);
$current_ban = $bans->getBan(get_client_ip(), get_id());
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="styles/theme.css">
    <link rel="stylesheet" href="styles/mainstyle.css">
    <link rel="stylesheet" href="styles/mobile.css?v=<?php echo time(); ?>">
</head>
<body>
<main>
    <h1>piBoard</h1>
    <h2><i>YOU HAVE BEEN BANNED</i></h2>
    <div>
        <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fmedia.giphy.com%2Fmedia%2F5kq0GCjHA8Rwc%2Fgiphy.gif&f=1&nofb=1&ipt=76e87323a3226ec0fc403d1bfbbd9014e2bdae4ac6aefcf62012c99f40516bb5"
             alt="">
    </div>
    <div>
        <i>Don't give up you'll be back in just <span id="time_left"></span></i>
        </div>
</main>
</body>
<script>
    const endDate = new Date(Date.parse("<?= $current_ban['EndDate'] ?>"));
    const timeLeftSpan = document.getElementById("time_left");
    window.addEventListener("load", (e) => {
        document.getElementById("time_left").innerHTML = getRemainingTime(endDate.getTime());
    })


    function getRemainingTime(remainingMilliseconds) {
        const now = new Date().getTime();

        const distance = remainingMilliseconds - now;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Display the result in the element with id="demo"
        return `${days} days, ${hours} hours, ${minutes} minutes and ${seconds} seconds`;
    }

    const x = setInterval(function () {
        document.getElementById("time_left").innerHTML = getRemainingTime(endDate.getTime());
    }, 1000)
</script>
</html>