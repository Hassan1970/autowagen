<?php
require_once __DIR__ . "/config/config.php";

/* =========================
   GET EPC STRUCTURE
========================= */

// Categories
$categories = $conn->query("SELECT id, name FROM backup_categories ORDER BY name ASC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>EPC Navigation Tree</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 20px;
        }

        h2 {
            margin-bottom: 10px;
        }

        .tree {
            font-size: 14px;
        }

        details {
            margin-left: 20px;
        }

        summary {
            cursor: pointer;
            font-weight: bold;
        }

        .component {
            margin-left: 20px;
            color: #333;
        }

        button {
            padding: 6px 12px;
            margin-bottom: 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<h2>EPC Navigation Tree</h2>

<button onclick="expandAll()">Expand All</button>
<button onclick="collapseAll()">Collapse All</button>

<div class="tree">

<?php while($cat = $categories->fetch_assoc()) { ?>

    <details open>
        <summary>Category [ID: <?php echo $cat['id']; ?>] <?php echo htmlspecialchars($cat['name']); ?></summary>

        <?php
        // Subcategories
        $sub = $conn->prepare("SELECT id, name FROM subcategories WHERE category_id=? ORDER BY name ASC");
        $sub->bind_param("i", $cat['id']);
        $sub->execute();
        $subRes = $sub->get_result();

        while($s = $subRes->fetch_assoc()) {
        ?>

            <details open>
                <summary>Subcategory [ID: <?php echo $s['id']; ?>] <?php echo htmlspecialchars($s['name']); ?></summary>

                <?php
                // Types
                $type = $conn->prepare("SELECT id, name FROM types WHERE subcategory_id=? ORDER BY name ASC");
                $type->bind_param("i", $s['id']);
                $type->execute();
                $typeRes = $type->get_result();

                while($t = $typeRes->fetch_assoc()) {
                ?>

                    <details open>
                        <summary>Type [ID: <?php echo $t['id']; ?>] <?php echo htmlspecialchars($t['name']); ?></summary>

                        <?php
                        // Components
                        $comp = $conn->prepare("
                            SELECT id, name 
                            FROM backup_components_20251210 
                            WHERE type_id=? 
                            ORDER BY name ASC
                        ");
                        $comp->bind_param("i", $t['id']);
                        $comp->execute();
                        $compRes = $comp->get_result();

                        if($compRes->num_rows > 0) {
                            while($c = $compRes->fetch_assoc()) {
                                echo "<div class='component'>• [ID: {$c['id']}] " . htmlspecialchars($c['name']) . "</div>";
                            }
                        } else {
                            echo "<div class='component' style='color:red;'>⚠ No components</div>";
                        }
                        ?>

                    </details>

                <?php } ?>

            </details>

        <?php } ?>

    </details>

<?php } ?>

</div>

<script>
function expandAll() {
    document.querySelectorAll("details").forEach(el => el.open = true);
}

function collapseAll() {
    document.querySelectorAll("details").forEach(el => el.open = false);
}
</script>

</body>
</html>