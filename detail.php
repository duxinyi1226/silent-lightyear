<?php
require_once __DIR__ . '/includes/data.php';

// 取得天體 ID，若找不到則預設顯示土星
$id = isset($_GET['id']) ? $_GET['id'] : 'saturn';
$body = isset($celestial_bodies[$id]) ? $celestial_bodies[$id] : $celestial_bodies['saturn'];

$page_title = $body['name_zh'] . ' ' . $body['name_en'];
require_once __DIR__ . '/includes/header.php';

// 判斷天體類型以套用對應主視覺
$is_jupiter = ($body['id'] === 'jupiter');
$is_planet_body = in_array($body['category'], ['solar', 'moon', 'star']) && !in_array($body['id'], ['halley', 'sagittarius_a', 'crab_pulsar']);
?>

<!-- 單頁全幅巨星特寫主視覺 -->
<section class="detail-hero">
    <?php if ($is_jupiter): ?>
        <!-- 木星專屬：純 CSS 巨幅靜止 3D 氣態行星 -->
        <div class="detail-bg-stage detail-jupiter-closeup"></div>
    <?php elseif ($is_planet_body): ?>
        <!-- 其他星球：頂天立地巨幅滿版靜止特寫 -->
        <div class="detail-bg-stage cinema-planet-closeup" 
             style="background-image: url('<?php echo htmlspecialchars($body['image']); ?>');">
        </div>
    <?php else: ?>
        <!-- 星座與深空星系：全幅靜止劇照 -->
        <div class="detail-bg-stage cinema-cosmos-zoom" 
             style="background-image: url('<?php echo htmlspecialchars($body['image']); ?>');">
        </div>
    <?php endif; ?>

    <!-- 電影級上下暗角與底部黑漸層融合 -->
    <div class="detail-overlay"></div>

    <!-- 左下角電影海報級標題排版 -->
    <div class="container detail-header-content">
        <a href="index.php#archive" class="back-link mb-3 d-inline-block">&larr; 返回觀測目錄</a>
        <div class="mb-2">
            <span class="detail-category"><?php echo htmlspecialchars($body['category_zh']); ?></span>
        </div>
        <h1 class="detail-title-zh"><?php echo htmlspecialchars($body['name_zh']); ?></h1>
        <p class="detail-title-en mb-0"><?php echo htmlspecialchars($body['name_en']); ?></p>
    </div>
</section>

<!-- 觀測數據儀表板與深度紀實 -->
<section class="detail-body py-5">
    <div class="container py-lg-4">
        <div class="row g-5">
            <!-- 左側：觀測參數表 -->
            <div class="col-12 col-lg-4">
                <div class="telemetry-box">
                    <h4 class="telemetry-heading">觀測數據 TELEMETRY</h4>
                    <div class="telemetry-item">
                        <span class="label">地球距離 DISTANCE</span>
                        <span class="value"><?php echo htmlspecialchars($body['distance']); ?></span>
                    </div>
                    <div class="telemetry-item">
                        <span class="label">時間延遲 LIGHT DELAY</span>
                        <span class="value"><?php echo htmlspecialchars($body['light_time']); ?></span>
                    </div>
                    <div class="telemetry-item">
                        <span class="label">視星等 MAGNITUDE</span>
                        <span class="value"><?php echo htmlspecialchars($body['magnitude']); ?></span>
                    </div>
                    <div class="telemetry-item border-0 pb-0 mb-0">
                        <span class="label">天體分類 CLASSIFICATION</span>
                        <span class="value"><?php echo htmlspecialchars($body['category_zh']); ?></span>
                    </div>
                </div>
            </div>

            <!-- 右側：深空故事與文字敘述 -->
            <div class="col-12 col-lg-8">
                <h3 class="story-lead mb-4"><?php echo htmlspecialchars($body['summary']); ?></h3>
                <p class="story-text"><?php echo nl2br(htmlspecialchars($body['description'])); ?></p>
                
                <div class="mt-5 pt-4 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <span class="text-secondary small">DATA RECORDED BY SILENT LIGHTYEAR OBSERVATORY</span>
                    <a href="index.php#archive" class="btn-cinema-sm">探索其他星體</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>