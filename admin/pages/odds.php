<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $odds = $_POST['odds'];
    $stmt = $db->prepare("UPDATE odds_config SET odds = :odds WHERE id = :id");
    $stmt->execute(['odds' => $odds, 'id' => $id]);
    echo "<div class='alert alert-success'>赔率更新成功</div>";
}

$odds_list = $db->query("SELECT * FROM odds_config ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">赔率设置</h1>
</div>

<div class="row">
    <?php foreach ($odds_list as $item): ?>
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="card-title text-muted text-uppercase small"><?php echo htmlspecialchars($item['play_type']); ?></h6>
                <form method="POST" class="d-flex gap-2">
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                    <input type="number" step="0.001" name="odds" class="form-control form-control-sm" value="<?php echo $item['odds']; ?>">
                    <button type="submit" class="btn btn-sm btn-primary">保存</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
