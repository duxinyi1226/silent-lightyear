<?php
// 處理頁尾的深空訊號訂閱表單
$subscribe_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['observer_email'])) {
    $email = filter_var(trim($_POST['observer_email']), FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $subscribe_msg = "訊號已確認：觀測日誌將定期發送至 " . htmlspecialchars($email);
    } else {
        $subscribe_msg = "訊號格式錯誤：請輸入有效的電子信箱座標。";
    }
}
?>

<footer class="cinema-footer py-5 mt-5">
    <div class="container py-4">
        <div class="row align-items-center justify-content-between g-4">
            <div class="col-12 col-lg-5">
                <h4 class="footer-brand mb-2"><?php echo $site_config['title_zh']; ?> <span>| <?php echo $site_config['title_en']; ?></span></h4>
                <p class="text-secondary small mb-0">在無聲的宇宙尺度下，每一道星光都是一封來自過去的信。</p>
            </div>
            
            <div class="col-12 col-lg-6">
                <form method="POST" action="#footer-form" id="footer-form" class="signal-form">
                    <label class="form-label text-uppercase small text-secondary letter-spacing-2">接收深空觀測週報 RECEIVE SIGNALS</label>
                    <div class="d-flex gap-2">
                        <input type="email" name="observer_email" class="form-control cinema-input" placeholder="輸入你的電子信箱..." required>
                        <button type="submit" class="btn-cinema-sm text-nowrap">發送訊號</button>
                    </div>
                    <?php if ($subscribe_msg): ?>
                        <div class="mt-2 small text-info"><?php echo $subscribe_msg; ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <div class="text-center text-secondary small mt-5 pt-4 border-top border-secondary border-opacity-10">
            &copy; <?php echo date('Y'); ?> <?php echo $site_config['title_zh'] . ' ' . $site_config['title_en']; ?>. All Rights Reserved.
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>