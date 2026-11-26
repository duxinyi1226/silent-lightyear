<?php
require_once __DIR__ . '/includes/data.php';

// 建立輸出資料夾 docs（GitHub Pages 預設支援從 docs 資料夾發佈網站）
$out_dir = __DIR__ . '/docs';
if (!is_dir($out_dir)) {
    mkdir($out_dir, 0777, true);
}

// 複製 assets 資料夾
function copy_dir($src, $dst) {
    if (!is_dir($dst)) mkdir($dst, 0777, true);
    foreach (scandir($src) as $file) {
        if ($file === '.' || $file === '..') continue;
        if (is_dir("$src/$file")) copy_dir("$src/$file", "$dst/$file");
        else copy("$src/$file", "$dst/$file");
    }
}
if (is_dir(__DIR__ . '/assets')) {
    copy_dir(__DIR__ . '/assets', "$out_dir/assets");
}

// 1. 產生詳情頁 detail.html（用前端 JS 讀取網址 ?id=xxx，不需重新整理超流暢）
ob_start();
$_GET['id'] = 'saturn';
include __DIR__ . '/detail.php';
$detail_html = ob_get_clean();

// 將 detail.php 內部的返回連結改為 index.html，並注入前端動態切換資料的 JS
$detail_html = str_replace('index.php', 'index.html', $detail_html);
$json_bodies = json_encode($celestial_bodies, JSON_UNESCAPED_UNICODE);
$detail_script = <<<HTML
<script>
const CELESTIAL_DATA = {$json_bodies};
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id') || 'saturn';
    const body = CELESTIAL_DATA[id] || CELESTIAL_DATA['saturn'];
    
    document.title = body.name_zh + ' ' + body.name_en + ' | 寂靜光年 SILENT LIGHTYEAR';
    document.querySelector('.detail-category').textContent = body.category_zh;
    document.querySelector('.detail-title-zh').textContent = body.name_zh;
    document.querySelector('.detail-title-en').textContent = body.name_en;
    
    const vals = document.querySelectorAll('.telemetry-item .value');
    if (vals.length >= 4) {
        vals[0].textContent = body.distance;
        vals[1].textContent = body.light_time;
        vals[2].textContent = body.magnitude;
        vals[3].textContent = body.category_zh;
    }
    document.querySelector('.story-lead').textContent = body.summary;
    document.querySelector('.story-text').innerHTML = body.description.replace(/\\n/g, '<br>');

    const stage = document.querySelector('.detail-bg-stage');
    const isPlanet = ['solar', 'moon', 'star'].includes(body.category) && !['halley', 'sagittarius_a', 'crab_pulsar'].includes(body.id);
    if (body.id === 'jupiter') {
        stage.className = 'detail-bg-stage detail-jupiter-closeup';
        stage.style.backgroundImage = '';
    } else if (isPlanet) {
        stage.className = 'detail-bg-stage cinema-planet-closeup';
        stage.style.backgroundImage = "url('" + body.image + "')";
    } else {
        stage.className = 'detail-bg-stage cinema-cosmos-zoom';
        stage.style.backgroundImage = "url('" + body.image + "')";
    }
});
</script>
</body>
HTML;
$detail_html = str_replace('</body>', $detail_script, $detail_html);
file_put_contents("$out_dir/detail.html", $detail_html);

// 2. 產生首頁 index.html（支援前端即時分類與關鍵字搜尋）
ob_start();
$_GET = [];
include __DIR__ . '/index.php';
$index_html = ob_get_clean();

$index_html = str_replace(['detail.php', 'index.php'], ['detail.html', 'index.html'], $index_html);
$index_script = <<<HTML
<script>
document.addEventListener('DOMContentLoaded', () => {
    const cards = Array.from(document.querySelectorAll('#archive .col-12.col-md-6'));
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const searchForm = document.querySelector('form');
    const searchInput = document.querySelector('input[name="q"]');
    let currentType = 'all';

    function filterCards() {
        const kw = (searchInput ? searchInput.value : '').trim().toLowerCase();
        cards.forEach(col => {
            const href = col.querySelector('a').getAttribute('href');
            const id = new URLSearchParams(href.split('?')[1]).get('id');
            const item = {$json_bodies}[id];
            const matchType = currentType === 'all' || item.category === currentType;
            const text = (item.name_zh + item.name_en + item.category_zh + item.summary + item.description).toLowerCase();
            const matchKw = !kw || text.includes(kw);
            col.style.display = (matchType && matchKw) ? '' : 'none';
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', e => {
            const href = link.getAttribute('href');
            if (href && href.includes('type=')) {
                e.preventDefault();
                currentType = new URLSearchParams(href.split('?')[1].split('#')[0]).get('type') || 'all';
                filterCards();
                document.getElementById('archive').scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    if (searchForm) {
        searchForm.addEventListener('submit', e => {
            e.preventDefault();
            filterCards();
        });
        searchInput.addEventListener('input', filterCards);
    }
});
</script>
</body>
HTML;
$index_html = str_replace('</body>', $index_script, $index_html);
file_put_contents("$out_dir/index.html", $index_html);

echo "<h1>成功！已產生 GitHub Pages 展示版於 /docs 資料夾內。</h1>";
?>