<?php
header("Access-Control-Allow-Origin: *");
$error = false;
$errorcode = "";

$ini = parse_ini_file("vars.ini");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $user = htmlspecialchars($_POST['username']);
    $desc = htmlspecialchars($_POST['request']);
} else {
    $email = $_GET['email'];
    $user = htmlspecialchars($_GET['username']);
    $desc = htmlspecialchars($_GET['request']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = true;
    $errorcode .= "Ungültige E-Mail-Adresse.<br>";
}
if (strlen(utf8_decode($user)) < 3) {
    $error = true;
    $errorcode .= "Ungültiger Nutzer.<br>";
}
if (strlen(utf8_decode($desc)) < 3) {
    $error = true;
    $errorcode .= "Ungültige Nachricht.<br>";
}

if (!$error) {
    $error = !mail(
        $ini["mail_target"],
        "RequestForm: Anfrage von $user",
        $desc,
        implode("\r\n", array(
            "MIME-Version: 1.0",
            "Content-type: text/plain; charset=utf-8",
            "From: " . $ini["mail_noreply"],
            "Reply-To: $user <$email>",
            "X-Mailer: PHP/" . phpversion(),
        ))
    );
    if ($error) {
        $errorcode .= "Serverseitiges Problem.";
        $gotify_data = [
            "title" => "RequestForm: Error",
            "message" => "Mail transfer failed.",
            "priority" => 1
        ];
    } else {
        $gotify_data = [
            "title" => "RequestForm: Anfrage von $user",
            "message" => $desc,
            "priority" => 5
        ];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ini["gotify_url"]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json; charset=utf-8"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($gotify_data));

    $result = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    switch ($code) {
        case "200":
            echo "<strong>Your Message was Submitted</strong>";
            break;
        case "400":
            echo "<strong>Bad Request</strong>";
            break;
        case "401":
            echo "<strong>Unauthorized Error - Invalid Token</strong>";
            break;
        case "403":
            echo "<strong>Forbidden</strong>";
            break;
        case "404":
            echo "<strong>API URL Not Found</strong>";
            break;
        default:
            echo "<strong>Hmm Something Went Wrong or HTTP Status Code is Missing</strong>";
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400&display=swap" rel="stylesheet">
    <title>Anfrage | <?= $ini["page_title"] ?></title>
</head>

<?php if (!$error) { ?>

<body>
    <div id="wrapper">
        <div id="banner"></div>
        <div id="form">
            <h1>Danke!</h1>
            <p>
                Deine Nachricht ist eingegangen und wird demnächst™ bearbeitet.
            </p>
            <p class="small">
                <a href="<?= $ini["this_url"] ?>">Weitere Anfrage</a>
            </p>
        </div>
        <div id="footer">
            <a href="<?= $ini["target_url"] ?>">Home</a>
        </div>
    </div>
</body>

<?php } else { ?>

<body>
    <div id="wrapper">
        <div id="banner"></div>
        <div id="form">
            <h1>Fehler...</h1>
            <p class="error">
                <?= $errorcode ?>
            </p>
            <p>
                Wenn der Fehler weiterhin besteht, sende die Anfrage mit deinem eigenen Mailclient ab:<br><br>
                <a
                    href="mailto:<?= $ini["mail_target"] ?>?subject=Anfrage&body=<?= $desc ?>"><?= $ini["mail_target"] ?></a>
            </p>
            <p class="small">
                Deine Nachricht ist im Link enthalten und muss nicht neu verfasst werden.
            </p>
        </div>
        <div id="footer">
            <a href="<?= $ini["this_url"] ?>">Zurück</a>
        </div>
    </div>
</body>
<?php } ?>


</html>