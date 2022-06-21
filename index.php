<?php
$ini = parse_ini_file("vars.ini");
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

    <script src="<?= $ini["keycloak_url"] ?>/js/keycloak.js" type="text/javascript"></script>
    <script type="text/javascript">
    const keycloak = Keycloak({
        "realm": <?= $ini["keycloak_realm"] ?>,
        "auth-server-url": <?= $ini["keycloak_url"] ?>,
        "ssl-required": "external",
        "resource": "account",
        "public-client": true,
        "confidential-port": 0,
        "url": <?= $ini["keycloak_url"] ?>,
        "clientId": "requestform",
        "enable-cors": true
    });
    const loadData = () => {
        console.log(keycloak.subject);
        if (keycloak.idToken) {
            document.getElementById("login").href =
                "<?= $ini["keycloak_url"] ?>/realms/<?= $ini["keycloak_realm"] ?>/account";
            document.getElementById("login").innerHTML = "[" + keycloak.idTokenParsed.preferred_username + "]";
            document.getElementById("username").value = keycloak.idTokenParsed.preferred_username;
            document.getElementById("email").value = keycloak.idTokenParsed.email;
        } else {
            keycloak.loadUserProfile(function() {
                document.getElementById("login").href =
                    "<?= $ini["keycloak_url"] ?>/realms/<?= $ini["keycloak_realm"] ?>/account";
                document.getElementById("login").innerHTML = "[" + keycloak.profile.username + "]";
                document.getElementById("username").value = keycloak.profile.username;
                document.getElementById("email").value = keycloak.profile.email;
            }, function() {
                // console.log('Failed to retrieve user details. Please enable claims or account role');
            });
        }
    };
    const loadFailure = () => {
        // console.log('Failed to load data.  Check console log');
    };
    const reloadData = () => {
        keycloak.updateToken(10)
            .success(loadData)
            .error(() => {
                // console.log('Failed to load data.  User is logged out.');
            });
    }
    keycloak.init({
        onLoad: 'check-sso'
    }).success(reloadData);
    </script>
</head>

<body>
    <div id="wrapper">
        <div id="banner"></div>
        <div id="form">
            <a id="home" href="<?= $ini["target_url"] ?>">← Home</a><a id="login"
                href="<?= $ini["keycloak_url"] ?>/realms/<?= $ini["keycloak_realm"] ?>/protocol/openid-connect/auth?client_id=requestform&response_type=code">Login
                →</a>
            <h1>Anfrage</h1>
            <form action="mail.php" method="post">
                <input type="hidden" name="username" id="username" value="anon">
                <label for="email">E-Mail</label>
                <input type="email" name="email" id="email" required>
                <label for="request">Beschreibung</label>
                <textarea name="request" id="request" cols="30" rows="10" minlength="3" required
                    placeholder="Titel, ISBN, Goodreads Link..."></textarea>
                <input type="submit" value="Absenden">
            </form>
        </div>
        <div id="footer">
            Oder sende eine E-Mail:<br><a href="mailto:<?= $ini["mail_target"] ?>"><?= $ini["mail_target"] ?></a>
        </div>
    </div>
</body>

</html>