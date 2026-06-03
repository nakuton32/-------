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

            case "業務日リセット":
                $pdo->exec("DELETE FROM reports WHERE work_type != '翌日引継ぎ'");
                $pdo->exec("DELETE FROM reports WHERE work_type = '翌日引継ぎ' AND created_at < NOW() - INTERVAL 48 HOUR");
                break;

            case "編集":
                if(!empty($_POST["FB_detail"]) and !empty($_POST["edit_id"])){
                    $FB = $_POST["FB_detail"];
                    $id = $_POST["edit_id"];
                    $sql  = "UPDATE complain SET FB=:FB WHERE id=:id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':FB', $FB, PDO::PARAM_STR);
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
        <title>TecherBoard - マネージャーページ</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body class="manager">

        <header class="site-header">
            <div class="site-title">Tech<span>er Bo</span>ard</div>
            <div class="site-meta">
                <span class="badge badge-manager">Manager</span> &nbsp;
                <?php echo htmlspecialchars($user); ?> さん &nbsp;|&nbsp;
                <?php echo date('Y年m月d日'); ?>
            </div>
        </header>

        <div class="container">

            <!-- リセットボタン -->
            <div class="card" style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <div class="card-title" style="margin-bottom:0.2rem;">🗑️ 業務データ管理</div>
                    <p style="font-size:0.85rem; color:#888780;">翌日引継ぎ以外の当日データを削除します（翌日引継ぎは48時間保持）</p>
                </div>
                <form method="post">
                    <input type="submit" name="int" value="業務日リセット" class="btn btn-danger" style="padding:0.7rem 1.5rem; font-size:0.95rem; white-space:nowrap;">
                </form>
            </div>

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

            <!-- 解決策編集 -->
            <div class="card">
                <div class="card-title">✏️ 解決策を入力</div>
                <p style="font-size:0.9rem; color:#888780; margin-bottom:1rem;">編集したいクレームのIDを入力してください。</p>
                <form method="post" style="display:flex; gap:0.75rem; align-items:center;">
                    <input type="text" name="upid" placeholder="クレームID" style="width:150px; margin-bottom:0;">
                    <input type="submit" name="int" value="検索" class="btn btn-secondary" style="padding:0.6rem 1.2rem;">
                </form>

                <?php if(!empty($_POST["upid"])): ?>
                    <?php
                        $id   = $_POST["upid"];
                        $sql  = "SELECT complain.id AS complain_id, users.name,
                                 DATE_FORMAT(complain.created_at, '%Y年%m月') AS formatted_date,
                                 complain.com_type, complain.com_detail
                                 FROM complain
                                 JOIN users ON complain.user_id = users.id
                                 WHERE complain.id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <?php if($result): ?>
                        <hr class="divider">
                        <table style="margin-bottom:1rem;">
                            <tr><th>報告者</th><th>時期</th><th>種類</th><th>詳細</th></tr>
                            <tr>
                                <td><?php echo htmlspecialchars($result['name']); ?></td>
                                <td><?php echo htmlspecialchars($result['formatted_date']); ?></td>
                                <td><?php echo htmlspecialchars($result['com_type']); ?></td>
                                <td><?php echo htmlspecialchars($result['com_detail']); ?></td>
                            </tr>
                        </table>
                        <form action="" method="post">
                            <input type="hidden" name="edit_id" value="<?php echo $id; ?>">
                            <textarea name="FB_detail" rows="4" placeholder="解決策を入力してください"></textarea>
                            <input type="submit" name="int" value="編集" class="btn btn-primary" style="padding:0.7rem 2rem; font-size:1rem;">
                        </form>
                    <?php else: ?>
                        <p style="color:#E24B4A; font-size:0.9rem; margin-top:0.75rem;">該当するIDが見つかりません。</p>
                    <?php endif; ?>
                <?php endif; ?>
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
                                <th>ID</th>
                                <th class="nowrap">時期</th>
                                <th class="nowrap">報告者</th>
                                <th>種類</th>
                                <th>詳細</th>
                                <th>解決策</th>
                            </tr>';
                        foreach($results as $row){
                            echo '<tr>';
                            echo '<td>'.htmlspecialchars($row['complain_id']).'</td>';
                            echo '<td class="nowrap">'.htmlspecialchars($row['formatted_date']).'</td>';
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
