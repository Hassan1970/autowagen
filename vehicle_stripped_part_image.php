<?php
require_once "config/config.php";
include "includes/header.php";

$part_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;

if ($part_id <= 0 || $vehicle_id <= 0) {
    die("Invalid IDs.");
}

// Load part
$stmt = $conn->prepare("
    SELECT sp.*, v.stock_code, v.year, v.make, v.model
    FROM vehicle_stripped_parts sp
    LEFT JOIN vehicles v ON v.id = sp.vehicle_id
    WHERE sp.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $part_id);
$stmt->execute();
$part = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$part) {
    die("Part not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Upload Photos</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial;
}
.wrap {
    width:70%;
    margin:30px auto;
    background:#111;
    border:2px solid #b00000;
    padding:25px;
    border-radius:10px;
}
h2 { color:#ff3333; }

#dropzone {
    width:100%;
    height:150px;
    border:2px dashed #b00000;
    border-radius:10px;
    text-align:center;
    margin-bottom:25px;
    cursor:pointer;
    padding-top:40px;
}
#dropzone:hover { border-color:#ff0000; }

.gallery img {
    width:120px;
    height:120px;
    object-fit:cover;
    border:2px solid #b00000;
    margin:8px;
    border-radius:8px;
}
.gallery-item {
    display:inline-block;
    position:relative;
}
.delete-btn {
    position:absolute;
    top:3px;
    right:3px;
    background:red;
    color:#fff;
    padding:3px 6px;
    font-size:12px;
    border-radius:4px;
    cursor:pointer;
}
</style>
</head>

<body>

<div class="wrap">
    <h2>Photos for: <?= $part['id'] ?></h2>

    <p>
        Vehicle: <b><?= $part['stock_code'] ?></b>
        (<?= $part['year'] ?> / <?= $part['make'] ?> / <?= $part['model'] ?>)
    </p>

    <a href="vehicle_stripping_view.php?vehicle_id=<?= $vehicle_id ?>" style="color:#ff3333;">
        ← Back to Vehicle
    </a>
    <br><br>

    <!-- Upload Box -->
    <div id="dropzone">
        Drop photos here or click to browse<br>
        <small>(JPG/PNG/GIF – Multi Upload)</small>
    </div>

    <!-- Hidden File Input -->
    <input type="file" id="fileInput" multiple style="display:none;">
    <input type="hidden" id="part_id" value="<?= $part_id ?>">

    <h3 style="color:#ff3333;">Gallery (drag to sort)</h3>
    <div id="gallery" class="gallery"></div>
</div>

<script>
// ------------------------------------------------------------
// TEST — CONFIRM PAGE JAVASCRIPT IS RUNNING
// ------------------------------------------------------------
console.log("UPLOAD PAGE JS IS RUNNING");

$("#dropzone").on("click", function() {
    alert("DROPZONE CLICKED");
    $("#fileInput").click();
});

// ------------------------------------------------------------
// FILE SELECT → UPLOAD
// ------------------------------------------------------------
$("#fileInput").on("change", function() {
    uploadFiles(this.files);
});

// ------------------------------------------------------------
// DRAG & DROP
// ------------------------------------------------------------
$("#dropzone").on("dragover", function(e){
    e.preventDefault();
    $(this).css("border-color", "#ff0000");
});
$("#dropzone").on("dragleave", function(e){
    e.preventDefault();
    $(this).css("border-color", "#b00000");
});
$("#dropzone").on("drop", function(e){
    e.preventDefault();
    $(this).css("border-color", "#b00000");

    let files = e.originalEvent.dataTransfer.files;
    uploadFiles(files);
});

// ------------------------------------------------------------
// AJAX UPLOAD FUNCTION
// ------------------------------------------------------------
function uploadFiles(files) {
    let partId = $("#part_id").val();
    let formData = new FormData();

    formData.append("part_id", partId);

    for (let i = 0; i < files.length; i++) {
        formData.append("photos[]", files[i]);
    }

    $.ajax({
        url: "ajax/upload_stripped_part_images.php",
        type: "POST",
        data: formData,
        processData:false,
        contentType:false,
        success:function(res){
            console.log("UPLOAD RESPONSE:", res);

            if(res.success){
                loadGallery();
            } else {
                alert("Upload failed: " + res.message);
            }
        },
        error:function(xhr){
            console.error(xhr.responseText);
            alert("AJAX error. Check console.");
        }
    });
}

// ------------------------------------------------------------
// LOAD GALLERY
// ------------------------------------------------------------
function loadGallery(){
    let partId = $("#part_id").val();

    $.get("ajax/get_stripped_part_images.php", {part_id: partId}, function(res){
        $("#gallery").html(res.html);

        // Enable drag-sort
        $("#gallery").sortable({
            update:function(){
                let order = [];
                $(".gallery-item").each(function(){
                    order.push($(this).data("id"));
                });

                $.post("ajax/sort_stripped_part_images.php", {
                    order: JSON.stringify(order),
                    part_id: partId
                });
            }
        });
    }, "json");
}

// Initial load
loadGallery();
</script>

</body>
</html>

