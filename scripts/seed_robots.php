<?php
require_once __DIR__ . '/../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

$names = [
    '阿强','老王','李哥','小陈','张工','陈姐','静静','安妮','龙哥','锋子',
    '快乐水','暴发户','顺哥','发财','财神到','梦里花','云淡风轻','往事随风','心如止水','海阔天空',
    '红蜻蜓','紫气东来','金榜题名','马到成功','大吉大利','万事如意','财源广进','年年有余','步步高升','平步青云',
    '追梦人','孤独狼','北方汉子','南方姑娘','漂泊者','流浪猫','贪吃蛇','大熊猫','小老虎','飞鸟',
    '晨曦','黄昏','半月','星辰','极光','流星','大海','森林','高山','流水'
];

foreach ($names as $name) {
    $username = 'bot_' . bin2hex(random_bytes(4));
    $stmt = $db->prepare("INSERT IGNORE INTO users (username, nickname, password, is_robot) VALUES (?, ?, ?, 1)");
    $stmt->execute([$username, $name, password_hash('bot_pass', PASSWORD_DEFAULT)]);
}
echo "Seeded " . count($names) . " robots.\n";
