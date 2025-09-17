<footer id="main-footer" class="text-light py-4 mt-5 border-top shadow-sm">
  <div class="container">
    <div class="row align-items-center">
      
      <!-- Logo & Tagline -->
      <div class="col-12 col-md-4 mb-3 mb-md-0 text-center text-md-start">
        <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2">
          <span class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 44px; height: 44px;">
            <i class="fas fa-bicycle text-white"></i>
          </span>
          <span class="fw-bold fs-4">EzBikez</span>
        </div>
        <div class="small">Ride Sri Lanka the easy way</div>
        <div class="text-secondary small">Unawatuna, Galle</div>
      </div>
      
      <!-- Quick Links -->
      <div class="col-12 col-md-4 mb-3 mb-md-0 text-center">
        <div class="fw-bold text-primary mb-2">Quick Links</div>
        <ul class="list-inline mb-0">
          <li class="list-inline-item"><a href="/ezbikez" class="text-light text-decoration-none px-2">Home</a></li>
          <li class="list-inline-item"><a href="/ezbikez/public/about.php" class="text-light text-decoration-none px-2">About</a></li>
          <li class="list-inline-item"><a href="/ezbikez/public/availability.php" class="text-light text-decoration-none px-2">Rent</a></li>
          <li class="list-inline-item"><a href="/ezbikez/public/login.php" class="text-light text-decoration-none px-2">Login</a></li>
          <li class="list-inline-item"><a href="/ezbikez/public/terms.php" class="text-light text-decoration-none px-2">Terms</a></li>
        </ul>
      </div>
      
      <!-- Contact & Social -->
      <div class="col-12 col-md-4 text-center text-md-end">
        <div class="fw-bold text-primary mb-2">Contact</div>
        <div class="small mb-2">
          <a href="tel:+94742184922" class="text-light text-decoration-none me-3">
            <i class="fas fa-phone me-1"></i>+94 74 218 4922
          </a>
          <a href="mailto:info@ezbikez.com" class="text-light text-decoration-none">
            <i class="fas fa-envelope me-1"></i>info@ezbikez.com
          </a>
        </div>
        <div class="mb-2 text-secondary small">
          <i class="fas fa-map-marker-alt me-1"></i>Unawatuna, Galle
        </div>
        <div class="social-links">
          <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-icon whatsapp"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>
    </div>
    
    <hr class="my-3 border-secondary">
    <div class="text-center small text-secondary">
      &copy; <?php echo date('Y'); ?> EzBikez. All rights reserved.
    </div>
  </div>
</footer>

<style>
  html, body {
    height: 100%;
  }
  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  footer {
    margin-top: auto;
  
    background-color: #003132;
    color: #ffffff;
    padding: 1rem 0;
    border-top: 1px solid #004d4d;
  }

  footer a {
    transition: color 0.3s ease, transform 0.2s ease;
  }
  footer a:hover {
    color: #4a8a26;
    text-decoration: none;
    transform: translateY(-2px);
  }

  .text-primary {
    color: #4a8a26 !important;
  }
  .text-secondary {
    color: #b0b0b0 !important;
  }

  .social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    margin: 0 5px;
    color: #fff;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;

  }

  .social-links a:hover {
    opacity: 0.8;
    transform: scale(1.1);
  }
</style>

<!-- Move to Top Button -->
<button id="moveToTopBtn" class="btn btn-success rounded-circle shadow" style="position: fixed; bottom: 90px; right: 30px; display: none; z-index: 1050;">
  <i class="fas fa-arrow-up"></i>
</button>
<script>
  // Show/hide Move to Top button on scroll
  const moveToTopBtn = document.getElementById('moveToTopBtn');
  window.addEventListener('scroll', function() {
    if (window.scrollY > 200) {
      moveToTopBtn.style.display = 'block';
    } else {
      moveToTopBtn.style.display = 'none';
    }
  });
  // Scroll to top on button click
  moveToTopBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
</script>

<!-- Font Awesome & Bootstrap -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ezbikez/public/assets/js/script.js"></script>
<script>
  function toggleFooterFixed() {
    const footer = document.getElementById('main-footer');
    if (document.body.scrollHeight <= window.innerHeight) {
      footer.classList.add('fixed-bottom');
    } else {
      footer.classList.remove('fixed-bottom');
    }
  }
  window.addEventListener('load', toggleFooterFixed);
  window.addEventListener('resize', toggleFooterFixed);
</script>
</body>
</html>
