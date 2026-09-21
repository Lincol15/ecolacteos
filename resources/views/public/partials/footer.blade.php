<footer>
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-logo logo">
                    <div class="logo-icon">
                        <img src="{{ asset('images/logo-ecolacteos.png') }}" alt="Ecolácteos Huata">
                    </div>
                    <div class="logo-wordmark">
                        <span class="lw-main">Ecolácteos</span>
                        <span class="lw-sub">Huata</span>
                    </div>
                </div>
                <p class="footer-desc">Sistema de gestión y comercialización de productos lácteos frescos. Calidad que se percibe en cada gota.</p>
                <div class="footer-socials">
                    <a href="#" class="social-icon">📘</a>
                    <a href="#" class="social-icon">📷</a>
                    <a href="#" class="social-icon">🐦</a>
                    <a href="#" class="social-icon">📱</a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Empresa</h4>
                <ul>
                    <li><a href="{{ route('about') }}">Nosotros</a></li>
                    <li><a href="{{ route('catalog') }}">Productos</a></li>
                    <li><a href="{{ route('contact') }}">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Soporte</h4>
                <ul>
                    <li><a href="{{ route('contact') }}">Preguntas Frecuentes</a></li>
                    <li><a href="{{ route('contact') }}">Envíos</a></li>
                    <li><a href="{{ route('contact') }}">Devoluciones</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contacto</h4>
                <ul>
                    <li>📍 Av. Principal S/N, Huata, Ancash</li>
                    <li>📞 +51 43 123456</li>
                    <li>✉️ info@ecolacteoshuata.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div>© {{ date('Y') }} Ecolácteos Huata. Todos los derechos reservados.</div>
            <div style="display:flex;gap:24px">
                <a href="{{ route('contact') }}">Política de Privacidad</a>
                <a href="{{ route('contact') }}">Términos y Condiciones</a>
            </div>
        </div>
    </div>
</footer>
