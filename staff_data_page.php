<?php require_once 'dbDteil.php'; ?>
<?php
    session_start();
    $user    = $_SESSION["user"];
    $user_id = $_SESSION["user_id"];
    $role    = $_SESSION["role"];

    if(isset($_POST['int'])){
        $kbn = htmlspecialchars($_POST["int"], ENT_QUOTES, "UTF-8");
        switch($kbn){
            case "送信":
                if(!empty($_POST["work_type"]) and !empty($_POST["work_detail"])){
                    $work_type   = $_POST["work_type"];
                    $work_detail = $_POST["work_detail"];
                    $sql  = "INSERT INTO reports(role,user_id,work_type,work_detail) VALUES(:role,:user_id,:work_type,:work_detail)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':role',        $role,        PDO::PARAM_STR);
                    $stmt->bindParam(':user_id',     $user_id,     PDO::PARAM_INT);
                    $stmt->bindParam(':work_type',   $work_type,   PDO::PARAM_STR);
                    $stmt->bindParam(':work_detail', $work_detail, PDO::PARAM_STR);
                    $stmt->execute();
                }
                break;

            case "追加":
                if(!empty($_POST["com_type"]) and !empty($_POST["com_detail"])){
                    $com_type   = $_POST["com_type"];
                    $com_detail = $_POST["com_detail"];
                    $FB         = "未対応（対応をお待ちください）";
                    $sql  = "INSERT INTO complain(created_at,user_id,com_type,com_detail,FB) VALUES(NOW(),:user_id,:com_type,:com_detail,:FB)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':user_id',    $user_id,    PDO::PARAM_INT);
                    $stmt->bindParam(':com_type',   $com_type,   PDO::PARAM_STR);
                    $stmt->bindParam(':com_detail', $com_detail, PDO::PARAM_STR);
                    $stmt->bindParam(':FB',         $FB,         PDO::PARAM_STR);
                    $stmt->execute();
                }
                break;
        }
    }
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>TecherBoard - スタッフページ</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

        <header class="site-header">
            <div class="site-title">Tech<span>er Bo</span>ard</div>
            <div class="site-meta">
                <?php echo htmlspecialchars($user); ?> さん &nbsp;|&nbsp;
                <?php echo date('Y年m月d日'); ?>
            </div>
        </header>

        <div class="container">

            <!-- 業務報告フォーム -->
            <div class="card">
                <div class="card-title">📋 業務連絡</div>
                <p style="font-size:0.9rem; color:#888780; margin-bottom:1rem;">
                    <?php echo htmlspecialchars($user); ?> さん、お疲れ様です！今日の業務内容を入力してください。
                </p>
                <form action="" method="post">
                    <div class="radio-group">
                        <label><input type="radio" name="work_type" value="開店作業"><span>開店作業</span></label>
                        <label><input type="radio" name="work_type" value="締め作業"><span>締め作業</span></label>
                        <label><input type="radio" name="work_type" value="補充作業"><span>補充作業</span></label>
                        <label><input type="radio" name="work_type" value="翌日引継ぎ"><span>翌日引継ぎ</span></label>
                        <label><input type="radio" name="work_type" value="その他"><span>その他</span></label>
                    </div>
                    <textarea name="work_detail" rows="4" placeholder="業務内容を入力してください"></textarea>
                    <input type="submit" name="int" value="送信" class="btn btn-primary" style="padding:0.7rem 2rem; font-size:1rem;">
                </form>
            </div>

            <!-- 業務報告一覧 -->
            <div class="card">
                <div class="card-title">📊 本日の業務一覧</div>
                <?php
                    $sql     = "SELECT reports.role, users.name, reports.work_type, reports.work_detail
                                FROM reports
                                JOIN users ON reports.user_id = users.id
                                ORDER BY reports.id";
                    $stmt    = $pdo->query($sql);
                    $results = $stmt->fetchAll();

                    if($results){
                        echo '<table>
                            <tr>
                                <th>役職</th>
                                <th>名前</th>
                                <th>業務の種類</th>
                                <th>業務の詳細</th>
                            </tr>';
                        foreach($results as $row){
                            $badge = $row['role'] === 'manager' ? 'badge-manager' : 'badge-staff';
                            $label = $row['role'] === 'manager' ? 'マネージャー' : 'スタッフ';
                            echo '<tr>';
                            echo '<td><span class="badge '.$badge.'">'.$label.'</span></td>';
                            echo '<td>'.htmlspecialchars($row['name']).'</td>';
                            echo '<td>'.htmlspecialchars($row['work_type']).'</td>';
                            echo '<td>'.htmlspecialchars($row['work_detail']).'</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    } else {
                        echo '<p style="color:#888780; font-size:0.9rem;">まだ業務報告がありません。</p>';
                    }
                ?>
            </div>

            <!-- クレーム追加フォーム -->
            <div class="card">
                <div class="card-title">⚠️ クレーム報告</div>
                <p style="font-size:0.9rem; color:#888780; margin-bottom:1rem;">
                    万が一クレームが発生した場合は追加をお願いします。
                </p>
                <form method="post">
                    <div class="radio-group">
                        <label><input type="radio" name="com_type" value="異物混入"><span>異物混入</span></label>
                        <label><input type="radio" name="com_type" value="お客様に被害"><span>お客様に被害</span></label>
                        <label><input type="radio" name="com_type" value="提供問題"><span>提供問題</span></label>
                        <label><input type="radio" name="com_type" value="その他"><span>その他</span></label>
                    </div>
                    <textarea name="com_detail" rows="4" placeholder="クレームの内容・原因を入力してください"></textarea>
                    <input type="submit" name="int" value="追加" class="btn btn-danger" style="padding:0.7rem 2rem; font-size:1rem;">
                </form>
            </div>

            <!-- クレーム一覧 -->
            <div class="card">
                <div class="card-title">📁 歴代クレーム一覧</div>
                <?php
                    $sql     = "SELECT complain.id AS complain_id, users.name,
                                DATE_FORMAT(complain.created_at, '%Y年%m月') AS formatted_date,
                                complain.com_type, complain.com_detail, complain.FB
                                FROM complain
                                JOIN users ON complain.user_id = users.id
                                ORDER BY CASE complain.com_type
                                    WHEN '異物混入'    THEN 1
                                    WHEN 'お客様に被害' THEN 2
                                    WHEN '提供問題'    THEN 3
                                    WHEN 'その他'      THEN 4
                                END";
                    $stmt    = $pdo->query($sql);
                    $results = $stmt->fetchAll();

                    if($results){
                        echo '<table>
                            <tr>
                                <th class="nowrap">時期</th>
                                <th class="nowrap">報告者</th>
                                <th>種類</th>
                                <th>詳細</th>
                                <th>解決策</th>
                            </tr>';
                        foreach($results as $row){
                            echo '<tr>';
                            echo'<td class="nowrap">'.htmlspecialchars($row['formatted_date']).'</td>';
                            echo '<td class="nowrap">'.htmlspecialchars($row['name']).'</td>';
                            echo '<td>'.htmlspecialchars($row['com_type']).'</td>';
                            echo '<td>'.htmlspecialchars($row['com_detail']).'</td>';
                            echo '<td>'.htmlspecialchars($row['FB']).'</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    } else {
                        echo '<p style="color:#888780; font-size:0.9rem;">クレームの記録はありません。</p>';
                    }
                ?>
            </div>

        </div>
    </body>
</html>
