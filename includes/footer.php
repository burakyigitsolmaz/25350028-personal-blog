<?php
/**
 * Site geneli alt parça: footer, toast, script.
 */
?>
  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div class="footer-social" aria-label="Sosyal ve iletişim bağlantıları">
        <a href="mailto:<?= e(setting('email', 'solmazburakss@gmail.com')) ?>" aria-label="E-posta gönder" title="E-posta">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
        </a>
        <a href="<?= e(setting('github_url', 'https://github.com/burakyigitsolmaz')) ?>" target="_blank" rel="noopener" aria-label="GitHub profili" title="GitHub">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M12 .5A11.5 11.5 0 0 0 .5 12a11.5 11.5 0 0 0 7.86 10.92c.58.1.79-.25.79-.56v-2c-3.21.7-3.89-1.35-3.89-1.35-.53-1.34-1.29-1.7-1.29-1.7-1.05-.72.08-.71.08-.71 1.16.08 1.77 1.19 1.77 1.19 1.04 1.78 2.72 1.27 3.38.97.11-.75.41-1.27.74-1.56-2.56-.29-5.25-1.28-5.25-5.71 0-1.26.45-2.29 1.19-3.1-.12-.29-.52-1.47.11-3.06 0 0 .97-.31 3.18 1.18a11 11 0 0 1 5.78 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.77.12 3.06.74.81 1.18 1.84 1.18 3.1 0 4.44-2.69 5.41-5.27 5.7.42.36.79 1.07.79 2.16v3.2c0 .31.2.67.8.56A11.5 11.5 0 0 0 23.5 12 11.5 11.5 0 0 0 12 .5z"/></svg>
        </a>
        <a href="<?= e(setting('linkedin_url', 'https://www.linkedin.com/in/burakyigitsolmaz/')) ?>" target="_blank" rel="noopener" aria-label="LinkedIn profili" title="LinkedIn">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05a3.74 3.74 0 0 1 3.37-1.85c3.6 0 4.27 2.37 4.27 5.46v6.28zM5.35 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77A1.75 1.75 0 0 0 0 1.73v20.54C0 23.22.79 24 1.77 24h20.45A1.77 1.77 0 0 0 24 22.27V1.73A1.77 1.77 0 0 0 22.22 0z"/></svg>
        </a>
        <a href="<?= e(setting('instagram_url', 'https://www.instagram.com/burakyigitsolmaz')) ?>" target="_blank" rel="noopener" aria-label="Instagram profili" title="Instagram">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
        </a>
      </div>
      <p class="footer-copy">
        © <span id="year">2026</span> <?= e(setting('site_title_short', 'Burak Yiğit Solmaz')) ?> — Öğrenci No: 25350028.
        Bu site kişisel tanıtım amaçlı hazırlanmıştır.
      </p>
    </div>
  </footer>

  <!-- BİLDİRİM -->
  <div class="toast" id="toast" role="status" aria-live="polite"></div>

  <button class="back-to-top" id="backToTop" type="button" aria-label="Sayfanın başına dön" title="Yukarı çık">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
  </button>

  <script src="<?= e(url('js/main.js')) ?>"></script>
</body>
</html>
