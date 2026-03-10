<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
$conn = new mysqli("localhost", "root", "", "autowagen_master_clean");
if ($conn->connect_error) {
    die("Database connection failed");
}

// Fetch EPC hierarchy
$sql = "
SELECT
    c.id   AS category_id,
    c.name AS category_name,
    s.id   AS subcategory_id,
    s.name AS subcategory_name,
    t.id   AS type_id,
    t.name AS type_name,
    cmp.id AS component_id,
    cmp.name AS component_name
FROM categories c
JOIN subcategories s ON s.category_id = c.id
JOIN types t ON t.subcategory_id = s.id
LEFT JOIN components cmp ON cmp.type_id = t.id
ORDER BY c.id, s.id, t.id, cmp.id
";

$result = $conn->query($sql);

// Build hierarchy
$tree = [];

while ($row = $result->fetch_assoc()) {
    $tree[$row['category_id']]['name'] = $row['category_name'];

    $tree[$row['category_id']]['sub'][$row['subcategory_id']]['name']
        = $row['subcategory_name'];

    $tree[$row['category_id']]['sub'][$row['subcategory_id']]['type'][$row['type_id']]['name']
        = $row['type_name'];

    if ($row['component_id']) {
        $tree[$row['category_id']]['sub'][$row['subcategory_id']]['type'][$row['type_id']]['comp'][$row['component_id']]
            = $row['component_name'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>EPC Navigation Tree</title>
<style>
body {
    font-family: Consolas, monospace;
    background: #f7f7f7;
    padding: 20px;
}
ul {
    list-style: none;
    margin-left: 20px;
    padding-left: 15px;
    border-left: 1px dashed #bbb;
}
li {
    margin: 4px 0;
}
.toggle {
    cursor: pointer;
    font-weight: bold;
}
.component {
    color: #333;
}
.hidden {
    display: none;
}
</style>
<script>
function toggle(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}
</script>
</head>
<body>

<h2>EPC Navigation Tree</h2>

<ul>
<?php foreach ($tree as $catId => $cat): ?>
    <li>
        <span class="toggle" onclick="toggle('cat<?= $catId ?>')">
            ▶ Category [ID: <?= $catId ?>] <?= htmlspecialchars($cat['name']) ?>
        </span>
        <ul id="cat<?= $catId ?>" class="hidden">
            <?php foreach ($cat['sub'] as $subId => $sub): ?>
                <li>
                    <span class="toggle" onclick="toggle('sub<?= $subId ?>')">
                        ▶ Subcategory [ID: <?= $subId ?>] <?= htmlspecialchars($sub['name']) ?>
                    </span>
                    <ul id="sub<?= $subId ?>" class="hidden">
                        <?php foreach ($sub['type'] as $typeId => $type): ?>
                            <li>
                                <span class="toggle" onclick="toggle('type<?= $typeId ?>')">
                                    ▶ Type [ID: <?= $typeId ?>] <?= htmlspecialchars($type['name']) ?>
                                </span>
                                <ul id="type<?= $typeId ?>" class="hidden">
                                    <?php if (!empty($type['comp'])): ?>
                                        <?php foreach ($type['comp'] as $compId => $compName): ?>
                                            <li class="component">
                                                • Component [ID: <?= $compId ?>] <?= htmlspecialchars($compName) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="component">(No components)</li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    </li>
<?php endforeach; ?>
</ul>

</body>
</html>
