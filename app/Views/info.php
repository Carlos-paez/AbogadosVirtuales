<div class="info-layout">
    <aside class="info-sidebar">
        <h3>En esta página</h3>
        <ul id="infoToc"></ul>
    </aside>
    <div class="info-page"><?= $infoHtml ?? '' ?></div>
</div>
<script>
(function() {
    const toc = document.getElementById("infoToc");
    if (!toc) return;
    const headings = document.querySelectorAll(".info-page h2, .info-page h3");
    headings.forEach(function(h) {
        if (!h.id) {
            h.id = "sec-" + Math.random().toString(36).slice(2, 8);
        }
        var li = document.createElement("li");
        var a = document.createElement("a");
        a.href = "#" + h.id;
        a.textContent = h.textContent;
        if (h.tagName === "H3") a.style.paddingLeft = "1rem";
        li.appendChild(a);
        toc.appendChild(li);
    });
})();
</script>
