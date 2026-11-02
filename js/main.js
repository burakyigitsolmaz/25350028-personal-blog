/* ============================================
   Kişisel Blog — Ana JavaScript
   Tema geçişi, mobil menü, yetenek çubukları
   ve iletişim formu ön doğrulaması.
   (Blog içerikleri artık PHP/MySQL tarafından
   üretildiği için JS'te veri dizisi yoktur.)
   ============================================ */

document.addEventListener("DOMContentLoaded", () => {

  /* ---------- Tema yönetimi ---------- */
  const themeToggle = document.getElementById("themeToggle");
  const root = document.documentElement;

  const savedTheme = localStorage.getItem("blog-theme");
  if (savedTheme) root.setAttribute("data-theme", savedTheme);

  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      const next = root.getAttribute("data-theme") === "dark" ? "light" : "dark";
      root.setAttribute("data-theme", next);
      localStorage.setItem("blog-theme", next);
    });
  }

  /* ---------- Mobil menü ---------- */
  const menuToggle = document.getElementById("menuToggle");
  const navLinks = document.getElementById("navLinks");

  if (menuToggle && navLinks) {
    menuToggle.addEventListener("click", () => {
      const open = navLinks.classList.toggle("open");
      menuToggle.classList.toggle("open", open);
      menuToggle.setAttribute("aria-expanded", open ? "true" : "false");
    });

    navLinks.querySelectorAll("a").forEach(link => {
      link.addEventListener("click", () => {
        navLinks.classList.remove("open");
        menuToggle.classList.remove("open");
        menuToggle.setAttribute("aria-expanded", "false");
      });
    });
  }

  /* ---------- Aktif navbar linki (yalnızca tek sayfa içi bağlantılar) ---------- */
  const sections = document.querySelectorAll("main section[id]");
  const navAnchors = document.querySelectorAll('.nav-link[href^="#"]');

  if (sections.length && navAnchors.length && "IntersectionObserver" in window) {
    const navObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          navAnchors.forEach(l => {
            l.classList.toggle("active", l.getAttribute("href") === "#" + entry.target.id);
          });
        }
      });
    }, { rootMargin: "-45% 0px -50% 0px" });

    sections.forEach(sec => navObserver.observe(sec));
  }

  /* ---------- Yetenek çubukları ---------- */
  const fills = document.querySelectorAll(".skill-fill");
  const pcts = document.querySelectorAll(".skill-pct");

  if (fills.length && "IntersectionObserver" in window) {
    const skillObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const target = entry.target;
          target.style.width = target.dataset.fill + "%";

          const pctEl = target.closest(".skill")?.querySelector(".skill-pct");
          if (pctEl) {
            const goal = Number(pctEl.dataset.pct);
            let current = 0;
            const step = Math.max(1, Math.round(goal / 30));
            const timer = setInterval(() => {
              current = Math.min(goal, current + step);
              pctEl.textContent = "%" + current;
              if (current >= goal) clearInterval(timer);
            }, 35);
          }

          skillObserver.unobserve(target);
        }
      });
    }, { threshold: 0.4 });

    fills.forEach(el => skillObserver.observe(el));
  } else {
    // Observer yoksa çubukları doğrudan doldur
    fills.forEach(el => { el.style.width = el.dataset.fill + "%"; });
    pcts.forEach(el => { el.textContent = "%" + el.dataset.pct; });
  }

  /* ---------- Kaydırma ilerlemesi, header gölgesi, yukarı çık ---------- */
  const progressBar = document.getElementById("scrollProgress");
  const header = document.querySelector(".site-header");
  const backToTop = document.getElementById("backToTop");
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  let scrollTicking = false;

  function updateScrollUI() {
    const doc = document.documentElement;
    const max = doc.scrollHeight - doc.clientHeight;
    const ratio = max > 0 ? Math.min(1, Math.max(0, doc.scrollTop / max)) : 0;

    if (progressBar) progressBar.style.width = (ratio * 100).toFixed(2) + "%";
    if (header) header.classList.toggle("scrolled", doc.scrollTop > 8);
    if (backToTop) backToTop.classList.toggle("show", doc.scrollTop > 480);

    scrollTicking = false;
  }

  window.addEventListener("scroll", () => {
    if (!scrollTicking) {
      scrollTicking = true;
      window.requestAnimationFrame(updateScrollUI);
    }
  }, { passive: true });

  window.addEventListener("resize", updateScrollUI, { passive: true });
  updateScrollUI();

  if (backToTop) {
    backToTop.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: reduceMotion ? "auto" : "smooth" });
    });
  }

  /* ---------- Görünüme girince yumuşak belirme ---------- */
  const revealTargets = document.querySelectorAll(
    ".section, .projects-teaser, .info-card, .contact-info, .contact-form"
  );

  if (!reduceMotion && "IntersectionObserver" in window && revealTargets.length) {
    revealTargets.forEach(el => el.classList.add("reveal"));

    const revealObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: "0px 0px -36px 0px" });

    revealTargets.forEach(el => revealObserver.observe(el));
  }

  /* ---------- İletişim formu ön doğrulaması ----------
     Sunucu tarafı doğrulama her zaman çalışır (PHP).
     Buradaki kontrol yalnızca hızlı geri bildirim içindir;
     form geçerliyse normal POST ile gönderilir.            */
  const form = document.getElementById("contactForm");
  const toast = document.getElementById("toast");
  let toastTimer = null;

  function showToast(message, isError) {
    if (!toast) return;
    clearTimeout(toastTimer);
    toast.textContent = message;
    toast.classList.toggle("error", Boolean(isError));
    toast.classList.add("show");
    toastTimer = setTimeout(() => toast.classList.remove("show"), 4000);
  }

  function setError(field, message) {
    const wrap = field.closest(".form-field");
    if (!wrap) return;
    wrap.classList.toggle("error", Boolean(message));
    const slot = wrap.querySelector(".form-error");
    if (slot) slot.textContent = message || "";
  }

  function validateField(field) {
    const value = field.value.trim();

    if (field.name === "adSoyad") {
      if (value.length < 3) return "Lütfen geçerli bir ad soyad giriniz.";
    } else if (field.name === "eposta") {
      if (!value) return "E-posta adresi boş bırakılamaz.";
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return "Geçerli bir e-posta adresi giriniz.";
    } else if (field.name === "konu") {
      if (value.length < 3) return "Lütfen bir konu belirtiniz.";
    } else if (field.name === "mesaj") {
      if (value.length < 10) return "Mesajınız en az 10 karakter olmalıdır.";
    }
    return "";
  }

  if (form) {
    const fields = form.querySelectorAll("input[name], textarea[name]");

    fields.forEach(field => {
      if (field.type === "hidden" || field.closest(".hp-field")) return;
      field.addEventListener("input", () => {
        const msg = field.closest(".form-field")?.classList.contains("error")
          ? validateField(field)
          : "";
        setError(field, msg);
      });
    });

    form.addEventListener("submit", e => {
      let firstInvalid = null;

      fields.forEach(field => {
        if (field.type === "hidden" || field.closest(".hp-field")) return;
        const msg = validateField(field);
        setError(field, msg);
        if (msg && !firstInvalid) firstInvalid = field;
      });

      if (firstInvalid) {
        e.preventDefault();
        showToast("Lütfen formdaki zorunlu alanları kontrol ediniz.", true);
        firstInvalid.focus();
      }
      // Geçerliyse engellemiyoruz: form PHP'ye POST edilir.
    });
  }

  /* ---------- Footer dinamik yıl ---------- */
  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();

});
