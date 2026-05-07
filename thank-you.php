<?php
session_start();

// Only allow access after a successful form submission
if (empty($_SESSION['form_submitted'])) {
    header('Location: /');
    exit;
}

// Consume the token — one-time access only
unset($_SESSION['form_submitted']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/lpu-favicon.png">
  <link rel="icon" type="image/png" sizes="16x16" href="images/lpu-favicon.png">
  <link rel="apple-touch-icon" sizes="180x180" href="images/lpu-favicon.png">

  <title>Thank You | LPU Online University</title>
  <meta name="description" content="Thank you for reaching out to LPU Online University. Our team will contact you shortly.">
  <meta name="robots" content="noindex, nofollow">
  <link rel="canonical" href="https://lpuonlineuniversity.in/thank-you">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Line Awesome Icons -->
  <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">

  <!-- Page CSS -->
  <link rel="stylesheet" href="css/index.css">

  <!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1470311560891339');
fbq('track', 'PageView');
fbq('track', 'Lead');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1470311560891339&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->

  <style>
    .thankyou-section {
      min-height: calc(100vh - 220px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 80px 20px;
      background: linear-gradient(135deg, #f0f4ff 0%, #fafbff 100%);
    }
    .thankyou-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 48px rgba(0,0,0,0.10);
      padding: 60px 48px;
      max-width: 560px;
      width: 100%;
      text-align: center;
    }
    .thankyou-card__icon {
      width: 88px;
      height: 88px;
      background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 28px;
    }
    .thankyou-card__icon i {
      font-size: 48px;
      color: #27ae60;
    }
    .thankyou-card__title {
      font-size: 32px;
      font-weight: 800;
      color: #1a1a2e;
      margin: 0 0 12px;
    }
    .thankyou-card__subtitle {
      font-size: 17px;
      color: #555;
      line-height: 1.7;
      margin: 0 0 32px;
    }
    .thankyou-card__divider {
      height: 2px;
      background: linear-gradient(90deg, transparent, #e0e7ff, transparent);
      margin: 0 0 32px;
      border: none;
    }
    .thankyou-card__steps {
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin-bottom: 36px;
      text-align: left;
    }
    .thankyou-card__step {
      display: flex;
      align-items: flex-start;
      gap: 14px;
    }
    .thankyou-card__step-num {
      width: 32px;
      height: 32px;
      min-width: 32px;
      background: linear-gradient(135deg, #6c63ff, #4e46e5);
      color: #fff;
      border-radius: 50%;
      font-size: 14px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .thankyou-card__step-text {
      font-size: 15px;
      color: #444;
      padding-top: 5px;
      line-height: 1.5;
    }
    .thankyou-card__actions {
      display: flex;
      gap: 14px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .thankyou-card__btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 13px 28px;
      border-radius: 50px;
      font-size: 15px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }
    .thankyou-card__btn--primary {
      background: linear-gradient(135deg, #6c63ff, #4e46e5);
      color: #fff;
      box-shadow: 0 4px 16px rgba(108,99,255,0.30);
    }
    .thankyou-card__btn--primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 22px rgba(108,99,255,0.40);
    }
    .thankyou-card__btn--secondary {
      background: #f3f4ff;
      color: #4e46e5;
      border: 1.5px solid #dde1ff;
    }
    .thankyou-card__btn--secondary:hover {
      background: #e8ebff;
    }
    @media (max-width: 600px) {
      .thankyou-card {
        padding: 40px 24px;
      }
      .thankyou-card__title {
        font-size: 26px;
      }
    }
  </style>
</head>
<body>

  <!-- ========== HEADER ========== -->
  <header class="header" id="header">
    <div class="header__inner">
      <div class="header__logo">
        <a href="/" aria-label="LPU Online University Home">
          <img src="images/LPU-Online-Logo.svg" alt="LPU Online University" width="280" height="60">
        </a>
      </div>
      <nav class="header__nav" aria-label="Main Navigation">
        <ul class="header__menu">
          <li class="header__menu-item"><a href="/" class="header__menu-link">Home</a></li>
          <li class="header__menu-item"><a href="jai-jawan-scholarship.html" class="header__menu-link">Jai Jawan Scholarship</a></li>
          <li class="header__menu-item"><a href="/blog/" class="header__menu-link">Blog</a></li>
        </ul>
      </nav>
      <div class="header__actions">
        <a href="tel:9311381814" class="btn-phone" aria-label="Call LPU Online">
          <i class="las la-phone"></i> 93113 81814
        </a>
      </div>
      <button class="header__toggle" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
        <i class="la la-bars"></i>
      </button>
    </div>
  </header>

  <!-- ========== MOBILE MENU ========== -->
  <div class="mobile-menu" id="mobileMenu" role="dialog" aria-label="Mobile Navigation">
    <div class="mobile-menu__overlay" id="menuOverlay"></div>
    <div class="mobile-menu__panel">
      <button class="mobile-menu__close" id="menuClose" aria-label="Close menu">
        <i class="la la-times"></i>
      </button>
      <ul class="mobile-menu__list">
        <li class="mobile-menu__item"><a href="/" class="mobile-menu__link">Home</a></li>
        <li class="mobile-menu__item"><a href="jai-jawan-scholarship.html" class="mobile-menu__link">Jai Jawan Scholarship</a></li>
        <li class="mobile-menu__item"><a href="/blog/" class="mobile-menu__link">Blog</a></li>
        <li class="mobile-menu__item"><a href="tel:9311381814" class="mobile-menu__link">93113 81814</a></li>
      </ul>
    </div>
  </div>

  <!-- ========== THANK YOU SECTION ========== -->
  <section class="thankyou-section">
    <div class="thankyou-card">
      <div class="thankyou-card__icon">
        <i class="la la-check-circle"></i>
      </div>
      <h1 class="thankyou-card__title">Thank You!</h1>
      <p class="thankyou-card__subtitle">
        Your enquiry has been received.<br>
        Our admissions team will get in touch with you <strong>soon</strong>.
      </p>
      <hr class="thankyou-card__divider">
      <div class="thankyou-card__steps">
        <div class="thankyou-card__step">
          <div class="thankyou-card__step-num">1</div>
          <div class="thankyou-card__step-text">Our counsellor will call you to understand your goals and suggest the best program.</div>
        </div>
        <div class="thankyou-card__step">
          <div class="thankyou-card__step-num">2</div>
          <div class="thankyou-card__step-text">You will receive a detailed program brochure on your email.</div>
        </div>
        <div class="thankyou-card__step">
          <div class="thankyou-card__step-num">3</div>
          <div class="thankyou-card__step-text">Complete your online application and secure your seat for the upcoming session.</div>
        </div>
      </div>
      <div class="thankyou-card__actions">
        <a href="/" class="thankyou-card__btn thankyou-card__btn--primary">
          <i class="la la-home"></i> Back to Home
        </a>
        <a href="tel:9311381814" class="thankyou-card__btn thankyou-card__btn--secondary">
          <i class="la la-phone"></i> Call Us Now
        </a>
      </div>
    </div>
  </section>

  <!-- ========== FOOTER ========== -->
  <footer class="footer">
    <div class="footer__inner">
      <div class="footer__top">
        <div class="footer__logo">
          <img src="images/footer-logo.svg" alt="LPU Online University" width="240" height="52">
        </div>
        <div class="footer__contact">
          <div class="footer__contact-item">
            <i class="la la-map-marker"></i>
            <div>
              <strong>Address:</strong>
              <p>LPU Online, Block 32, Lovely Professional University,<br>Jalandhar - Delhi G.T. Road, Phagwara, Punjab (India), 144411</p>
            </div>
          </div>
          <div class="footer__contact-item">
            <i class="la la-envelope"></i>
            <div>
              <strong>Email:</strong>
              <p><a href="mailto:admissions@lpuonlineuniversity.in">admissions@lpuonlineuniversity.in</a></p>
            </div>
          </div>
          <div class="footer__contact-row">
            <div class="footer__contact-item">
              <i class="la la-phone"></i>
              <div>
                <strong>For Admissions:</strong>
                <p><a href="tel:9311381814">93113 81814</a></p>
              </div>
            </div>
            <div class="footer__contact-item">
              <i class="la la-headset"></i>
              <div>
                <strong>For Students Support:</strong>
                <p><a href="tel:01824520500">93113 81814</a></p>
                <span class="footer__contact-note">(LMS, Classes, Exams, etc.)</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="footer__bottom">
        <p class="footer__copyright">&copy; <?= date('Y') ?> Lovely Professional University. All rights reserved.</p>
        <div class="footer__links">
          <a href="#">Privacy Policy</a>
          <span>|</span>
          <a href="#">Disclaimer</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    (function() {
      var h = document.getElementById('header');
      window.addEventListener('scroll', function() {
        if (window.scrollY > 10) h.classList.add('sticky');
        else h.classList.remove('sticky');
      });
      var toggle = document.getElementById('menuToggle');
      var menu = document.getElementById('mobileMenu');
      var close = document.getElementById('menuClose');
      var overlay = document.getElementById('menuOverlay');
      function openMenu() { menu.classList.add('active'); toggle.setAttribute('aria-expanded', 'true'); document.body.style.overflow = 'hidden'; }
      function closeMenu() { menu.classList.remove('active'); toggle.setAttribute('aria-expanded', 'false'); document.body.style.overflow = ''; }
      toggle.addEventListener('click', openMenu);
      close.addEventListener('click', closeMenu);
      overlay.addEventListener('click', closeMenu);
    })();
  </script>

</body>
</html>
