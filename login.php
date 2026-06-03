<!DOCTYPE html>
<?php
    require_once 'dbDteil.php';
?>
<?php
    if (isset($_POST['int'])){
        $kbn = htmlspecialchars($_POST["int"], ENT_QUOTES, "UTF-8");
        switch ($kbn) {
            case "送信":
                if(!empty($_POST["username"]) and !empty($_POST["password"])){
                    $sql="SELECT * FROM users WHERE name = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_POST["username"]]);
                    $user = $stmt->fetch();

                    if($user and password_verify($_POST["password"], $user["password"])){
                        session_start();
                        $_SESSION["user_id"] = $user["id"];
                        $_SESSION["user"] = $user["name"];
                        $_SESSION["role"] = $user["role"];

                        if($user['role'] == "manager"){
                            header('Location:https://tech-base.net/tb-280008/mission6/manager_data_page.php');
                            exit();
                        } else {
                            header('Location:https://tech-base.net/tb-280008/mission6/staff_data_page.php');
                            exit();
                        }
                    } else {
                        $error = "ユーザー名またはパスワードが違います";
                    }
                }
                break;

            case "新規登録はこちら！":
                header('Location:https://tech-base.net/tb-280008/mission6/newdata.php');
                exit();
        }
    }
?>

<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>TecherBoard - ログイン</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

        <div class="login-wrapper">
            <div class="login-card">

                <div class="login-logo">
                    Tech<span>er Bo</span>ard
                </div>
                <p class="login-subtitle">スタッフ業務管理システム</p>

                <hr class="divider">

                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="" method="post" style="text-align:left;">
                    <label style="font-size:0.85rem; color:#5F5E5A; display:block; margin-bottom:0.3rem;">ユーザー名</label>
                    <input type="text" name="username" placeholder="username">

                    <label style="font-size:0.85rem; color:#5F5E5A; display:block; margin-bottom:0.3rem;">パスワード</label>
                    <input type="password" name="password" placeholder="password">

                    <input type="submit" name="int" value="送信" class="btn btn-primary" style="width:100%; margin-top:0.5rem; padding:0.85rem; font-size:1rem;">
                </form>

                <hr class="divider">

                <form action="" method="post" style="text-align:left;">
                    <input type="submit" name="int" value="新規登録はこちら！" class="btn btn-secondary" style="width:100%; padding:0.85rem; font-size:1rem;">
                </form>

            </div>
        </div>

    </body>
</html>
