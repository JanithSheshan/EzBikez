<?php
require_once 'includes/header.php';
require_once 'includes/nav.php';
require_once 'config/db.php';
?>

<!-- Hero Section -->
<section class="hero-section text-center" style="min-height: 400px; position: relative;">
  <div style="position: absolute; inset: 0;"></div>
  <div class="container position-relative" style="z-index: 2;">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold mb-4 text-white">Ride Sri Lanka the Easy Way</h1>
        <p class="lead mb-5 text-white">Explore the beautiful beaches and landscapes of Unawatuna and Galle with our premium scooter and motorbike rentals.</p>
        <a href="/ezbikez/public/availability.php" class="btn btn-primary btn-lg me-3 shadow-lg px-5 py-3 fw-bold" style="font-size: 1.3rem; border-radius: 2rem; position: relative; overflow: hidden;">
          <span class="bike-anim" style="display: inline-block; position: relative;">
            <i class="fas fa-motorcycle me-2"></i>
            <span class="smoke"></span>
          </span>
          Rent a Bike Now
        </a>
        <style>
          .bike-anim {
            animation: bike-move 10s linear infinite;
          }
          @keyframes bike-move {
            0% { left: -50px; }
            50% { left: 250px; }
            100% { right: 40px;}
          }
          .bike-anim .smoke {
            position: absolute;
            left: -10px;
            top: 50%;
            width: 10px;
            height: 10px;
            background: radial-gradient(circle, #fff 60%, transparent 100%);
            opacity: 0.7;
            border-radius: 50%;
            transform: translateY(-50%) scale(1);
            animation: smoke-fade 5s linear infinite;
            z-index: 0;
          }
          @keyframes smoke-fade {
            0% {
              opacity: 0.7;
              transform: translateY(-50%) scale(1) translateX(0);
            }
            50% {
              opacity: 0.3;
              transform: translateY(-60%) scale(1.5) translateX(-10px);
            }
            
          }
        </style>
        <br>
        <a href="#features-bike" class="d-inline-block mt-4" aria-label="Scroll to features">
          <span style="display: inline-block; animation: bounce 2s infinite;">
            <i class="fas fa-angle-down fa-2x text-white"></i>
          </span>
        </a>
        <style>
          @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(15px);}
            60% {transform: translateY(8px);}
          }
        </style>
        <!-- Quick Procedure Tabs -->
        <div class="mt-5">
            <ul class="nav nav-tabs justify-content-center" id="quickProcedureTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-search" data-bs-toggle="tab" data-bs-target="#tab-pane-search" type="button" role="tab" aria-controls="tab-pane-search" aria-selected="true" style="color: #4a8a26;">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-book" data-bs-toggle="tab" data-bs-target="#tab-pane-book" type="button" role="tab" aria-controls="tab-pane-book" aria-selected="false" style="color: #4a8a26;">
                        <i class="fas fa-calendar-check me-1"></i> Book
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-pickup" data-bs-toggle="tab" data-bs-target="#tab-pane-pickup" type="button" role="tab" aria-controls="tab-pane-pickup" aria-selected="false" style="color: #4a8a26;">
                        <i class="fas fa-motorcycle me-1"></i> Pick Up
                    </button>
                </li>
            </ul>
            <div class="tab-content rounded-bottom shadow-sm p-2" id="quickProcedureTabContent" style="min-height:50px;">
                <div class="tab-pane fade show active" id="tab-pane-search" role="tabpanel" aria-labelledby="tab-search">
                    <strong>Search & Select:</strong> Check availability and choose your preferred bike category for your dates.
                </div>
                <div class="tab-pane fade" id="tab-pane-book" role="tabpanel" aria-labelledby="tab-book">
                    <strong>Book Online:</strong> Reserve your bike securely through our easy online booking system.
                </div>
                <div class="tab-pane fade" id="tab-pane-pickup" role="tabpanel" aria-labelledby="tab-pickup">
                    <strong>Pick Up:</strong> Visit our shop in Unawatuna or Galle and select your bike from prepared options.
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>

</section>


<!-- Features Section -->
<section class="py-5" id="features-bike">
    <!-- Inline Bike Images Row (Responsive) -->
<div class="container-fluid px-0" style="text-align: center; overflow-x: hidden;">
    <div class="bike-slider-wrapper" style="position: relative; width: 100%; max-width: 1000px; margin: 0 auto 20px auto; overflow: hidden;">
        <div class="bike-slider d-flex align-items-center" style="width: 2000px;">
            <?php
            $bikeImages = [
                'bikes/bike1.jpg',
                'bikes/bike2.jpg',
                'bikes/bike3.jpg',
                'bikes/bike4.jpg',
                'bikes/bike5.jpg',
                'bikes/bike6.jpg',
                'bikes/bike7.jpg',
                'bikes/bike8.jpg',
                'bikes/bike9.jpg',
                'bikes/bike10.jpg'
            ];
            // Duplicate the array for seamless looping
            $allBikes = array_merge($bikeImages, $bikeImages, $bikeImages, $bikeImages, $bikeImages, $bikeImages, $bikeImages);
            foreach ($allBikes as $i => $img): ?>
                <span class="bike-img-span" style="
                    display: inline-block;
                    position: relative;
                    z-index: <?php echo 20 - $i; ?>;
                ">
                    <img src="<?php echo $img; ?>" alt="Bike <?php echo ($i % 10) + 1; ?>"
                        class="bike-img-circle"
                        style="border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.2); object-fit:cover;">
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <style>
        .bike-slider-wrapper {
            width: 100%;
            max-width: 420px;
            height: 70px;
        }
        .bike-slider {
            display: flex;
            align-items: center;
            animation: bike-move-slider 30s linear infinite;
        }
        .bike-img-span {
            margin-left: -18px;
        }
        .bike-img-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
        }
        @media (max-width: 576px) {
            .bike-slider-wrapper { max-width: 220px; height: 44px; }
            .bike-img-span { margin-left: -10px !important; }
            .bike-img-circle { width: 44px !important; height: 44px !important; }
        }
        @media (min-width: 577px) and (max-width: 991px) {
            .bike-slider-wrapper { max-width: 320px; height: 54px; }
            .bike-img-span { margin-left: -14px !important; }
            .bike-img-circle { width: 54px !important; height: 54px !important; }
        }
        @media (min-width: 992px) {
            .bike-slider-wrapper { max-width: 420px; height: 70px; }
            .bike-img-span { margin-left: -18px !important; }
            .bike-img-circle { width: 70px !important; height: 70px !important; }
        }
        @keyframes bike-move-slider {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    </style>
    <script>
        // Optional: Pause animation on hover
        document.addEventListener('DOMContentLoaded', function() {
            var slider = document.querySelector('.bike-slider');
            var wrapper = document.querySelector('.bike-slider-wrapper');
            wrapper.addEventListener('mouseenter', function() {
                slider.style.animationPlayState = 'paused';
            });
            wrapper.addEventListener('mouseleave', function() {
                slider.style.animationPlayState = 'running';
            });
        });
    </script>
</div>
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col">
                <h2 class="fw-bold">Why Choose EzBikez?</h2>
                <p class="lead">We offer the best rental experience in Unawatuna</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-tag"></i>
                </div>
                <h4>Best Prices</h4>
                <p>Competitive rates with no hidden fees. Choose from our three categories to fit your budget.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h4>Quality Bikes</h4>
                <p>Well-maintained scooters and motorbikes for a safe and enjoyable riding experience.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h4>24/7 Support</h4>
                <p>Our team is always available to assist you with any issues during your rental period.</p>
            </div>
        </div>
    </div>
</section>

<!-- Google Reviews Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row text-center mb-4">
            <div class="col">
                <h2 class="fw-bold">What Our Customers Say</h2>
                <p class="lead">Read our latest Google reviews or leave your own!</p>
            </div>
        </div>
        <div class="row justify-content-center mb-4">
            <div class="col-md-4 text-center mb-3">
                <a href="https://g.page/r/CRNUxEOyYb8bEAE/review" target="_blank" rel="noopener" class="d-inline-block"></a>
                    <img src="data:image/png;base64,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" alt="Google Reviews QR Code" class="img-fluid mb-2" style="max-width: 180px;">
                </a>
                <div class="mt-2">
                    <a href="https://g.page/r/CRNUxEOyYb8bEAE/review" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                        Leave a Google Review
                    </a>
                </div>
            </div>
            <div class="col-md-6 text-center mb-3">
                <?php
                // Example static reviews array (replace with dynamic fetch if needed)
                $reviews = [
                    [
                        'author' => 'Anna S.',
                        'rating' => 5,
                        'text' => 'Amazing service and great bikes! Highly recommended for anyone visiting Unawatuna.',
                        'date' => '2024-05-10',
                        'photo' => 'https://randomuser.me/api/portraits/women/44.jpg'
                    ],
                    [
                        'author' => 'Liam P.',
                        'rating' => 5,
                        'text' => 'The booking process was super easy and the staff were very friendly.',
                        'date' => '2024-04-28',
                        'photo' => 'https://randomuser.me/api/portraits/men/32.jpg'
                    ],
                    [
                        'author' => 'Sophie D.',
                        'rating' => 4,
                        'text' => 'Bikes were in excellent condition. Will rent again next time!',
                        'date' => '2024-03-15',
                        'photo' => 'https://randomuser.me/api/portraits/women/65.jpg'
                    ],
                    [
                        'author' => 'Markus R.',
                        'rating' => 5,
                        'text' => 'Best prices and top-notch support. Thank you EzBikez!',
                        'date' => '2024-02-20',
                        'photo' => 'https://randomuser.me/api/portraits/men/76.jpg'
                    ],
                ];
                ?>
                <div id="googleReviewCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" style="max-width: 420px; margin: 0 auto;">
                  <div class="carousel-inner">
                    <?php foreach ($reviews as $i => $review): ?>
                      <div class="carousel-item<?php if ($i === 0) echo ' active'; ?>">
                        <div class="card shadow-sm border-0 h-100">
                          <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                              <img src="<?php echo htmlspecialchars($review['photo']); ?>" alt="<?php echo htmlspecialchars($review['author']); ?>" class="rounded-circle me-3" width="48" height="48" style="object-fit:cover;">
                              <div>
                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($review['author']); ?></h6>
                                <small class="text-muted"><?php echo date('M Y', strtotime($review['date'])); ?></small>
                              </div>
                            </div>
                            <div class="mb-2">
                              <?php for ($j = 1; $j <= 5; $j++): ?>
                                <i class="fas fa-star<?php if ($j > $review['rating']) echo '-o'; ?> text-warning"></i>
                              <?php endfor; ?>
                            </div>
                            <p class="mb-0 text-secondary" style="min-height: 60px;"><?php echo htmlspecialchars($review['text']); ?></p>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <style>
                #googleReviewCarousel .card {
                    border-radius: 1.2rem;
                    background: #f8f9fa;
                }
                #googleReviewCarousel .carousel-control-prev-icon,
                #googleReviewCarousel .carousel-control-next-icon {
                    filter: invert(0.7);
                }
                </style>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var carousel = document.querySelector('#googleReviewCarousel');
                    if (carousel) {
                        carousel.addEventListener('mouseenter', function() {
                            bootstrap.Carousel.getInstance(carousel).pause();
                        });
                        carousel.addEventListener('mouseleave', function() {
                            bootstrap.Carousel.getInstance(carousel).cycle();
                        });
                    }
                });
                </script>
            </div>
        </div>
    </div>
</section>

<!-- Bike Categories Section -->
<section class="py-5 position-relative" style="overflow: hidden;">
    <!-- Watermark Icon -->
    <i class="fas fa-motorcycle"
       style="
            position: absolute;
            top: 50%;
            left: 50%;
            font-size: 500px;
            color: rgba(0, 0, 0, 0.41);
            transform: translate(-50%, -50%) rotate(-15deg);
            pointer-events: none;
            z-index: 0;
       "></i>
    <div class="container position-relative" style="z-index: 1;">
        <div class="row text-center mb-5">
            <div class="col">
                <h2 class="fw-bold">Our Bike Categories</h2>
                <p class="lead">Choose the perfect bike for your adventure</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <span class="badge bg-primary mb-3">Category A</span>
                        <h4 class="card-title">Premium Bikes</h4>
                        <p class="card-text">High-quality scooters and motorbikes with the latest features for the discerning rider.</p>
                        <p class="fw-bold text-primary">From Rs. 3500/day</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <span class="badge bg-primary mb-3">Category B</span>
                        <h4 class="card-title">Standard Bikes</h4>
                        <p class="card-text">Reliable and well-maintained bikes offering great value for money.</p>
                        <p class="fw-bold text-primary">From Rs. 2800/day</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <span class="badge bg-primary mb-3">Category C</span>
                        <h4 class="card-title">Economy Bikes</h4>
                        <p class="card-text">Basic but functional bikes for those on a tight budget.</p>
                        <p class="fw-bold text-primary">From Rs. 2300/day</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col text-center">
                <a href="/ezbikez/public/availability.php" class="btn btn-success btn-lg px-5 py-3 fw-bold shadow" style="border-radius: 2rem; font-size: 1.2rem;">
                    <i class="fas fa-calendar-check me-2"></i>
                    Check Availability
                </a>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col">
                <h2 class="fw-bold">How It Works</h2>
                <p class="lead">Renting a bike has never been easier</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-search fa-2x"></i>
                </div>
                <h4>1. Search & Select</h4>
                <p>Check availability and choose your preferred bike category.</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
                <h4>2. Book Online</h4>
                <p>Make a reservation through our secure booking system.</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-motorcycle fa-2x"></i>
                </div>
                <h4>3. Pick Up</h4>
                <p>Visit our shop in Unawatuna or Galle and collect your bike.</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-road fa-2x"></i>
                </div>
                <h4>4. Ride & Enjoy</h4>
                <p>Hit the road and explore Sri Lanka with comfort and style!</p>
            </div>
        </div>
        <div class="row mt-4">
    <div class="col text-center">
        <p class="fw-bold text-muted">
            🚲 For every booking, we prepare <strong>3 bikes</strong> from your chosen category. 
            You can pick <strong>1 bike</strong> that suits you best.
        </p>
    </div>
</div>

    </div>
</section>


<?php
require_once 'includes/footer.php';
?>