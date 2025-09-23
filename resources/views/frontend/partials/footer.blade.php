<!-- Contact Section -->
<section id="contact-section" class="py-5 bg-gray">
    <div class="container">
        <div class="text-center mb-5">
            <div class="d-flex align-items-center gap-2" style="justify-content: center; margin-bottom: 1rem;">
                <i class="bi bi-headset text-warning display-5"></i>

            </div>

            <h2 class="fw-bold display-6 text-dark mb-3">Get in Touch</h2>
            <p class="fs-5 text-muted mx-auto" style="max-width: 600px;">Ready to start your adventure? Contact Alex
                directly to discuss your Land Rover rental or purchase.</p>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            <a href="{{ $settings->phone_link ?? 'tel:+1234567890' }}" class="btn btn-dark btn-lg px-4 fw-semibold">
                <i class="bi bi-telephone me-2"></i> {{ $settings->phone_btn_text ?? 'Call Alex' }}
            </a>

            <a href="{{ $settings->email_link ?? 'mailto:alex@example.com' }}"
                class="btn btn-dark btn-lg px-4 fw-semibold">
                <i class="bi bi-envelope me-2"></i> {{ $settings->email_btn_text ?? 'Email Alex' }}
            </a>

            <a href="{{ $settings->whatsapp_link ?? '#' }}" target="_blank" rel="noopener"
                class="btn btn-lg px-4 fw-semibold"
                style="background-color: #25D366; color: white; border-color: #25D366;"
                onmouseover="this.style.backgroundColor='#25D366'; this.style.filter='brightness(90%)';"
                onmouseout="this.style.backgroundColor='#25D366'; this.style.filter='brightness(100%)';">
                <i class="bi bi-whatsapp me-2 text-white"></i> {{ $settings->whatsapp_btn_text ?? 'WhatsApp' }}
            </a>

        </div>
    </div>
</section>
