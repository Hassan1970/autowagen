<?php
require_once __DIR__ . '/config/config.php';
$page_title = "OEM Parts";
include __DIR__ . '/includes/header.php';

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Load all OEM parts
$sql = "
SELECT 
    p.id,
    p.vehicle_id,
    p.category_id,
    p.subcategory_id,
    p.type_id,
    p.component_id,
    p.part_name,
    p.part_number,
    p.oem_number,
    p.part_condition,
    p.side,
    p.price,
    p.notes,
    p.created_at,
    v.stock_code,
    v.make,
    v.model,
    v.year,
    c.name AS category_name,
    sc.name AS subcategory_name,
    t.name AS type_name,
    comp.name AS component_name
FROM parts p
LEFT JOIN vehicles v ON v.id = p.vehicle_id
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN subcategories sc ON sc.id = p.subcategory_id
LEFT JOIN types t ON t.id = p.type_id
LEFT JOIN components comp ON comp.id = p.component_id
ORDER BY p.id DESC
";

$res = $conn->query($sql);
?>

<div class="page-header">
    <div>
        <h1>OEM Parts</h1>
        <p>All OEM parts linked to vehicles.</p>
    </div>
</div>

<div style="background:#111;border:1px solid #b00000;border-radius:8px;padding:15px;">
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vehicle</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Type</th>
                    <th>Component</th>
                    <th>Part Name</th>
                    <th>OEM Number</th>
                    <th>Side</th>
                    <th>Condition</th>
                    <th>Price</th>
                    <th style="width:160px;">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($res && $res->num_rows > 0): ?>
                    <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?=h($row['id'])?></td>

                            <td>
                                <?php if ($row['vehicle_id']): ?>
                                    <?=h($row['stock_code'].' - '.$row['make'].' '.$row['model'].' '.$row['year'])?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <td><?=h($row['category_name'])?></td>
                            <td><?=h($row['subcategory_name'])?></td>
                            <td><?=h($row['type_name'])?></td>
                            <td><?=h($row['component_name'])?></td>

                            <td><?=h($row['part_name'])?></td>
                            <td><?=h($row['oem_number'])?></td>
                            <td><?=h($row['side'])?></td>
                            <td><?=h($row['part_condition'])?></td>

                            <td>R <?=number_format($row['price'], 2)?></td>

                            <td>
                                <a href="part_view.php?id=<?=$row['id']?>" class="btn secondary" style="padding:4px 8px;">View</a>
                                <a href="part_edit.php?id=<?=$row['id']?>" class="btn secondary" style="padding:4px 8px;">Edit</a>
                                <a href="part_delete.php?id=<?=$row['id']?>" class="btn secondary"
                                   onclick="return confirm('Delete this part?');"
                                   style="padding:4px 8px;">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="12" style="text-align:center;color:#777;padding:18px;">
                            No OEM parts found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
