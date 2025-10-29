<!-- Contact Section -->
<section id="contact-section" class="py-5 bg-white">
    <div class="container text-center">
        <!-- Heading -->
        <div class="mb-5">
            <div class="d-flex align-items-center justify-content-center mb-3">
                <i class="bi bi-headset display-5" style="color: #CF9B4D;"></i>
            </div>
            <h2 class="fw-bold display-6  mb-3" style="color: #679767;">Get in Touch</h2>
            <p class="fs-5 text-muted mx-auto" style="max-width: 600px;">
                Ready to start your adventure? Contact Alex directly to discuss your Land Rover rental or purchase.
            </p>
        </div>

        <!-- Rounded Contact Icons -->
        <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap mb-5">
            <a href="tel:{{ $settings->phone_link ?? '+1234567890' }}"
               class="contact-icon-btn d-flex align-items-center justify-content-center">
                <i class="bi bi-telephone-fill fs-4"></i>
            </a>

            <a href="mailto:{{ $settings->email_link ?? 'mailto:alex@example.com' }}"
               class="contact-icon-btn d-flex align-items-center justify-content-center">
                <i class="bi bi-envelope-fill fs-4"></i>
            </a>

            <a href="https://wa.link/8bgpe5" target="_blank" rel="noopener"
               class="contact-icon-btn whatsapp d-flex align-items-center justify-content-center">
                <i class="bi bi-whatsapp fs-4"></i>
            </a>
        </div>
    </div>

   <footer class="footer border-top bg-white py-3">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center text-center">

        <!-- Left: Copyright -->
        <div class="text-muted small mb-2 mb-md-0 d-flex align-items-center justify-content-center">
            &copy; {{ date('Y') }} Alex's Land Rover Rentals. All rights reserved.
        </div>

        <!-- Right: Links -->
        <div class="footer-links d-flex align-items-center justify-content-center">
            <a  class="text-muted text-decoration-none me-3">Privacy Policy</a>
            <span class="text-muted">|</span>
            <a  class="text-muted text-decoration-none ms-3">Terms & Conditions</a>
        </div>
    </div>
</footer>
</section>

<!-- Styles -->
<style>
/* Contact Section */
#contact-section {
    background-color: #fff;
}

/* Contact Icons */
.contact-icon-btn {
    width: 65px;
    height: 65px;
    border-radius: 50%;
    background-color: white;
    color: black;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

.contact-icon-btn:hover {
    background-color: #679767;
    color: white;
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.25);
}

.contact-icon-btn.whatsapp {
    background-color: #25D366;
}

.contact-icon-btn.whatsapp:hover {
    background-color: #1ebd59;
}

/* Footer */
.footer {
    font-size: 0.95rem;
    border-top: 1px solid #dee2e6;
}

.footer a {
    color: #6c757d;
    transition: color 0.3s ease;
}

.footer a:hover {
    color: #CF9B4D;
}

.footer .text-muted {
    color: #6c757d !important;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .footer .container {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
}

@media (max-width: 576px) {
    .contact-icon-btn {
        width: 55px;
        height: 55px;
    }
}
</style>
