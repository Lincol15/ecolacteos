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
                @if($site['facebook_url'] || $site['instagram_url'] || $site['whatsapp_planta'])
                <div class="footer-socials">
                    @if($site['facebook_url'])<a href="{{ $site['facebook_url'] }}" class="social-icon" target="_blank" rel="noopener" aria-label="Facebook">📘</a>@endif
                    @if($site['instagram_url'])<a href="{{ $site['instagram_url'] }}" class="social-icon" target="_blank" rel="noopener" aria-label="Instagram">📷</a>@endif
                    @if($site['whatsapp_planta'])<a href="https://wa.me/{{ preg_replace('/\D/', '', $site['whatsapp_planta']) }}" class="social-icon" target="_blank" rel="noopener" aria-label="WhatsApp">💬</a>@endif
                </div>
                @endif
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
                    @if($site['direccion_planta'])<li>📍 {{ $site['direccion_planta'] }}</li>@endif
                    @if($site['telefono_planta'])<li>📞 <a href="tel:{{ preg_replace('/[^\d+]/', '', $site['telefono_planta']) }}">{{ $site['telefono_planta'] }}</a></li>@endif
                    @if($site['email_planta'])<li>✉️ <a href="mailto:{{ $site['email_planta'] }}">{{ $site['email_planta'] }}</a></li>@endif
                    @if($site['horario_atencion'])<li>🕒 {{ $site['horario_atencion'] }}</li>@endif
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div>© {{ date('Y') }} {{ $site['nombre_planta'] ?: 'Ecolácteos Huata' }}. Todos los derechos reservados.</div>
            <div style="display:flex;gap:24px">
                <a href="{{ route('contact') }}">Política de Privacidad</a>
                <a href="{{ route('contact') }}">Términos y Condiciones</a>            </div>
        </div>
    </div>
</footer>
