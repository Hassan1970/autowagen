<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =======================
// DB CONNECTION
// =======================
$conn = new mysqli("localhost", "root", "", "autowagen");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// =======================
// FETCH EPC DATA
// =======================
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

// =======================
// BUILD TREE
// =======================
$tree = [];

while ($row = $result->fetch_assoc()) {

    $catId = $row['category_id'];
    $subId = $row['subcategory_id'];
    $typeId = $row['type_id'];
    $compId = $row['component_id'];

    // CATEGORY
    if (!isset($tree[$catId])) {
        $tree[$catId] = [
            'name' => $row['category_name'],
            'sub' => []
        ];
    }

    // SUBCATEGORY
    if (!isset($tree[$catId]['sub'][$subId])) {
        $tree[$catId]['sub'][$subId] = [
            'name' => $row['subcategory_name'],
            'type' => []
        ];
    }

    // TYPE
    if (!isset($tree[$catId]['sub'][$subId]['type'][$typeId])) {
        $tree[$catId]['sub'][$subId]['type'][$typeId] = [
            'name' => $row['type_name'],
            'comp' => []
        ];
    }

    // COMPONENT
    if ($compId !== null) {
        $tree[$catId]['sub'][$subId]['type'][$typeId]['comp'][] = [
            'id' => $compId,
            'name' => $row['component_name']
        ];
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
    background: #f6f6f6;
    padding: 15px;
    font-size: 13px;
}

h2 {
    margin-bottom: 10px;
}

button {
    margin: 5px 5px 10px 0;
    padding: 6px 12px;
    cursor: pointer;
}

ul {
    list-style: none;
    padding-left: 15px;
    border-left: 1px dashed #bbb;
}

li {
    margin: 3px 0;
}

.toggle {
    cursor: pointer;
    font-weight: bold;
}

.component {
    color: #222;
}

.hidden {
    display: none;
}
</style>

<script>

// =======================
// TOGGLE
// =======================
function toggle(id) {
    document.getElementById(id).classList.toggle('hidden');
}

// =======================
// EXPAND ALL
// =======================
function expandAll() {
    document.querySelectorAll('.hidden').forEach(el => {
        el.classList.remove('hidden');
    });
}

// =======================
// COLLAPSE ALL
// =======================
function collapseAll() {
    document.querySelectorAll('#epcTree ul').forEach(el => {
        el.classList.add('hidden');
    });
}

// =======================
// EXPORT TO PDF
// =======================
function exportTreeToPDF() {

    const tree = document.getElementById("epcTree").cloneNode(true);

    tree.querySelectorAll('.hidden').forEach(el => {
        el.classList.remove('hidden');
    });

    const win = window.open("", "", "width=1000,height=800");

    win.document.write(`
        <html>
        <head>
            <title>EPC Report</title>
            <style>
                body { font-family: Arial; font-size: 10px; }
                ul { list-style: none; padding-left: 10px; }
                li { margin: 1px 0; }
            </style>
        </head>
        <body>
            <h2>EPC STRUCTURE REPORT</h2>
            <p>${new Date().toLocaleString()}</p>
        </body>
        </html>
    `);

    win.document.body.appendChild(tree);
    win.document.close();

    setTimeout(() => {
        win.print();
    }, 500);
}

// =======================
// EXPORT TO EXCEL (NEW)
// =======================
function exportToExcel() {

    let rows = [];
    rows.push(["Category", "Subcategory", "Type", "Component ID", "Component Name"]);

    const tree = <?php echo json_encode($tree); ?>;

    for (const catId in tree) {
        const cat = tree[catId];

        for (const subId in cat.sub) {
            const sub = cat.sub[subId];

            for (const typeId in sub.type) {
                const type = sub.type[typeId];

                if (type.comp.length > 0) {
                    type.comp.forEach(comp => {
                        rows.push([
                            cat.name,
                            sub.name,
                            type.name,
                            comp.id,
                            comp.name
                        ]);
                    });
                } else {
                    rows.push([
                        cat.name,
                        sub.name,
                        type.name,
                        "",
                        "(No components)"
                    ]);
                }
            }
        }
    }

    let csvContent = "data:application/vnd.ms-excel;charset=utf-8,";

    rows.forEach(row => {
        csvContent += row.join("\t") + "\n";
    });

    const encodedUri = encodeURI(csvContent);

    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "epc_structure.xls");

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// =======================
// AUTO EXPAND
// =======================
window.onload = function() {
    expandAll();
};

</script>

</head>

<body>

<h2>EPC Navigation Tree</h2>

<button onclick="exportTreeToPDF()">📄 Export to PDF</button>
<button onclick="exportToExcel()">📊 Export to Excel</button>
<button onclick="expandAll()">⬇ Expand All</button>
<button onclick="collapseAll()">⬆ Collapse All</button>

<ul id="epcTree">
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
                            <?php foreach ($type['comp'] as $comp): ?>
                                <li class="component">
                                    • Component [ID: <?= $comp['id'] ?>] <?= htmlspecialchars($comp['name']) ?>
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