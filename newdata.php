<?php
    require_once 'dbDteil.php';
?>
<?php
    if (isset($_POST["int"])){
        if(!empty($_POST["username"]) and !empty($_POST["password"]) and !empty($_POST["mail"])){
            $name     = $_POST["username"];
            $password = $_POST["password"];
            $mail     = $_POST["mail"];

            $sql     = "SELECT invite_code FROM store_settings";
            $stmt    = $pdo->query($sql);
            $setting = $stmt->fetch();

            if($_POST["invite_code"] === $setting["invite_code"]){
                $role = "manager";
            } else {
                $role = "staff";
            }

            $password = password_hash($password, PASSWORD_DEFAULT);

            $sql  = "INSERT INTO users (name,mail,password,role,created_at) VALUES(:name,:mail,:password,:role,NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name',     $name,     PDO::PARAM_STR);
            $stmt->bindParam(':mail',     $mail,     PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':role',     $role,     PDO::PARAM_STR);
            $stmt->execute();

            header('Location:https://tech-base.net/tb-280008/mission6/login.php');
            exit();
        } else {
            $error = "ユーザー名・パスワード・メールアドレスは必須です";
        }
    }
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>TecherBoard - 新規登録</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

        <div class="login-wrapper">
            <div class="login-card">

                <div class="login-logo">
                    Tech<span>er Bo</span>ard
                </div>
                <p class="login-subtitle">新規スタッフ登録</p>

                <hr class="divider">

                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="" method="post" style="text-align:left;">
                    <label style="font-size:0.85rem; color:#5F5E5A; display:block; margin-bottom:0.3rem;">ユーザー名</label>
                    <input type="text" name="username" placeholder="username">

                    <label style="font-size:0.85rem; color:#5F5E5A; display:block; margin-bottom:0.3rem;">パスワード</label>
                    <input type="password" name="password" placeholder="password">

                    <label style="font-size:0.85rem; color:#5F5E5A; display:block; margin-bottom:0.3rem;">メールアドレス</label>
                    <input type="text" name="mail" placeholder="e-mail">

                    <label style="font-size:0.85rem; color:#5F5E5A; display:block; margin-bottom:0.3rem;">管理者コード（任意）</label>
                    <input type="password" name="invite_code" placeholder="管理者の方のみ入力">

                    <input type="submit" name="int" value="登録する" class="btn btn-primary" style="width:100%; margin-top:0.75rem; padding:0.85rem; font-size:1rem;">
                </form>

                <hr class="divider">

                <p style="text-align:center; font-size:0.85rem; color:#888780;">
                    すでにアカウントをお持ちの方は
                    <a href="login.php" style="color:#185FA5;">ログインへ</a>
                </p>

            </div>
        </div>

    </body>
</html>
