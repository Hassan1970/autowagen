<?php
/**
 * EPC TREE – EG3 (GLOBAL FILTER)
 * Filters across Categories, Subcategories, Types, Components
 * UI ONLY – SAFE
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EPC Tree – Global Filter</title>

<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 15px;
    }

    h2 {
        margin-top: 25px;
        color: #222;
    }

    /* FILTER BAR */
    .filter-bar {
        position: sticky;
        top: 0;
        background: #ffffff;
        padding: 12px;
        border-bottom: 1px solid #ccc;
        z-index: 1000;
    }

    .filter-bar input {
        width: 100%;
        padding: 10px;
        font-size: 15px;
        border: 1px solid #bbb;
        border-radius: 4px;
    }

    /* TREE */
    ul.epc-tree {
        list-style: none;
        padding-left: 18px;
    }

    .category {
        font-size: 18px;
        font-weight: bold;
        margin-top: 20px;
    }

    .subcategory {
        font-size: 16px;
        font-weight: bold;
        margin-top: 12px;
        color: #333;
    }

    .epc-type {
        font-weight: bold;
        margin-top: 8px;
        cursor: pointer;
    }

    .epc-component {
        margin-left: 20px;
        color: #444;
    }

    .hidden {
        display: none;
    }

    /* HIGHLIGHT */
    .highlight {
        background: yellow;
        padding: 1px 2px;
    }
</style>
</head>
<body>

<!-- GLOBAL FILTER -->
<div class="filter-bar">
    <input
        type="text"
        id="epcFilter"
        placeholder="Search EPC (category, type, component...)"
        onkeyup="filterEpcTree()"
    >
</div>

<!-- EPC TREE -->
<ul class="epc-tree">

    <!-- CATEGORY -->
    <li class="category epc-node">Body Exterior</li>
    <ul>

        <!-- SUBCATEGORY -->
        <li class="subcategory epc-node">Doors (Front)</li>
        <ul>

            <!-- TYPE 2653 -->
            <li class="epc-type epc-node">Door Shell / Outer Panel</li>
            <ul>
                <li class="epc-component epc-node">Door Outer Panel / Skin</li>
                <li class="epc-component epc-node">Outer Panel Mounting Flanges</li>
                <li class="epc-component epc-node">Spot Weld Zones</li>
                <li class="epc-component epc-node">Panel Reinforcement Ribs</li>
            </ul>

            <!-- TYPE -->
            <li class="epc-type epc-node">Weather Seal</li>
            <ul>
                <li class="epc-component epc-node">Outer Belt Weatherstrip</li>
                <li class="epc-component epc-node">Inner Belt Weatherstrip</li>
                <li class="epc-component epc-node">Door Perimeter Seal</li>
                <li class="epc-component epc-node">Glass Run Seal</li>
            </ul>

        </ul>
    </ul>

    <!-- ANOTHER CATEGORY (EXAMPLE) -->
    <li class="category epc-node">Body Interior</li>
    <ul>
        <li class="subcategory epc-node">Seats</li>
        <ul>
            <li class="epc-type epc-node">Seat Frame</li>
            <ul>
                <li class="epc-component epc-node">Seat Base Frame</li>
                <li class="epc-component epc-node">Backrest Frame</li>
            </ul>
        </ul>
    </ul>

</ul>

<!-- SCRIPT -->
<script>
function filterEpcTree() {
    const filter = document.getElementById("epcFilter").value.toLowerCase();
    const nodes = document.querySelectorAll(".epc-node");

    // Remove previous highlights
    document.querySelectorAll(".highlight").forEach(el => {
        el.outerHTML = el.innerText;
    });

    nodes.forEach(node => {
        const text = node.innerText.toLowerCase();

        if (filter === "") {
            node.classList.remove("hidden");
            showParents(node);
        } else if (text.includes(filter)) {
            node.classList.remove("hidden");
            highlightText(node, filter);
            showParents(node);
        } else {
            node.classList.add("hidden");
        }
    });
}

function showParents(node) {
    let parent = node.parentElement;
    while (parent) {
        if (parent.tagName === "UL" || parent.tagName === "LI") {
            parent.classList.remove("hidden");
        }
        parent = parent.parentElement;
    }
}

function highlightText(element, filter) {
    const regex = new RegExp("(" + filter + ")", "gi");
    element.innerHTML = element.innerHTML.replace(
        regex,
        '<span class="highlight">$1</span>'
    );
}
</script>

</body>
</html>
